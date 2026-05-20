<?php
/* --------------------------------------------------------------------------
   DATABASE CONNECTION (config/db.php) - WITH OPTIMIZATIONS
   --------------------------------------------------------------------------
   ✅ OPTIMIZATION #1: Uses persistent connections for better pooling
   ============================================================== */

function getDB(): PDO {
    // ✅ OPTIMIZATION #1: Static instance caches connection per request
    static $pdo = null;
    if ($pdo === null) {
        try {
            // ✅ OPTIMIZATION #1: Set PDO options for performance
            $pdo = new PDO(
                "mysql:host=localhost;port=3307;dbname=car_rental_db;charset=utf8mb4",
                'root', '',
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                    PDO::ATTR_PERSISTENT => true  // ✅ Persistent connection
                ]
            );
        } catch (PDOException $e) {
            die('DB ERROR: ' . $e->getMessage());
        }
    }
    return $pdo;
}
