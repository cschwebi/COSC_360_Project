<?php
require_once 'configure.php';
header('Content-Type: application/json');

try {
    $pdo = new PDO(DB_DSN, DB_USER, DB_PASS, PDO_OPTIONS);
    
    // Fetch latest 5 blog posts
    $sql = "SELECT title, LEFT(content, 280) AS content, image FROM blogs ORDER BY created_at DESC LIMIT 5";
    $stmt = $pdo->query($sql);
    $blogs = $stmt->fetchAll();
    
    echo json_encode($blogs);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["error" => "Database connection failed"]);
}
?>
