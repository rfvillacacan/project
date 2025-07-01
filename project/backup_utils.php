<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

function createBackup($sourceFile) {
    // Create backup directory if it doesn't exist
    $backupDir = __DIR__ . '/backup';
    if (!file_exists($backupDir)) {
        if (!mkdir($backupDir, 0777, true)) {
            return "Failed to create backup directory";
        }
    }

    // Get file info
    $fileInfo = pathinfo($sourceFile);
    $timestamp = date('Y-m-d_H-i-s');
    
    // Create backup filename with version and timestamp
    $backupFilename = $fileInfo['filename'] . '_v1_' . $timestamp . '.' . $fileInfo['extension'];
    $backupPath = $backupDir . '/' . $backupFilename;

    // Copy file to backup location
    if (copy($sourceFile, $backupPath)) {
        return $backupPath;
    }
    return "Failed to copy file to backup location";
}

// Function to be called before any file modification
function backupFile($filePath) {
    if (file_exists($filePath)) {
        $backupPath = createBackup($filePath);
        if (is_string($backupPath) && strpos($backupPath, 'Failed') === false) {
            return "Backup created successfully at: " . $backupPath;
        } else {
            return "Failed to create backup: " . $backupPath;
        }
    }
    return "File does not exist: " . $filePath;
}

// Test the backup functionality
if (isset($_GET['test'])) {
    $testFile = __FILE__; // Backup this file as a test
    $result = backupFile($testFile);
    echo $result;
} 
