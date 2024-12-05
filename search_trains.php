<?php
session_start();
require_once 'db_connection.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] != 'POST') {
    header("Location: book_ticket.php");
    exit();
}

$source = $_POST['source'];
$destination = $_POST['destination'];
$travel_date = $_POST['travel_date'];

// Fetch available trains
$stmt = $conn->prepare("
    SELECT * FROM trains 
    WHERE source = ? 
    AND destination = ? 
    AND travel_date = ?
    AND available_seats > 0
");
$stmt->bind_param("sss", $source, $destination, $travel_date);
$stmt->execute();
$trains = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
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
        
        <?php if (empty($trains)): ?>
            <div class="bg-yellow-100 border-l-4 border-yellow-500 text-yellow-700 p-4 mb-4">
                No trains available for the selected route and date.
            </div>
        <?php else: ?>
            <div class="grid gap-6">
                <?php foreach ($trains as $train): ?>
                    <div class="bg-white p-6 rounded-lg shadow-md">
                        <div class="flex justify-between items-center mb-4">
                            <h2 class="text-xl font-bold"><?php echo htmlspecialchars($train['train_name']); ?></h2>
                            <span class="text-gray-600">#<?php echo htmlspecialchars($train['train_number']); ?></span>
                        </div>
                        
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-4">
                            <div>
                                <p class="text-gray-600">Departure</p>
                                <p class="font-semibold"><?php echo htmlspecialchars($train['departure_time']); ?></p>
                            </div>
                            <div>
                                <p class="text-gray-600">Arrival</p>
                                <p class="font-semibold"><?php echo htmlspecialchars($train['arrival_time']); ?></p>
                            </div>
                            <div>
                                <p class="text-gray-600">Available Seats</p>
                                <p class="font-semibold"><?php echo htmlspecialchars($train['available_seats']); ?></p>
                            </div>
                            <div>
                                <p class="text-gray-600">Price</p>
                                <p class="font-semibold">$<?php echo number_format($train['price'], 2); ?></p>
                            </div>
                        </div>
                        
                        <form action="passenger_details.php" method="POST">
                            <input type="hidden" name="train_id" value="<?php echo $train['id']; ?>">
                            <input type="hidden" name="travel_date" value="<?php echo htmlspecialchars($travel_date); ?>">
                            <button type="submit" class="bg-green-500 text-white font-bold py-2 px-4 rounded-lg hover:bg-green-600">
                                Select Train
                            </button>
                        </form>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <div class="mt-8">
            <a href="book_ticket.php" class="text-blue-500 hover:underline">Back to Search</a>
        </div>
    </div>
</body>
</html> 