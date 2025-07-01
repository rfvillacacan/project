<?php
file_put_contents(__DIR__.'/reporting_api_debug.log', date('c')." REQUEST: ".json_encode($_GET)."\n", FILE_APPEND);
ob_start();
set_exception_handler(function($e) {
    file_put_contents(__DIR__.'/reporting_api_debug.log', "EXCEPTION: ".$e->getMessage()."\n", FILE_APPEND);
    http_response_code(500);
    echo json_encode(['error' => 'Exception', 'message' => $e->getMessage()]);
    exit();
});
set_error_handler(function($errno, $errstr, $errfile, $errline) {
    file_put_contents(__DIR__.'/reporting_api_debug.log', "PHP Error: $errstr in $errfile on line $errline\n", FILE_APPEND);
    http_response_code(500);
    echo json_encode(['error' => 'PHP Error', 'message' => $errstr, 'file' => $errfile, 'line' => $errline]);
    exit();
});
register_shutdown_function(function() {
    $error = error_get_last();
    if ($error && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
        file_put_contents(__DIR__.'/reporting_api_debug.log', "FATAL: {$error['message']} in {$error['file']} on line {$error['line']}\n", FILE_APPEND);
        http_response_code(500);
        echo json_encode(['error' => 'Fatal Error', 'message' => $error['message'], 'file' => $error['file'], 'line' => $error['line']]);
        exit();
    }
});
ob_end_clean();
session_start();
error_reporting(E_ERROR); // Only show fatal errors, suppress warnings/notices
header('Content-Type: application/json');
if (!isset($_SESSION['user_id'])) {
    file_put_contents(__DIR__.'/reporting_api_debug.log', "AUTH ERROR\n", FILE_APPEND);
    echo json_encode(['error' => 'Not authenticated']);
    exit();
}
require_once 'includes/config.php';

// Allowed tables (sync with dashboard6.php logic)
$showOnly = ['tbl_isprojects', 'daily_tasks', 'service_progress'];
$allowedTables = $showOnly;

$table = isset($_GET['table']) ? $_GET['table'] : '';
$action = isset($_GET['action']) ? $_GET['action'] : 'data';

if (!in_array($table, $allowedTables)) {
    echo json_encode(['error' => 'Invalid table']);
    exit;
}

// Helper: get columns
function get_columns($conn, $table) {
    $cols = [];
    $res = $conn->query("SHOW COLUMNS FROM `" . $conn->real_escape_string($table) . "`");
    while ($row = $res->fetch_assoc()) {
        $cols[] = $row['Field'];
    }
    return $cols;
}

if ($action === 'summary') {
    // Summary stats
    $cols = get_columns($conn, $table);
    $summary = [];
    $where = [];
    // Filters (date range, status)
    if (isset($_GET['date_from']) && isset($_GET['date_to'])) {
        foreach ($cols as $col) {
            if (stripos($col, 'date') !== false) {
                $where[] = "(`$col` >= '" . $conn->real_escape_string($_GET['date_from']) . "' AND `$col` <= '" . $conn->real_escape_string($_GET['date_to']) . "')";
                break;
            }
        }
    }
    if (isset($_GET['status'])) {
        foreach ($cols as $col) {
            if (stripos($col, 'status') !== false) {
                $where[] = "(`$col` = '" . $conn->real_escape_string($_GET['status']) . "')";
                break;
            }
        }
    }
    $whereSql = $where ? ('WHERE ' . implode(' AND ', $where)) : '';
    $sql = "SELECT COUNT(*) FROM `$table` $whereSql";
    $res = $conn->query($sql);
    if (!$res) {
        file_put_contents(__DIR__.'/reporting_api_debug.log', "SQL ERROR: $sql | " . $conn->error . "\n", FILE_APPEND);
        throw new Exception("SQL error: $sql | " . $conn->error);
    }
    $total = $res->fetch_row()[0];
    $summary['total'] = (int)$total;
    // Status breakdown
    foreach (['completed', 'in progress', 'pending', 'closed', 'open'] as $status) {
        foreach ($cols as $col) {
            if (stripos($col, 'status') !== false || stripos($col, 'progress') !== false) {
                $extra = $whereSql ? "$whereSql AND" : "WHERE";
                $sql2 = "SELECT COUNT(*) FROM `$table` $extra `$col` LIKE '%$status%'";
                $res2 = $conn->query($sql2);
                if (!$res2) {
                    file_put_contents(__DIR__.'/reporting_api_debug.log', "SQL ERROR: $sql2 | " . $conn->error . "\n", FILE_APPEND);
                    throw new Exception("SQL error: $sql2 | " . $conn->error);
                }
                $count = $res2->fetch_row()[0];
                $summary[$status] = (int)$count;
            }
        }
    }
    echo json_encode($summary);
    exit;
}

// Default: data
$cols = get_columns($conn, $table);
$where = [];
// Filters (date range, status)
if (isset($_GET['date_from']) && isset($_GET['date_to'])) {
    foreach ($cols as $col) {
        if (stripos($col, 'date') !== false) {
            $where[] = "(`$col` >= '" . $conn->real_escape_string($_GET['date_from']) . "' AND `$col` <= '" . $conn->real_escape_string($_GET['date_to']) . "')";
            break;
        }
    }
}
if (isset($_GET['status'])) {
    foreach ($cols as $col) {
        if (stripos($col, 'status') !== false) {
            $where[] = "(`$col` = '" . $conn->real_escape_string($_GET['status']) . "')";
            break;
        }
    }
}
$whereSql = $where ? ('WHERE ' . implode(' AND ', $where)) : '';
$sql = "SELECT * FROM `$table` $whereSql";
$res = $conn->query($sql);
$data = [];
while ($row = $res->fetch_assoc()) {
    $data[] = $row;
}
echo json_encode([
    'columns' => $cols,
    'data' => $data
]); 
