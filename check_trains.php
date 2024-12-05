<?php

// ... existing code ...
$query = "SELECT t.*, 
          (SELECT COUNT(*) FROM tickets 
           WHERE train_id = t.train_id 
           AND journey_date = ?) as booked_seats
          FROM trains t 
          WHERE t.source = ? AND t.destination = ?";

$stmt = $conn->prepare($query);
$stmt->bind_param("sss", $journey_date, $source, $destination);
$stmt->execute();
$result = $stmt->get_result();

while($row = $result->fetch_assoc()) {
    // Compare booked seats with total seats
    $available_seats = $row['total_seats'] - $row['booked_seats'];
    $row['available'] = ($available_seats > 0) ? 'Available' : 'Not Available';
    $row['available_seats'] = $available_seats;
    // ... rest of the display code ...
} 