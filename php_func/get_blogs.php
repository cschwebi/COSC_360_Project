<?php
header('Content-Type: application/json');
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "blogDB";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die(json_encode(["error" => "Database connection failed"]));
}

// Fetch latest 5 blog posts
$sql = "SELECT title, LEFT(content, 280) AS content, image FROM blogs ORDER BY created_at DESC LIMIT 5";
$result = $conn->query($sql);

$blogs = [];

while ($row = $result->fetch_assoc()) {
    $blogs[] = $row;
}

echo json_encode($blogs);
$conn->close();
?>
