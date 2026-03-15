<?php
session_start();
include("../config/db.php");

if (!isset($_SESSION['user_id'])) {
    die("Unauthorized");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userId = $_SESSION['user_id'];
    
    // Handle comment deletion
    if (isset($_POST['delete_comment'])) {
        $commentId = $_POST['comment_id'];
        
        // Verify the comment belongs to the user
        $checkStmt = $conn->prepare("SELECT user_id FROM post_comments WHERE id = ?");
        $checkStmt->bind_param("i", $commentId);
        $checkStmt->execute();
        $result = $checkStmt->get_result();
        
        if ($result->num_rows === 0) {
            die("Comment not found");
        }
        
        $comment = $result->fetch_assoc();
        if ($comment['user_id'] != $userId && $_SESSION['role'] !== 'admin') {
            die("Unauthorized to delete this comment");
        }
        
        // Delete the comment
        $deleteStmt = $conn->prepare("DELETE FROM post_comments WHERE id = ?");
        $deleteStmt->bind_param("i", $commentId);
        $deleteStmt->execute();
        
        header("Location: " . $_SERVER['HTTP_REFERER']);
        exit();
    }

    // Handle new comment submission
    $postId = $_POST['post_id'];
    $comment = trim($_POST['comment']);

    if (!empty($comment)) {
        $stmt = $conn->prepare("INSERT INTO post_comments (post_id, user_id, comment) VALUES (?, ?, ?)");
        $stmt->bind_param("iis", $postId, $userId, $comment);
        $stmt->execute();
    }

    header("Location: " . $_SERVER['HTTP_REFERER']);
    exit();
}
?>
