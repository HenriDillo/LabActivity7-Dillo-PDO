<?php
// Local XAMPP settings. Change these if your MySQL account is different.
$host = '127.0.0.1';
$port = 3308; // This computer uses 3308. Standard XAMPP installations use 3306.
$database = 'blog_site';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$database;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
} catch (PDOException $e) {
    http_response_code(500);
    exit('Cannot connect to blog_site. Start MySQL and import database.sql. Check the settings in db.php.');
}
