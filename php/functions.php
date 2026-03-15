<?php
// functions.php
function getAllCategories($conn) {
    // Try to get from cache first (valid for 1 hour)
    if (isset($_SESSION['cached_categories']) && 
        isset($_SESSION['categories_cache_time']) && 
        (time() - $_SESSION['categories_cache_time']) < 3600) {
        return $_SESSION['cached_categories'];
    }
    
    $categories = array();
    $result = $conn->query("SELECT id, name FROM categories ORDER BY name ASC");
    while ($row = $result->fetch_assoc()) {
        $categories[] = $row;
    }
    
    // Cache the results
    $_SESSION['cached_categories'] = $categories;
    $_SESSION['categories_cache_time'] = time();
    
    return $categories;
}

function clearCategoriesCache() {
    unset($_SESSION['cached_categories']);
    unset($_SESSION['categories_cache_time']);
}
?>