<?php
require_once __DIR__ . '/../includes/config.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Project & Task Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="assets/project_management.css">
</head>
<body class="bg-light">
<div class="container py-4">
    <h1 class="mb-4">Project & Task Management</h1>
    <ul class="nav nav-tabs mb-3" id="pmTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="projects-tab" data-bs-toggle="tab" data-bs-target="#projects" type="button" role="tab">Projects</button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="tasks-tab" data-bs-toggle="tab" data-bs-target="#tasks" type="button" role="tab">Tasks</button>
        </li>
    </ul>
    <div class="tab-content" id="pmTabsContent">
        <div class="tab-pane fade show active" id="projects" role="tabpanel">
            <div class="table-responsive">
                <table id="projectsTable" class="table table-dark table-striped w-100">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Start Date</th>
                            <th>Due Date</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
        <div class="tab-pane fade" id="tasks" role="tabpanel">
            <div class="table-responsive">
                <table id="tasksTable" class="table table-dark table-striped w-100">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Project</th>
                            <th>Title</th>
                            <th>Assigned To</th>
                            <th>Status</th>
                            <th>Due Date</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
<script src="assets/project_management.js"></script>
</body>
</html>
