<?php
require_once 'includes/config.php';

$action = $_REQUEST['action'] ?? '';

switch ($action) {
    case 'fetch':
        // Fetch logbook entries (with optional filters)
        $shift = $_GET['shift'] ?? '';
        $query = "SELECT * FROM logbook";
        $params = [];
        if ($shift) {
            $query .= " WHERE shift = ?";
            $params[] = $shift;
        }
        $query .= " ORDER BY date DESC, time DESC, id DESC";
        $stmt = $conn->prepare($query);
        if ($params) $stmt->bind_param(str_repeat('s', count($params)), ...$params);
        $stmt->execute();
        $result = $stmt->get_result();
        $rows = [];
        while ($row = $result->fetch_assoc()) {
            $rows[] = $row;
        }
        echo json_encode($rows);
        break;

    case 'add':
        // Add new logbook entry
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
            exit;
        }
        $fields = ['shift','date','time','activity','status','action_needed','notes','assigned_to','severity','category','is_handover'];
        $data = [];
        foreach ($fields as $f) {
            $data[$f] = $_POST[$f] ?? null;
        }
        $data['created_by'] = $_SESSION['username'] ?? 'unknown';
        $attachment = null;
        if (!empty($_FILES['attachment']['name'])) {
            $uploadDir = __DIR__ . '/logbookfiles/';
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
            $allowed = ['jpg','jpeg','png','pdf'];
            $maxSize = 5 * 1024 * 1024;
            $ext = strtolower(pathinfo($_FILES['attachment']['name'], PATHINFO_EXTENSION));
            if (!in_array($ext, $allowed)) {
                echo json_encode(['error' => 'Invalid file type']); exit;
            }
            if ($_FILES['attachment']['size'] > $maxSize) {
                echo json_encode(['error' => 'File too large']); exit;
            }
            $filename = uniqid('log_', true) . '.' . $ext;
            $filepath = $uploadDir . $filename;
            if (!move_uploaded_file($_FILES['attachment']['tmp_name'], $filepath)) {
                echo json_encode(['error' => 'Upload failed']); exit;
            }
            $attachment = $filename;
        }
        $stmt = $conn->prepare("INSERT INTO logbook (shift,date,time,activity,status,action_needed,notes,assigned_to,severity,category,is_handover,attachment,created_by) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?)");
        $stmt->bind_param('sssssssssssss', $data['shift'],$data['date'],$data['time'],$data['activity'],$data['status'],$data['action_needed'],$data['notes'],$data['assigned_to'],$data['severity'],$data['category'],$data['is_handover'],$attachment,$data['created_by']);
        $stmt->execute();
        echo json_encode(['success' => true, 'id' => $conn->insert_id]);
        break;

    case 'edit':
        // Edit logbook entry (admin only)
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
            exit;
        }
        if (!isset($_POST['id'])) {
            echo json_encode(['error' => 'Missing ID']); exit;
        }
        $fields = ['shift','date','time','activity','status','action_needed','notes','assigned_to','severity','category','is_handover'];
        $data = [];
        foreach ($fields as $f) {
            $data[$f] = $_POST[$f] ?? null;
        }
        $id = intval($_POST['id']);
        $attachment = null;
        if (!empty($_FILES['attachment']['name'])) {
            $uploadDir = __DIR__ . '/logbookfiles/';
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
            $allowed = ['jpg','jpeg','png','pdf'];
            $maxSize = 5 * 1024 * 1024;
            $ext = strtolower(pathinfo($_FILES['attachment']['name'], PATHINFO_EXTENSION));
            if (!in_array($ext, $allowed)) {
                echo json_encode(['error' => 'Invalid file type']); exit;
            }
            if ($_FILES['attachment']['size'] > $maxSize) {
                echo json_encode(['error' => 'File too large']); exit;
            }
            $filename = uniqid('log_', true) . '.' . $ext;
            $filepath = $uploadDir . $filename;
            if (!move_uploaded_file($_FILES['attachment']['tmp_name'], $filepath)) {
                echo json_encode(['error' => 'Upload failed']); exit;
            }
            $attachment = $filename;
        }
        $sql = "UPDATE logbook SET shift=?,date=?,time=?,activity=?,status=?,action_needed=?,notes=?,assigned_to=?,severity=?,category=?,is_handover=?";
        $params = [$data['shift'],$data['date'],$data['time'],$data['activity'],$data['status'],$data['action_needed'],$data['notes'],$data['assigned_to'],$data['severity'],$data['category'],$data['is_handover']];
        if ($attachment) {
            $sql .= ",attachment=?";
            $params[] = $attachment;
        }
        $sql .= " WHERE id=?";
        $params[] = $id;
        $types = str_repeat('s', count($params)-1) . 'i';
        $stmt = $conn->prepare($sql);
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        echo json_encode(['success' => true]);
        break;

    case 'delete':
        // Delete logbook entry (admin or creator)
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
            exit;
        }
        if (!isset($_POST['id'])) {
            echo json_encode(['error' => 'Missing ID']); exit;
        }
        $id = intval($_POST['id']);
        
        // Check if user is admin or creator of the entry
        $stmt = $conn->prepare("SELECT created_by FROM logbook WHERE id=?");
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $stmt->bind_result($created_by);
        $stmt->fetch();
        $stmt->close();
        
        if (!isset($_SESSION['role']) || ($_SESSION['role'] !== 'admin' && $created_by !== $_SESSION['username'])) {
            http_response_code(403);
            echo json_encode(['error' => 'Permission denied']);
            exit;
        }
        
        // Optionally delete attachment file
        $stmt = $conn->prepare("SELECT attachment FROM logbook WHERE id=?");
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $stmt->bind_result($attachment);
        $stmt->fetch();
        $stmt->close();
        if ($attachment) {
            $filepath = __DIR__ . '/logbookfiles/' . $attachment;
            if (file_exists($filepath)) unlink($filepath);
        }
        $stmt = $conn->prepare("DELETE FROM logbook WHERE id=?");
        $stmt->bind_param('i', $id);
        $stmt->execute();
        echo json_encode(['success' => true]);
        break;

    case 'download':
        // Secure download of attachment
        if (!isset($_GET['file'])) {
            http_response_code(400);
            echo 'Missing file'; exit;
        }
        $filename = basename($_GET['file']);
        $filepath = __DIR__ . '/logbookfiles/' . $filename;
        $allowed = ['jpg','jpeg','png','pdf'];
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        if (!in_array($ext, $allowed) || !file_exists($filepath)) {
            http_response_code(404);
            echo 'File not found'; exit;
        }
        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($filepath));
        readfile($filepath);
        exit;
        
    default:
        http_response_code(400);
        echo json_encode(['error' => 'Invalid action']);
        break;
} 
