-- Add role column to users table
ALTER TABLE users ADD COLUMN role ENUM('readonly', 'operator', 'admin') NOT NULL DEFAULT 'readonly';

-- Add status column for user approval
ALTER TABLE users ADD COLUMN status ENUM('pending', 'active') NOT NULL DEFAULT 'pending';

-- Update existing users with appropriate roles and status
UPDATE users SET role = 'admin', status = 'active' WHERE username = 'admin';
UPDATE users SET role = 'readonly', status = 'active' WHERE username = 'user1';
UPDATE users SET role = 'operator', status = 'active' WHERE username = 'ops1'; 
