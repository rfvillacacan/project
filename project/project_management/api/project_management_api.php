<?php
require_once '../../includes/config.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];
// Allow method override via header or POST parameter for clients that cannot
// send PUT/DELETE requests directly
if ($method === 'POST') {
    if (isset($_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE'])) {
        $method = strtoupper($_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE']);
    } elseif (isset($_POST['_method'])) {
        $method = strtoupper($_POST['_method']);
    } else {
        $raw = file_get_contents('php://input');
        $tmpBody = json_decode($raw, true);
        if (isset($tmpBody['_method'])) {
            $method = strtoupper($tmpBody['_method']);
        }
        // Store raw input so getRequestBody() can reuse it
        $GLOBALS['raw_input'] = $raw;
    }
}
$endpoint = isset($_GET['endpoint']) ? $_GET['endpoint'] : '';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type, X-HTTP-Method-Override');

function getRequestBody() {
    static $cached = null;
    if ($cached !== null) return $cached;
    if (isset($GLOBALS['raw_input'])) {
        $cached = json_decode($GLOBALS['raw_input'], true);
    } else {
        $cached = json_decode(file_get_contents('php://input'), true);
    }
    return $cached;
}

function sendResponse($data, $status = 200) {
    http_response_code($status);
    echo json_encode($data);
    exit;
}

try {
    switch ($endpoint) {
        case 'projects':
            switch ($method) {
                case 'GET':
                    $sql = "SELECT * FROM projects ORDER BY due_date";
                    $result = $conn->query($sql);
                    $rows = [];
                    while ($row = $result->fetch_assoc()) {
                        $rows[] = $row;
                    }
                    sendResponse(['projects' => $rows]);
                    break;
                case 'POST':
                    $data = getRequestBody();
                    $createdBy = isset($data['created_by']) ? intval($data['created_by']) : intval($_SESSION['user_id']);
                    $stmt = $conn->prepare("INSERT INTO projects (name, description, start_date, due_date, created_by) VALUES (?,?,?,?,?)");
                    $stmt->bind_param("ssssi", $data['name'], $data['description'], $data['start_date'], $data['due_date'], $createdBy);
                    if ($stmt->execute()) {
                        sendResponse(['id' => $conn->insert_id]);
                    } else {
                        sendResponse(['error' => 'Insert failed'], 500);
                    }
                    break;
                case 'PUT':
                    $data = getRequestBody();
                    if (!isset($data['id'])) {
                        sendResponse(['error' => 'ID required'], 400);
                    }
                    $stmt = $conn->prepare("UPDATE projects SET name=?, description=?, start_date=?, due_date=? WHERE id=?");
                    $stmt->bind_param("ssssi", $data['name'], $data['description'], $data['start_date'], $data['due_date'], $data['id']);
                    if ($stmt->execute()) {
                        sendResponse(['success' => true]);
                    } else {
                        sendResponse(['error' => 'Update failed'], 500);
                    }
                    break;
                case 'DELETE':
                    $data = getRequestBody();
                    if (!is_array($data)) {
                        $data = [];
                    }
                    $id = isset($_GET['id']) && $_GET['id'] !== '' ? intval($_GET['id']) : (isset($data['id']) ? intval($data['id']) : 0);
                    if (!$id) {
                        sendResponse(['error' => 'ID required'], 400);
                    }
                    // Delete related tasks first to satisfy foreign key constraint
                    $taskStmt = $conn->prepare("DELETE FROM project_tasks WHERE project_id=?");
                    $taskStmt->bind_param("i", $id);
                    $taskStmt->execute();

                    $stmt = $conn->prepare("DELETE FROM projects WHERE id=?");
                    $stmt->bind_param("i", $id);
                    if ($stmt->execute()) {
                        sendResponse(['success' => true]);
                    } else {
                        sendResponse(['error' => 'Delete failed'], 500);
                    }
                    break;
            }
            break;
        case 'tasks':
            switch ($method) {
                case 'GET':
                    $baseSql = "SELECT pt.*, p.name AS project_name, u.username AS assigned_to_username FROM project_tasks pt JOIN projects p ON pt.project_id = p.id LEFT JOIN users u ON pt.assigned_to = u.id";
                    $projectId = isset($_GET['project_id']) ? intval($_GET['project_id']) : 0;
                    if ($projectId) {
                        $stmt = $conn->prepare($baseSql . " WHERE pt.project_id=? ORDER BY pt.due_date");
                        $stmt->bind_param('i', $projectId);
                        $stmt->execute();
                        $result = $stmt->get_result();
                    } else {
                        $result = $conn->query($baseSql . " ORDER BY pt.due_date");
                    }
                    $rows = [];
                    while ($row = $result->fetch_assoc()) {
                        $rows[] = $row;
                    }
                    sendResponse(['tasks' => $rows]);
                    break;
                case 'POST':
                    $data = getRequestBody();
                    $stmt = $conn->prepare("INSERT INTO project_tasks (project_id, title, description, assigned_to, due_date, status) VALUES (?,?,?,?,?,?)");
                    $stmt->bind_param("ississ", $data['project_id'], $data['title'], $data['description'], $data['assigned_to'], $data['due_date'], $data['status']);
                    if ($stmt->execute()) {
                        sendResponse(['id' => $conn->insert_id]);
                    } else {
                        sendResponse(['error' => 'Insert failed'], 500);
                    }
                    break;
                case 'PUT':
                    $data = getRequestBody();
                    if (!isset($data['id'])) {
                        sendResponse(['error' => 'ID required'], 400);
                    }
                    $stmt = $conn->prepare("UPDATE project_tasks SET project_id=?, title=?, description=?, assigned_to=?, due_date=?, status=? WHERE id=?");
                    $stmt->bind_param("ississi", $data['project_id'], $data['title'], $data['description'], $data['assigned_to'], $data['due_date'], $data['status'], $data['id']);
                    if ($stmt->execute()) {
                        sendResponse(['success' => true]);
                    } else {
                        sendResponse(['error' => 'Update failed'], 500);
                    }
                    break;
                case 'DELETE':
                    $data = getRequestBody();
                    if (!is_array($data)) {
                        $data = [];
                    }
                    $id = isset($_GET['id']) && $_GET['id'] !== '' ? intval($_GET['id']) : (isset($data['id']) ? intval($data['id']) : 0);
                    if (!$id) {
                        sendResponse(['error' => 'ID required'], 400);
                    }
                    $stmt = $conn->prepare("DELETE FROM project_tasks WHERE id=?");
                    $stmt->bind_param("i", $id);
                    if ($stmt->execute()) {
                        sendResponse(['success' => true]);
                    } else {
                        sendResponse(['error' => 'Delete failed'], 500);
                    }
                    break;
            }
            break;
        default:
            sendResponse(['error' => 'Invalid endpoint'], 404);
    }
} catch (Exception $e) {
    sendResponse(['error' => $e->getMessage()], 500);
}
$conn->close();
?>
