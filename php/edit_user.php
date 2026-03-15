<?php
require_once __DIR__ . '/../config/db.php';

// Admin check
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../index.php");
    exit();
}

// Get user ID
$user_id = intval($_GET['id'] ?? 0);
if ($user_id === 0) {
    header("Location: admin_dashboard.php?error=Invalid+user+ID");
    exit();
}

// Fetch user data
$stmt = $conn->prepare("SELECT id, name, email, role FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if (!$user) {
    header("Location: admin_dashboard.php?error=User+not+found");
    exit();
}

// Handle form submission
$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $role = $_POST['role'];
    $password = $_POST['password'];

    // Validation
    if (empty($name)) $errors['name'] = 'Name is required';
    if (empty($email)) $errors['email'] = 'Email is required';
    elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors['email'] = 'Invalid email format';

    // Check email uniqueness
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
    $stmt->bind_param("si", $email, $user_id);
    $stmt->execute();
    if ($stmt->get_result()->fetch_assoc()) {
        $errors['email'] = 'Email already in use';
    }

    if (empty($errors)) {
        try {
            $conn->begin_transaction();

            // Base query
            $query = "UPDATE users SET name = ?, email = ?, role = ?";
            $params = [$name, $email, $role];
            $types = "sss";

            // Add password if provided
            if (!empty($password)) {
                $query .= ", password = ?";
                $params[] = password_hash($password, PASSWORD_DEFAULT);
                $types .= "s";
            }

            $query .= " WHERE id = ?";
            $params[] = $user_id;
            $types .= "i";

            $stmt = $conn->prepare($query);
            $stmt->bind_param($types, ...$params);
            $stmt->execute();

            $conn->commit();
            $success = true;
        } catch (mysqli_sql_exception $e) {
            $conn->rollback();
            $errors['general'] = "Error updating user: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit User - Admin Panel</title>
    <link rel="stylesheet" href="../style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        /* Page-specific styles */
        .admin-form-container {
            max-width: 800px;
            margin: 2rem auto;
            padding: 2rem;
            background: white;
            border-radius: 12px;
            box-shadow: var(--shadow);
        }
        
        .password-section {
            margin-top: 2rem;
            padding-top: 2rem;
            border-top: 1px solid #e2e8f0;
        }
        
        .is-invalid {
            border-color: var(--accent) !important;
        }
        
        .error-message {
            color: var(--accent);
            font-size: 0.85rem;
            margin-top: 0.5rem;
        }
    </style>
</head>
<body class="admin-page">
    <div class="admin-container">
        <main class="admin-main">
            <div class="admin-form-container">
                <header class="admin-header">
                    <h1><i class="fas fa-user-edit"></i> Edit User</h1>
                    <div class="admin-actions">
                        <a href="admin_dashboard.php" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Back to Dashboard
                        </a>
                    </div>
                </header>

                <?php if ($success): ?>
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle"></i> User updated successfully!
                    </div>
                <?php endif; ?>

                <?php if (!empty($errors['general'])): ?>
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($errors['general']) ?>
                    </div>
                <?php endif; ?>

                <form method="POST" class="admin-form">
                    <div class="form-group">
                        <label for="name">Full Name</label>
                        <input type="text" id="name" name="name" 
                               value="<?= htmlspecialchars($user['name']) ?>"
                               class="form-control <?= !empty($errors['name']) ? 'is-invalid' : '' ?>">
                        <?php if (!empty($errors['name'])): ?>
                            <div class="error-message"><?= htmlspecialchars($errors['name']) ?></div>
                        <?php endif; ?>
                    </div>

                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" id="email" name="email" 
                               value="<?= htmlspecialchars($user['email']) ?>"
                               class="form-control <?= !empty($errors['email']) ? 'is-invalid' : '' ?>">
                        <?php if (!empty($errors['email'])): ?>
                            <div class="error-message"><?= htmlspecialchars($errors['email']) ?></div>
                        <?php endif; ?>
                    </div>

                    <div class="form-group">
                        <label for="role">Role</label>
                        <select id="role" name="role" class="form-control">
                            <option value="user" <?= $user['role'] === 'user' ? 'selected' : '' ?>>User</option>
                            <option value="admin" <?= $user['role'] === 'admin' ? 'selected' : '' ?>>Admin</option>
                        </select>
                    </div>

                    <div class="password-section">
                        <h3><i class="fas fa-lock"></i> Change Password</h3>
                        <p class="text-muted">Leave blank to keep current password</p>
                        
                        <div class="form-group">
                            <label for="password">New Password</label>
                            <input type="password" id="password" name="password"
                                   class="form-control">
                            <small class="text-muted">Minimum 8 characters</small>
                        </div>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Save Changes
                        </button>
                        <a href="admin_dashboard.php" class="btn btn-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </main>
    </div>
</body>
</html>