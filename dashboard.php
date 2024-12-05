<?php
session_start();
require_once 'db_connection.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch current wallet balance
$wallet_query = "SELECT balance FROM wallet WHERE user_id = ?";
$wallet_stmt = $conn->prepare($wallet_query);
$wallet_stmt->bind_param("i", $user_id);
$wallet_stmt->execute();
$wallet_result = $wallet_stmt->get_result();
$wallet = $wallet_result->fetch_assoc();
$wallet_balance = $wallet ? $wallet['balance'] : 0;

// Fetch user information
$stmt = $conn->prepare("SELECT username FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

// Add message display for transaction status if it exists
$message = '';
if (isset($_SESSION['message'])) {
    $message = $_SESSION['message'];
    unset($_SESSION['message']);
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Ticket Booking System</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100">
    <div class="container mx-auto px-4 py-8">
        <h1 class="text-3xl font-bold mb-8">Welcome, <?php echo htmlspecialchars($user['username']); ?>!</h1>
        
        <?php if ($message): ?>
        <div class="mb-4 p-4 bg-green-100 text-green-700 rounded-lg">
            <?php echo htmlspecialchars($message); ?>
        </div>
        <?php endif; ?>
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <div class="bg-white p-6 rounded-lg shadow-md">
                <h2 class="text-xl font-bold mb-4">Wallet Balance</h2>
                <p class="text-2xl font-semibold text-green-600">₹<?php echo number_format($wallet_balance, 2); ?></p>
                <div class="mt-4 space-y-2">
                    <a href="add_money.php" class="block text-center bg-green-500 text-white px-4 py-2 rounded-lg hover:bg-green-600">Add Money</a>
                    <a href="wallet.php" class="block text-center bg-blue-500 text-white px-4 py-2 rounded-lg hover:bg-blue-600">View Transactions</a>
                </div>
            </div>
            
            <div class="bg-white p-6 rounded-lg shadow-md">
                <h2 class="text-xl font-bold mb-4">Book a Ticket</h2>
                <?php if ($wallet_balance > 0): ?>
                <a href="book_ticket.php" class="inline-block bg-green-500 text-white px-4 py-2 rounded-lg hover:bg-green-600">Book Now</a>
                <?php else: ?>
                <p class="text-red-500 mb-2">Please add money to your wallet first</p>
                <a href="add_money.php" class="inline-block bg-blue-500 text-white px-4 py-2 rounded-lg hover:bg-blue-600">Add Money</a>
                <?php endif; ?>
            </div>
            
            <div class="bg-white p-6 rounded-lg shadow-md">
                <h2 class="text-xl font-bold mb-4">My Bookings</h2>
                <a href="my_bookings.php" class="inline-block bg-purple-500 text-white px-4 py-2 rounded-lg hover:bg-purple-600">View Bookings</a>
            </div>
        </div>
        
        <div class="mt-8 grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="bg-white p-6 rounded-lg shadow-md">
                <h2 class="text-xl font-bold mb-4">Transaction History</h2>
                <a href="transaction_history.php" class="inline-block bg-gray-500 text-white px-4 py-2 rounded-lg hover:bg-gray-600">View History</a>
            </div>
            
            <div class="bg-white p-6 rounded-lg shadow-md">
                <h2 class="text-xl font-bold mb-4">Feedback</h2>
                <a href="feedback.php" class="inline-block bg-yellow-500 text-white px-4 py-2 rounded-lg hover:bg-yellow-600">Submit Feedback</a>
            </div>
        </div>
        
        <div class="mt-8 text-center">
            <a href="logout.php" class="inline-block bg-red-500 text-white px-4 py-2 rounded-lg hover:bg-red-600">Logout</a>
        </div>
    </div>
</body>
</html>
