<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ?p=login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Reports - Cybersecurity Project Management</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel='stylesheet' href='assets/report.css'>
  <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
  <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.1/css/buttons.bootstrap5.min.css">
  
</head>
<body>
  <nav class="navbar navbar-report px-4 py-2 d-flex justify-content-between align-items-center">
    <span class="navbar-brand">Reports - Cybersecurity Project Management</span>
    <span>
      <span class="me-3">Logged in as <b><?php echo htmlspecialchars($_SESSION['username']); ?></b></span>
      <a href="?p=logout.php" class="logout-btn">Logout</a>
    </span>
  </nav>
  <div class="container-report">
    <h2 class="mb-4" style="color:#f6ad55;">Reports</h2>
    <div class="row mb-3 align-items-center">
      <div class="col-md-4">
        <label for="report-table-select" class="form-label">Select Table</label>
        <select id="report-table-select" class="form-select"></select>
      </div>
      <div class="col-md-8 d-flex justify-content-end align-items-end">
        <div id="report-filters" class="d-flex gap-3"></div>
      </div>
    </div>
    <div class="row mb-4" id="report-summary-cards">
      <div class="col-12 text-center text-secondary">Summary stats will appear here.</div>
    </div>
    <div class="d-flex justify-content-end mb-3" id="report-export-btns"></div>
    <div class="card shadow-sm">
      <div class="card-body p-0">
        <div id="report-table-container">
          <table id="report-table" class="table table-dark table-striped table-bordered mb-0" style="width:100%;">
            <thead><tr><th>Loading...</th></tr></thead>
            <tbody></tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
  <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
  <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
  <script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script>
  <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.bootstrap5.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
  <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.html5.min.js"></script>
  <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.print.min.js"></script>
  <script src="assets/report.js.php"></script>
  
</body>
</html> 
