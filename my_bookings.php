<?php
session_start();
require_once 'db_connection.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch all bookings for the user
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
    WHERE b.user_id = ?
    ORDER BY b.booking_date DESC
";

$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$bookings = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Bookings</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100">
    <div class="container mx-auto px-4 py-8">
        <div class="flex justify-between items-center mb-8">
            <h1 class="text-3xl font-bold">My Bookings</h1>
            <a href="dashboard.php" class="text-blue-500 hover:underline">Back to Dashboard</a>
        </div>

        <?php if (isset($_SESSION['success_message'])): ?>
            <div class="mb-4 p-4 bg-green-100 text-green-700 rounded-lg">
                <?php 
                echo htmlspecialchars($_SESSION['success_message']); 
                unset($_SESSION['success_message']);
                ?>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['error_message'])): ?>
            <div class="mb-4 p-4 bg-red-100 text-red-700 rounded-lg">
                <?php 
                echo htmlspecialchars($_SESSION['error_message']); 
                unset($_SESSION['error_message']);
                ?>
            </div>
        <?php endif; ?>

        <?php if (empty($bookings)): ?>
            <div class="bg-white p-6 rounded-lg shadow-md text-center">
                <p class="text-gray-600">No bookings found.</p>
                <a href="book_ticket.php" class="inline-block mt-4 bg-blue-500 text-white px-4 py-2 rounded-lg hover:bg-blue-600">
                    Book a Ticket
                </a>
            </div>
        <?php else: ?>
            <div class="grid gap-6">
                <?php foreach ($bookings as $booking): ?>
                    <?php
                    // Fetch passengers for this booking
                    $passenger_query = "SELECT * FROM passengers WHERE booking_id = ?";
                    $stmt = $conn->prepare($passenger_query);
                    $stmt->bind_param("i", $booking['id']);
                    $stmt->execute();
                    $passengers = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
                    ?>
                    <div class="bg-white p-6 rounded-lg shadow-md">
                        <div class="grid md:grid-cols-2 gap-4 mb-4">
                            <div>
                                <h2 class="text-xl font-bold mb-2">
                                    <?php echo htmlspecialchars($booking['train_name']); ?> 
                                    (<?php echo htmlspecialchars($booking['train_number']); ?>)
                                </h2>
                                <p class="text-gray-600">
                                    Booking ID: <?php echo $booking['id']; ?><br>
                                    Booked on: <?php echo date('d M Y, h:i A', strtotime($booking['booking_date'])); ?>
                                </p>
                            </div>
                            <div class="text-right">
                                <p class="text-lg font-bold text-green-600">
                                    ₹<?php echo number_format($booking['total_amount'], 2); ?>
                                </p>
                                <p class="text-gray-600">
                                    <?php echo ucfirst($booking['ticket_category']); ?> Class<br>
                                    <?php echo $booking['num_tickets']; ?> Ticket(s)
                                </p>
                            </div>
                        </div>

                        <div class="border-t pt-4">
                            <div class="grid md:grid-cols-2 gap-4">
                                <div>
                                    <p class="text-gray-600">Journey</p>
                                    <p class="font-semibold">
                                        <?php echo htmlspecialchars($booking['source_location']); ?> to 
                                        <?php echo htmlspecialchars($booking['destination_location']); ?>
                                    </p>
                                </div>
                                <div>
                                    <p class="text-gray-600">Timing</p>
                                    <p class="font-semibold">
                                        <?php echo date('h:i A', strtotime($booking['departure_time'])); ?> - 
                                        <?php echo date('h:i A', strtotime($booking['arrival_time'])); ?>
                                    </p>
                                </div>
                            </div>
                        </div>

                        <?php if (!empty($passengers)): ?>
                            <div class="border-t mt-4 pt-4">
                                <h3 class="font-bold mb-2">Passengers</h3>
                                <div class="grid gap-2">
                                    <?php foreach ($passengers as $passenger): ?>
                                        <div class="flex justify-between items-center">
                                            <span><?php echo htmlspecialchars($passenger['name']); ?></span>
                                            <span class="text-gray-600">
                                                <?php echo $passenger['age']; ?> yrs, 
                                                <?php echo match($passenger['gender']) {
                                                    'M' => 'Male',
                                                    'F' => 'Female',
                                                    'O' => 'Other',
                                                    default => $passenger['gender']
                                                }; ?>
                                            </span>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endif; ?>

                        <div class="mt-4 flex justify-end space-x-4">
                            <a href="view_ticket.php?booking_id=<?php echo $booking['id']; ?>" 
                               class="inline-block bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
                                View Ticket
                            </a>
                            <?php if (strtotime($booking['booking_date']) > strtotime('-24 hours')): ?>
                                <form method="POST" action="cancel_booking.php" class="inline" 
                                      onsubmit="return confirm('Are you sure you want to cancel this booking?');">
                                    <input type="hidden" name="booking_id" value="<?php echo $booking['id']; ?>">
                                    <button type="submit" 
                                            class="bg-red-500 text-white px-4 py-2 rounded hover:bg-red-600">
                                        Cancel Booking
                                    </button>
                                </form>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</body>
</html> 