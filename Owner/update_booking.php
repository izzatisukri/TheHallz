<?php
include 'db_connection.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['booking_id']) && isset($_POST['status'])) {
    
    $booking_id = $_POST['booking_id'];
    $status = $_POST['status'];

    $stmt = $conn->prepare("UPDATE booking SET bookingstatus = ? WHERE booking_id = ?");
    $stmt->bind_param("si", $status, $booking_id);
    
    if ($stmt->execute()) {
        header("Location: owner_panel.php");
        exit();
    } else {
        echo "Error: " . $stmt->error;
    }
    
    $stmt->close();
}
?>