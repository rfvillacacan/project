<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Get the requested page from query parameter
if(isset($_GET['p'])) {
    $p = $_GET['p'];
} else {
    $p = 'login.php'; // Default to login page
}

// Route to the appropriate page
switch ($p) {
    case 'login.php': include('login.php'); break;
    case 'register.php': include('register.php'); break;
    case 'verify_login.php': include('verify_login.php'); break;
    case 'dashboard6.php': include('dashboard6.php'); break;
    case 'team_task_dashboard.php': include('team_task_dashboard.php'); break;
    case 'enable_2fa.php': include('enable_2fa.php'); break;
    case 'disable_2fa.php': include('disable_2fa.php'); break;
    case 'logout.php': include('logout.php'); break;
    case 'cancel_registration.php': include('cancel_registration.php'); break;
    case 'hajj_program/hajj_program_dashboard.php': include('hajj_program/hajj_program_dashboard.php'); break;
    case 'project_management/project_management_dashboard.php': include('project_management/project_management_dashboard.php'); break;
    case 'logbook.php': include('logbook.php'); break;
    default: include('login.php'); break;
}
?> 
