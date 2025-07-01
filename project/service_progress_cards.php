<?php
require_once 'includes/config.php';
header('Content-Type: application/json');
$result = $conn->query("SELECT id, service, due_date, progress, comments FROM service_progress");
$rows = array();
while($row = $result->fetch_assoc()) {
    $rows[] = $row;
}
echo json_encode($rows); 
