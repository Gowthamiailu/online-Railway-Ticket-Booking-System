<?php
session_start();
require_once 'db_connection.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: book_ticket.php");
    exit();
}

$from_location = $_POST['from_location'];
$to_location = $_POST['to_location'];
$travel_date = $_POST['travel_date'];
$ticket_category = $_POST['ticket_category'];
$num_tickets = $_POST['num_tickets'];

// Fetch available trains with simplified query
$stmt = $conn->prepare("
    SELECT * FROM trains 
    WHERE source_location = ? 
    AND destination_location = ?
");

$stmt->bind_param("ss", $from_location, $to_location);
$stmt->execute();
$result = $stmt->get_result();
$trains = $result->fetch_all(MYSQLI_ASSOC);

// Get price and seats field based on ticket category
$price_field = $ticket_category . '_price';
$seats_field = $ticket_category . '_seats';

// Debug information
echo "<!-- Debug Info:
From: $from_location
To: $to_location
Category: $ticket_category
Number of tickets: $num_tickets
Number of trains found: " . count($trains) . "
-->";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Available Trains</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100">
    <div class="container mx-auto px-4 py-8">
        <h1 class="text-3xl font-bold mb-8">Available Trains</h1>

        <!-- Search Details -->
        <div class="bg-white p-6 rounded-lg shadow-md mb-6">
            <h2 class="text-xl font-bold mb-4">Search Details</h2>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div>
                    <p class="text-gray-600">From</p>
                    <p class="font-semibold"><?php echo htmlspecialchars($from_location); ?></p>
                </div>
                <div>
                    <p class="text-gray-600">To</p>
                    <p class="font-semibold"><?php echo htmlspecialchars($to_location); ?></p>
                </div>
                <div>
                    <p class="text-gray-600">Date</p>
                    <p class="font-semibold"><?php echo htmlspecialchars($travel_date); ?></p>
                </div>
                <div>
                    <p class="text-gray-600">Category</p>
                    <p class="font-semibold"><?php echo ucfirst(str_replace('_', ' ', $ticket_category)); ?></p>
                </div>
            </div>
        </div>

        <?php if (empty($trains)): ?>
            <div class="bg-yellow-100 border-l-4 border-yellow-500 text-yellow-700 p-4">
                No trains available for the selected route and date.
            </div>
        <?php else: ?>
            <div class="grid gap-6">
                <?php foreach ($trains as $train): ?>
                    <?php if ($train[$seats_field] >= $num_tickets): ?>
                        <div class="bg-white p-6 rounded-lg shadow-md">
                            <div class="flex justify-between items-center mb-4">
                                <h2 class="text-xl font-bold"><?php echo htmlspecialchars($train['train_name']); ?></h2>
                                <span class="text-gray-600">#<?php echo htmlspecialchars($train['train_number']); ?></span>
                            </div>
                            
                            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-4">
                                <div>
                                    <p class="text-gray-600">Departure</p>
                                    <p class="font-semibold"><?php echo date('h:i A', strtotime($train['departure_time'])); ?></p>
                                </div>
                                <div>
                                    <p class="text-gray-600">Arrival</p>
                                    <p class="font-semibold"><?php echo date('h:i A', strtotime($train['arrival_time'])); ?></p>
                                </div>
                                <div>
                                    <p class="text-gray-600">Price per Ticket</p>
                                    <p class="font-semibold">₹<?php echo number_format($train[$price_field], 2); ?></p>
                                </div>
                                <div>
                                    <p class="text-gray-600">Available Seats</p>
                                    <p class="font-semibold"><?php echo $train[$seats_field]; ?></p>
                                </div>
                            </div>

                            <div class="flex justify-between items-center">
                                <p class="text-lg font-bold">
                                    Total: ₹<?php echo number_format($train[$price_field] * $num_tickets, 2); ?>
                                </p>
                                <form action="passenger_details.php" method="POST">
                                    <input type="hidden" name="train_id" value="<?php echo $train['id']; ?>">
                                    <input type="hidden" name="journey_date" value="<?php echo $travel_date; ?>">
                                    <input type="hidden" name="num_tickets" value="<?php echo $num_tickets; ?>">
                                    <input type="hidden" name="ticket_category" value="<?php echo $ticket_category; ?>">
                                    <input type="hidden" name="total_fare" value="<?php echo $train[$price_field] * $num_tickets; ?>">
                                    <button type="submit" class="bg-green-500 text-white font-bold py-2 px-4 rounded-lg hover:bg-green-600">
                                        Book Now
                                    </button>
                                </form>
                            </div>
                        </div>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <div class="mt-8">
            <a href="book_ticket.php" class="text-blue-500 hover:underline">Back to Search</a>
        </div>
    </div>
</body>
</html> 