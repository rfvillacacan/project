CREATE TABLE IF NOT EXISTS task_updates (
    id INT AUTO_INCREMENT PRIMARY KEY,
    task_type ENUM('daily','project') NOT NULL,
    task_id INT NOT NULL,
    user_id INT NOT NULL,
    comment TEXT,
    progress TINYINT UNSIGNED DEFAULT 0,
    status ENUM('pending','inprogress','completed') DEFAULT 'inprogress',
    manager_seen TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
);
