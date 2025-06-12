<?php
require_once 'database.php';

// Read the SQL file
$sql = file_get_contents(__DIR__ . '/setup_role.sql');

// Execute multi query
if ($conn->multi_query($sql)) {
    do {
        // Store first result set
        if ($result = $conn->store_result()) {
            $result->free();
        }
    } while ($conn->more_results() && $conn->next_result());
    
    echo "Role table setup completed successfully!\n";
    echo "Created/updated the following tables:\n";
    echo "- role\n";
    echo "- pengguna\n";
    echo "\nDefault admin credentials:\n";
    echo "Username: admin\n";
    echo "Password: password\n";
} else {
    echo "Error setting up role tables: " . $conn->error;
}

$conn->close();
?> 