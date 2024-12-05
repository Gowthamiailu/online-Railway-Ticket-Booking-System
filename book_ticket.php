<?php
session_start();
require_once 'db_connection.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch available locations for dropdowns
$locations_query = "SELECT DISTINCT location_name FROM locations ORDER BY location_name";
$locations = $conn->query($locations_query)->fetch_all(MYSQLI_ASSOC);

// Fetch wallet balance
$stmt = $conn->prepare("SELECT balance FROM wallet WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$wallet = $stmt->get_result()->fetch_assoc();
$balance = $wallet ? $wallet['balance'] : 0;

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book Travel Ticket</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100">
    <div class="container mx-auto px-4 py-8">
        <h1 class="text-3xl font-bold mb-8">Book Travel Ticket</h1>
        
        <div class="bg-white p-6 rounded-lg shadow-md mb-6">
            <h2 class="text-xl font-bold mb-4">Wallet Balance: $<?php echo number_format($balance, 2); ?></h2>
        </div>

        <form id="search-form" method="POST" action="search_tickets.php" class="bg-white p-6 rounded-lg shadow-md">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="mb-6">
                    <label for="from_location" class="block text-gray-700 font-bold mb-2">From</label>
                    <select id="from_location" name="from_location" required class="w-full px-3 py-2 border rounded-lg">
                        <option value="">Select Source Location</option>
                        <?php foreach ($locations as $location): ?>
                            <option value="<?php echo htmlspecialchars($location['location_name']); ?>">
                                <?php echo htmlspecialchars($location['location_name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="mb-6">
                    <label for="to_location" class="block text-gray-700 font-bold mb-2">To</label>
                    <select id="to_location" name="to_location" required class="w-full px-3 py-2 border rounded-lg">
                        <option value="">Select Destination Location</option>
                        <?php foreach ($locations as $location): ?>
                            <option value="<?php echo htmlspecialchars($location['location_name']); ?>">
                                <?php echo htmlspecialchars($location['location_name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="mb-6">
                <label for="travel_date" class="block text-gray-700 font-bold mb-2">Travel Date</label>
                <input type="date" 
                       id="travel_date" 
                       name="travel_date" 
                       required 
                       min="<?php echo date('Y-m-d'); ?>"
                       class="w-full px-3 py-2 border rounded-lg">
            </div>

            <div class="mb-6">
                <label for="ticket_category" class="block text-gray-700 font-bold mb-2">Ticket Category</label>
                <select id="ticket_category" name="ticket_category" required class="w-full px-3 py-2 border rounded-lg">
                    <option value="">Select Ticket Category</option>
                    <option value="first_class">First Class</option>
                    <option value="second_class">Second Class</option>
                    <option value="general">General</option>
                </select>
            </div>

            <div class="mb-6">
                <label for="num_tickets" class="block text-gray-700 font-bold mb-2">Number of Tickets</label>
                <input type="number" 
                       id="num_tickets" 
                       name="num_tickets" 
                       min="1" 
                       max="6" 
                       required 
                       class="w-full px-3 py-2 border rounded-lg">
                <p class="text-sm text-gray-600 mt-1">Maximum 6 tickets per booking</p>
            </div>

            <button type="submit" class="bg-blue-500 text-white font-bold py-2 px-4 rounded-lg hover:bg-blue-600">
                Search Available Trains
            </button>
        </form>

        <div class="mt-8">
            <a href="dashboard.php" class="text-blue-500 hover:underline">Back to Dashboard</a>
        </div>
    </div>

    <script>
    // Add client-side validation
    document.getElementById('search-form').addEventListener('submit', function(e) {
        const fromLocation = document.getElementById('from_location').value;
        const toLocation = document.getElementById('to_location').value;
        const numTickets = document.getElementById('num_tickets').value;

        if (fromLocation === toLocation) {
            e.preventDefault();
            alert('Source and destination locations cannot be the same.');
            return;
        }

        if (numTickets < 1 || numTickets > 6) {
            e.preventDefault();
            alert('Please select between 1 and 6 tickets.');
            return;
        }
    });
    </script>
</body>
</html>
