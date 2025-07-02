# Team Member Task Dashboard Proposal

This document outlines a proposed approach for a dedicated task page that team members see immediately after logging in. The goal is to allow users to quickly review and update the tasks assigned to them while maintaining the current styling and components used throughout the dashboard.

## 1. Redirect on Login
- After a successful login, non-admin users (e.g., operators/team members) should be redirected to a new page `team_task_dashboard.php` instead of the main dashboard.
- Administrators would continue to see `dashboard6.php` as the default landing page.

## 2. Aggregated Task View
- Combine records from the `daily_tasks` and `project_tasks` tables where the `assigned_to` field matches the logged‑in user.
- Sort the combined list by `priority` (High → Medium → Low) and then by due date.
- If `project_tasks` does not yet contain a `priority` column, add an enum field similar to `daily_tasks` so both tables share the same priority options.

## 3. Toggle Layout (Table/Card)
- Reuse the existing Bootstrap styles and card markup found in `dashboard6.js.php` for the card view.
- Provide a toggle switch to alternate between a DataTable layout and the card layout. The switch should remember the user’s last choice using `localStorage`.
- Filtering by status (pending, in progress, completed) should be available in both views.

## 4. Task Update Comments
- Create a new table `task_updates` to store a history of comments:
  ```sql
  CREATE TABLE task_updates (
      id INT AUTO_INCREMENT PRIMARY KEY,
      task_type ENUM('daily','project') NOT NULL,
      task_id INT NOT NULL,
      user_id INT NOT NULL,
      comment TEXT NOT NULL,
      created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
      FOREIGN KEY (user_id) REFERENCES users(id)
  );
  ```
- Each task page entry displays its comment thread and provides a form to add a new update.
- Both the assignee and the manager who created the task can post comments.

## 5. Suggested UX Flow
1. **Login** → redirect to `team_task_dashboard.php` if the user is not an admin.
2. **Task List** – default to table view with sorting and filters. Toggle to card view if preferred.
3. **Update** – clicking a task opens a modal showing details and the comment history. Users can change status or add a new comment.
4. **Notifications** – optional improvement: notify managers when a comment is added or status changes.

## 6. Styling
- Use the existing Bootstrap and dark theme classes from the project. Avoid introducing new frameworks.
- Buttons, badges, and status colors should mirror those used on the main dashboard for consistency.

This approach keeps the design aligned with the current application while giving team members a focused workspace for their assigned tasks.
