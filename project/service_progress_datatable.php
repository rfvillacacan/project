<?php
header('Content-Type: application/json');
require_once 'includes/config.php';

// DataTables parameters
$draw = isset($_GET['draw']) ? intval($_GET['draw']) : 1;
$start = isset($_GET['start']) ? intval($_GET['start']) : 0;
$length = isset($_GET['length']) ? intval($_GET['length']) : 10;
$searchValue = isset($_GET['search']['value']) ? $conn->real_escape_string($_GET['search']['value']) : '';

$columns = [
  'service', 'due_date', 'progress', 'comments'
];

// Build WHERE clause
$where = "WHERE 1";
if ($searchValue !== '') {
  $terms = preg_split('/\s+/', trim($searchValue));
  foreach ($terms as $term) {
    $term = $conn->real_escape_string($term);
    $searchParts = [];
    foreach ($columns as $col) {
      $searchParts[] = "$col LIKE '%$term%'";
    }
    $where .= " AND (" . implode(' OR ', $searchParts) . ")";
  }
}

// Total records
$totalQuery = $conn->query("SELECT COUNT(*) FROM service_progress");
$totalRecords = $totalQuery ? $totalQuery->fetch_row()[0] : 0;

// Filtered records
$filteredQuery = $conn->query("SELECT COUNT(*) FROM service_progress $where");
$filteredRecords = $filteredQuery ? $filteredQuery->fetch_row()[0] : 0;

// Data query
$data = [];
$sql = "SELECT id, service, due_date, progress, comments FROM service_progress $where ORDER BY id ASC LIMIT $start, $length";
$result = $conn->query($sql);
if ($result) {
  while ($row = $result->fetch_assoc()) {
    $action = '';
    if ($_SESSION['role'] === 'admin' || $_SESSION['role'] === 'operator') {
      $action = '<button class="btn btn-sm btn-warning edit-service-progress-btn"'
        . ' data-id="' . $row['id'] . '"'
        . ' data-service="' . htmlspecialchars($row['service'], ENT_QUOTES) . '"'
        . ' data-due_date="' . htmlspecialchars($row['due_date'], ENT_QUOTES) . '"'
        . ' data-progress="' . htmlspecialchars($row['progress'], ENT_QUOTES) . '"'
        . ' data-comments="' . htmlspecialchars($row['comments'], ENT_QUOTES) . '"'
        . '>Edit</button> ';
      $action .= '<button class="btn btn-sm btn-danger delete-service-progress-btn" data-id="' . $row['id'] . '">Delete</button>';
    }
    $data[] = [
      'id' => $row['id'],
      'service' => htmlspecialchars($row['service']),
      'due_date' => htmlspecialchars($row['due_date']),
      'progress' => htmlspecialchars($row['progress']),
      'comments' => nl2br(htmlspecialchars($row['comments'])),
      'action' => $action
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
