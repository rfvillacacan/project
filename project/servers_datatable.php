<?php
require_once 'includes/config.php';
header('Content-Type: application/json');

$type = isset($_GET['type']) ? $conn->real_escape_string($_GET['type']) : '';

// DataTables parameters
$draw = isset($_GET['draw']) ? intval($_GET['draw']) : 1;
$start = isset($_GET['start']) ? intval($_GET['start']) : 0;
$length = isset($_GET['length']) ? intval($_GET['length']) : 10;
$searchValue = isset($_GET['search']['value']) ? $conn->real_escape_string($_GET['search']['value']) : '';

$columns = [
  'name', 'domain', 'ip', 'operating_system', 'application_name', 'status'
];

// Build WHERE clause
$where = "WHERE type='$type'";
if ($searchValue !== '') {
  $searchParts = [];
  foreach ($columns as $col) {
    $searchParts[] = "$col LIKE '%$searchValue%'";
  }
  $where .= " AND (" . implode(' OR ', $searchParts) . ")";
}

// Total records
$totalQuery = $conn->query("SELECT COUNT(*) FROM servers WHERE type='$type'");
$totalRecords = $totalQuery ? $totalQuery->fetch_row()[0] : 0;

// Filtered records
$filteredQuery = $conn->query("SELECT COUNT(*) FROM servers $where");
$filteredRecords = $filteredQuery ? $filteredQuery->fetch_row()[0] : 0;

// Data query
$data = [];
$sql = "SELECT id, name, domain, ip, operating_system, application_name, status, agent_online, siem_monitored, penetration_tested, user_access_review, vapt, availability, notes FROM servers $where LIMIT $start, $length";
$result = $conn->query($sql);
if ($result) {
  while ($row = $result->fetch_assoc()) {
    $row['status'] = strtolower($row['status']) === 'online'
      ? '<span class="badge bg-success">Online</span>'
      : '<span class="badge bg-danger">Offline</span>';
    $action = '<button class="btn btn-sm btn-warning edit-server-btn"'
        . ' data-id="' . htmlspecialchars($row['id']) . '"'
        . ' data-name="' . htmlspecialchars($row['name']) . '"'
        . ' data-domain="' . htmlspecialchars($row['domain']) . '"'
        . ' data-ip="' . htmlspecialchars($row['ip']) . '"'
        . ' data-os="' . htmlspecialchars($row['operating_system']) . '"'
        . ' data-appname="' . htmlspecialchars($row['application_name']) . '"'
        . ' data-status="' . htmlspecialchars($row['status']) . '"'
        . ' data-notes="' . htmlspecialchars($row['notes'] ?? '') . '"'
        . '>Edit</button> '
        . '<button class="btn btn-sm btn-success check-server-btn"'
        . ' data-id="' . htmlspecialchars($row['id']) . '"'
        . ' data-name="' . htmlspecialchars($row['name']) . '"'
        . ' data-ip="' . htmlspecialchars($row['ip']) . '"'
        . ' data-agent-online="' . htmlspecialchars($row['agent_online']) . '"'
        . ' data-siem-monitored="' . htmlspecialchars($row['siem_monitored']) . '"'
        . ' data-penetration-tested="' . htmlspecialchars($row['penetration_tested']) . '"'
        . ' data-user-access-review="' . htmlspecialchars($row['user_access_review']) . '"'
        . ' data-vapt="' . htmlspecialchars($row['vapt']) . '"'
        . ' data-availability="' . htmlspecialchars($row['availability']) . '"'
        . '>Check</button> '
        . '<button class="btn btn-sm btn-danger delete-server-btn" data-id="' . htmlspecialchars($row['id']) . '">Delete</button>';
    $data[] = [
      htmlspecialchars($row['name']),
      htmlspecialchars($row['domain']),
      htmlspecialchars($row['ip']),
      htmlspecialchars($row['operating_system']),
      htmlspecialchars($row['application_name']),
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
