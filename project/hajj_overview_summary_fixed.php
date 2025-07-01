<?php
require_once 'includes/config.php';
header('Content-Type: application/json');

try {
    // 1. KPIs
    $res = $conn->query("SELECT COUNT(*) as total, SUM(CASE WHEN progress = '100' THEN 1 ELSE 0 END) as completed FROM tbl_isprojects");
    if (!$res) {
        throw new Exception('Query failed: ' . $conn->error);
    }
    $row = $res->fetch_assoc();
    $total = (int)$row['total'];
    $completed = (int)$row['completed'];

    // Separate query for in-progress projects
    $res = $conn->query("SELECT COUNT(*) as inprogress FROM tbl_isprojects WHERE progress < 100");
    if (!$res) {
        throw new Exception('Query failed: ' . $conn->error);
    }
    $row = $res->fetch_assoc();
    $inprogress = (int)$row['inprogress'];
    $overall_health = $total > 0 ? round(($completed / $total) * 100) : 0;

    // 2. In Progress Projects
    $inprogress_projects = [];
    $res = $conn->query("SELECT id, service, due_date, assign_to_team, progress, comments FROM tbl_isprojects ORDER BY due_date ASC");
    if (!$res) {
        throw new Exception('Query failed: ' . $conn->error);
    }
    while ($r = $res->fetch_assoc()) {
        $inprogress_projects[] = $r;
    }

    // 3. Bar Chart Data (per team)
    $teams = ['Prep','GRC','SD','SecOPS','OT','IS'];
    $bar_data = [];
    foreach ($teams as $team) {
        $res = $conn->query("SELECT 
            COUNT(CASE WHEN CAST(progress AS UNSIGNED) = 100 THEN 1 END) as completed, 
            COUNT(CASE WHEN CAST(progress AS UNSIGNED) > 0 AND CAST(progress AS UNSIGNED) < 100 THEN 1 END) as inprogress, 
            COUNT(CASE WHEN CAST(progress AS UNSIGNED) = 0 THEN 1 END) as pending
            FROM tbl_isprojects WHERE assign_to_team='$team'");
        if (!$res) {
            throw new Exception('Query failed: ' . $conn->error);
        }
        $r = $res->fetch_assoc();
        $bar_data[$team] = [
            'completed' => (int)$r['completed'],
            'inprogress' => (int)$r['inprogress'],
            'pending' => (int)$r['pending']
        ];
    }

    // 4. Program Status Matrix Data
    $matrix = [];
    foreach ($teams as $team) {
        $res = $conn->query("SELECT COUNT(*) as total, SUM(CASE WHEN progress = '100' THEN 1 ELSE 0 END) as completed, SUM(CASE WHEN progress < '100' THEN 1 ELSE 0 END) as inprogress FROM tbl_isprojects WHERE assign_to_team='$team'");
        if (!$res) {
            throw new Exception('Query failed: ' . $conn->error);
        }
        $r = $res->fetch_assoc();
        $total_team = (int)$r['total'];
        $completed_team = (int)$r['completed'];
        $inprogress_team = (int)$r['inprogress'];
        $completion = $total_team > 0 ? round(($completed_team / $total_team) * 100) : 0;
        $matrix[$team] = [
            'completion' => $completion,
            'issues' => $inprogress_team
        ];
    }

    echo json_encode([
        'overall_health' => $overall_health,
        'total_projects' => $total,
        'active_projects' => $inprogress,
        'completed_projects' => $completed,
        'inprogress_projects' => $inprogress_projects,
        'bar_data' => $bar_data,
        'matrix' => $matrix,
        'debug' => [
            'db' => DB_NAME,
            'table' => 'tbl_isprojects',
            'query_count' => $total
        ]
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
} 
