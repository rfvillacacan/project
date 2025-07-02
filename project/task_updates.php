<?php
// Use absolute path to reliably load the config file regardless of the
// current working directory. A relative include may fail under certain
// environments and lead to a 500 error.
require_once __DIR__ . '/includes/config.php';

header('Content-Type: application/json');
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    exit;
}

$taskType = $_GET['task_type'] ?? '';
$taskId = intval($_GET['task_id'] ?? 0);
if (!$taskType || !$taskId) {
    http_response_code(400);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    try {
        $stmt = $conn->prepare(
            "SELECT tu.id, tu.comment, tu.progress, tu.status, tu.created_at, u.username
             FROM task_updates tu
             JOIN users u ON tu.user_id=u.id
             WHERE tu.task_type=? AND tu.task_id=?
             ORDER BY tu.created_at"
        );
        $stmt->bind_param('si', $taskType, $taskId);
        $stmt->execute();
        $res = $stmt->get_result();
        $rows = [];
        while ($row = $res->fetch_assoc()) {
            $rows[] = $row;
        }
        echo json_encode(['updates' => $rows]);
    } catch (Exception $e) {
        error_log($e->getMessage());
        http_response_code(500);
        echo json_encode(['error' => 'Database error']);
    }
    exit;
}

// POST: add new update
$data = json_decode(file_get_contents('php://input'), true);
$comment = $data['comment'] ?? '';
$progress = isset($data['progress']) ? intval($data['progress']) : 0;
$status = $data['status'] ?? null;
$userId = $_SESSION['user_id'];

try {
    $stmt = $conn->prepare(
        "INSERT INTO task_updates (task_type, task_id, user_id, comment, progress, status)
         VALUES (?,?,?,?,?,?)"
    );
    $stmt->bind_param('siisis', $taskType, $taskId, $userId, $comment, $progress, $status);
    $stmt->execute();

    if ($status !== null) {
        if ($taskType === 'daily') {
            $update = $conn->prepare("UPDATE daily_tasks SET status=?, progress=? WHERE id=?");
            $update->bind_param('sii', $status, $progress, $taskId);
        } else {
            $update = $conn->prepare("UPDATE project_tasks SET status=?, progress=? WHERE id=?");
            $update->bind_param('sii', $status, $progress, $taskId);
        }
        $update->execute();
    }

    echo json_encode(['success' => true]);
} catch (Exception $e) {
    error_log($e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Database error']);
}
