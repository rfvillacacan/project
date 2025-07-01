<?php
require_once 'includes/config.php';

// Get all tables
$tables = $conn->query("SHOW TABLES");

// Set content type to plain text
header('Content-Type: text/plain');

echo "DATABASE STRUCTURE\n";
echo "==================\n\n";

while ($table = $tables->fetch_array()) {
    $tableName = $table[0];
    echo "TABLE: " . $tableName . "\n";
    echo str_repeat("-", strlen($tableName) + 7) . "\n";
    
    // Get table structure
    $fields = $conn->query("DESCRIBE `$tableName`");
    
    while ($field = $fields->fetch_assoc()) {
        echo "  Field: " . $field['Field'] . "\n";
        echo "    Type: " . $field['Type'] . "\n";
        echo "    Null: " . $field['Null'] . "\n";
        echo "    Key: " . $field['Key'] . "\n";
        echo "    Default: " . ($field['Default'] ?? 'NULL') . "\n";
        echo "    Extra: " . $field['Extra'] . "\n\n";
    }
    echo "\n";
}
?> 
