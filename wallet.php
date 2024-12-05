<?php
session_start();
require_once 'db_connection.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch wallet balance
$stmt = $conn->prepare("SELECT balance FROM wallet WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$wallet = $result->fetch_assoc();
$balance = $wallet ? $wallet['balance'] : 0;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $amount = filter_var($_POST['amount'], FILTER_VALIDATE_FLOAT);
    
    if ($amount > 0) {
        $conn->begin_transaction();
        
        try {
            // Check if wallet exists and update or insert accordingly
            if ($wallet) {
                $new_balance = $balance + $amount;
                $stmt = $conn->prepare("UPDATE wallet SET balance = ? WHERE user_id = ?");
                $stmt->bind_param("di", $new_balance, $user_id);
            } else {
                $stmt = $conn->prepare("INSERT INTO wallet (user_id, balance) VALUES (?, ?)");
                $stmt->bind_param("id", $user_id, $amount);
                $new_balance = $amount;
            }
            
            if (!$stmt->execute()) {
                throw new Exception("Failed to update wallet");
            }

            // Record transaction
            $stmt = $conn->prepare("INSERT INTO transactions (user_id, type, amount, description) VALUES (?, 'credit', ?, 'Added money to wallet')");
            $stmt->bind_param("id", $user_id, $amount);
            if (!$stmt->execute()) {
                throw new Exception("Failed to record transaction");
            }

            $conn->commit();
            $_SESSION['wallet_balance'] = $new_balance; // Update session with new balance
            $success = "Successfully added ₹" . number_format($amount, 2) . " to your wallet";
            $balance = $new_balance;
            
            // Redirect to dashboard with success message
            $_SESSION['success_message'] = $success;
            header("Location: dashboard.php");
            exit();
            
        } catch (Exception $e) {
            $conn->rollback();
            error_log("Transaction failed: " . $e->getMessage());
            $error = "Failed to add money. Please try again.";
        }
    } else {
        $error = "Please enter a valid amount greater than 0";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Money - Ticket Booking System</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100">
    <div class="container mx-auto px-4 py-8">
        <h1 class="text-3xl font-bold mb-8">Add Money to Wallet</h1>
        
        <div class="bg-white p-6 rounded-lg shadow-md mb-8">
            <h2 class="text-xl font-bold mb-4">Current Balance</h2>
            <p class="text-2xl font-semibold">₹<?php echo number_format($balance, 2); ?></p>
        </div>
        
        <?php if (isset($error)): ?>
            <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>
        
        <div class="bg-white p-6 rounded-lg shadow-md">
            <form method="POST" action="">
                <div class="mb-4">
                    <label for="amount" class="block text-gray-700 font-bold mb-2">Amount to Add (₹)</label>
                    <input type="number" 
                           id="amount" 
                           name="amount" 
                           step="0.01" 
                           min="1" 
                           required 
                           class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:border-blue-500">
                </div>
                <button type="submit" 
                        class="bg-blue-500 text-white font-bold py-2 px-4 rounded-lg hover:bg-blue-600 w-full">
                    Add Money
                </button>
            </form>
        </div>
        
        <div class="mt-8">
            <a href="dashboard.php" class="text-blue-500 hover:underline">Back to Dashboard</a>
        </div>
    </div>
</body>
</html>
