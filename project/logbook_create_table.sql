CREATE TABLE logbook (
    id INT AUTO_INCREMENT PRIMARY KEY,
    shift ENUM('Morning', 'Night') NOT NULL,
    date DATE NOT NULL,
    time TIME NOT NULL,
    activity TEXT NOT NULL,
    status ENUM('Pending', 'In Progress', 'Completed', 'Escalated', 'Postponed') NOT NULL DEFAULT 'Pending',
    action_needed TEXT,
    notes TEXT,
    assigned_to VARCHAR(100),
    severity ENUM('Low','Medium','High','Critical') DEFAULT 'Low',
    category ENUM('Incident','Routine','Alert','Maintenance','Info','Other'),
    is_handover BOOLEAN DEFAULT 0,
    attachment VARCHAR(255),
    created_by VARCHAR(100) NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
); 
