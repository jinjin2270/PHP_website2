<?php
session_start();
require_once("../config/db.php");

if (!isset($_SESSION['user_id'])) {
    header("Location: ../pages/login.html");
    exit;
}

$message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title']);
    $content = trim($_POST['content']);
    $category_id = intval($_POST['category_id']);
    
    $image_url = null; // initialize as null
    
    if (isset($_FILES['image_file']) && $_FILES['image_file']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = "../uploads/";
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        $tmpName = $_FILES['image_file']['tmp_name'];
        $originalName = basename($_FILES['image_file']['name']);
        $uniqueName = uniqid() . "-" . preg_replace("/[^a-zA-Z0-9\._-]/", "", $originalName);
        $targetFilePath = $uploadDir . $uniqueName;
        if (move_uploaded_file($tmpName, $targetFilePath)) {
            $image_url = "uploads/" . $uniqueName;
        } else {
            $message = "Error uploading the image.";
        }
    }
    
    $author = $_SESSION['user_id'];
    $role = isset($_SESSION['role']) ? $_SESSION['role'] : 'user'; 
    $status = ($role === 'admin') ? 'approved' : 'pending';
    
    if ($title && $content && $category_id) {
        $stmt = $conn->prepare("INSERT INTO posts (title, content, category_id, author, image_url, is_premium, created_at, status) VALUES (?, ?, ?, ?, ?, ?, NOW(), ?)");
        $is_premium = isset($_POST['is_premium']) ? 1 : 0;
        $stmt->bind_param("ssiisis", $title, $content, $category_id, $author, $image_url, $is_premium, $status);
        
        if ($stmt->execute()) {
            $message = ($status === 'pending') 
                ? "Post submitted successfully! Pending admin approval." 
                : "Post submitted and published successfully!";
        } else {
            $message = "Error submitting post: " . $stmt->error;
        }
        $stmt->close();
    } else {
        $message = "Please fill in all required fields.";
    }
}

// Get categories from database
$categories = $conn->query("SELECT id, name FROM categories ORDER BY name ASC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Submit New Post</title>
    <link rel="stylesheet" href="../style.css">
    
</head>
<body>
    <div class="submit-form-container">
        <h1>Submit New Post</h1>

        <?php if ($message): ?>
            <div class="message" style="padding: 1rem; margin-bottom: 1rem; background: rgba(72, 187, 120, 0.1); color: #48BB78; border-radius: 4px;">
                <?= htmlspecialchars($message) ?>
            </div>
        <?php endif; ?>

        <form method="post" enctype="multipart/form-data" class="submit-form">
            <div class="form-group">
                <label for="title">Title:</label>
                <input type="text" id="title" name="title" class="form-control" required>
            </div>
            
            <div class="form-group">
                <label for="content">Content:</label>
                <textarea id="content" name="content" class="form-control" rows="8" required></textarea>
            </div>
            
            <div class="form-group">
                <label for="category_id">Category:</label>
                <select id="category_id" name="category_id" class="form-control" required>
                    <option value="">--Select--</option>
                    <?php while ($cat = $categories->fetch_assoc()): ?>
                        <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?></option>
                    <?php endwhile; ?>
                </select>
            </div>
            
            <div class="form-group">
                <label for="image_file">Upload Image (optional):</label>
                <input type="file" id="image_file" name="image_file" accept="image/*">
            </div>
            
            <div class="form-group">
                <label class="checkbox-label">
                    <input type="checkbox" name="is_premium"> Premium Post
                </label>
            </div>
            
            <button type="submit" class="btn-submit">Submit Post</button>
        </form>

        <p style="margin-top: 2rem;"><a href="../index.php">Back to Home</a></p>
    </div>
</body>
</html>