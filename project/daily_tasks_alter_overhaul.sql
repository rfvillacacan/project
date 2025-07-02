ALTER TABLE daily_tasks
  DROP COLUMN responsibility,
  DROP COLUMN required_action,
  ADD COLUMN due_date DATE NULL,
  ADD COLUMN priority ENUM('Low','Medium','High') DEFAULT 'Medium',
  ADD COLUMN task_category ENUM('Operational','Personal','Routine') DEFAULT 'Operational',
  ADD COLUMN estimated_time INT NULL,
  ADD COLUMN time_spent INT NULL;
