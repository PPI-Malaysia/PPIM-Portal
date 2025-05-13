<?php
// Database configuration
$host = 'localhost';
$port = 3307;
$dbname = 'ppim_portal';
$username = 'root';
$password = '';
$socket = '/Applications/XAMPP/xamppfiles/var/mysql/mysql.sock'; //delete when implemented in web server

// Create connection
#$conn = new mysqli($host, $username, $password, $dbname, $port);
$conn = new mysqli($host, $username, $password, $dbname, $port, $socket);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>