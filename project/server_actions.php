<?php
session_start();
require_once 'includes/config.php';
header('Content-Type: application/json');

// Database connection
if ($conn->connect_error) {
    die(json_encode(['success' => false, 'message' => 'Database connection failed']));
}

// Get the action type
$action = $_POST['action'] ?? '';

switch ($action) {
    case 'check_server':
        // Get form data
        $id = intval($_POST['id'] ?? 0);
        $agent_online = trim($_POST['agent_online'] ?? '');
        $siem_monitored = trim($_POST['siem_monitored'] ?? '');
        $penetration_tested = trim($_POST['penetration_tested'] ?? '');
        $user_access_review = trim($_POST['user_access_review'] ?? '');
        $vapt = trim($_POST['vapt'] ?? '');
        $availability = trim($_POST['availability'] ?? '');

        // Update the server check information
        $stmt = $conn->prepare("UPDATE servers SET agent_online=?, siem_monitored=?, penetration_tested=?, user_access_review=?, vapt=?, availability=? WHERE id=?");
        $stmt->bind_param('ssssssi', $agent_online, $siem_monitored, $penetration_tested, $user_access_review, $vapt, $availability, $id);

        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Server check updated successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to update server check: ' . $conn->error]);
        }
        break;

    default:
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
        break;
}

$conn->close(); 
