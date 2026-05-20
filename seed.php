<?php
require_once 'config/db.php';
$db = getDB();

echo "Seeding database with local cars...\n\n";

// Clear existing data for a clean state
$db->exec("DELETE FROM ratings");
$db->exec("DELETE FROM reservations");
$db->exec("DELETE FROM cars");
echo "Cleared existing cars.\n";

// All 6 cars with 100% local image paths
$cars = [
    [
        'make'              => 'Mercedes-Benz',
        'model'             => 'S-Class',
        'model_year'        => 2023,
        'number_of_seats'   => 4,
        'category'          => 'Luxury',
        'fuel_type'         => 'Gas',
        'transmission_type' => 'Automatic',
        'daily_rate'        => 12000,
        'booking_status'    => 'available',
        'image_url'         => 'images/cars/mercedes_s_class.png',
    ],
    [
        'make'              => 'Porsche',
        'model'             => '911',
        'model_year'        => 2023,
        'number_of_seats'   => 2,
        'category'          => 'Sports',
        'fuel_type'         => 'Gas',
        'transmission_type' => 'Automatic',
        'daily_rate'        => 18000,
        'booking_status'    => 'available',
        'image_url'         => 'images/cars/porsche_911.png',
    ],
    [
        'make'              => 'Toyota',
        'model'             => 'Land Cruiser',
        'model_year'        => 2022,
        'number_of_seats'   => 7,
        'category'          => 'SUV',
        'fuel_type'         => 'Diesel',
        'transmission_type' => 'Automatic',
        'daily_rate'        => 9000,
        'booking_status'    => 'available',
        'image_url'         => 'images/cars/toyota_land_cruiser.png',
    ],
    [
        'make'              => 'Renault',
        'model'             => 'Clio',
        'model_year'        => 2021,
        'number_of_seats'   => 5,
        'category'          => 'Economy',
        'fuel_type'         => 'Gas',
        'transmission_type' => 'Manual',
        'daily_rate'        => 2500,
        'booking_status'    => 'available',
        'image_url'         => 'images/cars/renault_clio.png',
    ],
    [
        'make'              => 'Volkswagen',
        'model'             => 'Touareg',
        'model_year'        => 2022,
        'number_of_seats'   => 7,
        'category'          => 'SUV',
        'fuel_type'         => 'Diesel',
        'transmission_type' => 'Automatic',
        'daily_rate'        => 7500,
        'booking_status'    => 'available',
        'image_url'         => 'images/cars/volkswagen_touareg.png',
    ],
    [
        'make'              => 'Toyota',
        'model'             => 'Hilux',
        'model_year'        => 2023,
        'number_of_seats'   => 5,
        'category'          => 'Pickup',
        'fuel_type'         => 'Diesel',
        'transmission_type' => 'Manual',
        'daily_rate'        => 5000,
        'booking_status'    => 'available',
        'image_url'         => 'images/cars/toyota_hilux.png',
    ],
];

$stmt = $db->prepare("
    INSERT INTO cars (make, model, model_year, number_of_seats, category, fuel_type, transmission_type, daily_rate, booking_status, image_url)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
");

foreach ($cars as $car) {
    $stmt->execute([
        $car['make'], $car['model'], $car['model_year'], $car['number_of_seats'],
        $car['category'], $car['fuel_type'], $car['transmission_type'],
        $car['daily_rate'], $car['booking_status'], $car['image_url']
    ]);
    echo "Inserted: " . $car['make'] . " " . $car['model'] . " (" . $car['category'] . ") — " . number_format($car['daily_rate']) . " DZD/day\n";
}

echo "\nAll 6 cars inserted successfully with local images!\n";
echo "Images are stored in: c:\\xampp\\htdocs\\php_car_rental_anes\\images\\cars\\\n";
