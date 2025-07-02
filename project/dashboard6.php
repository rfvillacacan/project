<?php
if (basename(__FILE__) == basename($_SERVER['SCRIPT_FILENAME'])) {
    header('Location: index.php?page=dashboard');
    exit();
}
// session_start(); // Removed because session is already started in index.php
require_once 'includes/dashboard_functions.php';

// Check if user is not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ?p=login.php");
    exit();
}

// Allow only admin users to access this page
if (($_SESSION['role'] ?? '') !== 'admin') {
    // Redirect non-admin users to their dashboard
    header('Location: ?p=team_task_dashboard.php');
    exit();
}


// --- Reporting: Get all tables, with hide config ---
$hiddenTables = [
    // Add any tables you want to hide here (case-sensitive)
    'users', 'servers', 'network_devices', 'urls', 'service_progress', 'some_sensitive_table'
];
$showOnly = ['tbl_isprojects', 'daily_tasks', 'service_progress']; // If you want to only show these, comment this line to show all except hidden

$allTables = get_table_list($conn, $hiddenTables, $showOnly);
$jsTableList = json_encode($allTables);

$metrics = get_dashboard_metrics($conn);
extract($metrics);

// Server health
$servers = $conn->query("SELECT name, ip, status, domain, operating_system, application_name FROM servers");

// Vulnerabilities (TEMP: using servers table criticality)
$high = $conn->query("SELECT COUNT(*) FROM servers WHERE criticality='High'")->fetch_row()[0];
$medium = $conn->query("SELECT COUNT(*) FROM servers WHERE criticality='Medium'")->fetch_row()[0];
$low = $conn->query("SELECT COUNT(*) FROM servers WHERE criticality='Low'")->fetch_row()[0];

// Security incidents (TEMP: using servers table for critical servers)
$incidents = fetch_incidents($conn);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>CyberSecurity Project & Task Management</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel='stylesheet' href='assets/dashboard6.css'>
  <!-- DataTables CSS -->
  <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
  
</head>
<body class="text-light">
  <div class="user-info">
    <div>
      <span class="username"><?php echo htmlspecialchars($_SESSION['username']); ?></span>
      <span class="role">(<?php echo htmlspecialchars($_SESSION['role']); ?>)</span>
    </div>
    <a href="?p=logout.php" class="badge text-decoration-none">Logout</a>
  </div>

  <div class="container-fluid py-4" id="dashboard-main-container" style="max-width:70vw; width:70vw; margin:0 auto;">
    <div class="d-flex justify-content-between align-items-baseline mb-4">
      <h1 class="mb-0">
        <span id="dashboard-title">CyberSecurity Project & Task Management - Overview</span>
      </h1>
      <div class="d-flex align-items-center">
        <div id="dashboard-datetime" style="min-width: 220px; text-align: right; margin-right: 40px;">
          <div id="dashboard-time" style="font-size:3.2rem; color:#f6ad55; font-weight:bold; line-height:1;"></div>
          <div id="dashboard-date" style="font-size:1rem; color:#fff; opacity:0.8;"></div>
        </div>
        <div class="me-3" id="dashboard-width-radios" style="font-size:1rem;">
          <label class="me-1"><input type="radio" name="dashboard-width" value="90"> 90%</label>
          <label class="me-1"><input type="radio" name="dashboard-width" value="80"> 80%</label>
          <label><input type="radio" name="dashboard-width" value="70" checked> 70%</label>
        </div>
        <button id="toggleTabs" class="btn btn-outline-light me-2" style="height:fit-content;">
          <i class="fas fa-chevron-up"></i> Hide Tabs
        </button>
      </div>
    </div>
    <ul class="nav nav-tabs mb-4" id="dashboardTabs" role="tablist">
      <li class="nav-item" role="presentation">
        <button class="nav-link active" id="overview-tab" data-bs-toggle="tab" data-bs-target="#overview" type="button" role="tab">Overview</button>
      </li>
      <li class="nav-item" role="presentation">
        <button class="nav-link" id="dailytask-tab" data-bs-toggle="tab" data-bs-target="#dailytask" type="button" role="tab">Daily Task Tracking</button>
      </li>
      <li class="nav-item" role="presentation">
        <button class="nav-link" id="projecttask-tab" data-bs-toggle="tab" data-bs-target="#projecttask" type="button" role="tab">Project and Task Management</button>
      </li>
      <li class="nav-item" role="presentation">
        <a class="nav-link" id="reports-tab" href="report.php" target="_blank" role="tab">Reports</a>
      </li>
      <li class="nav-item" role="presentation" id="usermanagement-main-tab-li" style="display:none;">
        <button class="nav-link" id="usermanagement-main-tab" data-bs-toggle="tab" data-bs-target="#usermanagement-main" type="button" role="tab">User Management</button>
      </li>
    </ul>
    <div class="tab-content" id="dashboardTabsContent">
      <div class="tab-pane fade show active" id="overview" role="tabpanel">
        <ul class="nav nav-tabs mb-3" id="overviewSubTabs" role="tablist">
          <li class="nav-item" role="presentation">
            <button class="nav-link active" id="taskanalytics-tab" data-bs-toggle="tab" data-bs-target="#taskanalytics" type="button" role="tab">Daily Task Analytics</button>
          </li>
          <li class="nav-item" role="presentation">
            <button class="nav-link" id="taskcards-tab" data-bs-toggle="tab" data-bs-target="#taskcards" type="button" role="tab">Daily Task Cards</button>
          </li>
        </ul>
        <div class="tab-content" id="overviewSubTabsContent">
          <div class="tab-pane fade show active" id="taskanalytics" role="tabpanel">
            <div class="row g-4 mb-4">
              <div class="col-6 col-md-2">
                <div class="summary-card p-4 text-center shadow" style="border-top: 4px solid #0dcaf0; border-radius: 1rem;">
                  <div class="summary-title">Total Tasks</div>
                  <div class="summary-value" id="task-total" style="font-size:2.5rem; font-weight:900; color:#0dcaf0;">...</div>
                </div>
              </div>
              <div class="col-6 col-md-2">
                <div class="summary-card p-4 text-center shadow" style="border-top: 4px solid #198754; border-radius: 1rem;">
                  <div class="summary-title">Completed</div>
                  <div class="summary-value text-success" id="task-completed" style="font-size:2.5rem; font-weight:900; color:#198754;">...</div>
                </div>
              </div>
              <div class="col-6 col-md-2">
                <div class="summary-card p-4 text-center shadow" style="border-top: 4px solid #ffc107; border-radius: 1rem;">
                  <div class="summary-title">In Progress</div>
                  <div class="summary-value text-warning" id="task-inprogress" style="font-size:2.5rem; font-weight:900; color:#ffc107;">...</div>
                </div>
              </div>
              <div class="col-6 col-md-2">
                <div class="summary-card p-4 text-center shadow" style="border-top: 4px solid #dc3545; border-radius: 1rem;">
                  <div class="summary-title">Pending</div>
                  <div class="summary-value text-danger" id="task-pending" style="font-size:2.5rem; font-weight:900; color:#dc3545;">...</div>
                </div>
              </div>
              <div class="col-6 col-md-2">
                <div class="summary-card p-4 text-center shadow" style="border-top: 4px solid #0dcaf0; border-radius: 1rem;">
                  <div class="summary-title">Completion Rate</div>
                  <div class="summary-value text-info" id="task-completion-rate" style="font-size:2.5rem; font-weight:900; color:#0dcaf0;">...</div>
                </div>
              </div>
            </div>
            <div class="row g-4 mb-4 align-items-stretch">
              <div class="col-md-6">
                <div class="card shadow-sm h-100">
                  <div class="card-header fw-bold">Task Status Distribution</div>
                  <div class="card-body text-center">
                    <canvas id="task-status-pie" width="80" height="80"></canvas>
                  </div>
                </div>
              </div>
              <div class="col-md-6">
                <div class="card shadow-sm h-100">
                  <div class="card-header fw-bold">Task Status Bar Chart</div>
                  <div class="card-body text-center">
                    <canvas id="task-status-bar" width="80" height="80"></canvas>
                  </div>
                </div>
              </div>
            </div>
            <div class="row g-4 mb-4">
              <div class="col-12">
                <div class="card shadow-sm mb-3">
                  <div class="card-header fw-bold">Overall Completion</div>
                  <div class="card-body">
                    <div class="progress" style="height: 2.5rem; border-radius: 1.2rem; background: #23272b;">
                      <div id="task-completion-bar" class="progress-bar bg-success" role="progressbar" style="width: 0%; font-size:1.5rem; font-weight:900; border-radius: 1.2rem; background: linear-gradient(90deg, #198754 60%, #0dcaf0 100%);" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">0%</div>
                    </div>
                  </div>
                </div>
                <div class="d-flex justify-content-center mb-3">
                  <div class="btn-group" role="group" aria-label="Task Analytics Display Mode">
                    <button type="button" class="btn btn-outline-light active" id="task-analytics-pagination-btn">Enable Pagination</button>
                    <button type="button" class="btn btn-outline-warning" id="task-analytics-showall-btn">Display All</button>
                  </div>
                </div>
                <div class="card shadow-sm">
                  <div class="card-header fw-bold">Latest Tasks</div>
                  <div class="card-body p-0">
                    <table class="table table-dark mb-0">
                      <thead>
                        <tr>
                          <th>Date/Time</th>
                          <th>Description</th>
                          <th>Status</th>
                        </tr>
                      </thead>
                      <tbody id="task-latest-tasks">
                        <tr><td colspan="3">Loading...</td></tr>
                      </tbody>
                    </table>
                    <div class="d-flex justify-content-between align-items-center mt-3">
                      <div class="dataTables_info" id="task-latest-tasks_info" role="status" aria-live="polite"></div>
                      <div class="dataTables_paginate paging_simple_numbers" id="task-latest-tasks_paginate">
                        <ul class="pagination mb-0">
                          <li class="paginate_button previous disabled" id="task-latest-tasks_previous">
                            <a href="#" aria-controls="task-latest-tasks" data-dt-idx="0" tabindex="0" class="page-link">Previous</a>
                          </li>
                          <li class="paginate_button next" id="task-latest-tasks_next">
                            <a href="#" aria-controls="task-latest-tasks" data-dt-idx="1" tabindex="0" class="page-link">Next</a>
                          </li>
                        </ul>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div class="tab-pane fade" id="taskcards" role="tabpanel">
            <div class="d-flex justify-content-center mb-3">
              <div class="btn-group" role="group" aria-label="Task Status Filter">
                <button type="button" class="btn btn-outline-light active" id="filter-all">All</button>
                <button type="button" class="btn btn-outline-warning" id="filter-inprogress">In Progress</button>
                <button type="button" class="btn btn-outline-danger" id="filter-pending">Pending</button>
                <button type="button" class="btn btn-outline-success" id="filter-completed">Completed</button>
              </div>
            </div>
            <div class="row gx-2 gy-3 d-flex align-items-stretch" id="task-cards-row">
              <!-- Cards will be injected here by JS -->
            </div>
          </div>
          <div class="tab-pane fade" id="servicecards" role="tabpanel">
            <div class="d-flex justify-content-center mb-3">
              <div class="btn-group" role="group" aria-label="Service Status Filter">
                <button type="button" class="btn btn-outline-light active" id="service-filter-all">All</button>
                <button type="button" class="btn btn-outline-warning" id="service-filter-inprogress">In Progress</button>
                <button type="button" class="btn btn-outline-danger" id="service-filter-pending">Pending</button>
                <button type="button" class="btn btn-outline-success" id="service-filter-completed">Completed</button>
              </div>
            </div>
            <div class="row gx-2 gy-3 d-flex align-items-stretch" id="service-cards-row">
              <!-- Cards will be injected here by JS -->
            </div>
          </div>
          <div class="tab-pane fade" id="serviceanalytics" role="tabpanel">
            <div class="row g-4 mb-4">
              <div class="col-6 col-md-2">
                <div class="summary-card p-4 text-center shadow" style="border-top: 4px solid #0dcaf0; border-radius: 1rem;">
                  <div class="summary-title">Total Services</div>
                  <div class="summary-value" id="service-total" style="font-size:2.5rem; font-weight:900; color:#0dcaf0;">...</div>
                </div>
              </div>
              <div class="col-6 col-md-2">
                <div class="summary-card p-4 text-center shadow" style="border-top: 4px solid #198754; border-radius: 1rem;">
                  <div class="summary-title">Completed</div>
                  <div class="summary-value text-success" id="service-completed" style="font-size:2.5rem; font-weight:900; color:#198754;">...</div>
                </div>
              </div>
              <div class="col-6 col-md-2">
                <div class="summary-card p-4 text-center shadow" style="border-top: 4px solid #ffc107; border-radius: 1rem;">
                  <div class="summary-title">In Progress</div>
                  <div class="summary-value text-warning" id="service-inprogress" style="font-size:2.5rem; font-weight:900; color:#ffc107;">...</div>
                </div>
              </div>
              <div class="col-6 col-md-2">
                <div class="summary-card p-4 text-center shadow" style="border-top: 4px solid #0dcaf0; border-radius: 1rem;">
                  <div class="summary-title">Completion Rate</div>
                  <div class="summary-value text-info" id="service-completion-rate" style="font-size:2.5rem; font-weight:900; color:#0dcaf0;">...</div>
                </div>
              </div>
            </div>
            <div class="row g-4 mb-4 align-items-stretch">
              <div class="col-md-6">
                <div class="card shadow-sm h-100">
                  <div class="card-header fw-bold">IS Project Status Distribution.</div>
                  <div class="card-body text-center">
                    <canvas id="service-status-pie" width="80" height="80"></canvas>
                  </div>
                </div>
              </div>
              <div class="col-md-6">
                <div class="card shadow-sm h-100">
                  <div class="card-header fw-bold">IS Project Status Bar Chart.</div>
                  <div class="card-body text-center">
                    <canvas id="service-status-bar" width="80" height="80"></canvas>
                  </div>
                </div>
              </div>
            </div>
            <div class="row g-4 mb-4">
              <div class="col-12">
                <div class="card shadow-sm mb-3">
                  <div class="card-header fw-bold">Overall Project Completion.</div>
                  <div class="card-body">
                    <div class="progress" style="height: 2.5rem; border-radius: 1.2rem; background: #23272b;">
                      <div id="service-completion-bar" class="progress-bar bg-success" role="progressbar" style="width: 0%; font-size:1.5rem; font-weight:900; border-radius: 1.2rem; background: linear-gradient(90deg, #198754 60%, #0dcaf0 100%);" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">0%</div>
                    </div>
                  </div>
                </div>
                <div class="d-flex justify-content-center mb-3">
                  <div class="btn-group" role="group" aria-label="Service Analytics Display Mode">
                    <button type="button" class="btn btn-outline-light active" id="service-analytics-pagination-btn">Enable Pagination</button>
                    <button type="button" class="btn btn-outline-warning" id="service-analytics-showall-btn">Display All</button>
                  </div>
                </div>
                <div class="card shadow-sm">
                  <div class="card-header fw-bold">Latest IS Projects.</div>

                  <div class="card-body p-0">
                    <table class="table table-dark mb-0">
                      <thead>
                        <tr>
                          <th>Date</th>
                          <th>Service</th>
                          <th>Status</th>
                        </tr>
                      </thead>
                      <tbody id="service-latest-services">
                        <tr><td colspan="3">Loading...</td></tr>
                      </tbody>
                    </table>
                    <div class="d-flex justify-content-between align-items-center mt-3">
                      <div class="dataTables_info" id="service-latest-services_info" role="status" aria-live="polite"></div>
                      <div class="dataTables_paginate paging_simple_numbers" id="service-latest-services_paginate">
                        <ul class="pagination mb-0">
                          <li class="paginate_button previous disabled" id="service-latest-services_previous">
                            <a href="#" aria-controls="service-latest-services" data-dt-idx="0" tabindex="0" class="page-link">Previous</a>
                          </li>
                          <li class="paginate_button next" id="service-latest-services_next">
                            <a href="#" aria-controls="service-latest-services" data-dt-idx="1" tabindex="0" class="page-link">Next</a>
                          </li>
                        </ul>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
      <!-- Main Service Progress Tab (top row) -->
      <div class="tab-pane fade" id="serviceprogress-main" role="tabpanel">
        <ul class="nav nav-tabs mb-3" id="serviceProgressSubTabs" role="tablist">
          <li class="nav-item" role="presentation">
            <button class="nav-link active" id="service-progress-table-tab" data-bs-toggle="tab" data-bs-target="#service-progress-table" type="button" role="tab">Service Table</button>
          </li>
          <li class="nav-item" role="presentation">
            <button class="nav-link" id="service-progress-analytics-tab" data-bs-toggle="tab" data-bs-target="#service-progress-analytics" type="button" role="tab">Service Analytics</button>
          </li>
          <li class="nav-item" role="presentation">
            <button class="nav-link" id="service-progress-cards-tab" data-bs-toggle="tab" data-bs-target="#service-progress-cards" type="button" role="tab">Service Cards</button>
          </li>
        </ul>
        <div class="tab-content" id="serviceProgressSubTabsContent">
          <div class="tab-pane fade show active" id="service-progress-table" role="tabpanel">
            <button class="btn btn-primary mb-2" id="addServiceProgressBtn">Add Service</button>
            <?php include 'service_progress_table.php'; ?>
          </div>
          <div class="tab-pane fade" id="service-progress-analytics" role="tabpanel">
            <div class="row g-4 mb-4">
              <div class="col-6 col-md-2">
                <div class="summary-card p-4 text-center shadow" style="border-top: 4px solid #0dcaf0; border-radius: 1rem;">
                  <div class="summary-title">Total Services</div>
                  <div class="summary-value" id="service-progress-total" style="font-size:2.5rem; font-weight:900; color:#0dcaf0;">...</div>
                </div>
              </div>
              <div class="col-6 col-md-2">
                <div class="summary-card p-4 text-center shadow" style="border-top: 4px solid #198754; border-radius: 1rem;">
                  <div class="summary-title">Completed</div>
                  <div class="summary-value text-success" id="service-progress-completed" style="font-size:2.5rem; font-weight:900; color:#198754;">...</div>
                </div>
              </div>
              <div class="col-6 col-md-2">
                <div class="summary-card p-4 text-center shadow" style="border-top: 4px solid #ffc107; border-radius: 1rem;">
                  <div class="summary-title">In Progress</div>
                  <div class="summary-value text-warning" id="service-progress-inprogress" style="font-size:2.5rem; font-weight:900; color:#ffc107;">...</div>
                </div>
              </div>
              <div class="col-6 col-md-2">
                <div class="summary-card p-4 text-center shadow" style="border-top: 4px solid #0dcaf0; border-radius: 1rem;">
                  <div class="summary-title">Completion Rate</div>
                  <div class="summary-value text-info" id="service-progress-completion-rate" style="font-size:2.5rem; font-weight:900; color:#0dcaf0;">...</div>
                </div>
              </div>
            </div>
            <div class="row g-4 mb-4 align-items-stretch">
              <div class="col-md-6">
                <div class="card shadow-sm h-100">
                  <div class="card-header fw-bold">Service Status Distribution</div>
                  <div class="card-body text-center">
                    <canvas id="service-progress-status-pie" width="80" height="80"></canvas>
                  </div>
                </div>
              </div>
              <div class="col-md-6">
                <div class="card shadow-sm h-100">
                  <div class="card-header fw-bold">Service Status Bar Chart</div>
                  <div class="card-body text-center">
                    <canvas id="service-progress-status-bar" width="80" height="80"></canvas>
                  </div>
                </div>
              </div>
            </div>
            <div class="row g-4 mb-4">
              <div class="col-12">
                <div class="card shadow-sm mb-3">
                  <div class="card-header fw-bold">Overall Completion</div>
                  <div class="card-body">
                    <div class="progress" style="height: 2.5rem; border-radius: 1.2rem; background: #23272b;">
                      <div id="service-progress-completion-bar" class="progress-bar bg-success" role="progressbar" style="width: 0%; font-size:1.5rem; font-weight:900; border-radius: 1.2rem; background: linear-gradient(90deg, #198754 60%, #0dcaf0 100%);" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">0%</div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <div class="d-flex justify-content-center mb-3">
              <div class="btn-group" role="group" aria-label="Service Analytics Display Mode">
                <button type="button" class="btn btn-outline-light active" id="service-progress-analytics-pagination-btn">Enable Pagination</button>
                <button type="button" class="btn btn-outline-warning" id="service-progress-analytics-showall-btn">Display All</button>
              </div>
            </div>
            <div class="card shadow-sm">
              <div class="card-header fw-bold">Latest Services</div>
              <div class="card-body p-0">
                <table class="table table-dark mb-0">
                  <thead>
                    <tr>
                      <th>Date</th>
                      <th>Service</th>
                      <th>Status</th>
                    </tr>
                  </thead>
                  <tbody id="service-progress-latest-services">
                    <tr><td colspan="3">Loading...</td></tr>
                  </tbody>
                </table>
                <div class="d-flex justify-content-between align-items-center mt-3">
                  <div class="dataTables_info" id="service-progress-latest-services_info" role="status" aria-live="polite"></div>
                  <div class="dataTables_paginate paging_simple_numbers" id="service-progress-latest-services_paginate">
                    <ul class="pagination mb-0">
                      <li class="paginate_button previous disabled" id="service-progress-latest-services_previous">
                        <a href="#" aria-controls="service-progress-latest-services" data-dt-idx="0" tabindex="0" class="page-link">Previous</a>
                      </li>
                      <li class="paginate_button next" id="service-progress-latest-services_next">
                        <a href="#" aria-controls="service-progress-latest-services" data-dt-idx="1" tabindex="0" class="page-link">Next</a>
                      </li>
                    </ul>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div class="tab-pane fade" id="service-progress-cards" role="tabpanel">
            <div class="d-flex justify-content-center mb-3">
              <div class="btn-group" role="group" aria-label="Service Status Filter">
                <button type="button" class="btn btn-outline-light active" id="service-progress-filter-all">All</button>
                <button type="button" class="btn btn-outline-warning" id="service-progress-filter-inprogress">In Progress</button>
                <button type="button" class="btn btn-outline-danger" id="service-progress-filter-pending">Pending</button>
                <button type="button" class="btn btn-outline-success" id="service-progress-filter-completed">Completed</button>
              </div>
            </div>
            <div class="row gx-2 gy-3 d-flex align-items-stretch" id="service-progress-cards-row">
              <!-- Cards will be injected here by JS -->
            </div>
          </div>
        </div>
      </div>
      <div class="tab-pane fade" id="dailytask" role="tabpanel">
        <ul class="nav nav-tabs mb-3" id="dailyTaskSubTabs" role="tablist">
          <li class="nav-item" role="presentation">
            <button class="nav-link active" id="daily-task-table-tab" data-bs-toggle="tab" data-bs-target="#daily-task-table" type="button" role="tab">Task Table</button>
          </li>
          <li class="nav-item" role="presentation">
            <button class="nav-link" id="daily-task-analytics-tab" data-bs-toggle="tab" data-bs-target="#daily-task-analytics" type="button" role="tab">Task Analytics</button>
          </li>
          <li class="nav-item" role="presentation">
            <button class="nav-link" id="daily-task-cards-tab" data-bs-toggle="tab" data-bs-target="#daily-task-cards" type="button" role="tab">Task Cards</button>
          </li>
        </ul>
        <div class="tab-content" id="dailyTaskSubTabsContent">
          <div class="tab-pane fade show active" id="daily-task-table" role="tabpanel">
            <div class="card shadow-sm">
              <div class="card-header fw-bold">Daily Task Tracking</div>
              <div class="card-body p-0">
                <button class="btn btn-primary mb-2" id="addDailyTaskBtn">Add Task</button>
                <table class="table table-dark mb-0 datatable-dailytask">
                  <thead>
                    <tr>
                      <th>No</th>
                      <th>Date/Time</th>
                      <th>Task Description</th>
                      <th>Assigned To</th>
                      <th>Created By</th>
                      <th>Status</th>
                      <th>Due Date</th>
                      <th>Priority</th>
                      <th>Comment</th>
                      <th>Action</th>
                    </tr>
                  </thead>
                  <tbody></tbody>
                </table>
              </div>
            </div>
          </div>
          <div class="tab-pane fade" id="daily-task-analytics" role="tabpanel">
            <div class="row g-4 mb-4">
              <div class="col-6 col-md-2">
                <div class="summary-card p-4 text-center shadow" style="border-top: 4px solid #0dcaf0; border-radius: 1rem;">
                  <div class="summary-title">Total Tasks</div>
                  <div class="summary-value" id="daily-task-total" style="font-size:2.5rem; font-weight:900; color:#0dcaf0;">...</div>
                </div>
              </div>
              <div class="col-6 col-md-2">
                <div class="summary-card p-4 text-center shadow" style="border-top: 4px solid #198754; border-radius: 1rem;">
                  <div class="summary-title">Completed</div>
                  <div class="summary-value text-success" id="daily-task-completed" style="font-size:2.5rem; font-weight:900; color:#198754;">...</div>
                </div>
              </div>
              <div class="col-6 col-md-2">
                <div class="summary-card p-4 text-center shadow" style="border-top: 4px solid #ffc107; border-radius: 1rem;">
                  <div class="summary-title">In Progress</div>
                  <div class="summary-value text-warning" id="daily-task-inprogress" style="font-size:2.5rem; font-weight:900; color:#ffc107;">...</div>
                </div>
              </div>
              <div class="col-6 col-md-2">
                <div class="summary-card p-4 text-center shadow" style="border-top: 4px solid #dc3545; border-radius: 1rem;">
                  <div class="summary-title">Pending</div>
                  <div class="summary-value text-danger" id="daily-task-pending" style="font-size:2.5rem; font-weight:900; color:#dc3545;">...</div>
                </div>
              </div>
              <div class="col-6 col-md-2">
                <div class="summary-card p-4 text-center shadow" style="border-top: 4px solid #0dcaf0; border-radius: 1rem;">
                  <div class="summary-title">Completion Rate</div>
                  <div class="summary-value text-info" id="daily-task-completion-rate" style="font-size:2.5rem; font-weight:900; color:#0dcaf0;">...</div>
                </div>
              </div>
            </div>
            <div class="row g-4 mb-4 align-items-stretch">
              <div class="col-md-6">
                <div class="card shadow-sm h-100">
                  <div class="card-header fw-bold">Task Status Distribution</div>
                  <div class="card-body text-center">
                    <canvas id="daily-task-status-pie" width="80" height="80"></canvas>
                  </div>
                </div>
              </div>
              <div class="col-md-6">
                <div class="card shadow-sm h-100">
                  <div class="card-header fw-bold">Task Status Bar Chart</div>
                  <div class="card-body text-center">
                    <canvas id="daily-task-status-bar" width="80" height="80"></canvas>
                  </div>
                </div>
              </div>
            </div>
            <div class="row g-4 mb-4">
              <div class="col-12">
                <div class="card shadow-sm mb-3">
                  <div class="card-header fw-bold">Overall Completion</div>
                  <div class="card-body">
                    <div class="progress" style="height: 2.5rem; border-radius: 1.2rem; background: #23272b;">
                      <div id="daily-task-completion-bar" class="progress-bar bg-success" role="progressbar" style="width: 0%; font-size:1.5rem; font-weight:900; border-radius: 1.2rem; background: linear-gradient(90deg, #198754 60%, #0dcaf0 100%);" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">0%</div>
                    </div>
                  </div>
                </div>
                <div class="d-flex justify-content-center mb-3">
                  <div class="btn-group" role="group" aria-label="Task Analytics Display Mode">
                    <button type="button" class="btn btn-outline-light active" id="daily-task-analytics-pagination-btn">Enable Pagination</button>
                    <button type="button" class="btn btn-outline-warning" id="daily-task-analytics-showall-btn">Display All</button>
                  </div>
                </div>
                <div class="card shadow-sm">
                  <div class="card-header fw-bold">Latest Tasks</div>
                  <div class="card-body p-0">
                    <table class="table table-dark mb-0">
                      <thead>
                        <tr>
                          <th>Date/Time</th>
                          <th>Description</th>
                          <th>Status</th>
                        </tr>
                      </thead>
                      <tbody id="daily-task-latest-tasks">
                        <tr><td colspan="3">Loading...</td></tr>
                      </tbody>
                    </table>
                    <div class="d-flex justify-content-between align-items-center mt-3">
                      <div class="dataTables_info" id="daily-task-latest-tasks_info" role="status" aria-live="polite"></div>
                      <div class="dataTables_paginate paging_simple_numbers" id="daily-task-latest-tasks_paginate">
                        <ul class="pagination mb-0">
                          <li class="paginate_button previous disabled" id="daily-task-latest-tasks_previous">
                            <a href="#" aria-controls="daily-task-latest-tasks" data-dt-idx="0" tabindex="0" class="page-link">Previous</a>
                          </li>
                          <li class="paginate_button next" id="daily-task-latest-tasks_next">
                            <a href="#" aria-controls="daily-task-latest-tasks" data-dt-idx="1" tabindex="0" class="page-link">Next</a>
                          </li>
                        </ul>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div class="tab-pane fade" id="daily-task-cards" role="tabpanel">
            <div class="d-flex justify-content-center mb-3">
              <div class="btn-group" role="group" aria-label="Task Status Filter">
                <button type="button" class="btn btn-outline-light active" id="daily-filter-all">All</button>
                <button type="button" class="btn btn-outline-warning" id="daily-filter-inprogress">In Progress</button>
                <button type="button" class="btn btn-outline-danger" id="daily-filter-pending">Pending</button>
                <button type="button" class="btn btn-outline-success" id="daily-filter-completed">Completed</button>
              </div>
            </div>
            <div class="row gx-2 gy-3 d-flex align-items-stretch" id="daily-task-cards-row">
              <!-- Cards will be injected here by JS -->
            </div>
          </div>
        </div>
      </div>
      <div class="tab-pane fade" id="usermanagement-main" role="tabpanel">
        <div class="card shadow-sm">
          <div class="card-header fw-bold">User Management</div>
          <div class="card-body">
            <div id="user-management-table-container">Loading...</div>
          </div>
        </div>
      </div>
      <div class="tab-pane fade" id="servicecards" role="tabpanel">
        <div class="d-flex justify-content-center mb-3">
          <div class="btn-group" role="group" aria-label="Service Status Filter">
            <button type="button" class="btn btn-outline-light active" id="service-filter-all">All</button>
            <button type="button" class="btn btn-outline-warning" id="service-filter-inprogress">In Progress</button>
            <button type="button" class="btn btn-outline-danger" id="service-filter-pending">Pending</button>
            <button type="button" class="btn btn-outline-success" id="service-filter-completed">Completed</button>
          </div>
        </div>
        <div class="row gx-2 gy-3 d-flex align-items-stretch" id="service-cards-row">
          <!-- Cards will be injected here by JS -->
        </div>
      </div>
      <div class="tab-pane fade" id="projecttask" role="tabpanel">
        <ul class="nav nav-tabs mb-3" id="projectTaskSubTabs" role="tablist">
          <li class="nav-item" role="presentation">
            <button class="nav-link active" id="projecttask-table-tab" data-bs-toggle="tab" data-bs-target="#projecttask-table" type="button" role="tab">Table</button>
          </li>
          <!-- Removed Project Task Analytics and Project Task Cards subtabs -->
        </ul>
        <div class="tab-content" id="projectTaskSubTabsContent">
          <div class="tab-pane fade show active" id="projecttask-table" role="tabpanel">
            <div class="card shadow-sm mb-4">
              <div class="card-header fw-bold d-flex justify-content-between align-items-center">
                <span>Projects</span>
                <button class="btn btn-primary btn-sm" id="addProjectBtn">Add Project</button>
              </div>
              <div class="card-body p-0">
                <table class="table table-dark mb-0 w-100" id="projectsTable">
                  <thead>
                    <tr>
                      <th>ID</th>
                      <th>Name</th>
                      <th>Description</th>
                      <th>Start Date</th>
                      <th>Due Date</th>
                      <th>Created By</th>
                      <th>Created At</th>
                      <th>Action</th>
                    </tr>
                  </thead>
                  <tbody></tbody>
                </table>
              </div>
            </div>
            <!-- Add Project Modal -->
            <div class="modal fade" id="addProjectModal" tabindex="-1" aria-labelledby="addProjectModalLabel" aria-hidden="true">
              <div class="modal-dialog">
                <div class="modal-content bg-dark text-light">
                  <div class="modal-header">
                    <h5 class="modal-title" id="addProjectModalLabel">Add Project</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                  </div>
                  <form id="addProjectForm">
                    <div class="modal-body">
                      <div class="mb-3">
                        <label class="form-label">Name</label>
                        <input type="text" class="form-control" name="name" required>
                      </div>
                      <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea class="form-control" name="description"></textarea>
                      </div>
                      <div class="mb-3">
                        <label class="form-label">Start Date</label>
                        <input type="date" class="form-control" name="start_date">
                      </div>
                      <div class="mb-3">
                        <label class="form-label">Due Date</label>
                        <input type="date" class="form-control" name="due_date">
                      </div>
                    </div>
                    <div class="modal-footer">
                      <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                      <button type="submit" class="btn btn-primary">Add Project</button>
                    </div>
                  </form>
                </div>
              </div>
            </div>
            <div class="card shadow-sm">
              <div class="card-header fw-bold d-flex justify-content-between align-items-center">
                <span>Project Tasks</span>
                <button class="btn btn-primary btn-sm" id="addProjectTaskBtn">Add Project Task</button>
              </div>
              <div class="card-body p-0">
                <table class="table table-dark mb-0 w-100" id="projectTasksTable">
                  <thead>
                    <tr>
                      <th>ID</th>
                      <th>Project</th>
                      <th>Title</th>
                      <th>Description</th>
                      <th>Assigned To</th>
                      <th>Due Date</th>
                      <th>Status</th>
                      <th>Created At</th>
                      <th>Action</th>
                    </tr>
                  </thead>
                  <tbody></tbody>
                </table>
              </div>
            </div>
            <!-- Add Project Task Modal -->
            <div class="modal fade" id="addProjectTaskModal" tabindex="-1" aria-labelledby="addProjectTaskModalLabel" aria-hidden="true">
              <div class="modal-dialog">
                <div class="modal-content bg-dark text-light">
                  <div class="modal-header">
                    <h5 class="modal-title" id="addProjectTaskModalLabel">Add Project Task</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                  </div>
                  <form id="addProjectTaskForm">
                    <div class="modal-body">
                      <div class="mb-3">
                        <label class="form-label">Project</label>
                        <select class="form-select" name="project_id" id="projectTaskProjectSelect" required>
                          <option value="">Select project...</option>
                        </select>
                      </div>
                      <div class="mb-3">
                        <label class="form-label">Title</label>
                        <input type="text" class="form-control" name="title" required>
                      </div>
                      <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea class="form-control" name="description"></textarea>
                      </div>
                      <div class="mb-3">
                        <label class="form-label">Assigned To</label>
                        <select class="form-select" name="assigned_to" id="projectTaskAssignedToSelect">
                          <option value="">Select user...</option>
                        </select>
                      </div>
                      <div class="mb-3">
                        <label class="form-label">Due Date</label>
                        <input type="date" class="form-control" name="due_date">
                      </div>
                      <div class="mb-3">
                        <label class="form-label">Status</label>
                        <select class="form-select" name="status">
                          <option value="pending">Pending</option>
                          <option value="inprogress">In Progress</option>
                          <option value="completed">Completed</option>
                        </select>
                      </div>
                    </div>
                    <div class="modal-footer">
                      <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                      <button type="submit" class="btn btn-primary">Add Task</button>
                    </div>
                  </form>
                </div>
              </div>
            </div>
          </div>
          <!-- Removed tab-pane content for Project Task Analytics and Project Task Cards -->
        </div>
      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/js/bootstrap.bundle.min.js"></script>
  <!-- jQuery and DataTables JS -->
  <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
  <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
  <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
  <!-- Add Blueimp MD5 library for hashing -->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/blueimp-md5/2.19.0/js/md5.min.js"></script>
  
  
  
  <!-- Auto tab switching functionality -->
  
<script src="assets/dashboard6.js.php"></script>

<!-- Edit Daily Task Modal -->
<div class="modal fade" id="editDailyTaskModal" tabindex="-1" aria-labelledby="editDailyTaskModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content bg-dark text-light">
      <div class="modal-header">
        <h5 class="modal-title" id="editDailyTaskModalLabel">Edit Daily Task</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form id="editDailyTaskForm">
        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
        <div class="modal-body">
          <input type="hidden" id="edit-dailytask-id" name="id">
          <div class="mb-3">
            <label class="form-label">Date/Time</label>
            <input type="datetime-local" class="form-control" id="edit-dailytask-datetime" name="datetime">
          </div>
          <div class="mb-3">
            <label class="form-label">Task Description</label>
            <textarea class="form-control" id="edit-dailytask-description" name="task_description"></textarea>
          </div>
          <div class="mb-3">
            <label class="form-label">Assigned To</label>
            <select class="form-select" id="edit-dailytask-assigned-to" name="assigned_to"></select>
          </div>
          <div class="mb-3">
            <label class="form-label">Due Date</label>
            <input type="date" class="form-control" id="edit-dailytask-due-date" name="due_date">
          </div>
          <div class="mb-3">
            <label class="form-label">Priority</label>
            <select class="form-select" id="edit-dailytask-priority" name="priority">
              <option value="Low">Low</option>
              <option value="Medium" selected>Medium</option>
              <option value="High">High</option>
            </select>
          </div>
          <div class="mb-3">
            <label class="form-label">Status</label>
            <select class="form-select" id="edit-dailytask-status" name="status">
              <option value="pending">Pending</option>
              <option value="inprogress">In Progress</option>
              <option value="completed">Completed</option>
            </select>
          </div>
          <div class="mb-3">
            <label class="form-label">Comment</label>
            <textarea class="form-control" id="edit-dailytask-comment" name="comment"></textarea>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="button" class="btn btn-primary" id="saveDailyTaskBtn">Save Changes</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Edit Project Modal -->
<div class="modal fade" id="editProjectModal" tabindex="-1" aria-labelledby="editProjectModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content bg-dark text-light">
      <div class="modal-header">
        <h5 class="modal-title" id="editProjectModalLabel">Edit Project</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form id="editProjectForm">
        <div class="modal-body">
          <input type="hidden" id="edit-project-id" name="id">
          <div class="mb-3">
            <label class="form-label">Name</label>
            <input type="text" class="form-control" id="edit-project-name" name="name" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Description</label>
            <textarea class="form-control" id="edit-project-description" name="description"></textarea>
          </div>
          <div class="mb-3">
            <label class="form-label">Start Date</label>
            <input type="date" class="form-control" id="edit-project-start-date" name="start_date">
          </div>
          <div class="mb-3">
            <label class="form-label">Due Date</label>
            <input type="date" class="form-control" id="edit-project-due-date" name="due_date">
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="button" class="btn btn-primary" id="saveProjectBtn">Save Changes</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Edit Project Task Modal -->
<div class="modal fade" id="editProjectTaskModal" tabindex="-1" aria-labelledby="editProjectTaskModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content bg-dark text-light">
      <div class="modal-header">
        <h5 class="modal-title" id="editProjectTaskModalLabel">Edit Project Task</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form id="editProjectTaskForm">
        <div class="modal-body">
          <input type="hidden" id="edit-projecttask-id" name="id">
          <div class="mb-3">
            <label class="form-label">Project</label>
            <select class="form-select" id="edit-projecttask-project" name="project_id" required>
              <option value="">Select project...</option>
            </select>
          </div>
          <div class="mb-3">
            <label class="form-label">Title</label>
            <input type="text" class="form-control" id="edit-projecttask-title" name="title" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Description</label>
            <textarea class="form-control" id="edit-projecttask-description" name="description"></textarea>
          </div>
          <div class="mb-3">
            <label class="form-label">Assigned To</label>
            <select class="form-select" id="edit-projecttask-assigned-to" name="assigned_to">
              <option value="">Select user...</option>
            </select>
          </div>
          <div class="mb-3">
            <label class="form-label">Due Date</label>
            <input type="date" class="form-control" id="edit-projecttask-due-date" name="due_date">
          </div>
          <div class="mb-3">
            <label class="form-label">Status</label>
            <select class="form-select" id="edit-projecttask-status" name="status">
              <option value="pending">Pending</option>
              <option value="inprogress">In Progress</option>
              <option value="completed">Completed</option>
            </select>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="button" class="btn btn-primary" id="saveProjectTaskBtn">Save Changes</button>
        </div>
      </form>
    </div>
  </div>
</div>

</body>
</html>

<?php
function badge($val) {
  if (strtolower($val) === 'yes') return '<span class="badge bg-success">Yes</span>';
  if (strtolower($val) === 'no') return '<span class="badge bg-danger">No</span>';
  return '<span class="badge bg-secondary">N/A</span>';
}
?> 
