<?php
require_once __DIR__ . '/../includes/config.php';

try {
    // Create hajj_program_1446 table
    $sql = "CREATE TABLE IF NOT EXISTS hajj_program_1446 (
        id INT(11) NOT NULL AUTO_INCREMENT,
        project_name VARCHAR(255) NOT NULL,
        domain ENUM('Prep', 'GRC', 'SD', 'SecOPS', 'OT', 'IS') NOT NULL,
        description TEXT,
        start_date DATE NOT NULL,
        due_date DATE NOT NULL,
        status ENUM('Not Started', 'In Progress', 'Completed', 'On Hold') NOT NULL DEFAULT 'Not Started',
        progress INT(3) NOT NULL DEFAULT 0,
        priority ENUM('Low', 'Medium', 'High', 'Critical') NOT NULL DEFAULT 'Medium',
        assigned_to VARCHAR(255),
        dependencies TEXT,
        risks TEXT,
        created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (id)
    )";
    
    if ($conn->query($sql)) {
        echo "Table hajj_program_1446 created successfully\n";
    }

    // Create hajj_program_milestones table
    $sql = "CREATE TABLE IF NOT EXISTS hajj_program_milestones (
        id INT(11) NOT NULL AUTO_INCREMENT,
        project_id INT(11) NOT NULL,
        milestone_name VARCHAR(255) NOT NULL,
        description TEXT,
        due_date DATE NOT NULL,
        status ENUM('Not Started', 'In Progress', 'Completed', 'Delayed') NOT NULL DEFAULT 'Not Started',
        completion_percentage INT(3) NOT NULL DEFAULT 0,
        created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        FOREIGN KEY (project_id) REFERENCES hajj_program_1446(id) ON DELETE CASCADE
    )";
    
    if ($conn->query($sql)) {
        echo "Table hajj_program_milestones created successfully\n";
    }

    // Create hajj_program_risks table
    $sql = "CREATE TABLE IF NOT EXISTS hajj_program_risks (
        id INT(11) NOT NULL AUTO_INCREMENT,
        project_id INT(11) NOT NULL,
        risk_description TEXT NOT NULL,
        impact ENUM('Low', 'Medium', 'High', 'Critical') NOT NULL,
        probability ENUM('Low', 'Medium', 'High') NOT NULL,
        mitigation_plan TEXT,
        status ENUM('Open', 'In Progress', 'Mitigated', 'Closed') NOT NULL DEFAULT 'Open',
        assigned_to VARCHAR(255),
        due_date DATE,
        created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        FOREIGN KEY (project_id) REFERENCES hajj_program_1446(id) ON DELETE CASCADE
    )";
    
    if ($conn->query($sql)) {
        echo "Table hajj_program_risks created successfully\n";
    }

    // Create hajj_program_activities table
    $sql = "CREATE TABLE IF NOT EXISTS hajj_program_activities (
        id INT(11) NOT NULL AUTO_INCREMENT,
        project_id INT(11) NOT NULL,
        activity_type ENUM('Update', 'Comment', 'Status Change', 'Risk Update') NOT NULL,
        description TEXT NOT NULL,
        created_by VARCHAR(255) NOT NULL,
        created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        FOREIGN KEY (project_id) REFERENCES hajj_program_1446(id) ON DELETE CASCADE
    )";
    
    if ($conn->query($sql)) {
        echo "Table hajj_program_activities created successfully\n";
    }

    echo "All tables created successfully!";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}

$conn->close();
?> 
