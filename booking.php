<?php

// ... existing code ...
if(isset($_POST['train_id']) && isset($_POST['journey_date'])) {
    $train_id = $_POST['train_id'];
    $journey_date = $_POST['journey_date'];
    
    // Get train details
    $query = "SELECT * FROM trains WHERE train_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $train_id);
    $stmt->execute();
    $train = $stmt->get_result()->fetch_assoc();
    
    if(!$train) {
        echo "Train not found";
        exit();
    }
    
    // Check available seats
    $query = "SELECT COUNT(*) as booked_seats FROM tickets 
              WHERE train_id = ? AND journey_date = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("is", $train_id, $journey_date);
    $stmt->execute();
    $booked = $stmt->get_result()->fetch_assoc();
    
    $available_seats = $train['total_seats'] - $booked['booked_seats'];
    
    if($available_seats <= 0) {
        echo "No seats available";
        exit();
    }
} 