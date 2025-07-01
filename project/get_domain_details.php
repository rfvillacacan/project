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
    // Get domain completion and task statistics
    $stats_query = "SELECT 
        ds.completion_percentage,
        COUNT(t.id) as total_tasks,
        SUM(CASE WHEN t.status = 'completed' THEN 1 ELSE 0 END) as completed_tasks,
        COUNT(DISTINCT CASE WHEN i.severity = 'critical' AND i.status != 'resolved' THEN i.id END) as critical_issues
        FROM domain_stats ds
        LEFT JOIN tasks t ON t.domain = ds.domain
        LEFT JOIN issues i ON i.domain = ds.domain
        WHERE ds.domain = ?
        GROUP BY ds.domain, ds.completion_percentage";
    
    $stmt = $conn->prepare($stats_query);
    $stmt->bind_param("s", $domain);
    $stmt->execute();
    $stats_result = $stmt->get_result();
    $stats = $stats_result->fetch_assoc();

    // Get recent activities
    $activities_query = "SELECT 
        a.id,
        a.title,
        a.description,
        a.timestamp,
        a.type,
        a.status
        FROM domain_activities a
        WHERE a.domain = ?
        ORDER BY a.timestamp DESC
        LIMIT 5";
    
    $stmt = $conn->prepare($activities_query);
    $stmt->bind_param("s", $domain);
    $stmt->execute();
    $activities_result = $stmt->get_result();
    $activities = [];

    while ($row = $activities_result->fetch_assoc()) {
        $activities[] = [
            'id' => (int)$row['id'],
            'title' => $row['title'],
            'description' => $row['description'],
            'timestamp' => $row['timestamp'],
            'type' => $row['type'],
            'status' => $row['status']
        ];
    }

    // Get team members
    $team_query = "SELECT 
        u.id,
        u.username,
        u.role,
        u.status
        FROM domain_team_members dtm
        JOIN users u ON u.id = dtm.user_id
        WHERE dtm.domain = ?";
    
    $stmt = $conn->prepare($team_query);
    $stmt->bind_param("s", $domain);
    $stmt->execute();
    $team_result = $stmt->get_result();
    $team = [];

    while ($row = $team_result->fetch_assoc()) {
        $team[] = [
            'id' => (int)$row['id'],
            'username' => $row['username'],
            'role' => $row['role'],
            'status' => $row['status']
        ];
    }

    // Prepare response
    $response = [
        'completion' => (int)$stats['completion_percentage'],
        'completed_tasks' => (int)$stats['completed_tasks'],
        'total_tasks' => (int)$stats['total_tasks'],
        'critical_issues' => (int)$stats['critical_issues'],
        'activities' => $activities,
        'team' => $team
    ];

    // Send response
    header('Content-Type: application/json');
    echo json_encode($response);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Internal server error']);
}
?> 
