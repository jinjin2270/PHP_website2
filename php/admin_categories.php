<?php
session_start();
require_once("../config/db.php");
require_once("./functions.php");

// Check admin login
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../pages/login.html");
    exit;
}

// Handle category actions
if (isset($_GET['action'])) {
    $action = $_GET['action'];
    
    if ($action === 'add' && isset($_POST['name'])) {
        // Add new category
        $name = trim($_POST['name']);
        
        try {
            $stmt = $conn->prepare("INSERT INTO categories (name) VALUES (?)");
            $stmt->bind_param("s", $name);
            $stmt->execute();
            clearCategoriesCache();
            header("Location: admin_categories.php?success=Category+added");
            exit;
        } catch (mysqli_sql_exception $e) {
            if ($e->getCode() === 1062) { // Duplicate entry
                header("Location: admin_categories.php?error=Category+already+exists");
            } else {
                header("Location: admin_categories.php?error=Error+adding+category");
            }
            exit;
        }
        
    } elseif ($action === 'delete' && isset($_GET['id'])) {
        // Delete category
        $id = intval($_GET['id']);
        
        // Check if any posts use this category
        $stmt = $conn->prepare("SELECT COUNT(*) as post_count FROM posts WHERE category_id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        
        if ($result['post_count'] > 0) {
            header("Location: admin_categories.php?error=Cannot+delete+category+while+posts+are+using+it");
            exit;
        }
        
        // Delete the category
        $stmt = $conn->prepare("DELETE FROM categories WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        clearCategoriesCache();
        header("Location: admin_categories.php?success=Category+deleted");
        exit;
        
    } elseif ($action === 'edit' && isset($_POST['id']) && isset($_POST['name'])) {
        // Edit category
        $id = intval($_POST['id']);
        $name = trim($_POST['name']);
        
        try {
            $stmt = $conn->prepare("UPDATE categories SET name = ? WHERE id = ?");
            $stmt->bind_param("si", $name, $id);
            $stmt->execute();
            clearCategoriesCache();
            header("Location: admin_categories.php?success=Category+updated");
            exit;
        } catch (mysqli_sql_exception $e) {
            if ($e->getCode() === 1062) { // Duplicate entry
                header("Location: admin_categories.php?error=Category+name+already+exists");
            } else {
                header("Location: admin_categories.php?error=Error+updating+category");
            }
            exit;
        }
    }
}

// Fetch all categories with post counts using JOIN
$categories = $conn->query("
    SELECT 
        c.id,
        c.name as category_name,
        c.created_at,
        COUNT(p.id) as post_count 
    FROM categories c
    LEFT JOIN posts p ON c.id = p.category_id
    GROUP BY c.id, c.name, c.created_at
    ORDER BY c.name ASC
");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Category Management - Admin Panel</title>
    <link rel="stylesheet" href="../style.css">
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700&family=Playfair+Display:wght@700&display=swap" rel="stylesheet">
    <style>
        .category-tag {
            display: inline-block;
            background: #e9ecef;
            padding: 5px 10px;
            border-radius: 20px;
            margin: 5px;
            font-size: 0.9rem;
        }
        .category-tag .post-count {
            background: #dee2e6;
            border-radius: 10px;
            padding: 0 6px;
            margin-left: 5px;
            font-size: 0.8rem;
        }
        .modal {
            display: none;
            position: fixed;
            z-index: 100;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.4);
        }
        .modal-content {
            background-color: #fff;
            margin: 15% auto;
            padding: 20px;
            border-radius: 5px;
            width: 80%;
            max-width: 500px;
        }
        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
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
                <a href="admin_categories.php" class="nav-link active">
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
        </aside>

        <main class="admin-main">
            <header class="admin-header">
                <h2>Category Management</h2>
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
                <?php if (isset($_GET['error'])): ?>
                    <div class="alert alert-danger">
                        <?= htmlspecialchars(urldecode($_GET['error'])) ?>
                    </div>
                <?php endif; ?>

                <div class="admin-card">
                    <h3>Add New Category</h3>
                    <form action="admin_categories.php?action=add" method="POST">
                        <div class="form-group">
                            <input type="text" name="name" placeholder="Category Name" required>
                            <button type="submit" class="btn btn-primary">Add Category</button>
                        </div>
                    </form>
                </div>

                <div class="admin-table-container">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Posts</th>
                                <th>Created</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($category = $categories->fetch_assoc()): ?>
                                <tr>
                                    <td><?= $category['id'] ?></td>
                                    <td><?= htmlspecialchars($category['category_name']) ?></td>
                                    <td><?= $category['post_count'] ?></td>
                                    <td><?= date("M j, Y", strtotime($category['created_at'])) ?></td>
                                    <td class="action-buttons">
                                        <button class="btn btn-edit edit-category" 
                                                data-id="<?= $category['id'] ?>" 
                                                data-name="<?= htmlspecialchars($category['category_name']) ?>">
                                            Edit
                                        </button>
                                        <a href="admin_categories.php?action=delete&id=<?= $category['id'] ?>" 
                                           class="btn btn-delete"
                                           onclick="return confirm('Delete this category? This will fail if any posts are using it.');">
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

    <!-- Edit Category Modal -->
    <div id="editModal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h3>Edit Category</h3>
            <form id="editForm" action="admin_categories.php?action=edit" method="POST">
                <input type="hidden" name="id" id="editId">
                <div class="form-group">
                    <input type="text" name="name" id="editName" required>
                    <button type="submit" class="btn btn-primary">Update</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Edit Category Modal
        const modal = document.getElementById('editModal');
        const editButtons = document.querySelectorAll('.edit-category');
        const closeBtn = document.querySelector('.close');
        const editId = document.getElementById('editId');
        const editName = document.getElementById('editName');
        
        editButtons.forEach(btn => {
            btn.addEventListener('click', () => {
                editId.value = btn.dataset.id;
                editName.value = btn.dataset.name;
                modal.style.display = 'block';
            });
        });
        
        closeBtn.addEventListener('click', () => {
            modal.style.display = 'none';
        });
        
        window.addEventListener('click', (e) => {
            if (e.target === modal) {
                modal.style.display = 'none';
            }
        });
    </script>
</body>
</html>