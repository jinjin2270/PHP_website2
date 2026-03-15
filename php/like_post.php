<?php
session_start();
ob_start(); // Start output buffering at the very beginning
include("../config/db.php");

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    ob_end_clean(); // Clean buffer before error response
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    ob_end_clean();
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit();
}

if (!isset($_POST['post_id'])) {
    ob_end_clean();
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Post ID missing']);
    exit();
}

$userId = $_SESSION['user_id'];
$postId = $_POST['post_id'];

try {
    // Check if the user already liked the post
    $check = $conn->prepare("SELECT * FROM post_likes WHERE user_id = ? AND post_id = ?");
    $check->bind_param("ii", $userId, $postId);
    $check->execute();
    $result = $check->get_result();

    if ($result->num_rows === 0) {
        // Insert like
        $stmt = $conn->prepare("INSERT INTO post_likes (user_id, post_id) VALUES (?, ?)");
        $stmt->bind_param("ii", $userId, $postId);
        $stmt->execute();

        $update = $conn->prepare("UPDATE posts SET likes = likes + 1 WHERE id = ?");
        $update->bind_param("i", $postId);
        $update->execute();
    } else {
        // Remove like
        $delete = $conn->prepare("DELETE FROM post_likes WHERE user_id = ? AND post_id = ?");
        $delete->bind_param("ii", $userId, $postId);
        $delete->execute();

        $update = $conn->prepare("UPDATE posts SET likes = likes - 1 WHERE id = ?");
        $update->bind_param("i", $postId);
        $update->execute();
    }

    // Get the updated like count
    $countQuery = $conn->prepare("SELECT COUNT(*) as cnt FROM post_likes WHERE post_id = ?");
    $countQuery->bind_param("i", $postId);
    $countQuery->execute();
    $countResult = $countQuery->get_result();
    $likeCount = $countResult->fetch_assoc()['cnt'];

    // Update the posts table to keep in sync
    $update = $conn->prepare("UPDATE posts SET likes = ? WHERE id = ?");
    $update->bind_param("ii", $likeCount, $postId);
    $update->execute();

    // Prepare the response
    $response = [
        'success' => true,
        'action' => $result->num_rows === 0 ? 'liked' : 'unliked',
        'likeCount' => $likeCount,
        'message' => $result->num_rows === 0 ? 'Post liked successfully' : 'Post unliked successfully'
    ];

    // Clean buffer and send JSON response
    ob_end_clean();
    echo json_encode($response);
    exit();

} catch (Exception $e) {
    ob_end_clean();
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    exit();
}

$conn->close();
?>