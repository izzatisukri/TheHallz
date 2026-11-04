<?php
$host = "localhost";
$user = "root";
$pass = "";
$db   = "wedding_db";
$port = 3307;
$conn = new mysqli($host, $user, $pass, $db, $port);

if ($conn->connect_error) {
    die("Connection Failed: " . $conn->connect_error);
}
?>