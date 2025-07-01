<?php
require_once 'includes/config.php';
header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 1);

try {
    $conn = db_connect();

    // Get counts by status
    $statusCounts = [
        'completed' => 0,
        'inprogress' => 0,
        'pending' => 0
    ];
    $total = 0;

    // Check if table exists
    $tableCheck = $conn->query("SHOW TABLES LIKE 'service_progress'");
    if ($tableCheck->num_rows === 0) {
        // Create the table if it doesn't exist
        $createTable = "CREATE TABLE IF NOT EXISTS service_progress (
            id INT AUTO_INCREMENT PRIMARY KEY,
            service VARCHAR(255) NOT NULL,
            due_date DATE,
            progress INT DEFAULT 0,
            comments TEXT,
            status VARCHAR(50) DEFAULT 'pending',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )";
        
        if (!$conn->query($createTable)) {
            throw new Exception("Failed to create table: " . $conn->error);
        }

        // Add a test record if the table is empty
        $checkEmpty = $conn->query("SELECT COUNT(*) as cnt FROM service_progress");
        if ($checkEmpty && $checkEmpty->fetch_assoc()['cnt'] == 0) {
            $testRecord = "INSERT INTO service_progress (service, due_date, progress, comments, status) 
                          VALUES ('Test Service', CURDATE(), 50, 'Test comment', 'inprogress')";
            if (!$conn->query($testRecord)) {
                throw new Exception("Failed to insert test record: " . $conn->error);
            }
        }
    }

    $res = $conn->query("SELECT status, COUNT(*) as cnt FROM service_progress GROUP BY status");
    if (!$res) {
        throw new Exception("Query failed: " . $conn->error);
    }

    while ($row = $res->fetch_assoc()) {
        $status = strtolower($row['status']);
        if (isset($statusCounts[$status])) {
            $statusCounts[$status] = (int)$row['cnt'];
        }
        $total += (int)$row['cnt'];
    }

    // Calculate completion rate
    $completed = $statusCounts['completed'];
    $inprogress = $statusCounts['inprogress'];
    $pending = $statusCounts['pending'];
    $completion_rate = $total > 0 ? round(($completed / $total) * 100, 1) : 0;

    // Get latest services
    $latest = [];
    $res = $conn->query("SELECT service, due_date, progress, comments, status FROM service_progress ORDER BY id DESC");
    if (!$res) {
        throw new Exception("Query failed: " . $conn->error);
    }

    while ($row = $res->fetch_assoc()) {
        $latest[] = [
            'service' => $row['service'],
            'due_date' => $row['due_date'],
            'progress' => isset($row['progress']) ? (int)$row['progress'] : 0,
            'comments' => $row['comments'],
            'status' => ucfirst($row['status'])
        ];
    }

    echo json_encode([
        'total' => $total,
        'completed' => $completed,
        'inprogress' => $inprogress,
        'pending' => $pending,
        'completion_rate' => $completion_rate,
        'latest' => $latest
    ]);

} catch (Exception $e) {
    error_log("Service Progress Error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'error' => true,
        'message' => $e->getMessage(),
        'details' => [
            'file' => $e->getFile(),
            'line' => $e->getLine()
        ]
    ]);
}
?> 
