<?php
include("../config/db.php");  
session_start();

// Initialize variables
$category_name = '';
$category_id = '';
$display_name = '';

// Handle category parameter
if (isset($_GET['cat'])) {
    if (is_array($_GET['cat'])) {
        // Find first non-empty string value
        foreach ($_GET['cat'] as $cat) {
            if (is_string($cat) && trim($cat) !== '') {
                if (is_numeric($cat)) {
                    $category_id = (int)$cat;
                } else {
                    $category_name = trim($cat);
                }
                break;
            }
        }
    } elseif (is_string($_GET['cat']) && trim($_GET['cat']) !== '') {
        if (is_numeric($_GET['cat'])) {
            $category_id = (int)$_GET['cat'];
        } else {
            $category_name = trim($_GET['cat']);
        }
    }
}

$isLoggedIn = isset($_SESSION['user_id']);
$isAdmin = ($_SESSION['role'] ?? '') === 'admin';

try {
    if ($category_name !== '' || $category_id !== '') {
        // Get the proper category name first
        $name_stmt = $conn->prepare("SELECT name FROM categories WHERE name = ? OR id = ? LIMIT 1");
        $name_stmt->bind_param("si", $category_name, $category_id);
        $name_stmt->execute();
        $name_result = $name_stmt->get_result();
        
        if ($name_result->num_rows > 0) {
            $display_name = $name_result->fetch_assoc()['name'];
        } else {
            $display_name = $category_name ?: "ID $category_id";
        }
        $name_stmt->close();

        // Get posts for this category
        $stmt = $conn->prepare(" 
            SELECT p.*, u.name as author_name, c.name as category_name 
            FROM posts p
            JOIN users u ON p.author = u.id
            JOIN categories c ON p.category_id = c.id
            WHERE (c.name = ? OR c.id = ?) AND (p.status = 'approved' OR ?)
            ORDER BY p.created_at DESC
        ");
        $isAdminBool = $isAdmin ? 1 : 0;
        $stmt->bind_param("sii", $category_name, $category_id, $isAdminBool);
    } else {
        $stmt = $conn->prepare("
            SELECT p.*, u.name as author_name, c.name as category_name 
            FROM posts p
            JOIN users u ON p.author = u.id
            JOIN categories c ON p.category_id = c.id
            WHERE p.status = 'approved' OR ?
            ORDER BY p.created_at DESC
        ");
        $isAdminBool = $isAdmin ? 1 : 0;
        $stmt->bind_param("i", $isAdminBool);
    }

    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result === false) {
        throw new Exception("Database query failed");
    }
} catch (Exception $e) {
    die("Error fetching posts: " . $e->getMessage());
}

include("../includes/header.php");
?>

<div class="container">
    <h1>
        <?php 
        if ($display_name !== '') {
            echo "Posts in '" . htmlspecialchars($display_name) . "'";
        } elseif ($category_name !== '' || $category_id !== '') {
            echo "Posts in '" . htmlspecialchars($category_name ?: "ID $category_id") . "' (category not found)";
        } else {
            echo "Latest Posts";
        }
        ?>
    </h1>

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
                // Ensure all values are strings before passing to htmlspecialchars
                $row['title'] = (string)($row['title'] ?? '');
                $row['author_name'] = (string)($row['author_name'] ?? '');
                $row['category_name'] = (string)($row['category_name'] ?? '');
                $row['content'] = (string)($row['content'] ?? '');
                $row['image_url'] = (string)($row['image_url'] ?? '');
            ?>
                <article class="post-card">
                    <?php if (!empty($row['image_url'])): ?>
                        <img src="../<?php echo htmlspecialchars($row['image_url']); ?>" alt="Post image">
                        <?php if (!empty($row['is_premium'])): ?>
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
                        Category: <?= htmlspecialchars($row['category_name']); ?>
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
        ❤️ Like (<?= $row['likes'] ?? 0 ?>)
    </a>
<?php endif; ?>
                        <a href="view_post.php?id=<?= $row['id']; ?>#comments" class="comment-btn">💬 Comment</a>
                    </div>
                </article>
            <?php endwhile; ?>
        </div>
    <?php else: ?>
        <div class="no-posts">
            <h3>No posts found<?= ($display_name !== '' || $category_name !== '' || $category_id !== '') ? " in this category" : ""; ?></h3>
            <a href="category.php" class="btn">View All Posts</a>
        </div>
    <?php endif; ?>
</div>


<script>
document.addEventListener('DOMContentLoaded', function() {
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