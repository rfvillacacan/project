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
$type = isset($_GET['type']) ? $conn->real_escape_string($_GET['type']) : '';
if ($type === '') {
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
function get_count($conn, $type, $col) {
    $yes = $conn->query("SELECT COUNT(*) FROM servers WHERE type='$type' AND $col='Yes'")->fetch_row()[0];
    $all = $conn->query("SELECT COUNT(*) FROM servers WHERE type='$type'")->fetch_row()[0];
    return "$yes / $all";
}
$agent_online = get_count($conn, $type, 'agent_online');
$siem_monitored = get_count($conn, $type, 'siem_monitored');
$penetration_tested = get_count($conn, $type, 'penetration_tested');
$user_access_review = get_count($conn, $type, 'user_access_review');
$vapt = get_count($conn, $type, 'vapt');
$availability = get_count($conn, $type, 'availability');
echo json_encode([
    "agent_online" => $agent_online,
    "siem_monitored" => $siem_monitored,
    "penetration_tested" => $penetration_tested,
    "user_access_review" => $user_access_review,
    "vapt" => $vapt,
    "availability" => $availability
]);
?>
