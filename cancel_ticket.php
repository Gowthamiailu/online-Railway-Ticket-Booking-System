<?php
session_start();
require_once 'db_connection.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

if (isset($_GET['id'])) {
    $ticket_id = $_GET['id'];

    // Fetch ticket information
    $stmt = $conn->prepare("SELECT * FROM tickets WHERE id = ? AND user_id = ? AND status = 'booked'");
    $stmt->bind_param("ii", $ticket_id, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $ticket = $result->fetch_assoc();

    if ($ticket) {
        // Update ticket status
        $stmt = $conn->prepare("UPDATE tickets SET status = 'cancelled' WHERE id = ?");
        $stmt->bind_param("i", $ticket_id);
        
        if ($stmt->execute()) {
            // Refund the ticket price to the wallet
            $stmt = $conn->prepare("UPDATE wallet SET balance = balance + ? WHERE user_id = ?");
            $stmt->bind_param("di", $ticket['ticket_price'], $user_id);
            $stmt->execute();

            // Record transaction
            $stmt = $conn->prepare("INSERT INTO transactions (user_id, amount, type) VALUES (?, ?, 'ticket_cancellation')");
            $stmt->bind_param("id", $user_id, $ticket['ticket_price']);
            $stmt->execute();

            $success = "Ticket cancelled successfully and refunded to your wallet.";
        } else {
            $error = "Error cancelling ticket. Please try again.";
        }
    } else {
        $error = "Invalid ticket or ticket already cancelled.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cancel Ticket - Ticket Booking System</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100">
    <div class="container mx-auto px-4 py-8">
        <h1 class="text-3xl font-bold mb-8">Cancel Ticket</h1>
        
        <?php if (isset($success)): ?>
            <p class="text-green-500 mb-4"><?php echo $success; ?></p>
        <?php endif; ?>
        
        <?php if (isset($error)): ?>
            <p class="text-red-500 mb-4"><?php echo $error; ?></p>
        <?php endif; ?>
        
        <div class="mt-8">
            <a href="booking_status.php" class="text-blue-500 hover:underline">Back to My Bookings</a>
        </div>
    </div>
</body>
</html>
