<?php
require_once 'includes/config.php';
header('Content-Type: application/json');

// 1. KPIs
$res = $conn->query("SELECT COUNT(*) as total, 
    SUM(CASE WHEN progress = '100' THEN 1 ELSE 0 END) as completed, 
    SUM(CASE WHEN progress < '100' THEN 1 ELSE 0 END) as inprogress 
    FROM tbl_isprojects");
$row = $res->fetch_assoc();
$total = (int)$row['total'];
$completed = (int)$row['completed'];
$inprogress = (int)$row['inprogress'];
$overall_health = $total > 0 ? round(($completed / $total) * 100) : 0;

// 2. In Progress Projects
$inprogress_projects = [];
$res = $conn->query("SELECT id, service, due_date, assign_to_team, progress FROM tbl_isprojects WHERE progress < 100 ORDER BY due_date ASC");
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
    $r = $res->fetch_assoc();
    $total = (int)$r['total'];
    $completed = (int)$r['completed'];
    $inprogress = (int)$r['inprogress'];
    $completion = $total > 0 ? round(($completed / $total) * 100) : 0;
    $matrix[$team] = [
        'completion' => $completion,
        'issues' => $inprogress
    ];
}

echo json_encode([
    'overall_health' => $overall_health,
    'total_projects' => $total,
    'active_projects' => $inprogress,
    'completed_projects' => $completed,
    'inprogress_projects' => $inprogress_projects,
    'bar_data' => $bar_data,
    'matrix' => $matrix
]); 
