<?php
session_start();
require_once 'includes/config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

try {
    // Get timeline events
    $timeline_query = "SELECT 
        e.id,
        e.title,
        e.description,
        e.start_date,
        e.end_date,
        e.domain,
        e.status,
        e.milestone_type
        FROM timeline_events e
        WHERE e.start_date >= DATE_SUB(CURDATE(), INTERVAL 3 MONTH)
        AND e.end_date <= DATE_ADD(CURDATE(), INTERVAL 3 MONTH)
        ORDER BY e.start_date ASC";
    
    $timeline_result = $conn->query($timeline_query);
    $events = [];

    while ($row = $timeline_result->fetch_assoc()) {
        $events[] = [
            'id' => (int)$row['id'],
            'title' => $row['title'],
            'description' => $row['description'],
            'start_date' => $row['start_date'],
            'end_date' => $row['end_date'],
            'domain' => $row['domain'],
            'status' => $row['status'],
            'milestone_type' => $row['milestone_type']
        ];
    }

    // Get milestones
    $milestones_query = "SELECT 
        m.id,
        m.title,
        m.description,
        m.due_date,
        m.domain,
        m.status
        FROM milestones m
        WHERE m.due_date >= DATE_SUB(CURDATE(), INTERVAL 3 MONTH)
        AND m.due_date <= DATE_ADD(CURDATE(), INTERVAL 3 MONTH)
        ORDER BY m.due_date ASC";
    
    $milestones_result = $conn->query($milestones_query);
    $milestones = [];

    while ($row = $milestones_result->fetch_assoc()) {
        $milestones[] = [
            'id' => (int)$row['id'],
            'title' => $row['title'],
            'description' => $row['description'],
            'due_date' => $row['due_date'],
            'domain' => $row['domain'],
            'status' => $row['status']
        ];
    }

    // Prepare response
    $response = [
        'events' => $events,
        'milestones' => $milestones
    ];

    // Send response
    header('Content-Type: application/json');
    echo json_encode($response);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Internal server error']);
}
?> 
