<?php
session_start();
require_once 'db_connection.php';

if (!isset($_SESSION['user_id']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: book_ticket.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$train_id = $_POST['train_id'];
$ticket_category = $_POST['ticket_category'];
$num_tickets = $_POST['num_tickets'];
$total_price = $_POST['total_price'];

try {
    // Start transaction
    $conn->begin_transaction();

    // Check wallet balance
    $stmt = $conn->prepare("SELECT balance FROM wallet WHERE user_id = ? FOR UPDATE");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $wallet = $stmt->get_result()->fetch_assoc();

    if (!$wallet || $wallet['balance'] < $total_price) {
        throw new Exception("Insufficient wallet balance");
    }

    // Create booking
    $stmt = $conn->prepare("
        INSERT INTO bookings (user_id, train_id, ticket_category, num_tickets, total_amount, booking_date)
        VALUES (?, ?, ?, ?, ?, NOW())
    ");
    $stmt->bind_param("isidi", $user_id, $train_id, $ticket_category, $num_tickets, $total_price);
    $stmt->execute();
    $booking_id = $conn->insert_id;

    // Add passengers
    $stmt = $conn->prepare("
        INSERT INTO passengers (booking_id, name, age, gender)
        VALUES (?, ?, ?, ?)
    ");
    
    for ($i = 0; $i < $num_tickets; $i++) {
        $stmt->bind_param("isis", 
            $booking_id, 
            $_POST['passenger_name'][$i],
            $_POST['passenger_age'][$i],
            $_POST['passenger_gender'][$i]
        );
        $stmt->execute();
    }

    // Update wallet balance
    $new_balance = $wallet['balance'] - $total_price;
    $stmt = $conn->prepare("UPDATE wallet SET balance = ? WHERE user_id = ?");
    $stmt->bind_param("di", $new_balance, $user_id);
    $stmt->execute();

    // Update available seats
    $seats_field = $ticket_category . '_seats';
    $stmt = $conn->prepare("
        UPDATE trains 
        SET $seats_field = $seats_field - ? 
        WHERE id = ?
    ");
    $stmt->bind_param("ii", $num_tickets, $train_id);
    $stmt->execute();

    $conn->commit();
    
    // Redirect to booking status
    header("Location: booking_status.php?booking_id=" . $booking_id);
    exit();

} catch (Exception $e) {
    $conn->rollback();
    $_SESSION['error'] = $e->getMessage();
    header("Location: passenger_details.php");
    exit();
}
?> 