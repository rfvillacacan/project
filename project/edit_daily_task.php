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
$shift = $_POST['shift'] ?? '';
$task_description = $_POST['task_description'] ?? '';
$responsibility = $_POST['responsibility'] ?? '';
$assigned_to = $_POST['assigned_to'] ?? '';
$required_action = $_POST['required_action'] ?? '';
$status = $_POST['status'] ?? '';
$percent_completed = isset($_POST['percent_completed']) ? intval($_POST['percent_completed']) : 0;
$comment = $_POST['comment'] ?? '';

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
    $stmt = $conn->prepare("UPDATE daily_tasks SET datetime=?, shift=?, task_description=?, responsibility=?, assigned_to=?, required_action=?, status=?, percent_completed=?, comment=? WHERE id=?");
    $stmt->bind_param('sssssssisi', $datetime, $shift, $task_description, $responsibility, $assigned_to, $required_action, $status, $percent_completed, $comment, $id);
    $stmt->execute();
    echo $stmt->affected_rows !== false ? 'success' : 'fail';
  }
  // Original permission logic for non-admin users
  else if ($current_user !== $created_by && $current_user !== $assigned_to_db) {
    echo 'Permission denied';
    exit;
  }
  else if ($current_user === $created_by) {
    // Creator: can update all fields (9 fields + id = 10 params)
    $stmt = $conn->prepare("UPDATE daily_tasks SET datetime=?, shift=?, task_description=?, responsibility=?, assigned_to=?, required_action=?, status=?, percent_completed=?, comment=? WHERE id=?");
    $stmt->bind_param('sssssssisi', $datetime, $shift, $task_description, $responsibility, $assigned_to, $required_action, $status, $percent_completed, $comment, $id);
    $stmt->execute();
    echo $stmt->affected_rows !== false ? 'success' : 'fail';
  } else if ($current_user === $assigned_to_db) {
    // Assigned user: can only update status, percent_completed, comment (3 fields + id = 4 params)
    $stmt = $conn->prepare("UPDATE daily_tasks SET status=?, percent_completed=?, comment=? WHERE id=?");
    $stmt->bind_param('sisi', $status, $percent_completed, $comment, $id);
    $stmt->execute();
    echo $stmt->affected_rows !== false ? 'success' : 'fail';
  }
} else {
  // Insert: set created_by to current user (9 fields + created_by = 10 params)
  $created_by = $current_user;
  $stmt = $conn->prepare("INSERT INTO daily_tasks (created_by, datetime, shift, task_description, responsibility, assigned_to, required_action, status, percent_completed, comment) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
  $stmt->bind_param('ssssssssis', $created_by, $datetime, $shift, $task_description, $responsibility, $assigned_to, $required_action, $status, $percent_completed, $comment);
  $stmt->execute();
  echo $stmt->affected_rows ? 'success' : 'fail';
}
