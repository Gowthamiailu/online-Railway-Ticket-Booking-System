<?php
session_start();
require_once 'db_connection.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Get train details from previous form
if (!isset($_POST['train_id']) || !isset($_POST['num_tickets'])) {
    header("Location: book_ticket.php");
    exit();
}

$train_id = $_POST['train_id'];
$journey_date = $_POST['journey_date'];
$num_tickets = (int)$_POST['num_tickets'];
$ticket_category = $_POST['ticket_category'];
$total_fare = (float)$_POST['total_fare'];

// Validate total_fare
if ($total_fare <= 0) {
    header("Location: book_ticket.php");
    exit();
}

// Double-check the fare calculation
$train_query = "SELECT $ticket_category"."_price as price FROM trains WHERE id = ?";
$stmt = $conn->prepare($train_query);
$stmt->bind_param("i", $train_id);
$stmt->execute();
$result = $stmt->get_result();
$train_price = $result->fetch_assoc();

$calculated_fare = $train_price['price'] * $num_tickets;
if (abs($calculated_fare - $total_fare) > 0.01) { // Allow for small floating point differences
    header("Location: book_ticket.php");
    exit();
}

// Fetch train details
$train_query = "SELECT * FROM trains WHERE id = ?";
$stmt = $conn->prepare($train_query);
$stmt->bind_param("i", $train_id);
$stmt->execute();
$train = $stmt->get_result()->fetch_assoc();

if (!$train) {
    header("Location: book_ticket.php");
    exit();
}

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_booking'])) {
    try {
        $conn->begin_transaction();

        // Check wallet balance
        $wallet_query = "SELECT balance FROM wallet WHERE user_id = ? FOR UPDATE";
        $stmt = $conn->prepare($wallet_query);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $wallet = $stmt->get_result()->fetch_assoc();

        if (!$wallet || $wallet['balance'] < $total_fare) {
            throw new Exception("Insufficient wallet balance. Please add money to your wallet.");
        }

        // Create booking
        $booking_query = "INSERT INTO bookings (
            user_id, 
            train_id,
            journey_date,
            ticket_category,
            num_tickets,
            total_amount,
            booking_date
        ) VALUES (?, ?, ?, ?, ?, ?, NOW())";

        $stmt = $conn->prepare($booking_query);
        $stmt->bind_param("iissid", 
            $user_id, 
            $train_id, 
            $journey_date,
            $ticket_category, 
            $num_tickets, 
            $total_fare
        );
        
        if (!$stmt->execute()) {
            throw new Exception("Failed to create booking");
        }

        $booking_id = $conn->insert_id;

        // Insert passengers
        $passenger_query = "INSERT INTO passengers (booking_id, name, age, gender) VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($passenger_query);

        for ($i = 0; $i < $num_tickets; $i++) {
            $name = $_POST['passenger_name'][$i];
            $age = $_POST['passenger_age'][$i];
            $gender = $_POST['passenger_gender'][$i];

            $stmt->bind_param("isis", $booking_id, $name, $age, $gender);
            if (!$stmt->execute()) {
                throw new Exception("Failed to add passenger details");
            }
        }

        // Update wallet balance
        $update_wallet = "UPDATE wallet SET balance = balance - ? WHERE user_id = ?";
        $stmt = $conn->prepare($update_wallet);
        $stmt->bind_param("di", $total_fare, $user_id);
        
        if (!$stmt->execute()) {
            throw new Exception("Failed to update wallet balance");
        }

        // Record transaction
        $transaction_query = "INSERT INTO transactions (user_id, amount, type) VALUES (?, ?, 'ticket_purchase')";
        $stmt = $conn->prepare($transaction_query);
        $stmt->bind_param("id", $user_id, $total_fare);
        
        if (!$stmt->execute()) {
            throw new Exception("Failed to record transaction");
        }

        $conn->commit();
        header("Location: booking_confirmation.php?booking_id=" . $booking_id);
        exit();

    } catch (Exception $e) {
        $conn->rollback();
        $error = $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Passenger Details</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100">
    <div class="container mx-auto px-4 py-8">
        <h1 class="text-3xl font-bold mb-8">Enter Passenger Details</h1>

        <!-- Booking Summary -->
        <div class="bg-white p-6 rounded-lg shadow-md mb-8">
            <h2 class="text-xl font-bold mb-4">Booking Summary</h2>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <p class="text-gray-600">Train</p>
                    <p class="font-semibold"><?php echo htmlspecialchars($train['train_name']); ?> (<?php echo htmlspecialchars($train['train_number']); ?>)</p>
                </div>
                <div>
                    <p class="text-gray-600">Journey Date</p>
                    <p class="font-semibold"><?php echo date('d M Y', strtotime($journey_date)); ?></p>
                </div>
                <div>
                    <p class="text-gray-600">From - To</p>
                    <p class="font-semibold">
                        <?php echo htmlspecialchars($train['source_location']); ?> - 
                        <?php echo htmlspecialchars($train['destination_location']); ?>
                    </p>
                </div>
                <div>
                    <p class="text-gray-600">Total Amount</p>
                    <p class="font-semibold">₹<?php echo number_format($total_fare, 2); ?></p>
                </div>
            </div>
        </div>

        <?php if (isset($error)): ?>
            <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <!-- Passenger Details Form -->
        <form method="POST" class="bg-white p-6 rounded-lg shadow-md">
            <input type="hidden" name="train_id" value="<?php echo htmlspecialchars($train_id); ?>">
            <input type="hidden" name="journey_date" value="<?php echo htmlspecialchars($journey_date); ?>">
            <input type="hidden" name="num_tickets" value="<?php echo htmlspecialchars($num_tickets); ?>">
            <input type="hidden" name="ticket_category" value="<?php echo htmlspecialchars($ticket_category); ?>">
            <input type="hidden" name="total_fare" value="<?php echo htmlspecialchars($total_fare); ?>">

            <?php for ($i = 0; $i < $num_tickets; $i++): ?>
                <div class="mb-6">
                    <h3 class="text-lg font-bold mb-4">Passenger <?php echo $i + 1; ?></h3>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-gray-700 font-bold mb-2">Full Name</label>
                            <input type="text" 
                                   name="passenger_name[]" 
                                   required 
                                   class="w-full px-3 py-2 border rounded-lg">
                        </div>
                        <div>
                            <label class="block text-gray-700 font-bold mb-2">Age</label>
                            <input type="number" 
                                   name="passenger_age[]" 
                                   required 
                                   min="1" 
                                   max="120" 
                                   class="w-full px-3 py-2 border rounded-lg">
                        </div>
                        <div>
                            <label class="block text-gray-700 font-bold mb-2">Gender</label>
                            <select name="passenger_gender[]" 
                                    required 
                                    class="w-full px-3 py-2 border rounded-lg">
                                <option value="">Select Gender</option>
                                <option value="M">Male</option>
                                <option value="F">Female</option>
                                <option value="O">Other</option>
                            </select>
                        </div>
                    </div>
                </div>
            <?php endfor; ?>

            <div class="flex justify-between items-center">
                <a href="book_ticket.php" class="text-blue-500 hover:underline">Back</a>
                <button type="submit" 
                        name="submit_booking"
                        class="bg-blue-500 text-white font-bold py-2 px-4 rounded-lg hover:bg-blue-600">
                    Confirm Booking
                </button>
            </div>
        </form>
    </div>
</body>
</html> 