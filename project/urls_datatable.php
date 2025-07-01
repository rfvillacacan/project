<?php
require_once 'includes/config.php';
header('Content-Type: application/json');

// DataTables parameters
$draw = isset($_GET['draw']) ? intval($_GET['draw']) : 1;
$start = isset($_GET['start']) ? intval($_GET['start']) : 0;
$length = isset($_GET['length']) ? intval($_GET['length']) : 10;
$searchValue = isset($_GET['search']['value']) ? $conn->real_escape_string($_GET['search']['value']) : '';

$columns = [
  'urls.id', 'urls.url', 'urls.category', 'urls.status', 'urls.last_checked', 'urls.notes', 'servers.application_name'
];

// Build WHERE clause
$where = "WHERE 1=1";
if ($searchValue !== '') {
  $searchParts = [];
  foreach ($columns as $col) {
    $searchParts[] = "$col LIKE '%$searchValue%'";
  }
  $where .= " AND (" . implode(' OR ', $searchParts) . ")";
}

// Total records
$totalQuery = $conn->query("SELECT COUNT(*) FROM urls");
$totalRecords = $totalQuery ? $totalQuery->fetch_row()[0] : 0;

// Filtered records
$filteredQuery = $conn->query("SELECT COUNT(*) FROM urls LEFT JOIN servers ON urls.application_id = servers.id $where");
$filteredRecords = $filteredQuery ? $filteredQuery->fetch_row()[0] : 0;

// Data query
$data = [];
$sql = "SELECT urls.id, urls.url, urls.category, urls.status, urls.last_checked, urls.notes, urls.agent_online, urls.siem_monitored, urls.penetration_tested, urls.user_access_review, urls.vapt, urls.availability FROM urls $where ORDER BY urls.id DESC LIMIT $start, $length";
$result = $conn->query($sql);
if ($result) {
  while ($row = $result->fetch_assoc()) {
    $status = strtolower($row['status']) === 'active'
      ? '<span class="badge bg-success">Active</span>'
      : '<span class="badge bg-danger">' . htmlspecialchars($row['status']) . '</span>';
    $action = '<button type="button" class="btn btn-sm btn-warning edit-url-btn"'
      . ' data-id="' . htmlspecialchars($row['id']) . '"'
      . ' data-url="' . htmlspecialchars($row['url']) . '"'
      . ' data-category="' . htmlspecialchars($row['category']) . '"'
      . ' data-status="' . htmlspecialchars($row['status']) . '"'
      . ' data-last-checked="' . htmlspecialchars($row['last_checked']) . '"'
      . ' data-notes="' . htmlspecialchars($row['notes']) . '"'
      . '>Edit</button> '
      . '<button type="button" class="btn btn-sm btn-success check-url-btn"'
      . ' data-id="' . htmlspecialchars($row['id']) . '"'
      . ' data-url="' . htmlspecialchars($row['url']) . '"'
      . ' data-agent-online="' . htmlspecialchars($row['agent_online']) . '"'
      . ' data-siem-monitored="' . htmlspecialchars($row['siem_monitored']) . '"'
      . ' data-penetration-tested="' . htmlspecialchars($row['penetration_tested']) . '"'
      . ' data-user-access-review="' . htmlspecialchars($row['user_access_review']) . '"'
      . ' data-vapt="' . htmlspecialchars($row['vapt']) . '"'
      . ' data-availability="' . htmlspecialchars($row['availability']) . '"'
      . '>Check</button> '
      . '<button type="button" class="btn btn-sm btn-danger delete-url-btn" data-id="' . htmlspecialchars($row['id']) . '">Delete</button>';
    $data[] = [
      htmlspecialchars($row['id']),
      htmlspecialchars($row['url']),
      htmlspecialchars($row['category']),
      $status,
      htmlspecialchars($row['last_checked']),
      htmlspecialchars($row['notes']),
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
