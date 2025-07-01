<?php
session_start();
require_once 'includes/config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

// Validate domain parameter
$valid_domains = ['prep', 'grc', 'sd', 'secops', 'ot', 'is'];
$domain = $_GET['domain'] ?? '';

if (!in_array($domain, $valid_domains)) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid domain']);
    exit();
}

try {
    // Get domain completion percentage
    $completion_query = "SELECT completion_percentage FROM domain_stats WHERE domain = ?";
    $stmt = $conn->prepare($completion_query);
    $stmt->bind_param("s", $domain);
    $stmt->execute();
    $completion_result = $stmt->get_result();
    $completion = $completion_result->fetch_assoc()['completion_percentage'] ?? 0;

    // Get number of issues for the domain
    $issues_query = "SELECT COUNT(*) as issues_count FROM domain_issues WHERE domain = ? AND status != 'resolved'";
    $stmt = $conn->prepare($issues_query);
    $stmt->bind_param("s", $domain);
    $stmt->execute();
    $issues_result = $stmt->get_result();
    $issues_count = $issues_result->fetch_assoc()['issues_count'] ?? 0;

    // Prepare response
    $response = [
        'completion' => (int)$completion,
        'issues_count' => (int)$issues_count
    ];

    // Send response
    header('Content-Type: application/json');
    echo json_encode($response);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Internal server error']);
}
?> 
