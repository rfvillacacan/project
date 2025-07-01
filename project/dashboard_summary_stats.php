<?php
require_once 'includes/config.php';
header('Content-Type: application/json');
if ($conn->connect_error) {
    echo json_encode([
        "total_assets" => 0,
        "agents_online" => 0,
        "agents_online_pct" => 0,
        "siem_monitored" => 0,
        "siem_monitored_pct" => 0,
        "pen_tested" => 0,
        "pen_tested_pct" => 0,
        "user_access_reviewed" => 0,
        "user_access_reviewed_pct" => 0,
        "vapt" => 0,
        "vapt_pct" => 0,
        "overall_compliance" => 0
    ]);
    exit;
}
$totalServers = $conn->query("SELECT COUNT(*) FROM servers")->fetch_row()[0];
$totalUrls = $conn->query("SELECT COUNT(*) FROM urls")->fetch_row()[0];
$totalAssets = $totalServers + $totalUrls;

function count_yes($conn, $table, $col) {
    return $conn->query("SELECT COUNT(*) FROM $table WHERE $col='Yes'")->fetch_row()[0];
}

$agentsOnline = count_yes($conn, 'servers', 'agent_online') + count_yes($conn, 'urls', 'agent_online');
$siemMonitored = count_yes($conn, 'servers', 'siem_monitored') + count_yes($conn, 'urls', 'siem_monitored');
$penTested = count_yes($conn, 'servers', 'penetration_tested') + count_yes($conn, 'urls', 'penetration_tested');
$userAccessReviewed = count_yes($conn, 'servers', 'user_access_review') + count_yes($conn, 'urls', 'user_access_review');
$vapt = count_yes($conn, 'servers', 'vapt') + count_yes($conn, 'urls', 'vapt');

$agentsOnlinePct = $totalAssets > 0 ? round(($agentsOnline / $totalAssets) * 100, 2) : 0;
$siemMonitoredPct = $totalAssets > 0 ? round(($siemMonitored / $totalAssets) * 100, 2) : 0;
$penTestedPct = $totalAssets > 0 ? round(($penTested / $totalAssets) * 100, 2) : 0;
$userAccessReviewedPct = $totalAssets > 0 ? round(($userAccessReviewed / $totalAssets) * 100, 2) : 0;
$vaptPct = $totalAssets > 0 ? round(($vapt / $totalAssets) * 100, 2) : 0;
$overallCompliance = round((($agentsOnlinePct + $siemMonitoredPct + $penTestedPct + $userAccessReviewedPct + $vaptPct) / 5), 2);

echo json_encode([
    "total_assets" => $totalAssets,
    "agents_online" => $agentsOnline,
    "agents_online_pct" => $agentsOnlinePct,
    "siem_monitored" => $siemMonitored,
    "siem_monitored_pct" => $siemMonitoredPct,
    "pen_tested" => $penTested,
    "pen_tested_pct" => $penTestedPct,
    "user_access_reviewed" => $userAccessReviewed,
    "user_access_reviewed_pct" => $userAccessReviewedPct,
    "vapt" => $vapt,
    "vapt_pct" => $vaptPct,
    "overall_compliance" => $overallCompliance
]); 
