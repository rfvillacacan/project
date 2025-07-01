<?php
require_once '../../includes/config.php';
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

// Get request method and endpoint
$method = $_SERVER['REQUEST_METHOD'];
$endpoint = isset($_GET['endpoint']) ? $_GET['endpoint'] : '';

// Handle CORS
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json');

// Helper function to get request body
function getRequestBody() {
    return json_decode(file_get_contents('php://input'), true);
}

// Helper function to send response
function sendResponse($data, $status = 200) {
    http_response_code($status);
    echo json_encode($data);
    exit;
}

// Helper function to validate required fields
function validateRequiredFields($data, $fields) {
    foreach ($fields as $field) {
        if (!isset($data[$field]) || empty($data[$field])) {
            return false;
        }
    }
    return true;
}

try {
    switch ($endpoint) {
        case 'projects':
            switch ($method) {
                case 'GET':
                    // Get all projects or filter by domain
                    $domain = isset($_GET['domain']) ? $_GET['domain'] : null;
                    $status = isset($_GET['status']) ? $_GET['status'] : null;
                    
                    $sql = "SELECT * FROM hajj_program_1446 WHERE 1=1";
                    if ($domain) {
                        $sql .= " AND domain = ?";
                    }
                    if ($status) {
                        $sql .= " AND status = ?";
                    }
                    $sql .= " ORDER BY due_date ASC";
                    
                    $stmt = $conn->prepare($sql);
                    if ($domain && $status) {
                        $stmt->bind_param("ss", $domain, $status);
                    } elseif ($domain) {
                        $stmt->bind_param("s", $domain);
                    } elseif ($status) {
                        $stmt->bind_param("s", $status);
                    }
                    
                    $stmt->execute();
                    $result = $stmt->get_result();
                    $projects = [];
                    while ($row = $result->fetch_assoc()) {
                        $projects[] = $row;
                    }
                    sendResponse(['projects' => $projects]);
                    break;

                case 'POST':
                    // Create new project
                    $data = getRequestBody();
                    $requiredFields = ['project_name', 'domain', 'start_date', 'due_date'];
                    
                    if (!validateRequiredFields($data, $requiredFields)) {
                        sendResponse(['error' => 'Missing required fields'], 400);
                    }
                    
                    $sql = "INSERT INTO hajj_program_1446 (project_name, domain, description, start_date, due_date, 
                            status, progress, priority, assigned_to, dependencies, risks) 
                            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                    
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param("ssssssissss", 
                        $data['project_name'],
                        $data['domain'],
                        $data['description'],
                        $data['start_date'],
                        $data['due_date'],
                        $data['status'] ?? 'Not Started',
                        $data['progress'] ?? 0,
                        $data['priority'] ?? 'Medium',
                        $data['assigned_to'],
                        $data['dependencies'],
                        $data['risks']
                    );
                    
                    if ($stmt->execute()) {
                        sendResponse(['id' => $conn->insert_id, 'message' => 'Project created successfully']);
                    } else {
                        sendResponse(['error' => 'Failed to create project'], 500);
                    }
                    break;

                case 'PUT':
                    // Update project
                    $data = getRequestBody();
                    if (!isset($data['id'])) {
                        sendResponse(['error' => 'Project ID is required'], 400);
                    }
                    
                    $sql = "UPDATE hajj_program_1446 SET 
                            project_name = ?, domain = ?, description = ?, start_date = ?, 
                            due_date = ?, status = ?, progress = ?, priority = ?, 
                            assigned_to = ?, dependencies = ?, risks = ? 
                            WHERE id = ?";
                    
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param("ssssssissssi", 
                        $data['project_name'],
                        $data['domain'],
                        $data['description'],
                        $data['start_date'],
                        $data['due_date'],
                        $data['status'],
                        $data['progress'],
                        $data['priority'],
                        $data['assigned_to'],
                        $data['dependencies'],
                        $data['risks'],
                        $data['id']
                    );
                    
                    if ($stmt->execute()) {
                        sendResponse(['message' => 'Project updated successfully']);
                    } else {
                        sendResponse(['error' => 'Failed to update project'], 500);
                    }
                    break;

                case 'DELETE':
                    // Delete project
                    $id = isset($_GET['id']) ? $_GET['id'] : null;
                    if (!$id) {
                        sendResponse(['error' => 'Project ID is required'], 400);
                    }
                    
                    $sql = "DELETE FROM hajj_program_1446 WHERE id = ?";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param("i", $id);
                    
                    if ($stmt->execute()) {
                        sendResponse(['message' => 'Project deleted successfully']);
                    } else {
                        sendResponse(['error' => 'Failed to delete project'], 500);
                    }
                    break;
            }
            break;

        case 'milestones':
            switch ($method) {
                case 'GET':
                    // Get milestones for a project
                    $project_id = isset($_GET['project_id']) ? $_GET['project_id'] : null;
                    if (!$project_id) {
                        sendResponse(['error' => 'Project ID is required'], 400);
                    }
                    
                    $sql = "SELECT * FROM hajj_program_milestones WHERE project_id = ? ORDER BY due_date ASC";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param("i", $project_id);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    $milestones = [];
                    while ($row = $result->fetch_assoc()) {
                        $milestones[] = $row;
                    }
                    sendResponse(['milestones' => $milestones]);
                    break;

                case 'POST':
                    // Create new milestone
                    $data = getRequestBody();
                    $requiredFields = ['project_id', 'milestone_name', 'due_date'];
                    
                    if (!validateRequiredFields($data, $requiredFields)) {
                        sendResponse(['error' => 'Missing required fields'], 400);
                    }
                    
                    $sql = "INSERT INTO hajj_program_milestones (project_id, milestone_name, description, due_date, 
                            status, completion_percentage) VALUES (?, ?, ?, ?, ?, ?)";
                    
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param("issssi", 
                        $data['project_id'],
                        $data['milestone_name'],
                        $data['description'],
                        $data['due_date'],
                        $data['status'] ?? 'Not Started',
                        $data['completion_percentage'] ?? 0
                    );
                    
                    if ($stmt->execute()) {
                        sendResponse(['id' => $conn->insert_id, 'message' => 'Milestone created successfully']);
                    } else {
                        sendResponse(['error' => 'Failed to create milestone'], 500);
                    }
                    break;
            }
            break;

        case 'risks':
            switch ($method) {
                case 'GET':
                    // Get risks for a project
                    $project_id = isset($_GET['project_id']) ? $_GET['project_id'] : null;
                    if (!$project_id) {
                        sendResponse(['error' => 'Project ID is required'], 400);
                    }
                    
                    $sql = "SELECT * FROM hajj_program_risks WHERE project_id = ? ORDER BY impact DESC, probability DESC";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param("i", $project_id);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    $risks = [];
                    while ($row = $result->fetch_assoc()) {
                        $risks[] = $row;
                    }
                    sendResponse(['risks' => $risks]);
                    break;

                case 'POST':
                    // Create new risk
                    $data = getRequestBody();
                    $requiredFields = ['project_id', 'risk_description', 'impact', 'probability'];
                    
                    if (!validateRequiredFields($data, $requiredFields)) {
                        sendResponse(['error' => 'Missing required fields'], 400);
                    }
                    
                    $sql = "INSERT INTO hajj_program_risks (project_id, risk_description, impact, probability, 
                            mitigation_plan, status, assigned_to, due_date) 
                            VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
                    
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param("isssssss", 
                        $data['project_id'],
                        $data['risk_description'],
                        $data['impact'],
                        $data['probability'],
                        $data['mitigation_plan'],
                        $data['status'] ?? 'Open',
                        $data['assigned_to'],
                        $data['due_date']
                    );
                    
                    if ($stmt->execute()) {
                        sendResponse(['id' => $conn->insert_id, 'message' => 'Risk created successfully']);
                    } else {
                        sendResponse(['error' => 'Failed to create risk'], 500);
                    }
                    break;
            }
            break;

        case 'activities':
            switch ($method) {
                case 'GET':
                    // Get activities for a project
                    $project_id = isset($_GET['project_id']) ? $_GET['project_id'] : null;
                    if (!$project_id) {
                        sendResponse(['error' => 'Project ID is required'], 400);
                    }
                    
                    $sql = "SELECT * FROM hajj_program_activities WHERE project_id = ? ORDER BY created_at DESC";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param("i", $project_id);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    $activities = [];
                    while ($row = $result->fetch_assoc()) {
                        $activities[] = $row;
                    }
                    sendResponse(['activities' => $activities]);
                    break;

                case 'POST':
                    // Create new activity
                    $data = getRequestBody();
                    $requiredFields = ['project_id', 'activity_type', 'description'];
                    
                    if (!validateRequiredFields($data, $requiredFields)) {
                        sendResponse(['error' => 'Missing required fields'], 400);
                    }
                    
                    $sql = "INSERT INTO hajj_program_activities (project_id, activity_type, description, created_by) 
                            VALUES (?, ?, ?, ?)";
                    
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param("isss", 
                        $data['project_id'],
                        $data['activity_type'],
                        $data['description'],
                        $_SESSION['username']
                    );
                    
                    if ($stmt->execute()) {
                        sendResponse(['id' => $conn->insert_id, 'message' => 'Activity logged successfully']);
                    } else {
                        sendResponse(['error' => 'Failed to log activity'], 500);
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
