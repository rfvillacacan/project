<?php
require_once 'includes/config.php';
header('Content-Type: application/json');

// DataTables parameters
$draw = isset($_GET['draw']) ? intval($_GET['draw']) : 1;
$start = isset($_GET['start']) ? intval($_GET['start']) : 0;
$length = isset($_GET['length']) ? intval($_GET['length']) : 10;
$searchValue = isset($_GET['search']['value']) ? $conn->real_escape_string($_GET['search']['value']) : '';

$columns = [
  'hostname', 'domain', 'ip_address', 'operating_system', 'role', 'criticality', 'status', 'notes'
];

// Build WHERE clause
$where = "";
if ($searchValue !== '') {
  $searchParts = [];
  foreach ($columns as $col) {
    $searchParts[] = "$col LIKE '%$searchValue%'";
  }
  $where = "WHERE " . implode(' OR ', $searchParts);
}

// Total records
$totalQuery = $conn->query("SELECT COUNT(*) FROM network_devices");
$totalRecords = $totalQuery ? $totalQuery->fetch_row()[0] : 0;

// Filtered records
$filteredQuery = $conn->query("SELECT COUNT(*) FROM network_devices $where");
$filteredRecords = $filteredQuery ? $filteredQuery->fetch_row()[0] : 0;

// Data query
$data = [];
$sql = "SELECT id, hostname, domain, ip_address, operating_system, role, criticality, status, notes, agent_online, siem_monitored, penetration_tested, user_access_review, vapt, availability FROM network_devices $where LIMIT $start, $length";
$result = $conn->query($sql);
if ($result) {
  while ($row = $result->fetch_assoc()) {
    $row['status'] = strtolower($row['status']) === 'online'
      ? '<span class="badge bg-success">Online</span>'
      : '<span class="badge bg-danger">Offline</span>';
    $action = '<button class="btn btn-sm btn-warning edit-network-btn"'
        . ' data-id="' . htmlspecialchars($row['id']) . '"'
        . ' data-hostname="' . htmlspecialchars($row['hostname']) . '"'
        . ' data-domain="' . htmlspecialchars($row['domain']) . '"'
        . ' data-ip="' . htmlspecialchars($row['ip_address']) . '"'
        . ' data-os="' . htmlspecialchars($row['operating_system']) . '"'
        . ' data-role="' . htmlspecialchars($row['role']) . '"'
        . ' data-criticality="' . htmlspecialchars($row['criticality']) . '"'
        . ' data-status="' . htmlspecialchars($row['status']) . '"'
        . ' data-notes="' . htmlspecialchars($row['notes']) . '"'
        . '>Edit</button> '
        . '<button class="btn btn-sm btn-success check-network-btn"'
        . ' data-id="' . htmlspecialchars($row['id']) . '"'
        . ' data-hostname="' . htmlspecialchars($row['hostname']) . '"'
        . ' data-ip="' . htmlspecialchars($row['ip_address']) . '"'
        . ' data-agent-online="' . htmlspecialchars($row['agent_online']) . '"'
        . ' data-siem-monitored="' . htmlspecialchars($row['siem_monitored']) . '"'
        . ' data-penetration-tested="' . htmlspecialchars($row['penetration_tested']) . '"'
        . ' data-user-access-review="' . htmlspecialchars($row['user_access_review']) . '"'
        . ' data-vapt="' . htmlspecialchars($row['vapt']) . '"'
        . ' data-availability="' . htmlspecialchars($row['availability']) . '"'
        . '>Check</button> '
        . '<button class="btn btn-sm btn-danger delete-network-btn" data-id="' . htmlspecialchars($row['id']) . '">Delete</button>';
    $data[] = [
      htmlspecialchars($row['hostname']),
      htmlspecialchars($row['domain']),
      htmlspecialchars($row['ip_address']),
      htmlspecialchars($row['operating_system']),
      htmlspecialchars($row['role']),
      htmlspecialchars($row['criticality']),
      $row['status'],
      $action
    ];
  }
}

// Output JSON
$response = [
  "draw" => $draw,
  "recordsTotal" => $totalRecords,
  "recordsFiltered" => $filteredRecords,
  "data" => $data
];
echo json_encode($response); 
