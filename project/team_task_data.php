<?php
require_once 'includes/config.php';
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    exit;
}

$userId = $_SESSION['user_id'];
$username = $_SESSION['username'];
$tasks = [];

// Daily tasks assigned to the user by username
$stmt = $conn->prepare("SELECT id, task_description AS description, status, priority, due_date, progress FROM daily_tasks WHERE assigned_to=?");
$stmt->bind_param('s', $username);
$stmt->execute();
$res = $stmt->get_result();
while ($row = $res->fetch_assoc()) {
    $row['type'] = 'daily';
    $tasks[] = $row;
}
$stmt->close();

// Project tasks assigned to the user by user_id
$stmt = $conn->prepare("SELECT id, title AS description, status, priority, due_date, progress FROM project_tasks WHERE assigned_to=?");
$stmt->bind_param('i', $userId);
$stmt->execute();
$res = $stmt->get_result();
while ($row = $res->fetch_assoc()) {
    $row['type'] = 'project';
    $tasks[] = $row;
}
$stmt->close();

// Sort by priority High->Medium->Low then due date
$priorityOrder = ['High' => 1, 'Medium' => 2, 'Low' => 3];
usort($tasks, function ($a, $b) use ($priorityOrder) {
    $pa = $priorityOrder[$a['priority']] ?? 4;
    $pb = $priorityOrder[$b['priority']] ?? 4;
    if ($pa === $pb) {
        return strcmp($a['due_date'], $b['due_date']);
    }
    return $pa - $pb;
});

echo json_encode(['data' => $tasks]);
