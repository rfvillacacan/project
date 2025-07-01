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
        $stmt = $conn->prepare("DELETE FROM tbl_isprojects WHERE id=?");
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
    $assign_to_team = $_POST['assign_to_team'];
    $comments = $_POST['comments'];

    // Check if user is admin for updates
    if ($id > 0 && $_SESSION['role'] !== 'admin') {
        echo 'Permission denied';
        exit;
    }

    if ($id > 0) {
        $stmt = $conn->prepare("UPDATE tbl_isprojects SET service=?, due_date=?, progress=?, assign_to_team=?, comments=? WHERE id=?");
        $stmt->bind_param("sssssi", $service, $due_date, $progress, $assign_to_team, $comments, $id);
    } else {
        $stmt = $conn->prepare("INSERT INTO tbl_isprojects (service, due_date, progress, assign_to_team, comments) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sssss", $service, $due_date, $progress, $assign_to_team, $comments);
    }

    if ($stmt->execute()) {
        echo 'success';
    } else {
        echo 'error';
    }
    exit;
}
?> 
