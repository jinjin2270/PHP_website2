<?php
session_start();
require_once("../config/db.php");

// Check admin login
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../pages/login.html");
    exit;
}

// Handle comment actions
if (isset($_GET['action'], $_GET['id'])) {
    $id = intval($_GET['id']);
    $action = $_GET['action'];
    
    if ($action === 'delete') {
        $stmt = $conn->prepare("DELETE FROM post_comments WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        header("Location: admin_comments.php?success=Comment+deleted");
        exit;
    }
}

// Fetch all comments with post, user, and category info
$stmt = $conn->prepare("
    SELECT 
        c.id, 
        c.comment, 
        c.created_at, 
        p.title AS post_title, 
        p.id AS post_id,
        cat.name AS category_name,
        u.name AS author_name
    FROM post_comments c
    JOIN posts p ON c.post_id = p.id
    JOIN categories cat ON p.category_id = cat.id
    JOIN users u ON c.user_id = u.id
    ORDER BY c.created_at DESC
");
$stmt->execute();
$comments = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Comment Control - Admin Panel</title>
    <link rel="stylesheet" href="../style.css">
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700&family=Playfair+Display:wght@700&display=swap" rel="stylesheet">
    <style>
        .category-badge {
            display: inline-block;
            background: #e9ecef;
            padding: 2px 8px;
            border-radius: 12px;
            font-size: 0.8rem;
            margin-left: 5px;
        }
    </style>
</head>
<body class="admin-page">
    <div class="admin-container">
        <aside class="admin-sidebar">
            <div class="admin-brand">
                <h1>Admin Panel</h1>
            </div>
            <nav class="admin-nav">
                <a href="./admin_dashboard.php" class="nav-link">
                    <span class="nav-icon">🏠</span>
                    <span class="nav-text">Dashboard</span>
                </a>
                <a href="admin_review.php" class="nav-link">
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
                <a href="admin_comments.php" class="nav-link active">
                    <span class="nav-icon">💬</span>
                    <span class="nav-text">Comment Control</span>
                </a>
                <a href="../php/logout.php" class="nav-link logout">
                    <span class="nav-icon">🚪</span>
                    <span class="nav-text">Logout</span>
                </a>
            </nav>
        </aside>

        <main class="admin-main">
            <header class="admin-header">
                <h2>Comment Control</h2>
                <div class="admin-actions">
                    <span class="welcome">Welcome, <?= htmlspecialchars($_SESSION['name'] ?? 'Admin') ?></span>
                </div>
            </header>

            <div class="admin-content">
                <?php if (isset($_GET['success'])): ?>
                    <div class="alert alert-success">
                        <?= htmlspecialchars(urldecode($_GET['success'])) ?>
                    </div>
                <?php endif; ?>

                <div class="admin-table-container">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>Comment</th>
                                <th>Author</th>
                                <th>Post (Category)</th>
                                <th>Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($comment = $comments->fetch_assoc()): ?>
                                <tr>
                                    <td><?= nl2br(htmlspecialchars(substr($comment['comment'], 0, 100))) . (strlen($comment['comment']) > 100 ? '...' : '') ?></td>
                                    <td><?= htmlspecialchars($comment['author_name']) ?></td>
                                    <td>
                                        <a href="./view_post.php?id=<?= $comment['post_id'] ?>" target="_blank">
                                            <?= htmlspecialchars($comment['post_title']) ?>
                                        </a>
                                        <span class="category-badge"><?= htmlspecialchars($comment['category_name']) ?></span>
                                    </td>
                                    <td><?= date("M j, Y", strtotime($comment['created_at'])) ?></td>
                                    <td class="action-buttons">
                                        <a href="admin_comments.php?action=delete&id=<?= $comment['id'] ?>" 
                                           class="btn btn-delete"
                                           onclick="return confirm('Delete this comment? This cannot be undone.');">
                                            Delete
                                        </a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>
</body>
</html>