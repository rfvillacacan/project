<?php
session_start();
require_once __DIR__ . '/../includes/config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

try {
    // Get overall health (average completion across all domains)
    $health_query = "SELECT AVG(completion_percentage) as overall_health FROM domain_stats";
    $health_result = $conn->query($health_query);
    $overall_health = round($health_result->fetch_assoc()['overall_health'] ?? 0);

    // Get project counts
    $projects_query = "SELECT 
        COUNT(*) as total_projects,
        SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) as active_projects,
        SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed_projects
        FROM projects";
    $projects_result = $conn->query($projects_query);
    $project_stats = $projects_result->fetch_assoc();

    // Prepare response
    $response = [
        'overall_health' => $overall_health,
        'total_projects' => (int)$project_stats['total_projects'],
        'active_projects' => (int)$project_stats['active_projects'],
        'completed_projects' => (int)$project_stats['completed_projects']
    ];

    // Send response
    header('Content-Type: application/json');
    echo json_encode($response);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Internal server error']);
}
?> 
