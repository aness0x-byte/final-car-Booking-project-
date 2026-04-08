<?php
function getDB(): PDO {
    static $pdo = null;
    if ($pdo === null) {
        try {
            $pdo = new PDO(
                "mysql:host=localhost;port=3307;dbname=driveease;charset=utf8mb4",
                'root', '',
                [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                 PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                 PDO::ATTR_EMULATE_PREPARES => false]
            );
        } catch (PDOException $e) {
            die('DB ERROR: ' . $e->getMessage());
        }
    }
    return $pdo;
}