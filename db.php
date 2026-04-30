<?php

$host = "localhost";
$user = "root";
$pass = "root";
$db   = "loom";
$port = 8889;

$conn = mysqli_connect($host, $user, $pass, $db, $port);

if (!$conn) {
    die("Database connection failed: " . mysqli_connect_error());
}
?>