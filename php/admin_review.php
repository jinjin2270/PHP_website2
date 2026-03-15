<?php
session_start();
require_once("../config/db.php");

// Check admin login
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../pages/login.html");
    exit;
}

// Action handling
if (isset($_GET['action'], $_GET['post_id'])) {
    $post_id = intval($_GET['post_id']);
    $action = $_GET['action'];

    if ($action === 'approve') {
        $stmt = $conn->prepare("UPDATE posts SET status = 'approved' WHERE id = ?");
        $stmt->bind_param("i", $post_id);
        $stmt->execute();
        $stmt->close();
    } elseif ($action === 'reject') {
        $stmt = $conn->prepare("DELETE FROM posts WHERE id = ?");
        $stmt->bind_param("i", $post_id);
        $stmt->execute();
        $stmt->close();
    }

    header("Location: admin_review.php");
    exit;
}

// Fetch pending posts
$stmt = $conn->prepare("
    SELECT p.*, u.name AS author_name, c.name AS category_name
    FROM posts p
    JOIN users u ON p.author = u.id
    JOIN categories c ON p.category_id = c.id
    WHERE p.status = 'pending'
    ORDER BY p.created_at DESC
");
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Review - Pending Posts</title>
    <link rel="stylesheet" href="../style.css">
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700&family=Playfair+Display:wght@700&display=swap" rel="stylesheet">
</head>
<body class="admin-page">
    <div class="admin-container">
        <aside class="admin-sidebar">
            <div class="admin-brand">
                <h1>Admin Panel</h1>
            </div>
            <!-- Previous code remains the same until the nav section -->
<nav class="admin-nav">
    <a href="./admin_dashboard.php" class="nav-link">
        <span class="nav-icon">🏠</span>
        <span class="nav-text">Dashboard</span>
    </a>
    <a href="admin_review.php" class="nav-link active">
        <span class="nav-icon">📝</span>
        <span class="nav-text">Post Review</span>
    </a>
    <a href="admin_categories.php" class="nav-link">
        <span class="nav-icon">🗂️</span>
        <span class="nav-text">Category Management</span>
    </a>
    <a href="admin_posts.php" class="nav-link">
        <span class="nav-icon">📄</span>
        <span class="nav-text">Post Management</span>
    </a>
    <a href="admin_comments.php" class="nav-link">
        <span class="nav-icon">💬</span>
        <span class="nav-text">Comment Control</span>
    </a>
    <a href="../php/logout.php" class="nav-link logout">
        <span class="nav-icon">🚪</span>
        <span class="nav-text">Logout</span>
    </a>
</nav>
<!-- Rest of the code remains the same -->
        </aside>

        <main class="admin-main">
            <header class="admin-header">
                <h2>Pending Posts Review</h2>
                <div class="admin-actions">
                    <span class="welcome">Welcome, <?= htmlspecialchars($_SESSION['name'] ?? 'Admin') ?></span>
                </div>
            </header>

            <div class="admin-content">
                <?php if ($result->num_rows === 0): ?>
                    <div class="empty-state">
                        <img src="../assets/images/empty-state.svg" alt="No pending posts" class="empty-icon">
                        <h3>No Pending Posts</h3>
                        <p>There are currently no posts waiting for review.</p>
                    </div>
                <?php else: ?>
                    <div class="admin-table-container">
                        <table class="admin-table">
                            <thead>
                                <tr>
                                    <th>Title</th>
                                    <th>Author</th>
                                    <th>Category</th>
                                    <th>Submitted</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($post = $result->fetch_assoc()): ?>
                                    <tr>
                                        <td>
                                            <a href="view_post.php?id=<?= $post['id'] ?>" class="post-title-link">
                                                <?= htmlspecialchars($post['title']) ?>
                                            </a>
                                        </td>
                                        <td><?= htmlspecialchars($post['author_name']) ?></td>
                                        <td>
                                            <span class="category-tag"><?= htmlspecialchars($post['category_name']) ?></span>
                                        </td>
                                        <td>
                                            <span class="date-badge">
                                                <?= date("M j, Y", strtotime($post['created_at'])) ?>
                                            </span>
                                        </td>
                                        <td class="action-buttons">
                                            <a href="admin_review.php?action=approve&post_id=<?= $post['id'] ?>" 
                                               class="btn btn-approve"
                                               onclick="return confirm('Approve this post?');">
                                                Approve
                                            </a>
                                            <a href="admin_review.php?action=reject&post_id=<?= $post['id'] ?>" 
                                               class="btn btn-reject"
                                               onclick="return confirm('Reject this post?');">
                                                Reject
                                            </a>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </main>
    </div>
</body>
</html>

<?php
$stmt->close();
$conn->close();
?>