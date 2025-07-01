<?php
require_once '../../includes/config.php';
session_start();

// Simulate a logged-in user for testing
$_SESSION['user_id'] = 1;
$_SESSION['username'] = 'test_user';

// Test configuration
$base_url = 'http://localhost/hajj2/hajj_program/api/hajj_program_api.php';
$test_project_id = null;
$test_milestone_id = null;
$test_risk_id = null;

// Helper function to make API calls
function makeRequest($endpoint, $method = 'GET', $data = null) {
    global $base_url;
    
    $url = $base_url . '?endpoint=' . $endpoint;
    $options = [
        'http' => [
            'method' => $method,
            'header' => 'Content-Type: application/json',
            'content' => $data ? json_encode($data) : null
        ]
    ];
    
    $context = stream_context_create($options);
    $result = file_get_contents($url, false, $context);
    
    return [
        'status' => $http_response_header[0],
        'data' => json_decode($result, true)
    ];
}

// Helper function to print test results
function printTestResult($test_name, $result, $expected_status = '200 OK') {
    echo "\nTest: $test_name\n";
    echo "Status: " . $result['status'] . "\n";
    echo "Response: " . json_encode($result['data'], JSON_PRETTY_PRINT) . "\n";
    echo "Test " . ($result['status'] === $expected_status ? "PASSED" : "FAILED") . "\n";
    echo "----------------------------------------\n";
}

echo "Starting API Tests...\n";
echo "========================================\n";

// Test 1: Create a new project
$project_data = [
    'project_name' => 'Test Project',
    'domain' => 'SecOPS',
    'description' => 'Test project for API verification',
    'start_date' => '2024-06-01',
    'due_date' => '2024-12-31',
    'status' => 'Not Started',
    'progress' => 0,
    'priority' => 'High',
    'assigned_to' => 'test_user'
];

$result = makeRequest('projects', 'POST', $project_data);
printTestResult('Create Project', $result);

if (isset($result['data']['id'])) {
    $test_project_id = $result['data']['id'];
}

// Test 2: Get all projects
$result = makeRequest('projects');
printTestResult('Get All Projects', $result);

// Test 3: Get projects by domain
$result = makeRequest('projects&domain=SecOPS');
printTestResult('Get Projects by Domain', $result);

// Test 4: Update project
if ($test_project_id) {
    $update_data = [
        'id' => $test_project_id,
        'project_name' => 'Updated Test Project',
        'domain' => 'SecOPS',
        'description' => 'Updated test project',
        'start_date' => '2024-06-01',
        'due_date' => '2024-12-31',
        'status' => 'In Progress',
        'progress' => 50,
        'priority' => 'High',
        'assigned_to' => 'test_user'
    ];
    
    $result = makeRequest('projects', 'PUT', $update_data);
    printTestResult('Update Project', $result);
}

// Test 5: Create milestone
if ($test_project_id) {
    $milestone_data = [
        'project_id' => $test_project_id,
        'milestone_name' => 'Test Milestone',
        'description' => 'Test milestone for API verification',
        'due_date' => '2024-09-30',
        'status' => 'Not Started',
        'completion_percentage' => 0
    ];
    
    $result = makeRequest('milestones', 'POST', $milestone_data);
    printTestResult('Create Milestone', $result);
    
    if (isset($result['data']['id'])) {
        $test_milestone_id = $result['data']['id'];
    }
}

// Test 6: Get milestones
if ($test_project_id) {
    $result = makeRequest('milestones&project_id=' . $test_project_id);
    printTestResult('Get Milestones', $result);
}

// Test 7: Create risk
if ($test_project_id) {
    $risk_data = [
        'project_id' => $test_project_id,
        'risk_description' => 'Test Risk',
        'impact' => 'High',
        'probability' => 'Medium',
        'mitigation_plan' => 'Test mitigation plan',
        'status' => 'Open',
        'assigned_to' => 'test_user',
        'due_date' => '2024-08-31'
    ];
    
    $result = makeRequest('risks', 'POST', $risk_data);
    printTestResult('Create Risk', $result);
    
    if (isset($result['data']['id'])) {
        $test_risk_id = $result['data']['id'];
    }
}

// Test 8: Get risks
if ($test_project_id) {
    $result = makeRequest('risks&project_id=' . $test_project_id);
    printTestResult('Get Risks', $result);
}

// Test 9: Create activity
if ($test_project_id) {
    $activity_data = [
        'project_id' => $test_project_id,
        'activity_type' => 'Update',
        'description' => 'Test activity log'
    ];
    
    $result = makeRequest('activities', 'POST', $activity_data);
    printTestResult('Create Activity', $result);
}

// Test 10: Get activities
if ($test_project_id) {
    $result = makeRequest('activities&project_id=' . $test_project_id);
    printTestResult('Get Activities', $result);
}

// Test 11: Delete project (cleanup)
if ($test_project_id) {
    $result = makeRequest('projects&id=' . $test_project_id, 'DELETE');
    printTestResult('Delete Project', $result);
}

echo "\nAPI Testing Completed!\n";
echo "========================================\n";

// Clean up session
session_destroy();
?> 
