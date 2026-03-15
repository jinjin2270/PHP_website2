<?php
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../config/db.php';

// Check if user is logged in
if (!$isLoggedIn) {
    header("Location: ../pages/login.html");
    exit();
}

// Initialize variables
$errors = [];
$success = false;
$userData = [];

// Get current user data
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
$userData = $result->fetch_assoc();
$stmt->close();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate and sanitize input
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    // Basic validation
    if (empty($name)) {
        $errors['name'] = 'Name is required';
    }
    
    if (empty($email)) {
        $errors['email'] = 'Email is required';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Invalid email format';
    }
    
    // Check if email is already taken by another user
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
    $stmt->bind_param("si", $email, $_SESSION['user_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->fetch_assoc()) {
        $errors['email'] = 'Email is already taken';
    }
    $stmt->close();
    
    // Password change validation
    if (!empty($new_password)) {
        if (empty($password)) {
            $errors['password'] = 'Current password is required to change password';
        } elseif (!password_verify($password, $userData['password'])) {
            $errors['password'] = 'Current password is incorrect';
        } elseif (strlen($new_password) < 8) {
            $errors['new_password'] = 'New password must be at least 8 characters';
        } elseif ($new_password !== $confirm_password) {
            $errors['confirm_password'] = 'Passwords do not match';
        }
    }
    
    // If no errors, update the profile
    if (empty($errors)) {
        try {
            $conn->autocommit(FALSE); // Start transaction
            
            // Prepare the update query
            $query = "UPDATE users SET name = ?, email = ?";
            $params = [$name, $email];
            $types = "ss";
            
            // Add password to query if changing
            if (!empty($new_password)) {
                $query .= ", password = ?";
                $params[] = password_hash($new_password, PASSWORD_DEFAULT);
                $types .= "s";
            }
            
            $query .= " WHERE id = ?";
            $params[] = $_SESSION['user_id'];
            $types .= "i";
            
            $stmt = $conn->prepare($query);
            $stmt->bind_param($types, ...$params);
            $stmt->execute();
            
            $conn->commit();
            
            // Update session data
            $_SESSION['name'] = $name;
            $_SESSION['email'] = $email;
            
            $success = true;
            // Refresh user data
            $stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
            $stmt->bind_param("i", $_SESSION['user_id']);
            $stmt->execute();
            $result = $stmt->get_result();
            $userData = $result->fetch_assoc();
            $stmt->close();
        } catch (Exception $e) {
            $conn->rollback();
            $errors['general'] = 'An error occurred while updating your profile: ' . $e->getMessage();
        } finally {
            $conn->autocommit(TRUE);
        }
    }
}
?>

<!-- The rest of your HTML remains the same -->

<link rel="stylesheet" href="../style.css">

<main class="edit-profile-container">
    <div class="profile-header">
        <h1>Edit Profile</h1>
        <p>Update your personal information</p>
    </div>
    
    <div class="profile-content">
        <?php if ($success): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i> Your profile has been updated successfully!
            </div>
        <?php endif; ?>
        
        <?php if (!empty($errors['general'])): ?>
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($errors['general']); ?>
            </div>
        <?php endif; ?>
        
        <form method="POST" class="profile-form">
            <div class="form-fields">
                <div class="form-group">
                    <label for="name">Full Name</label>
                    <input type="text" id="name" name="name" 
                           value="<?php echo htmlspecialchars($userData['name'] ?? ''); ?>" 
                           class="<?php echo !empty($errors['name']) ? 'is-invalid' : ''; ?>">
                    <?php if (!empty($errors['name'])): ?>
                        <div class="error-message"><?php echo htmlspecialchars($errors['name']); ?></div>
                    <?php endif; ?>
                </div>
                
                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input type="email" id="email" name="email" 
                           value="<?php echo htmlspecialchars($userData['email'] ?? ''); ?>" 
                           class="<?php echo !empty($errors['email']) ? 'is-invalid' : ''; ?>">
                    <?php if (!empty($errors['email'])): ?>
                        <div class="error-message"><?php echo htmlspecialchars($errors['email']); ?></div>
                    <?php endif; ?>
                </div>
            
            <div class="password-section">
                <h3>Change Password</h3>
                
                <div class="form-group">
                    <label for="password">Current Password</label>
                    <input type="password" id="password" name="password" 
                           class="<?php echo !empty($errors['password']) ? 'is-invalid' : ''; ?>">
                    <?php if (!empty($errors['password'])): ?>
                        <div class="error-message"><?php echo htmlspecialchars($errors['password']); ?></div>
                    <?php endif; ?>
                </div>
                
                <div class="form-group">
                    <label for="new_password">New Password</label>
                    <input type="password" id="new_password" name="new_password" 
                           class="<?php echo !empty($errors['new_password']) ? 'is-invalid' : ''; ?>">
                    <?php if (!empty($errors['new_password'])): ?>
                        <div class="error-message"><?php echo htmlspecialchars($errors['new_password']); ?></div>
                    <?php endif; ?>
                </div>
                
                <div class="form-group">
                    <label for="confirm_password">Confirm New Password</label>
                    <input type="password" id="confirm_password" name="confirm_password" 
                           class="<?php echo !empty($errors['confirm_password']) ? 'is-invalid' : ''; ?>">
                    <?php if (!empty($errors['confirm_password'])): ?>
                        <div class="error-message"><?php echo htmlspecialchars($errors['confirm_password']); ?></div>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Save Changes
                </button>
                <a href="../index.php" class="btn btn-secondary">Cancel</a>
                
                <?php if ($_SESSION['role'] === 'admin'): ?>
                    <a href="admin_dashboard.php" class="btn btn-admin">
                        <i class="fas fa-tachometer-alt"></i> Admin Dashboard
                    </a>
                <?php endif; ?>
            </div>
        </form>
    </div>
</main>


<?php
require_once __DIR__ . '/../includes/footer.php';
?>