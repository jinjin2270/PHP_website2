<?php
session_start();
require '../config/db.php';

$is_logged_in = isset($_SESSION['user_id']);
$is_premium = isset($_SESSION['is_premium']) && $_SESSION['is_premium'] == 1;
$is_admin = ($_SESSION['role'] ?? '') === 'admin';

$post_id = $_GET['id'] ?? 0;
if (!$post_id) die("Post ID missing");

// Get post with category information
$stmt = $conn->prepare("
    SELECT p.*, u.name as author_name, c.name as category_name 
    FROM posts p
    JOIN users u ON p.author = u.id
    JOIN categories c ON p.category_id = c.id
    WHERE p.id = ?
");
$stmt->bind_param("i", $post_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("Post not found");
}

$post = $result->fetch_assoc();

// If not approved, allow only admin
if ($post['status'] !== 'approved' && !$is_admin) {
    die("This post is not available.");
}

// Get comments for this post
$commentStmt = $conn->prepare("
    SELECT pc.*, u.name as author_name 
    FROM post_comments pc
    JOIN users u ON pc.user_id = u.id
    WHERE pc.post_id = ?
    ORDER BY pc.created_at DESC
");
$commentStmt->bind_param("i", $post_id);
$commentStmt->execute();
$commentResult = $commentStmt->get_result();
?>

<!DOCTYPE html>
<html>
<head>
    <title><?= htmlspecialchars($post['title']); ?></title>
    <link rel="stylesheet" href="../style.css">
</head>
<body>

<div class="container">
    <h1><?= htmlspecialchars($post['title']); ?></h1>
    <p>By <?= htmlspecialchars($post['author_name']); ?> | <?= date("M d, Y", strtotime($post['created_at'])); ?> | Category: <?= htmlspecialchars($post['category_name']); ?></p>

    <?php if ($post['image_url']): ?>
        <img src="../<?= htmlspecialchars($post['image_url']); ?>" alt="Post image">
    <?php endif; ?>

    <div class="post-body">
    <?php if ($post['is_premium']): ?>
        <?php if ($is_logged_in && ($is_premium || $is_admin)): ?>
            <div class="premium-content">
                <?= nl2br(htmlspecialchars($post['content'])); ?>
            </div>
        <?php else: ?>
            <div class="premium-warning">
                <p>This is a <strong>premium</strong> post. <a href="../php/subscribe.php">Subscribe</a> to view full content.</p>
                <p><?= nl2br(htmlspecialchars(substr($post['content'], 0, 200))) . '...'; ?></p>
            </div>
        <?php endif; ?>
    <?php else: ?>
        <?= nl2br(htmlspecialchars($post['content'])); ?>
    <?php endif; ?>
</div>
    <div class="actions">
    <?php 
    // Check if current user has liked this post
    $isLiked = false;
    if ($is_logged_in) {
        $check_like = $conn->prepare("SELECT * FROM post_likes WHERE user_id = ? AND post_id = ?");
        $check_like->bind_param("ii", $_SESSION['user_id'], $post['id']);
        $check_like->execute();
        $isLiked = $check_like->get_result()->num_rows > 0;
    }
    ?>
    
    <?php if ($is_logged_in): ?>
        <form action="../php/like_post.php" method="post" class="like-form" style="display:inline;">
            <input type="hidden" name="post_id" value="<?= $post['id']; ?>">
            <button type="submit" class="like-btn <?= $isLiked ? 'liked' : '' ?>">
                ❤️ Like (<?= $post['likes'] ?? 0 ?>)
            </button>
        </form>
        <a href="#comments" class="comment-btn">💬 Comment</a>
        
    <?php else: ?>
        <a href="../pages/login.html" class="like-btn disabled" onclick="return confirm('Login required');">❤️ Like</a>
        <a href="../pages/login.html" class="comment-btn disabled" onclick="return confirm('Login required');">💬 Comment</a>
    <?php endif; ?>
    </div>
    <hr>
    <div id="comments">
        <h3>Leave a Comment</h3>
        <?php if ($is_logged_in): ?>
            <form action="../php/comment_post.php" method="POST" class="comment-form">
                <input type="hidden" name="post_id" value="<?= $post['id'] ?>">
                <textarea name="comment" required placeholder="Write your comment..." rows="3" style="width: 100%;"></textarea>
                <br>
                <button type="submit" style="margin-top: 5px;">Post Comment</button>
            </form>
        <?php else: ?>
            <p><a href="../pages/login.html">Login</a> to leave a comment.</p>
        <?php endif; ?>
    </div>
    <h3 style="margin-top: 30px;">Comments</h3>
    <?php if ($commentResult->num_rows > 0): ?>
        <?php while ($comment = $commentResult->fetch_assoc()): ?>
            <div class="comment" style="border-top: 1px solid #ddd; padding: 10px 0;">
                <strong><?= htmlspecialchars($comment['author_name']) ?></strong>
                <small style="color: gray;"> | <?= date("M d, Y H:i", strtotime($comment['created_at'])) ?></small>


                <?php if ($is_logged_in && ($comment['user_id'] == $_SESSION['user_id'] || $is_admin)): ?>
                    <form action="../php/comment_post.php" method="POST" style="display: inline; position: absolute; right: 0; top: 10px;">
                        <input type="hidden" name="comment_id" value="<?= $comment['id'] ?>">
                        <input type="hidden" name="delete_comment" value="1">
                        <button type="submit" style="background: none; border: none; color: red; cursor: pointer;" 
                                onclick="return confirm('Are you sure you want to delete this comment?')">❌</button>
                    </form>
                <?php endif; ?>
                
                <p><?= nl2br(htmlspecialchars($comment['comment'])) ?></p>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <p>No comments yet.</p>
    <?php endif; ?>

    <a href="category.php?cat=<?= urlencode($post['category_name']); ?>" class="back-link">← Back to <?= htmlspecialchars($post['category_name']); ?></a>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.like-form').forEach(form => {
        form.addEventListener('submit', async function(e) {
            e.preventDefault();
            
            if (!<?= $is_logged_in ? 'true' : 'false' ?>) {
                alert('Please login to like posts');
                return;
            }
            
            <?php if ($post['is_premium'] && !$is_premium && !$is_admin): ?>
            alert('Please subscribe to like premium posts');
            return;
            <?php endif; ?>
            
            const form = this;
            const button = form.querySelector('.like-btn');
            const postId = form.querySelector('input[name="post_id"]').value;
            
            button.disabled = true;
            
            try {
                const response = await fetch(form.action, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `post_id=${postId}`
                });
                
                const data = await response.json();
                
                if (data.success) {
                    // Toggle like state visually
                    button.classList.toggle('liked');
                    // Update like count with the value from server
                    button.innerHTML = `❤️ Like (${data.likeCount})`;
                } else {
                    alert(data.message);
                }
            } catch (error) {
                console.error('Error:', error);
                alert('An error occurred while processing your like');
            } finally {
                button.disabled = false;
            }
        });
    });
});
</script>
</body>
</html>