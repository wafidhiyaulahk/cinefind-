<?php
require_once 'service/database.php';

// Read the SQL file
$sql = file_get_contents('cinefind_optimized.sql');

// Split the SQL file into individual queries
$queries = array_filter(array_map('trim', explode(';', $sql)));

$success = true;
$errors = array();

// Execute each query
foreach ($queries as $query) {
    if (empty($query)) continue;
    
    try {
        if (!$conn->query($query)) {
            throw new Exception($conn->error);
        }
    } catch (Exception $e) {
        $success = false;
        $errors[] = "Error executing query: " . $e->getMessage() . "\nQuery: " . $query;
    }
}

// Output results
if ($success) {
    echo "Database setup completed successfully!\n";
    echo "Created/Modified tables:\n";
    echo "- film_genre\n";
    echo "- film (added rating_avg and rating_count)\n";
    echo "- watchlist (optimized)\n";
    echo "- review (optimized)\n";
    echo "- pengguna (optimized)\n";
    echo "\nAdded sample data for testing.\n";
} else {
    echo "Errors occurred during database setup:\n";
    foreach ($errors as $error) {
        echo "- " . $error . "\n";
    }
}

$conn->close();
?> 