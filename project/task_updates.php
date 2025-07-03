<?php
// Use absolute path to ensure the config file is loaded correctly regardless
// of the current working directory. In some environments the working directory
// may differ from the script location which could lead to a failed include and
// consequently a 500 error.
require_once __DIR__ . '/includes/config.php';

/**
 * Ensure the task_updates table exists. The SQL migration may have been missed
 * during setup which results in a 500 error when this script queries the table.
 * Creating the table on demand avoids breaking the dashboard if the migration
 * wasn't run.
 */
function ensureTaskUpdatesTable(mysqli $conn): void
{
    $sql = "CREATE TABLE IF NOT EXISTS task_updates (
        id INT AUTO_INCREMENT PRIMARY KEY,
        task_type ENUM('daily','project') NOT NULL,
        task_id INT NOT NULL,
        user_id INT NOT NULL,
        comment TEXT,
        progress TINYINT UNSIGNED DEFAULT 0,
        status ENUM('pending','inprogress','completed') DEFAULT 'inprogress',
        manager_seen TINYINT(1) DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id)
    )";

    $conn->query($sql);
}

// Automatically create the table if it doesn't exist
ensureTaskUpdatesTable($conn);

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
            "SELECT tu.id, tu.user_id, tu.comment, tu.progress, tu.status, tu.created_at, u.username
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

if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    $updateId = intval($_GET['id'] ?? 0);
    if (!$updateId) {
        parse_str(file_get_contents('php://input'), $body);
        $updateId = intval($body['id'] ?? 0);
    }
    if (!$updateId) {
        http_response_code(400);
        exit;
    }

    // Fetch update info to determine permissions
    $stmt = $conn->prepare('SELECT task_type, task_id, user_id FROM task_updates WHERE id=?');
    $stmt->bind_param('i', $updateId);
    $stmt->execute();
    $stmt->bind_result($type, $taskIdRow, $authorId);
    if (!$stmt->fetch()) {
        http_response_code(404);
        exit;
    }
    $stmt->close();

    $isManager = false;
    if ($type === 'daily') {
        $stmt = $conn->prepare('SELECT created_by FROM daily_tasks WHERE id=?');
        $stmt->bind_param('i', $taskIdRow);
        $stmt->execute();
        $stmt->bind_result($creator);
        $stmt->fetch();
        $stmt->close();
        $isManager = ($creator === $_SESSION['username']);
    } else {
        $stmt = $conn->prepare('SELECT p.created_by FROM project_tasks pt JOIN projects p ON pt.project_id=p.id WHERE pt.id=?');
        $stmt->bind_param('i', $taskIdRow);
        $stmt->execute();
        $stmt->bind_result($creatorId);
        $stmt->fetch();
        $stmt->close();
        $isManager = ($creatorId == $_SESSION['user_id']);
    }

    if ($authorId == $_SESSION['user_id'] || $isManager) {
        $stmt = $conn->prepare('DELETE FROM task_updates WHERE id=?');
        $stmt->bind_param('i', $updateId);
        $stmt->execute();
        echo json_encode(['success' => true]);
    } else {
        http_response_code(403);
        echo json_encode(['error' => 'Not authorized']);
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
