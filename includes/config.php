<?php
session_start();
$host = $_SERVER['HTTP_HOST'] ?? '';
if (strpos($host, 'localhost') !== false || strpos($host, '127.0.0.1') !== false) {
    // Localhost XAMPP credentials
    $conn = mysqli_connect("localhost", "root", "", "db_ghealth");
} else {
    // InfinityFree Live credentials
    $conn = mysqli_connect("sql210.infinityfree.com", "if0_42394947", "JNTPCCkOUESli", "if0_42394947_db_ghealth");
}
if (!$conn) {
    die("Connection Failed: " . mysqli_connect_error());
}
mysqli_set_charset($conn, "utf8mb4");

// Ensure audit_logs table exists
$createTableQuery = "CREATE TABLE IF NOT EXISTS audit_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    admin_email VARCHAR(255) NOT NULL,
    action VARCHAR(255) NOT NULL,
    timestamp DATETIME DEFAULT CURRENT_TIMESTAMP
)";
mysqli_query($conn, $createTableQuery);

function logAudit($conn, $admin_email, $action)
{
    $admin_email = mysqli_real_escape_string($conn, $admin_email);
    $action = mysqli_real_escape_string($conn, $action);
    $timestamp = date('Y-m-d H:i:s');
    $query = "INSERT INTO audit_logs (admin_email, action, timestamp) VALUES ('$admin_email', '$action', '$timestamp')";
    mysqli_query($conn, $query);
}
?>