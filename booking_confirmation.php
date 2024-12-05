<?php
session_start();
require_once 'db_connection.php';

if (!isset($_SESSION['user_id']) || !isset($_GET['booking_id'])) {
    header("Location: login.php");
    exit();
}

$booking_id = $_GET['booking_id'];
$user_id = $_SESSION['user_id'];

// Fetch booking details
$query = "
    SELECT 
        b.*,
        t.train_name,
        t.train_number,
        t.source_location,
        t.destination_location,
        t.departure_time,
        t.arrival_time
    FROM bookings b
    JOIN trains t ON b.train_id = t.id
    WHERE b.id = ? AND b.user_id = ?
";

$stmt = $conn->prepare($query);
$stmt->bind_param("ii", $booking_id, $user_id);
$stmt->execute();
$booking = $stmt->get_result()->fetch_assoc();

if (!$booking) {
    header("Location: dashboard.php");
    exit();
}

// Fetch passengers
$passenger_query = "SELECT * FROM passengers WHERE booking_id = ?";
$stmt = $conn->prepare($passenger_query);
$stmt->bind_param("i", $booking_id);
$stmt->execute();
$passengers = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking Confirmation</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100">
    <div class="container mx-auto px-4 py-8">
        <div class="bg-white p-6 rounded-lg shadow-md">
            <h1 class="text-3xl font-bold mb-8 text-center text-green-600">Booking Confirmed!</h1>
            
            <div class="mb-8">
                <h2 class="text-xl font-bold mb-4">Booking Details</h2>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <p class="text-gray-600">Booking ID</p>
                        <p class="font-semibold"><?php echo $booking_id; ?></p>
                    </div>
                    <div>
                        <p class="text-gray-600">Booking Date</p>
                        <p class="font-semibold"><?php echo date('d M Y', strtotime($booking['booking_date'])); ?></p>
                    </div>
                    <div>
                        <p class="text-gray-600">Train</p>
                        <p class="font-semibold">
                            <?php echo htmlspecialchars($booking['train_name']); ?> 
                            (<?php echo htmlspecialchars($booking['train_number']); ?>)
                        </p>
                    </div>
                    <div>
                        <p class="text-gray-600">Journey</p>
                        <p class="font-semibold">
                            <?php echo htmlspecialchars($booking['source_location']); ?> to 
                            <?php echo htmlspecialchars($booking['destination_location']); ?>
                        </p>
                    </div>
                    <div>
                        <p class="text-gray-600">Departure - Arrival</p>
                        <p class="font-semibold">
                            <?php echo date('h:i A', strtotime($booking['departure_time'])); ?> - 
                            <?php echo date('h:i A', strtotime($booking['arrival_time'])); ?>
                        </p>
                    </div>
                    <div>
                        <p class="text-gray-600">Total Amount</p>
                        <p class="font-semibold">₹<?php echo number_format($booking['total_amount'], 2); ?></p>
                    </div>
                </div>
            </div>

            <div class="mb-8">
                <h2 class="text-xl font-bold mb-4">Passenger Details</h2>
                <div class="grid gap-4">
                    <?php foreach ($passengers as $passenger): ?>
                        <div class="border p-4 rounded-lg">
                            <div class="grid grid-cols-3 gap-4">
                                <div>
                                    <p class="text-gray-600">Name</p>
                                    <p class="font-semibold"><?php echo htmlspecialchars($passenger['name']); ?></p>
                                </div>
                                <div>
                                    <p class="text-gray-600">Age</p>
                                    <p class="font-semibold"><?php echo $passenger['age']; ?></p>
                                </div>
                                <div>
                                    <p class="text-gray-600">Gender</p>
                                    <p class="font-semibold">
                                        <?php 
                                        echo match($passenger['gender']) {
                                            'M' => 'Male',
                                            'F' => 'Female',
                                            'O' => 'Other',
                                            default => $passenger['gender']
                                        };
                                        ?>
                                    </p>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="flex justify-between items-center">
                <a href="dashboard.php" class="text-blue-500 hover:underline">Back to Dashboard</a>
                <button onclick="window.print()" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
                    Print Ticket
                </button>
            </div>
        </div>
    </div>
</body>
</html> 