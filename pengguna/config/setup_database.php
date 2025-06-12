<?php
require_once '../service/database.php';

// Read the SQL file
$sql = file_get_contents('setup_database.sql');

// Execute multi query
if ($conn->multi_query($sql)) {
    do {
        // Store first result set
        if ($result = $conn->store_result()) {
            $result->free();
        }
    } while ($conn->more_results() && $conn->next_result());
    
    echo "Database setup completed successfully!";
} else {
    echo "Error setting up database: " . $conn->error;
}

$conn->close();
?> 