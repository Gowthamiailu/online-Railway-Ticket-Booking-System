<?php
session_start();
require_once 'db_connection.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

if (!isset($_POST['booking_id'])) {
    $_SESSION['error_message'] = "No booking specified for cancellation.";
    header("Location: my_bookings.php");
    exit();
}

$booking_id = $_POST['booking_id'];

// Start transaction
$conn->begin_transaction();

try {
    // Debug information
    error_log("Processing cancellation for booking_id: " . $booking_id . " and user_id: " . $user_id);

    // Fetch booking information
    $booking_query = "SELECT * FROM bookings WHERE id = ? AND user_id = ?";
    $stmt = $conn->prepare($booking_query);
    if (!$stmt) {
        throw new Exception("Database error: " . $conn->error);
    }
    
    $stmt->bind_param("ii", $booking_id, $user_id);
    $stmt->execute();
    $booking = $stmt->get_result()->fetch_assoc();

    if (!$booking) {
        throw new Exception("Invalid booking or unauthorized access.");
    }

    // Check if booking is within 24 hours
    if (strtotime($booking['booking_date']) <= strtotime('-24 hours')) {
        throw new Exception("Bookings can only be cancelled within 24 hours of booking.");
    }

    // Calculate refund amount (90% refund)
    $refund_amount = $booking['total_amount'] * 0.9;

    // Update wallet balance - Fixed query
    $wallet_query = "UPDATE wallet SET balance = balance + ? WHERE user_id = ?";
    $stmt = $conn->prepare($wallet_query);
    if (!$stmt) {
        throw new Exception("Database error: " . $conn->error);
    }
    
    $stmt->bind_param("di", $refund_amount, $user_id);
    if (!$stmt->execute()) {
        throw new Exception("Failed to process refund: " . $stmt->error);
    }

    // Record transaction
    $transaction_query = "INSERT INTO transactions (user_id, amount, type, description) VALUES (?, ?, 'REFUND', ?)";
    $stmt = $conn->prepare($transaction_query);
    if (!$stmt) {
        throw new Exception("Database error: " . $conn->error);
    }
    
    $description = "Refund for cancelled booking #" . $booking_id;
    $stmt->bind_param("ids", $user_id, $refund_amount, $description);
    if (!$stmt->execute()) {
        throw new Exception("Failed to record transaction: " . $stmt->error);
    }

    // Delete passengers
    $delete_passengers = "DELETE FROM passengers WHERE booking_id = ?";
    $stmt = $conn->prepare($delete_passengers);
    if (!$stmt) {
        throw new Exception("Database error: " . $conn->error);
    }
    
    $stmt->bind_param("i", $booking_id);
    if (!$stmt->execute()) {
        throw new Exception("Failed to remove passenger information: " . $stmt->error);
    }

    // Delete booking
    $delete_booking = "DELETE FROM bookings WHERE id = ? AND user_id = ?";
    $stmt = $conn->prepare($delete_booking);
    if (!$stmt) {
        throw new Exception("Database error: " . $conn->error);
    }
    
    $stmt->bind_param("ii", $booking_id, $user_id);
    if (!$stmt->execute()) {
        throw new Exception("Failed to cancel booking: " . $stmt->error);
    }

    // Commit transaction
    $conn->commit();

    $_SESSION['success_message'] = "Booking cancelled successfully. ₹" . number_format($refund_amount, 2) . " has been refunded to your wallet.";
    header("Location: my_bookings.php");
    exit();

} catch (Exception $e) {
    // Rollback transaction on error
    $conn->rollback();
    error_log("Cancellation Error: " . $e->getMessage());
    $_SESSION['error_message'] = "Error cancelling booking: " . $e->getMessage();
    header("Location: my_bookings.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cancel Booking</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100">
    <div class="container mx-auto px-4 py-8">
        <h1 class="text-3xl font-bold mb-8">Processing Cancellation</h1>
        <p class="text-gray-600">Please wait while we process your cancellation request...</p>
    </div>
</body>
</html> 