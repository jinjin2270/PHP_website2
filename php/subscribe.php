<?php
session_start();
require_once("../config/db.php");

if (!isset($_SESSION['user_id'])) {
    header("Location: ../pages/login.html");
    exit();
}

$pageTitle = "Subscribe to Premium";
$isLoggedIn = true;
include("../includes/header.php");

// Subscription settings
$premiumPrice = 9.99; // Monthly price
$sharedBankAccount = '0000000001'; // Shared bank account number
$sharedBankPin = '1234'; // Shared bank PIN (in real app, store hashed)
$user_id = $_SESSION['user_id'];

// Handle form submission
$message = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $entered_pin = trim($_POST['account_pin']);
    $months = intval($_POST['months']);

    try {
        // Start transaction
        $conn->begin_transaction();

        // 1. Verify shared bank account has sufficient balance
        $stmt = $conn->prepare("SELECT balance FROM bank_accounts WHERE account_number = ?");
        $stmt->bind_param("s", $sharedBankAccount);
        $stmt->execute();
        $bankBalance = $stmt->get_result()->fetch_assoc()['balance'];
        
        $totalAmount = $premiumPrice * $months;
        
        if ($bankBalance < $totalAmount) {
            throw new Exception("System error: Insufficient funds in bank account");
        }

        // 2. Verify PIN (in real app, compare hashed PIN)
        if ($entered_pin !== $sharedBankPin) {
            throw new Exception("Invalid PIN");
        }

        // 3. Deduct amount from shared bank account
        $updateBank = $conn->prepare("
            UPDATE bank_accounts 
            SET balance = balance - ? 
            WHERE account_number = ?
        ");
        $updateBank->bind_param("ds", $totalAmount, $sharedBankAccount);
        $updateBank->execute();

        // 4. Update user to premium status
        $endDate = date('Y-m-d H:i:s', strtotime("+$months months"));
        $updateUser = $conn->prepare("
            INSERT INTO user_subscriptions (user_id, start_date, end_date, amount_paid)
            VALUES (?, NOW(), ?, ?)
            ON DUPLICATE KEY UPDATE end_date = ?, amount_paid = ?
        ");
        $updateUser->bind_param("isdsd", $user_id, $endDate, $totalAmount, $endDate, $totalAmount);
        $updateUser->execute();

        // 5. Record transaction
        $recordTransaction = $conn->prepare("
            INSERT INTO transactions (user_id, account_number, amount, description, type)
            VALUES (?, ?, ?, ?, 'premium_subscription')
        ");
        $description = "Premium subscription for $months month(s)";
        $recordTransaction->bind_param("isds", $user_id, $sharedBankAccount, $totalAmount, $description);
        $recordTransaction->execute();



$conn->commit();

$is_premium = true;
$_SESSION['premium_until'] = $endDate;

$message = "Success! You are now a premium member until " . date('F j, Y', strtotime($endDate));

// Redirect to premium posts page to see the newly accessible content
header("Refresh: 3; url=premium.php");
    } catch (Exception $e) {
        $conn->rollback();
        $message = "Error: " . $e->getMessage();
    }
}
?>

<div class="container subscribe-container">
    <h1>Subscribe to Premium</h1>
    <p>Get access to exclusive content for just $<?= number_format($premiumPrice, 2) ?> per month</p>

    <?php if ($message): ?>
        <div class="message <?= strpos($message, 'Success') !== false ? 'success' : 'error' ?>">
            <?= htmlspecialchars($message) ?>
        </div>
    <?php endif; ?>

    <div class="subscription-form">
        <form method="post">
            <div class="form-group">
                <label for="months">Subscription Duration:</label>
                <select id="months" name="months" required>
                    <option value="1">1 Month ($<?= number_format($premiumPrice, 2) ?>)</option>
                    <option value="3">3 Months ($<?= number_format($premiumPrice * 3, 2) ?>)</option>
                    <option value="6">6 Months ($<?= number_format($premiumPrice * 6, 2) ?>)</option>
                    <option value="12">12 Months ($<?= number_format($premiumPrice * 12, 2) ?>)</option>
                </select>
            </div>

            <div class="form-group">
                <label>Bank Account:</label>
                <div class="bank-info">
                    <strong>System Bank Account:</strong> ****<?= substr($sharedBankAccount, -4) ?>
                </div>
            </div>

            <div class="form-group">
                <label for="account_pin">Bank Account PIN:</label>
                <input type="password" id="account_pin" name="account_pin" required minlength="4" maxlength="4">
                <small class="hint">Use the system PIN provided to you</small>
            </div>

            <button type="submit" class="btn-subscribe">Subscribe Now</button>
        </form>
    </div>

    <div class="premium-benefits">
        <h2>Premium Membership Benefits:</h2>
        <ul>
            <li>Access to exclusive premium content</li>
            <li>Ad-free browsing experience</li>
            <li>Early access to new features</li>
            <li>Priority support</li>
            <li>Ability to create premium posts</li>
        </ul>
    </div>
</div>

<?php include("../includes/footer.php"); ?>

<style>
.subscribe-container {
    max-width: 600px;
    margin: 40px auto;
    padding: 30px;
    background: white;
    border-radius: 10px;
    box-shadow: 0 2px 15px rgba(0,0,0,0.1);
}

.subscription-form {
    margin: 30px 0;
    padding: 20px;
    background: #f9f9f9;
    border-radius: 8px;
}

.form-group {
    margin-bottom: 20px;
}

.form-group label {
    display: block;
    margin-bottom: 8px;
    font-weight: 600;
}

.form-group select, 
.form-group input {
    width: 100%;
    padding: 12px;
    border: 1px solid #ddd;
    border-radius: 4px;
    font-size: 16px;
}

.bank-info {
    padding: 12px;
    background: #f0f0f0;
    border-radius: 4px;
}

.hint {
    display: block;
    margin-top: 5px;
    color: #666;
    font-size: 0.8rem;
}

.btn-subscribe {
    background: linear-gradient(135deg, #667eea, #764ba2);
    color: white;
    border: none;
    padding: 15px;
    font-size: 16px;
    border-radius: 4px;
    cursor: pointer;
    transition: all 0.3s;
    width: 100%;
    font-weight: bold;
}

.btn-subscribe:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
}

.premium-benefits {
    margin-top: 40px;
    padding: 20px;
    background: #f5f7ff;
    border-radius: 8px;
}

.premium-benefits h2 {
    color: #667eea;
    margin-bottom: 15px;
}

.premium-benefits ul {
    list-style-type: none;
    padding: 0;
}

.premium-benefits li {
    padding: 8px 0;
    position: relative;
    padding-left: 25px;
}

.premium-benefits li:before {
    content: "✓";
    color: #48bb78;
    position: absolute;
    left: 0;
    font-weight: bold;
}

.message {
    padding: 15px;
    margin: 20px 0;
    border-radius: 4px;
}

.message.success {
    background: rgba(72, 187, 120, 0.1);
    color: #48bb78;
    border: 1px solid #48bb78;
}

.message.error {
    background: rgba(245, 101, 101, 0.1);
    color: #f56565;
    border: 1px solid #f56565;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Update displayed amount when months change
    const monthsSelect = document.getElementById('months');
    
    monthsSelect.addEventListener('change', function() {
        const months = parseInt(this.value);
        const total = months * <?= $premiumPrice ?>;
        document.querySelector('button[type="submit"]').textContent = 
            `Subscribe Now ($${total.toFixed(2)})`;
    });
    
    // Validate PIN is numeric
    document.querySelector('form').addEventListener('submit', function(e) {
        const pin = document.getElementById('account_pin').value;
        if (!/^\d{4}$/.test(pin)) {
            alert('PIN must be exactly 4 digits');
            e.preventDefault();
        }
    });
});
</script>