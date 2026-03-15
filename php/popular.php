<?php
include("../config/db.php");
session_start();

$isLoggedIn = isset($_SESSION['user_id']);
$isAdmin = ($_SESSION['role'] ?? '') === 'admin';
$category = null;
if (isset($_GET['cat'])) {
    if (is_array($_GET['cat'])) {
        $category = trim((string)$_GET['cat'][0]);
    } else {
        $category = trim((string)$_GET['cat']);
    }
    
    
    if ($category === '') {
        $category = null;
    }
}

try {
    if ($category) {
        // Query with category filter
        $stmt = $conn->prepare("
            SELECT p.*, u.name as author_name, c.name as category_name
            FROM posts p
            JOIN users u ON p.author = u.id
            JOIN categories c ON p.category_id = c.id
            WHERE c.name = ? AND (p.status = 'approved' OR ?)
            ORDER BY p.likes DESC, p.created_at DESC
            LIMIT 12
        ");
        $isAdminBool = $isAdmin ? 1 : 0;
        $stmt->bind_param("si", $category, $isAdminBool);
    } else {
        // Query without category filter
        $stmt = $conn->prepare("
            SELECT p.*, u.name as author_name, c.name as category_name
            FROM posts p
            JOIN users u ON p.author = u.id
            JOIN categories c ON p.category_id = c.id
            WHERE p.status = 'approved' OR ?
            ORDER BY p.likes DESC, p.created_at DESC
            LIMIT 12
        ");
        $isAdminBool = $isAdmin ? 1 : 0;
        $stmt->bind_param("i", $isAdminBool);
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
} catch (Exception $e) {
    die("Error fetching posts: " . $e->getMessage());
}

$pageTitle = "Popular Posts - Future Blog";
include("../includes/header.php");
?>

<div class="container">
    <h1>Most Popular Posts</h1>
    <p class="hero-subtitle">Discover what our community is loving right now</p>

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
                <article class="post-card">
                    <?php if ($row['image_url']): ?>
                        <img src="../<?php echo htmlspecialchars($row['image_url']); ?>" alt="Post image">
                        <?php if ($row['is_premium']): ?>
                            <div class="premium-badge">Premium</div>
                        <?php endif; ?>
                    <?php endif; ?>

                    <h2>
                        <a href="view_post.php?id=<?= $row['id']; ?>">
                            <?= htmlspecialchars($row['title']); ?>
                        </a>
                    </h2>
                    <p class="post-meta">
                        By <?= htmlspecialchars($row['author_name']); ?> | 
                        <?= date("M d, Y", strtotime($row['created_at'])); ?> |
                        <?= htmlspecialchars($row['category_name']); ?>
                    </p>

                    <p class="post-excerpt"><?= htmlspecialchars(substr($row['content'], 0, 200)); ?>...</p>

                    <div class="actions">
                        <?php if ($isLoggedIn): ?>
    <form action="../php/like_post.php" method="post" class="like-form" style="display:inline;">
        <input type="hidden" name="post_id" value="<?= $row['id']; ?>">
        <button type="submit" class="like-btn <?= $isLiked ? 'liked' : '' ?>" data-post-id="<?= $row['id'] ?>">
    ❤️ Like (<?= $likeCount ?>)
</button>
    </form>
<?php else: ?>
    <a href="../pages/login.html" class="like-btn disabled" onclick="return confirm('Please login to like posts')">
    ❤️ Like (<?= $likeCount ?>)
</a>
<?php endif; ?>
                        <a href="view_post.php?id=<?= $row['id']; ?>#comments" class="comment-btn">💬 Comment</a>
                    </div>
                </article>
            <?php endwhile; ?>
        </div>
    <?php else: ?>
        <div class="no-posts">
            <h3>No popular posts found<?= $category ? " in this category" : ""; ?></h3>
            <a href="category.php" class="btn">Browse All Posts</a>
        </div>
    <?php endif; ?>
</div>


<script>
document.addEventListener('DOMContentLoaded', function() {
    // Function to sort posts by like count
    function sortPosts() {
        const grid = document.querySelector('.posts-grid');
        if (!grid) return;
        
        const posts = Array.from(grid.querySelectorAll('.post-card'));
        
        posts.sort((a, b) => {
            const aLikes = parseInt(a.querySelector('.like-btn').textContent.match(/\((\d+)\)/)[1]);
            const bLikes = parseInt(b.querySelector('.like-btn').textContent.match(/\((\d+)\)/)[1]);
            return bLikes - aLikes;
        });
        
        // Re-append sorted posts
        posts.forEach(post => grid.appendChild(post));
    }

    // Use event delegation for all like buttons
    document.addEventListener('submit', async function(e) {
        if (e.target.classList.contains('like-form')) {
            e.preventDefault();
            
            const form = e.target;
            const postId = form.querySelector('input[name="post_id"]').value;
            const buttons = document.querySelectorAll(`.like-btn[data-post-id="${postId}"]`);
            
            // Add loading state
            buttons.forEach(btn => {
                btn.disabled = true;
                btn.innerHTML = '❤️ ...';
            });
            
            try {
                const formData = new FormData(form);
                const response = await fetch(form.action, {
                    method: 'POST',
                    body: formData
                });
                
                if (!response.ok) throw new Error('Network response was not ok');
                
                const data = await response.json();
                
                if (data.success) {
                    // Update all buttons for this post
                    buttons.forEach(btn => {
                        btn.classList.toggle('liked', data.action === 'liked');
                        btn.innerHTML = `❤️ Like (${data.likeCount})`;
                    });
                    
                    // Re-sort the posts after like count changes
                    sortPosts();
                } else {
                    throw new Error(data.message || 'Error processing like');
                }
            } catch (error) {
                console.error('Like error:', error);
                alert(error.message);
            } finally {
                buttons.forEach(btn => btn.disabled = false);
            }
        }
    });
});
</script>

<?php
include("../includes/footer.php");
$stmt->close();
$conn->close();
?>