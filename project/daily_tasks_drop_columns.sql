ALTER TABLE daily_tasks
  DROP FOREIGN KEY fk_daily_tasks_project;
ALTER TABLE daily_tasks
  DROP COLUMN shift,
  DROP COLUMN project_id,
  DROP COLUMN task_category,
  DROP COLUMN estimated_time,
  DROP COLUMN time_spent,
  DROP COLUMN percent_completed;
