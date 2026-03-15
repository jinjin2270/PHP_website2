<?php
include("../config/db.php");
session_start();

$isLoggedIn = isset($_SESSION['user_id']);
$isAdmin = ($_SESSION['role'] ?? '') === 'admin';
$isPremium = false;
if (isset($_SESSION['user_id'])) {
    $checkPremium = $conn->prepare("
        SELECT 1 FROM user_subscriptions 
        WHERE user_id = ? AND end_date > NOW()
    ");
    $checkPremium->bind_param("i", $_SESSION['user_id']);
    $checkPremium->execute();
    $isPremium = $checkPremium->get_result()->num_rows > 0;
    $_SESSION['is_premium'] = $isPremium ? 1 : 0;
}

try {
    $stmt = $conn->prepare("
        SELECT p.*, u.name as author_name 
        FROM posts p
        JOIN users u ON p.author = u.id
        JOIN categories c ON p.category_id = c.id
        WHERE p.is_premium = 1 AND (p.status = 'approved' OR ?)
        ORDER BY p.created_at DESC
    ");
    $isAdminBool = $isAdmin ? 1 : 0;
    $stmt->bind_param("i", $isAdminBool);
    $stmt->execute();
    $result = $stmt->get_result();
} catch (Exception $e) {
    die("Error fetching posts: " . $e->getMessage());
}

$pageTitle = "Premium Posts - Future Blog";
include("../includes/header.php");
?>

<div class="container">
    <div class="premium-header">
        <h1>Premium Content</h1>
        <p>Exclusive high-quality posts for our premium members</p>
        <?php if (!$isLoggedIn): ?>
            <a href="../pages/register.html" class="premium-cta">Become a Premium Member</a>
        <?php endif; ?>
    </div>

    <?php if ($result->num_rows > 0): ?>
        <div class="posts-grid">
            <?php while ($row = $result->fetch_assoc()): 
                // Check if current user has liked this post
                $isLiked = false;
                if ($isLoggedIn) {
                    $check_like = $conn->prepare("SELECT * FROM post_likes WHERE user_id = ? AND post_id = ?");
                    $check_like->bind_param("ii", $_SESSION['user_id'], $row['id']);
                    $check_like->execute();
                    $isLiked = $check_like->get_result()->num_rows > 0;
                }
                $likeCount = $row['likes'] ?? 0;
            ?>
<article class="post-card premium-post">
    <?php if ($row['image_url']): ?>
        <img src="../<?php echo htmlspecialchars($row['image_url']); ?>" alt="Post image">
        <div class="premium-badge">Premium</div>
    <?php endif; ?>

    <div class="post-content">
        <h2>
            <a href="view_post.php?id=<?= $row['id']; ?>">
                <?= htmlspecialchars($row['title']); ?>
            </a>
        </h2>
        <p class="post-meta">By <?= htmlspecialchars($row['author_name']); ?> | <?= date("M d, Y", strtotime($row['created_at'])); ?></p>

        <?php if ($isPremium || $isAdmin): ?>
            <p class="post-excerpt"><?= htmlspecialchars(substr($row['content'], 0, 200)); ?>...</p>
        <?php else: ?>
            <div class="premium-warning">
                <p>This is premium content. <a href="subscribe.php">Subscribe</a> to view.</p>
                <p><?= htmlspecialchars(substr($row['content'], 0, 50)); ?>...</p>
            </div>
        <?php endif; ?>

        
    </div>
</article>
            <?php endwhile; ?>
        </div>
    <?php else: ?>
        <div class="no-posts">
            <h3>No premium posts available yet</h3>
            <p>Check back later or consider submitting your own premium content!</p>
            <?php if ($isLoggedIn && !$isAdmin): ?>
                <a href="submit_post.php" class="btn">Submit Premium Post</a>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>


<?php
include("../includes/footer.php");
$stmt->close();
$conn->close();
?>

<style>
.premium-header {
    text-align: center;
    margin-bottom: 40px;
    padding: 20px;
    background: linear-gradient(135deg, #f6d365 0%, #fda085 100%);
    border-radius: 10px;
    color: white;
}

.premium-header h1 {
    font-size: 2.5rem;
    margin-bottom: 10px;
}

.premium-header p {
    font-size: 1.1rem;
    margin-bottom: 20px;
}

.premium-cta {
    display: inline-block;
    padding: 12px 25px;
    background: white;
    color: #fda085;
    border-radius: 50px;
    font-weight: bold;
    text-decoration: none;
    transition: all 0.3s;
}

.premium-cta:hover {
    transform: translateY(-3px);
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
}

.premium-post {
    border: 2px solid #f6d365;
    position: relative;
}

.premium-post .premium-badge {
    position: absolute;
    top: 10px;
    right: 10px;
    background: gold;
    color: #333;
    padding: 5px 10px;
    border-radius: 20px;
    font-weight: bold;
    font-size: 0.8rem;
}

.posts-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 30px;
    margin-top: 30px;
}

.post-card {
    background: white;
    border-radius: 8px;
    overflow: hidden;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    transition: transform 0.3s ease;
}

.post-card:hover {
    transform: translateY(-5px);
}

.post-card img {
    width: 100%;
    height: 200px;
    object-fit: cover;
}

.post-content {
    padding: 15px;
}

.post-card h2 {
    font-size: 1.2rem;
    margin-bottom: 5px;
}

.post-meta {
    color: #666;
    font-size: 0.9rem;
    margin-bottom: 10px;
}


.post-excerpt {
    color: #444;
    margin-bottom: 15px;
}

.actions {
    display: flex;
    gap: 10px;
}

.like-btn, .comment-btn {
    padding: 8px 12px;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    font-size: 0.9rem;
}


.comment-btn {
    background: #e0e0ff;
    color: #333;
    text-decoration: none;
}

.no-posts {
    text-align: center;
    padding: 40px;
    background: #f9f9f9;
    border-radius: 8px;
}

.no-posts h3 {
    margin-bottom: 10px;
}


</style>