<?php

function getWalletBalance($user_id, $conn) {
    $query = "SELECT balance FROM wallet WHERE user_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $wallet = $result->fetch_assoc();
    return $wallet ? $wallet['balance'] : 0;
} 