<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once("../config/db.php");
require_once("../php/functions.php"); // Include the functions file

$isLoggedIn = isset($_SESSION['user_id']);
$name = $isLoggedIn ? $_SESSION['name'] : '';
$categories = getAllCategories($conn); // Get all categories from database
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Future Blog</title>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700&family=Playfair+Display:wght@700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="../style.css">
</head>
<body>
   
    <nav>
        <div class="container navbar">
            <a href="../index.php" class="logo">
                <i class="fas fa-feather-alt logo-icon"></i> FUTURE
            </a>
            <div class="nav-links">
                <a href="../index.php">Home</a>
                <div class="dropdown">
                    <a href="../php/category.php" class="dropbtn">Categories <i class="fas fa-caret-down"></i></a>
                    <div class="dropdown-content">
                        <?php foreach ($categories as $category): ?>
                            <a href="../php/category.php?cat=<?= urlencode($category['name']) ?>&id=<?= $category['id'] ?>">
                                <?= htmlspecialchars($category['name']) ?>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
                <a href="../php/popular.php">Popular</a>
                <a href="../php/premium.php">Premium</a>
                <a href="../php/about.php">About</a>
            </div>

            <?php if ($isLoggedIn): ?>
                <div class="user-profile">
                    <div class="profile-dropdown">
                        <span><i class="fas fa-user-circle"></i> <?php echo htmlspecialchars($name); ?></span>
                        <div class="dropdown-menu">
                            <a href="../php/edit_profile.php">Edit Profile</a>
                            <a href="../php/submit_post.php">Submit Post</a>
                            <?php if ($_SESSION['role'] === 'admin'): ?>
                                <a href="../php/admin_review.php">Review Posts</a>
                            <?php endif; ?>
                            <a href="../php/logout.php">Logout</a>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <div class="auth-buttons">
                    <a href="../pages/login.html" class="btn btn-login">Login</a>
                    <a href="../pages/register.html" class="btn btn-register">Register</a>
                </div>
            <?php endif; ?>
        </div>
    </nav>