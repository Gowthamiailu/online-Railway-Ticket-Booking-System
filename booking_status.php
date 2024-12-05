<?php
session_start();
require_once 'db_connection.php';

if (!isset($_SESSION['user_id']) || !isset($_GET['booking_id'])) {
    header("Location: dashboard.php");
    exit();
}

$booking_id = $_GET['booking_id'];
$user_id = $_SESSION['user_id'];

// Fetch booking details
$stmt = $conn->prepare("
    SELECT b.*, t.train_name, t.train_number, t.source_location, t.destination_location,
           t.departure_time, t.arrival_time
    FROM bookings b
    JOIN trains t ON b.train_id = t.id
    WHERE b.id = ? AND b.user_id = ?
");
$stmt->bind_param("ii", $booking_id, $user_id);
$stmt->execute();
$booking = $stmt->get_result()->fetch_assoc();

if (!$booking) {
    header("Location: dashboard.php");
    exit();
}

// Fetch passengers
$stmt = $conn->prepare("SELECT * FROM passengers WHERE booking_id = ?");
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
        <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6">
            <p class="font-bold">Booking Successful!</p>
            <p>Your booking ID is: <?php echo $booking_id; ?></p>
        </div>

        <!-- Booking Details -->
        <div class="bg-white p-6 rounded-lg shadow-md mb-6">
            <h2 class="text-xl font-bold mb-4">Journey Details</h2>
            <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                <div>
                    <p class="text-gray-600">Train</p>
                    <p class="font-semibold"><?php echo htmlspecialchars($booking['train_name']); ?> (<?php echo htmlspecialchars($booking['train_number']); ?>)</p>
                </div>
                <div>
                    <p class="text-gray-600">From - To</p>
                    <p class="font-semibold"><?php echo htmlspecialchars($booking['source_location']); ?> - <?php echo htmlspecialchars($booking['destination_location']); ?></p>
                </div>
                <div>
                    <p class="text-gray-600">Travel Date</p>
                    <p class="font-semibold"><?php echo date('d M Y', strtotime($booking['departure_time'])); ?></p>
                </div>
            </div>
        </div>

        <!-- Passengers -->
        <div class="bg-white p-6 rounded-lg shadow-md mb-6">
            <h2 class="text-xl font-bold mb-4">Passengers</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <?php foreach ($passengers as $passenger): ?>
                    <div class="bg-gray-100 p-4 rounded-lg">
                        <p class="font-semibold"><?php echo htmlspecialchars($passenger['name']); ?></p>
                        <p class="text-gray-600"><?php echo htmlspecialchars($passenger['age']); ?> years, <?php echo htmlspecialchars($passenger['gender']); ?></p>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="mt-8">
            <a href="dashboard.php" class="text-blue-500 hover:underline">Back to Dashboard</a>
        </div>
    </div>
</body>
</html>
