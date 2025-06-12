<?php
require_once 'database.php';

// Read and execute the SQL file
try {
    $sql = file_get_contents(__DIR__ . '/cinefind.sql');
    
    // Split the SQL file into individual queries
    $queries = array_filter(array_map('trim', explode(';', $sql)));
    
    // Execute each query
    foreach ($queries as $query) {
        if (!empty($query)) {
            if (!$conn->query($query)) {
                throw new Exception("Error executing query: " . $conn->error . "\nQuery: " . $query);
            }
        }
    }
    
    echo "Database setup completed successfully!";
    
} catch (Exception $e) {
    echo "Error setting up database: " . $e->getMessage();
}

$conn->close();
?> 