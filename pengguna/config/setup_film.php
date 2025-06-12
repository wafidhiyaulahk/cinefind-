<?php
require_once '../service/database.php';

// Read the SQL file
$sql = file_get_contents('setup_film.sql');

// Split the SQL file into individual queries
$queries = array_filter(array_map('trim', explode(';', $sql)));

// Execute each query
$success = true;
$errors = [];

foreach ($queries as $query) {
    if (!empty($query)) {
        try {
            if (!$conn->query($query)) {
                throw new Exception($conn->error);
            }
        } catch (Exception $e) {
            $success = false;
            $errors[] = "Error executing query: " . $e->getMessage();
        }
    }
}

// Output results
if ($success) {
    echo "Database setup completed successfully!\n";
    echo "Created and populated the following tables:\n";
    echo "- film\n";
    echo "- film_cast\n";
    echo "- film_genre\n";
    echo "- film_rating\n";
    echo "- film_review\n";
    echo "- film_award\n";
} else {
    echo "Database setup encountered errors:\n";
    foreach ($errors as $error) {
        echo "- " . $error . "\n";
    }
}

$conn->close();
?> 