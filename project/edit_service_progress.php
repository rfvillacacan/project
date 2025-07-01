<?php
require_once 'includes/config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        http_response_code(403);
        echo 'Invalid CSRF token';
        exit;
    }
    if (isset($_POST['delete']) && isset($_POST['id'])) {
        $id = intval($_POST['id']);
        // Check if user is admin
        if ($_SESSION['role'] !== 'admin') {
            echo 'Permission denied';
            exit;
        }
        $stmt = $conn->prepare("DELETE FROM service_progress WHERE id=?");
        $stmt->bind_param("i", $id);
        if ($stmt->execute()) {
            echo 'success';
        } else {
            echo 'error';
        }
        exit;
    }

    $id = intval($_POST['id']);
    $service = $_POST['service'];
    $due_date = $_POST['due_date'];
    $progress = $_POST['progress'];
    $comments = $_POST['comments'];

    // Check if user is admin for updates
    if ($id > 0 && $_SESSION['role'] !== 'admin') {
        echo 'Permission denied';
        exit;
    }

    if ($id > 0) {
        $stmt = $conn->prepare("UPDATE service_progress SET service=?, due_date=?, progress=?, comments=? WHERE id=?");
        $stmt->bind_param("ssssi", $service, $due_date, $progress, $comments, $id);
    } else {
        $stmt = $conn->prepare("INSERT INTO service_progress (service, due_date, progress, comments) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $service, $due_date, $progress, $comments);
    }

    if ($stmt->execute()) {
        echo 'success';
    } else {
        echo 'error';
    }
    exit;
}
?> 
