<?php
require_once 'includes/config.php';
header('Content-Type: application/json');
if ($conn->connect_error) {
    echo json_encode([
        "agent_online" => "0 / 0",
        "siem_monitored" => "0 / 0",
        "penetration_tested" => "0 / 0",
        "user_access_review" => "0 / 0",
        "vapt" => "0 / 0",
        "availability" => "0 / 0"
    ]);
    exit;
}
function get_count($conn, $col) {
    $yes = $conn->query("SELECT COUNT(*) FROM urls WHERE $col='Yes'")->fetch_row()[0];
    $all = $conn->query("SELECT COUNT(*) FROM urls")->fetch_row()[0];
    return "$yes / $all";
}
$agent_online = get_count($conn, 'agent_online');
$siem_monitored = get_count($conn, 'siem_monitored');
$penetration_tested = get_count($conn, 'penetration_tested');
$user_access_review = get_count($conn, 'user_access_review');
$vapt = get_count($conn, 'vapt');
$availability = get_count($conn, 'availability');
echo json_encode([
    "agent_online" => $agent_online,
    "siem_monitored" => $siem_monitored,
    "penetration_tested" => $penetration_tested,
    "user_access_review" => $user_access_review,
    "vapt" => $vapt,
    "availability" => $availability
]); 
