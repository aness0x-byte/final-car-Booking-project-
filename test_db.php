<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
echo "<h2>Testing MySQL connection...</h2>";
$hosts = ['localhost', '127.0.0.1'];
$ports = [3307, 3306];
foreach ($hosts as $host) {
    foreach ($ports as $port) {
        try {
            $dsn = "mysql:host=$host;port=$port;charset=utf8mb4";
            $pdo = new PDO($dsn, 'root', '', [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
            echo "<p style='color:green'>✅ Connected! Host: $host | Port: $port</p>";
        } catch (PDOException $e) {
            echo "<p style='color:red'>❌ Failed — $host:$port → " . htmlspecialchars($e->getMessage()) . "</p>";
        }
    }
}
?>