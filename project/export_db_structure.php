<?php
require_once 'includes/config.php';

// Function to get table structure
function getTableStructure($conn, $tableName) {
    $structure = "Table: $tableName\n";
    $structure .= "----------------------------------------\n";
    
    // Get column information
    $result = $conn->query("DESCRIBE $tableName");
    if ($result) {
        $structure .= "Columns:\n";
        while ($row = $result->fetch_assoc()) {
            $structure .= sprintf(
                "- %s (%s) %s %s %s\n",
                $row['Field'],
                $row['Type'],
                $row['Null'] === 'YES' ? 'NULL' : 'NOT NULL',
                $row['Default'] ? "DEFAULT '{$row['Default']}'" : '',
                $row['Extra']
            );
        }
    }
    
    // Get foreign key information
    $result = $conn->query("
        SELECT 
            COLUMN_NAME,
            REFERENCED_TABLE_NAME,
            REFERENCED_COLUMN_NAME
        FROM
            INFORMATION_SCHEMA.KEY_COLUMN_USAGE
        WHERE
            TABLE_SCHEMA = DATABASE()
            AND TABLE_NAME = '$tableName'
            AND REFERENCED_TABLE_NAME IS NOT NULL
    ");
    
    if ($result && $result->num_rows > 0) {
        $structure .= "\nForeign Keys:\n";
        while ($row = $result->fetch_assoc()) {
            $structure .= sprintf(
                "- %s -> %s.%s\n",
                $row['COLUMN_NAME'],
                $row['REFERENCED_TABLE_NAME'],
                $row['REFERENCED_COLUMN_NAME']
            );
        }
    }
    
    // Get sample data (first 5 rows)
    $result = $conn->query("SELECT * FROM $tableName LIMIT 5");
    if ($result && $result->num_rows > 0) {
        $structure .= "\nSample Data (first 5 rows):\n";
        $structure .= "----------------------------------------\n";
        
        // Get column names
        $columns = array();
        $fields = $result->fetch_fields();
        foreach ($fields as $field) {
            $columns[] = $field->name;
        }
        
        // Reset result pointer
        $result->data_seek(0);
        
        // Print column headers
        $structure .= implode("\t", $columns) . "\n";
        $structure .= str_repeat("-", 80) . "\n";
        
        // Print data rows
        while ($row = $result->fetch_assoc()) {
            $structure .= implode("\t", array_map(function($value) {
                return is_null($value) ? 'NULL' : $value;
            }, $row)) . "\n";
        }
    }
    
    $structure .= "\n\n";
    return $structure;
}

try {
    // Create output file
    $outputFile = 'database_structure.txt';
    $fp = fopen($outputFile, 'w');
    
    // Write header
    fwrite($fp, "Database Structure Export\n");
    fwrite($fp, "Generated on: " . date('Y-m-d H:i:s') . "\n");
    fwrite($fp, "Database: " . DB_NAME . "\n\n");
    
    // Get all tables
    $result = $conn->query("SHOW TABLES");
    if ($result) {
        while ($row = $result->fetch_row()) {
            $tableName = $row[0];
            $tableStructure = getTableStructure($conn, $tableName);
            fwrite($fp, $tableStructure);
        }
    }
    
    fclose($fp);
    echo "Database structure has been exported to $outputFile\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?> 
