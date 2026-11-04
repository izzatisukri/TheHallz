<?php
include 'db_connection.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $step = $_POST['step'];
    $email = $_POST['email'];

    if ($step == '1') {
        $found = false;
        $role = '';

        $stmt = $conn->prepare("SELECT client_id FROM client WHERE client_email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        if ($stmt->get_result()->num_rows > 0) { $found = true; $role = 'client'; }

        if (!$found) {
            $stmt = $conn->prepare("SELECT owner_id FROM venue_owner WHERE owner_email = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            if ($stmt->get_result()->num_rows > 0) { $found = true; $role = 'venue_owner'; }
        }

        if (!$found) {
            $stmt = $conn->prepare("SELECT admin_id FROM admin WHERE admin_email = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            if ($stmt->get_result()->num_rows > 0) { $found = true; $role = 'admin'; }
        }

        if ($found) {
            echo "<script>alert('Account found as $role!');</script>";
        } else {
            echo "<script>alert('Email not found!'); history.back();</script>";
        }
    } 
    
    elseif ($step == '2') {
        $new_password = password_hash($_POST['new_password'], PASSWORD_DEFAULT);
        $role = $_POST['role'];

        if ($role == 'client') {
            $sql = "UPDATE client SET client_password = ? WHERE client_email = ?";
        } elseif ($role == 'venue_owner') {
            $sql = "UPDATE venue_owner SET owner_password = ? WHERE owner_email = ?";
        } else {
            $sql = "UPDATE admin SET admin_password = ? WHERE admin_email = ?";
        }

        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $new_password, $email);
        
        if ($stmt->execute()) {
            echo "<script>alert('Password updated!'); window.location.href='login.php';</script>";
        }
    }
}
?>