<?php
require_once 'includes/config.php';
header('Content-Type: application/json');
$online = $conn->query("SELECT COUNT(*) FROM servers WHERE type='Application' AND agent_online='Yes'")->fetch_row()[0];
$total = $conn->query("SELECT COUNT(*) FROM servers WHERE type='Application'")->fetch_row()[0];
echo json_encode(["online" => (int)$online, "total" => (int)$total]); 
