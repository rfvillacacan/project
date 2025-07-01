<?php
require_once 'includes/config.php';
header('Content-Type: application/json');

// Get all services
$result = $conn->query("SELECT id, service, due_date, progress, comments FROM service_progress ORDER BY id DESC");
$rows = array();
$total = 0;
$completed = 0;
$inprogress = 0;
$latest = array();
while($row = $result->fetch_assoc()) {
    $total++;
    $progressNum = intval($row['progress']);
    if ($progressNum === 100) {
        $completed++;
        $status = 'completed';
    } else {
        $inprogress++;
        $status = 'inprogress';
    }
    // Format date
    $formattedDate = $row['due_date'];
    if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $row['due_date'])) {
        $d = DateTime::createFromFormat('Y-m-d', $row['due_date']);
        if ($d) $formattedDate = $d->format('M j, Y');
    }
    $latest[] = array(
        'datetime' => $formattedDate,
        'service' => $row['service'],
        'status' => $status
    );
}
$completion_rate = $total > 0 ? round(($completed / $total) * 100) : 0;
echo json_encode([
    'total' => $total,
    'completed' => $completed,
    'inprogress' => $inprogress,
    'completion_rate' => $completion_rate,
    'latest' => $latest
]); 
