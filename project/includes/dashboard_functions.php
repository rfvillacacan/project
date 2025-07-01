<?php
require_once __DIR__ . '/config.php';

function get_table_list($conn, $hiddenTables = [], $showOnly = null) {
    $allTables = [];
    $res = $conn->query('SHOW TABLES');
    while ($row = $res->fetch_array()) {
        $table = $row[0];
        if ($showOnly !== null) {
            if (in_array($table, $showOnly)) {
                $allTables[] = $table;
            }
        } elseif (!in_array($table, $hiddenTables)) {
            $allTables[] = $table;
        }
    }
    return $allTables;
}

function count_yes($conn, $table, $col) {
    return $conn->query("SELECT COUNT(*) FROM $table WHERE $col='Yes'")->fetch_row()[0];
}

function get_dashboard_metrics($conn) {
    $totalServers = $conn->query("SELECT COUNT(*) FROM servers")->fetch_row()[0];
    $totalUrls    = $conn->query("SELECT COUNT(*) FROM urls")->fetch_row()[0];
    $totalAssets  = $totalServers + $totalUrls;

    $agentsOnline      = count_yes($conn, 'servers', 'agent_online') + count_yes($conn, 'urls', 'agent_online');
    $siemMonitored     = count_yes($conn, 'servers', 'siem_monitored') + count_yes($conn, 'urls', 'siem_monitored');
    $penTested         = count_yes($conn, 'servers', 'penetration_tested') + count_yes($conn, 'urls', 'penetration_tested');
    $userAccessReviewed= count_yes($conn, 'servers', 'user_access_review') + count_yes($conn, 'urls', 'user_access_review');
    $vapt              = count_yes($conn, 'servers', 'vapt') + count_yes($conn, 'urls', 'vapt');

    $agentsOnlinePct      = $totalAssets ? $agentsOnline / $totalAssets : 0;
    $siemMonitoredPct     = $totalAssets ? $siemMonitored / $totalAssets : 0;
    $penTestedPct         = $totalAssets ? $penTested / $totalAssets : 0;
    $userAccessReviewedPct= $totalAssets ? $userAccessReviewed / $totalAssets : 0;
    $vaptPct              = $totalAssets ? $vapt / $totalAssets : 0;

    $overallCompliance = round((($agentsOnlinePct + $siemMonitoredPct + $penTestedPct +
                               $userAccessReviewedPct + $vaptPct + 1) / 6) * 100);

    return [
        'totalServers' => $totalServers,
        'totalUrls' => $totalUrls,
        'totalAssets' => $totalAssets,
        'agentsOnlinePct' => $agentsOnlinePct,
        'siemMonitoredPct' => $siemMonitoredPct,
        'penTestedPct' => $penTestedPct,
        'userAccessReviewedPct' => $userAccessReviewedPct,
        'vaptPct' => $vaptPct,
        'overallCompliance' => $overallCompliance,
    ];
}

function fetch_servers($conn, $type) {
    $stmt = $conn->prepare("SELECT id, name, domain, ip, operating_system, application_name, status, notes FROM servers WHERE type=?");
    $stmt->bind_param('s', $type);
    $stmt->execute();
    return $stmt->get_result();
}

function fetch_network_devices($conn) {
    return $conn->query("SELECT id, hostname, domain, ip_address, operating_system, role, criticality, status, notes FROM network_devices");
}

function fetch_incidents($conn) {
    return $conn->query("SELECT criticality AS severity, CONCAT(name, ' is critical') AS description FROM servers WHERE criticality='High'");
}
