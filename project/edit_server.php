<?php
session_start();
require_once 'includes/config.php';
if ($conn->connect_error) {
    http_response_code(500);
    echo "DB connection failed";
    exit;
}

if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    http_response_code(403);
    echo 'Invalid CSRF token';
    exit;
}

if (isset($_POST['agent_online']) && isset($_POST['siem_monitored']) && isset($_POST['penetration_tested']) && isset($_POST['user_access_review']) && isset($_POST['vapt']) && isset($_POST['availability'])) {
    // Only compliance fields are being updated (from Check modal)
    $id = intval($_POST['id']);
    $agent_online = trim($_POST['agent_online']);
    $siem_monitored = trim($_POST['siem_monitored']);
    $penetration_tested = trim($_POST['penetration_tested']);
    $user_access_review = trim($_POST['user_access_review']);
    $vapt = trim($_POST['vapt']);
    $availability = trim($_POST['availability']);
    $stmt = $conn->prepare("UPDATE servers SET agent_online=?, siem_monitored=?, penetration_tested=?, user_access_review=?, vapt=?, availability=? WHERE id=?");
    $stmt->bind_param('ssssssi', $agent_online, $siem_monitored, $penetration_tested, $user_access_review, $vapt, $availability, $id);
    if ($stmt->execute()) {
        echo "success";
    } else {
        http_response_code(500);
        echo "Update failed";
    }
    exit;
}

if (isset($_POST['delete']) && isset($_POST['id'])) {
    $id = intval($_POST['id']);
    $stmt = $conn->prepare("DELETE FROM servers WHERE id=?");
    $stmt->bind_param('i', $id);
    if ($stmt->execute()) {
        echo "success";
    } else {
        http_response_code(500);
        echo "Delete failed";
    }
    exit;
}

// Only run update logic if all required fields are present
if (isset($_POST['id'], $_POST['name'], $_POST['domain'], $_POST['ip'], $_POST['os'], $_POST['appname'], $_POST['status'], $_POST['notes'])) {
    $id = intval($_POST['id']);
    $name = trim($_POST['name']);
    $domain = trim($_POST['domain']);
    $ip = trim($_POST['ip']);
    $os = trim($_POST['os']);
    $appname = trim($_POST['appname']);
    $status = trim($_POST['status']);
    $notes = trim($_POST['notes']);

    $stmt = $conn->prepare("UPDATE servers SET name=?, domain=?, ip=?, operating_system=?, application_name=?, status=?, notes=? WHERE id=?");
    $stmt->bind_param('sssssssi', $name, $domain, $ip, $os, $appname, $status, $notes, $id);
    if ($stmt->execute()) {
        echo "success";
    } else {
        http_response_code(500);
        echo "Update failed";
    }
    exit;
} 
