<?php
$host = "localhost";
$user = "root";
$pass = "";
$db   = "hygeadb"; // MUST match your database name

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}
