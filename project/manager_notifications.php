<?php
require_once 'includes/config.php';
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    exit;
}

$userId = $_SESSION['user_id'];
$username = $_SESSION['username'];

// Count unseen updates for tasks created by this user
$sql = "SELECT COUNT(*) FROM task_updates tu JOIN daily_tasks dt ON tu.task_type='daily' AND tu.task_id=dt.id WHERE dt.created_by=? AND tu.manager_seen=0 AND tu.user_id<>?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('si', $username, $userId);
$stmt->execute();
$stmt->bind_result($countDaily);
$stmt->fetch();
$stmt->close();

$sql = "SELECT COUNT(*) FROM task_updates tu JOIN project_tasks pt ON tu.task_type='project' AND tu.task_id=pt.id JOIN projects p ON pt.project_id=p.id WHERE p.created_by=? AND tu.manager_seen=0 AND tu.user_id<>?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('ii', $userId, $userId);
$stmt->execute();
$stmt->bind_result($countProject);
$stmt->fetch();
$stmt->close();

$total = (int)$countDaily + (int)$countProject;

// Mark these updates as seen
if ($total > 0) {
    $stmt = $conn->prepare("UPDATE task_updates tu JOIN daily_tasks dt ON tu.task_type='daily' AND tu.task_id=dt.id SET tu.manager_seen=1 WHERE dt.created_by=? AND tu.manager_seen=0 AND tu.user_id<>?");
    $stmt->bind_param('si', $username, $userId);
    $stmt->execute();
    $stmt->close();

    $stmt = $conn->prepare("UPDATE task_updates tu JOIN project_tasks pt ON tu.task_type='project' AND tu.task_id=pt.id JOIN projects p ON pt.project_id=p.id SET tu.manager_seen=1 WHERE p.created_by=? AND tu.manager_seen=0 AND tu.user_id<>?");
    $stmt->bind_param('ii', $userId, $userId);
    $stmt->execute();
    $stmt->close();
}

echo json_encode(['count' => $total]);
