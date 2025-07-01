<?php
session_start();
require_once 'includes/config.php';

header('Content-Type: application/json');

// Only allow admin to update
function is_admin() {
  return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

if ($_SERVER['REQUEST_METHOD'] === 'GET' && $_GET['action'] === 'list') {
  $result = $conn->query("SELECT username, role, status FROM users ORDER BY username ASC");
  $users = [];
  while ($row = $result->fetch_assoc()) {
    $users[] = $row;
  }
  echo json_encode($users);
  exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $data = json_decode(file_get_contents('php://input'), true);
  if (!is_admin()) {
    echo json_encode(['success' => false, 'error' => 'Not authorized']);
    exit();
  }
  $username = trim($data['username'] ?? '');
  $role = trim($data['role'] ?? 'readonly');
  $status = trim($data['status'] ?? 'pending');
  // Prevent admin from changing their own role/status
  if ($username === $_SESSION['username']) {
    echo json_encode(['success' => false, 'error' => 'Cannot change your own role/status']);
    exit();
  }
  $allowed_roles = ['readonly', 'operator', 'admin'];
  $allowed_status = ['pending', 'active'];
  if (!in_array($role, $allowed_roles) || !in_array($status, $allowed_status)) {
    echo json_encode(['success' => false, 'error' => 'Invalid role or status']);
    exit();
  }
  $stmt = $conn->prepare("UPDATE users SET role=?, status=? WHERE username=?");
  $stmt->bind_param('sss', $role, $status, $username);
  if ($stmt->execute()) {
      echo json_encode(['success' => true]);
  } else {
      echo json_encode(['success' => false, 'error' => $conn->error]);
  }
  exit();
}

echo json_encode(['error' => 'Invalid request']); 
