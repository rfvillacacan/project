<?php
//session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: ?p=login.php');
    exit;
}
$isAdmin = ($_SESSION['role'] ?? '') === 'admin';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Logbook</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel='stylesheet' href='assets/logbook.css'>
    <link href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    
</head>
<body class="bg-dark text-light">
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Logbook</h2>
        <button class="btn btn-secondary" onclick="window.close()">Close</button>
    </div>
    <ul class="nav nav-tabs mb-3" id="logbookTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="logbook-table-tab" data-bs-toggle="tab" data-bs-target="#logbook-table" type="button" role="tab">Logbook Table</button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="current-shift-tab" data-bs-toggle="tab" data-bs-target="#current-shift-table" type="button" role="tab">Current Shift Logs</button>
        </li>
    </ul>
    <div class="tab-content" id="logbookTabsContent">
        <!-- Logbook Table Tab -->
        <div class="tab-pane fade show active" id="logbook-table" role="tabpanel">
            <?php if ($isAdmin): ?>
            <button class="btn btn-primary mb-3" id="addLogEntryBtn">Add New Entry</button>
            <?php endif; ?>
            <div class="table-responsive">
                <table id="logbookTable" class="table table-dark table-striped table-bordered w-100">
                    <thead>
                        <tr>
                            <th>Shift</th>
                            <th>Date</th>
                            <th>Time</th>
                            <th>Activity</th>
                            <th>Status</th>
                            <th>Severity</th>
                            <th>Assigned To</th>
                            <th>Category</th>
                            <th>Action Needed</th>
                            <th>Notes</th>
                            <th>Attachment</th>
                            <th>Handover</th>
                            <th>Created By</th>
                            <th>Created At</th>
                            <?php if ($isAdmin): ?><th>Actions</th><?php endif; ?>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
        <!-- Current Shift Logs Tab -->
        <div class="tab-pane fade" id="current-shift-table" role="tabpanel">
            <div class="d-flex align-items-center mb-3 gap-3">
                <div class="btn-group" role="group" aria-label="Shift filter">
                    <button type="button" class="btn btn-outline-primary shift-filter-btn active" data-shift="day">Day</button>
                    <button type="button" class="btn btn-outline-primary shift-filter-btn" data-shift="night">Night</button>
                </div>
                <div class="ms-3">
                    <label for="refresh-interval" class="form-label mb-0 me-2">Auto-refresh (sec):</label>
                    <select id="refresh-interval" class="form-select d-inline-block w-auto">
                        <option value="0">Off</option>
                        <option value="5">5</option>
                        <option value="10">10</option>
                        <option value="30">30</option>
                        <option value="60">60</option>
                    </select>
                </div>
            </div>
            <div class="table-responsive">
                <table id="currentShiftTable" class="table table-dark table-striped table-bordered w-100">
                    <thead>
                        <tr>
                            <th>Shift</th>
                            <th>Date</th>
                            <th>Time</th>
                            <th>Activity</th>
                            <th>Status</th>
                            <th>Severity</th>
                            <th>Assigned To</th>
                            <th>Category</th>
                            <th>Action Needed</th>
                            <th>Notes</th>
                            <th>Attachment</th>
                            <th>Handover</th>
                            <th>Created By</th>
                            <th>Created At</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Add/Edit Modal -->
<div class="modal fade" id="logEntryModal" tabindex="-1" aria-labelledby="logEntryModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content bg-dark text-light">
      <form id="logEntryForm" enctype="multipart/form-data">
        <div class="modal-header">
          <h5 class="modal-title" id="logEntryModalLabel">Add/Edit Log Entry</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body row g-3">
          <input type="hidden" name="id" id="logEntryId">
          <div class="col-md-3">
            <label class="form-label">Shift</label>
            <select class="form-select" name="shift" id="logEntryShift" required>
              <option value="">Select</option>
              <option value="day">Day</option>
              <option value="night">Night</option>
            </select>
          </div>
          <div class="col-md-3">
            <label class="form-label">Date</label>
            <input type="date" class="form-control" name="date" id="logEntryDate" required>
          </div>
          <div class="col-md-3">
            <label class="form-label">Time</label>
            <input type="time" class="form-control" name="time" id="logEntryTime" required>
          </div>
          <div class="col-md-3">
            <label class="form-label">Category</label>
            <select class="form-select" name="category" id="logEntryCategory" required>
              <option value="">Select</option>
              <option value="Incident">Incident</option>
              <option value="Routine">Routine</option>
              <option value="Alert">Alert</option>
              <option value="Maintenance">Maintenance</option>
              <option value="Info">Info</option>
              <option value="Other">Other</option>
            </select>
          </div>
          <div class="col-md-12">
            <label class="form-label">Activity</label>
            <textarea class="form-control" name="activity" id="logEntryActivity" rows="2" required></textarea>
          </div>
          <div class="col-md-4">
            <label class="form-label">Status</label>
            <select class="form-select" name="status" id="logEntryStatus" required>
              <option value="Pending">Pending</option>
              <option value="In Progress">In Progress</option>
              <option value="Completed">Completed</option>
              <option value="Escalated">Escalated</option>
              <option value="Postponed">Postponed</option>
            </select>
          </div>
          <div class="col-md-4">
            <label class="form-label">Severity</label>
            <select class="form-select" name="severity" id="logEntrySeverity" required>
              <option value="Low">Low</option>
              <option value="Medium">Medium</option>
              <option value="High">High</option>
              <option value="Critical">Critical</option>
            </select>
          </div>
          <div class="col-md-4">
            <label class="form-label">Assigned To</label>
            <input type="text" class="form-control" name="assigned_to" id="logEntryAssignedTo" maxlength="100">
          </div>
          <div class="col-md-6">
            <label class="form-label">Action Needed</label>
            <textarea class="form-control" name="action_needed" id="logEntryActionNeeded" rows="1"></textarea>
          </div>
          <div class="col-md-6">
            <label class="form-label">Notes</label>
            <textarea class="form-control" name="notes" id="logEntryNotes" rows="1"></textarea>
          </div>
          <div class="col-md-6">
            <label class="form-label">Attachment (jpg, png, pdf, max 5MB)</label>
            <input type="file" class="form-control" name="attachment" id="logEntryAttachment" accept=".jpg,.jpeg,.png,.pdf">
            <div id="currentAttachment" class="mt-1"></div>
          </div>
          <div class="col-md-6 d-flex align-items-center">
            <div class="form-check form-switch mt-4">
              <input class="form-check-input" type="checkbox" id="logEntryHandover" name="is_handover" value="1">
              <label class="form-check-label" for="logEntryHandover">Handover Entry</label>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-success">Save</button>
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
<script src="assets/toast.js"></script>
<script src="assets/logbook.js.php"></script>

</body>
</html> 
