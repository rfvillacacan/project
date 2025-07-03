-- Ensure task_updates table has all expected fields
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

ALTER TABLE task_updates
    ADD COLUMN IF NOT EXISTS progress TINYINT UNSIGNED DEFAULT 0;

ALTER TABLE task_updates
    ADD COLUMN IF NOT EXISTS status ENUM('pending','inprogress','completed') DEFAULT 'inprogress';

ALTER TABLE task_updates
    ADD COLUMN IF NOT EXISTS manager_seen TINYINT(1) DEFAULT 0;

ALTER TABLE task_updates
    ADD COLUMN IF NOT EXISTS created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP;

ALTER TABLE task_updates
    MODIFY COLUMN comment TEXT NULL;
