<?php
require_once 'includes/config.php';
header('Content-Type: application/json');

$team = isset($_GET['team']) ? $conn->real_escape_string($_GET['team']) : '';
$where = '';
if ($team && in_array($team, ['Prep','GRC','SD','SecOPS','OT','IS'])) {
    $where = "WHERE assign_to_team='" . $team . "'";
}

$result = $conn->query("SELECT id, service, due_date, progress, comments FROM tbl_isprojects $where ORDER BY id DESC");
$total = 0;
$completed = 0;
$inprogress = 0;
$pending = 0;
$latest = array();
while($row = $result->fetch_assoc()) {
    $total++;
    $progressNum = intval($row['progress']);
    if ($progressNum === 100) {
        $completed++;
        $status = 'completed';
    } else if ($progressNum > 0) {
        $inprogress++;
        $status = 'inprogress';
    } else {
        $pending++;
        $status = 'pending';
    }
    // Format date
    $formattedDate = $row['due_date'];
    if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $row['due_date'])) {
        $d = DateTime::createFromFormat('Y-m-d', $row['due_date']);
        if ($d) $formattedDate = $d->format('M j, Y');
    }
    $latest[] = array(
        'datetime' => $formattedDate,
        'service' => $row['service'],
        'status' => $status
    );
}
$completion_rate = $total > 0 ? round(($completed / $total) * 100) : 0;
echo json_encode([
    'total' => $total,
    'completed' => $completed,
    'inprogress' => $inprogress,
    'pending' => $pending,
    'completion_rate' => $completion_rate,
    'latest' => $latest
]); 
