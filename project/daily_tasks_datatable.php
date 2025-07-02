<?php
require_once 'includes/config.php';
// daily_tasks_datatable.php
error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: application/json');

// DataTables params
$draw = intval($_GET['draw'] ?? 1);
$start = intval($_GET['start'] ?? 0);
$length = intval($_GET['length'] ?? 10);
$search = $_GET['search']['value'] ?? '';
$orderCol = $_GET['order'][0]['column'] ?? 0;
$orderDir = $_GET['order'][0]['dir'] ?? 'asc';

// Filtering
$shift = $_GET['shift'] ?? '';

// Columns in the table (add all columns, even if not displayed)
$columns = [
  'id', 'datetime', 'shift', 'task_description', 'assigned_to', 'created_by',
  'status', 'percent_completed', 'comment', 'project_id', 'due_date', 'priority',
  'task_category', 'estimated_time', 'time_spent'
];

// Build WHERE
$where = [];
$params = [];
$types = '';
if ($shift) {
  $where[] = "shift = ?";
  $params[] = $shift;
  $types .= 's';
}
if ($search) {
  $searchTerms = preg_split('/\s+/', trim($search));
  foreach ($searchTerms as $term) {
    $termWhere = [];
    foreach ($columns as $col) {
      if ($col === 'datetime') {
        // Match month names, and formatted date/time as displayed in the table
        $termWhere[] = "DATE_FORMAT(datetime, '%l:%i %p %b %e, %Y') LIKE ?";
        $params[] = "%$term%";
        $types .= 's';
        $termWhere[] = "DATE_FORMAT(datetime, '%b') LIKE ?";
        $params[] = "%$term%";
        $types .= 's';
        $termWhere[] = "DATE_FORMAT(datetime, '%M') LIKE ?";
        $params[] = "%$term%";
        $types .= 's';
      } else {
        $termWhere[] = "$col LIKE ?";
        $params[] = "%$term%";
        $types .= 's';
      }
    }
    $where[] = '(' . implode(' OR ', $termWhere) . ')';
  }
}
$whereSql = $where ? "WHERE " . implode(" AND ", $where) : "";

// Total records
$totalRecords = $conn->query("SELECT COUNT(*) FROM daily_tasks")->fetch_row()[0];

// Filtered records
$sql = "SELECT COUNT(*) FROM daily_tasks $whereSql";
$stmt = $conn->prepare($sql);
if ($params) $stmt->bind_param($types, ...$params);
$stmt->execute();
$stmt->bind_result($filteredRecords);
$stmt->fetch();
$stmt->close();

// Fetch data
$orderBy = $columns[$orderCol] ?? 'datetime';
$sql = "SELECT * FROM daily_tasks $whereSql ORDER BY $orderBy $orderDir LIMIT ?, ?";
$stmt = $conn->prepare($sql);
if ($params) {
    $types2 = $types . 'ii';
    $params2 = array_merge($params, [$start, $length]);
    $stmt->bind_param($types2, ...$params2);
} else {
    $stmt->bind_param('ii', $start, $length);
}
$stmt->execute();
$result = $stmt->get_result();

$data = [];
while ($row = $result->fetch_assoc()) {
  $data[] = $row;
}
$stmt->close();

echo json_encode([
  "draw" => $draw,
  "recordsTotal" => $totalRecords,
  "recordsFiltered" => $filteredRecords,
  "data" => $data
]);
