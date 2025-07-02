<?php
require_once 'includes/config.php';
header('Content-Type: application/json');

// Get counts by status
$statusCounts = [
  'completed' => 0,
  'inprogress' => 0,
  'pending' => 0
];
$total = 0;
$res = $conn->query("SELECT status, COUNT(*) as cnt FROM daily_tasks GROUP BY status");
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

// Get latest 5 tasks
$latest = [];
$res = $conn->query("SELECT datetime, task_description, status, assigned_to, due_date, priority FROM daily_tasks ORDER BY datetime DESC LIMIT 5");
while ($row = $res->fetch_assoc()) {
  // Format datetime for display (short date)
  $dt = date('M j, Y', strtotime($row['datetime']));
  $latest[] = [
    'datetime' => $dt,
    'task_description' => $row['task_description'],
    'status' => ucfirst($row['status']),
    'assigned_to' => $row['assigned_to'],
    'due_date' => $row['due_date'],
    'priority' => $row['priority']
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
