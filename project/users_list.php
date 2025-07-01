<?php
require_once 'includes/config.php';
$result = $conn->query("SELECT username FROM users ORDER BY username ASC");
$users = [];
while ($row = $result->fetch_assoc()) {
    $users[] = $row['username'];
}
echo json_encode($users);
