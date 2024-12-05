<?php
session_start();
require_once 'db_connection.php';

// Debug connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Debug query
$check_query = "SELECT * FROM wallet WHERE user_id = ?";
error_log("Checking wallet for user_id: " . $user_id);

$check_stmt = $conn->prepare($check_query);
if (!$check_stmt) {
    error_log("Prepare failed: " . $conn->error);
    die("Database error: " . $conn->error);
}

$check_stmt->bind_param("i", $user_id);
$check_stmt->execute();
$result = $check_stmt->get_result();
$wallet = $result->fetch_assoc();
$current_balance = $wallet ? $wallet['balance'] : 0;
$check_stmt->close();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $amount = filter_var($_POST['amount'], FILTER_VALIDATE_FLOAT);
    
    if ($amount > 0) {
        $conn->begin_transaction();
        
        try {
            if ($wallet) {
                // Update existing wallet
                $query = "UPDATE wallet SET balance = balance + ? WHERE user_id = ?";
            } else {
                // Create new wallet entry
                $query = "INSERT INTO wallet (user_id, balance) VALUES (?, ?)";
            }
            
            error_log("Query to execute: " . $query);
            $stmt = $conn->prepare($query);
            
            if (!$stmt) {
                throw new Exception("Prepare failed: " . $conn->error);
            }
            
            if ($wallet) {
                $stmt->bind_param("di", $amount, $user_id);
            } else {
                $stmt->bind_param("id", $user_id, $amount);
            }
            
            if (!$stmt->execute()) {
                throw new Exception("Execute failed: " . $stmt->error);
            }

            $conn->commit();
            $_SESSION['success_message'] = "Successfully added ₹" . number_format($amount, 2) . " to your wallet";
            header("Location: dashboard.php");
            exit();
            
        } catch (Exception $e) {
            $conn->rollback();
            error_log("Transaction failed: " . $e->getMessage());
            $error = "Transaction failed: " . $e->getMessage();
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
            <p class="text-2xl font-semibold">₹<?php echo number_format($current_balance, 2); ?></p>
        </div>
        
        <?php if (isset($error)): ?>
            <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>
        
        <div class="max-w-md mx-auto bg-white p-6 rounded-lg shadow-md">
            <form method="POST" action="">
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="amount">
                        Amount (₹)
                    </label>
                    <input class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                           id="amount" 
                           name="amount" 
                           type="number" 
                           step="0.01" 
                           min="1" 
                           required>
                </div>
                <div class="flex items-center justify-between">
                    <button class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline"
                            type="submit">
                        Add Money
                    </button>
                    <a href="dashboard.php" class="text-blue-500 hover:text-blue-800">Back to Dashboard</a>
                </div>
            </form>
        </div>
    </div>
</body>
</html> 