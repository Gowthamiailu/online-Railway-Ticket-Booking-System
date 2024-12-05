<?php
session_start();
require_once 'db_connection.php';

if (!isset($_SESSION['user_id']) || !isset($_POST['train_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$train_id = $_POST['train_id'];
$journey_date = $_POST['journey_date'];
$num_tickets = $_POST['num_tickets'];
$ticket_category = $_POST['ticket_category'];
$total_fare = $_POST['total_fare'];

// Start transaction
$conn->begin_transaction();

try {
    // Check wallet balance
    $wallet_query = "SELECT balance FROM wallet WHERE user_id = ? ORDER BY id DESC LIMIT 1";
    $stmt = $conn->prepare($wallet_query);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $wallet_result = $stmt->get_result();
    $wallet = $wallet_result->fetch_assoc();

    if (!$wallet || $wallet['balance'] < $total_fare) {
        throw new Exception("Insufficient balance. Please add money to your wallet.");
    }

    // Create booking
    $booking_query = "INSERT INTO bookings (
        user_id, 
        train_id, 
        ticket_category,
        num_tickets,
        total_amount,
        booking_date
    ) VALUES (?, ?, ?, ?, ?, NOW())";

    $stmt = $conn->prepare($booking_query);
    $stmt->bind_param("iisid", $user_id, $train_id, $ticket_category, $num_tickets, $total_fare);
    
    if (!$stmt->execute()) {
        throw new Exception("Failed to create booking.");
    }

    $booking_id = $conn->insert_id;

    // Add passengers
    $passenger_query = "INSERT INTO passengers (
        booking_id,
        name,
        age,
        gender
    ) VALUES (?, ?, ?, ?)";

    $stmt = $conn->prepare($passenger_query);

    foreach ($_POST['passengers'] as $passenger) {
        $stmt->bind_param("isis", 
            $booking_id, 
            $passenger['name'], 
            $passenger['age'], 
            $passenger['gender']
        );
        
        if (!$stmt->execute()) {
            throw new Exception("Failed to add passenger information.");
        }
    }

    // Update wallet balance
    $update_wallet = "UPDATE wallet SET balance = balance - ? WHERE user_id = ? AND id = (
        SELECT id FROM (SELECT id FROM wallet WHERE user_id = ? ORDER BY id DESC LIMIT 1) as w
    )";
    $stmt = $conn->prepare($update_wallet);
    $stmt->bind_param("dii", $total_fare, $user_id, $user_id);
    
    if (!$stmt->execute()) {
        throw new Exception("Failed to update wallet balance.");
    }

    // Add transaction record
    $transaction_query = "INSERT INTO transactions (
        user_id,
        amount,
        type
    ) VALUES (?, ?, 'ticket_purchase')";
    
    $stmt = $conn->prepare($transaction_query);
    $stmt->bind_param("id", $user_id, $total_fare);
    
    if (!$stmt->execute()) {
        throw new Exception("Failed to record transaction.");
    }

    // Commit transaction
    $conn->commit();

    // Store success message in session
    $_SESSION['success_message'] = "Booking confirmed successfully!";
    
    // Redirect to booking confirmation page
    header("Location: booking_confirmation.php?booking_id=" . $booking_id);
    exit();

} catch (Exception $e) {
    // Rollback transaction on error
    $conn->rollback();
    $_SESSION['error_message'] = $e->getMessage();
    header("Location: passenger_details.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Processing Booking</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100">
    <div class="container mx-auto px-4 py-8">
        <div class="text-center">
            <h1 class="text-2xl font-bold mb-4">Processing your booking...</h1>
            <p>Please wait while we confirm your reservation.</p>
        </div>
    </div>
</body>
</html> 