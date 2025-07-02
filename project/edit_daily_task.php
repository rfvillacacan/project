<?php
session_start();
require_once 'includes/config.php';

if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
  http_response_code(403);
  echo 'Invalid CSRF token';
  exit;
}

$current_user = $_SESSION['username'];

if (isset($_POST['delete']) && isset($_POST['id'])) {
  $id = $_POST['id'];
  // Permission check for delete: admin, creator or assigned user can delete
  $stmt = $conn->prepare("SELECT created_by, assigned_to FROM daily_tasks WHERE id=?");
  $stmt->bind_param('i', $id);
  $stmt->execute();
  $stmt->bind_result($created_by, $assigned_to);
  $stmt->fetch();
  $stmt->close();
  if ($_SESSION['role'] !== 'admin' && $current_user !== $created_by && $current_user !== $assigned_to) {
    echo 'Permission denied';
    exit;
  }
  $stmt = $conn->prepare("DELETE FROM daily_tasks WHERE id=?");
  $stmt->bind_param('i', $id);
  $stmt->execute();
  echo $stmt->affected_rows ? 'success' : 'fail';
  exit;
}

$id = $_POST['id'] ?? null;
$datetime = $_POST['datetime'] ?? '';
$task_description = $_POST['task_description'] ?? '';
$assigned_to = $_POST['assigned_to'] ?? '';
$status = $_POST['status'] ?? '';
$comment = $_POST['comment'] ?? '';
$due_date = $_POST['due_date'] ?? null;
$priority = $_POST['priority'] ?? 'Medium';

if ($id) {
  // Update: fetch the task first
  $stmt = $conn->prepare("SELECT created_by, assigned_to FROM daily_tasks WHERE id=?");
  $stmt->bind_param('i', $id);
  $stmt->execute();
  $stmt->bind_result($created_by, $assigned_to_db);
  $stmt->fetch();
  $stmt->close();
  
  // Allow admin to edit all fields
  if ($_SESSION['role'] === 'admin') {
    $stmt = $conn->prepare("UPDATE daily_tasks SET datetime=?, task_description=?, assigned_to=?, status=?, comment=?, due_date=?, priority=? WHERE id=?");
    $stmt->bind_param('sssssssi', $datetime, $task_description, $assigned_to, $status, $comment, $due_date, $priority, $id);
    $stmt->execute();
    echo $stmt->affected_rows !== false ? 'success' : 'fail';
  }
  // Original permission logic for non-admin users
  else if ($current_user !== $created_by && $current_user !== $assigned_to_db) {
    echo 'Permission denied';
    exit;
  }
  else if ($current_user === $created_by) {
    // Creator: can update all fields
    $stmt = $conn->prepare("UPDATE daily_tasks SET datetime=?, task_description=?, assigned_to=?, status=?, comment=?, due_date=?, priority=? WHERE id=?");
    $stmt->bind_param('sssssssi', $datetime, $task_description, $assigned_to, $status, $comment, $due_date, $priority, $id);
    $stmt->execute();
    echo $stmt->affected_rows !== false ? 'success' : 'fail';
  } else if ($current_user === $assigned_to_db) {
    // Assigned user: can update status and comment
    $stmt = $conn->prepare("UPDATE daily_tasks SET status=?, comment=? WHERE id=?");
    $stmt->bind_param('ssi', $status, $comment, $id);
    $stmt->execute();
    echo $stmt->affected_rows !== false ? 'success' : 'fail';
  }
} else {
  // Insert: set created_by to current user
  $created_by = $current_user;
  $stmt = $conn->prepare("INSERT INTO daily_tasks (created_by, datetime, task_description, assigned_to, status, comment, due_date, priority) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
  $stmt->bind_param('ssssssss', $created_by, $datetime, $task_description, $assigned_to, $status, $comment, $due_date, $priority);
  $stmt->execute();
  echo $stmt->affected_rows ? 'success' : 'fail';
}
