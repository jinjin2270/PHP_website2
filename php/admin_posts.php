<?php
session_start();
require_once("../config/db.php");

// Check admin login
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../pages/login.html");
    exit;
}

// Handle post actions
if (isset($_GET['action'], $_GET['id'])) {
    $id = intval($_GET['id']);
    $action = $_GET['action'];
    
    if ($action === 'delete') {
        $stmt = $conn->prepare("DELETE FROM posts WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        header("Location: admin_posts.php?success=Post+deleted");
        exit;
    } elseif ($action === 'toggle_status') {
        $stmt = $conn->prepare("UPDATE posts SET status = IF(status='approved', 'pending', 'approved') WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        header("Location: admin_posts.php?success=Post+status+updated");
        exit;
    }
}

// Fetch all posts with category information
$stmt = $conn->prepare("
    SELECT 
        p.*, 
        u.name AS author_name,
        c.name AS category_name
    FROM posts p
    JOIN users u ON p.author = u.id
    JOIN categories c ON p.category_id = c.id
    ORDER BY p.created_at DESC
");
$stmt->execute();
$posts = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Post Management - Admin Panel</title>
    <link rel="stylesheet" href="../style.css">
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700&family=Playfair+Display:wght@700&display=swap" rel="stylesheet">
    <style>
        .category-badge {
            display: inline-block;
            background: #e9ecef;
            padding: 3px 8px;
            border-radius: 12px;
            font-size: 0.8rem;
        }
        .status-badge {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 12px;
            font-size: 0.8rem;
            font-weight: 500;
        }
        .status-badge.approved {
            background: #d1fae5;
            color: #065f46;
        }
        .status-badge.pending {
            background: #fef3c7;
            color: #92400e;
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
                <a href="admin_posts.php" class="nav-link active">
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
        </aside>

        <main class="admin-main">
            <header class="admin-header">
                <h2>Post Management</h2>
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
                                <th>Title</th>
                                <th>Author</th>
                                <th>Category</th>
                                <th>Status</th>
                                <th>Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($post = $posts->fetch_assoc()): ?>
                                <tr>
                                    <td>
                                        <a href="./view_post.php?id=<?= $post['id'] ?>" target="_blank">
                                            <?= htmlspecialchars($post['title']) ?>
                                        </a>
                                        <?php if ($post['is_premium']): ?>
                                            <span class="category-badge" style="background:#f3e8ff; color:#6b21a8;">Premium</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= htmlspecialchars($post['author_name']) ?></td>
                                    <td>
                                        <span class="category-badge"><?= htmlspecialchars($post['category_name']) ?></span>
                                    </td>
                                    <td>
                                        <span class="status-badge <?= $post['status'] ?>">
                                            <?= ucfirst($post['status']) ?>
                                        </span>
                                    </td>
                                    <td><?= date("M j, Y", strtotime($post['created_at'])) ?></td>
                                    <td class="action-buttons">
                                        <a href="admin_posts.php?action=toggle_status&id=<?= $post['id'] ?>" 
                                           class="btn btn-<?= $post['status'] === 'approved' ? 'warning' : 'success' ?>">
                                            <?= $post['status'] === 'approved' ? 'Unpublish' : 'Publish' ?>
                                        </a>
                                        <a href="admin_posts.php?action=delete&id=<?= $post['id'] ?>" 
                                           class="btn btn-delete"
                                           onclick="return confirm('Delete this post? This cannot be undone.');">
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