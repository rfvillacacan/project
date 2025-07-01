-- Insert demo users
-- Read Only user (user1)
INSERT INTO users (username, password) VALUES 
('user1', '$2y$10$8K1p/a0dR1xqM8K1p/a0dR1xqM8K1p/a0dR1xqM8K1p/a0dR1xqM');

-- Operator user (ops1)
INSERT INTO users (username, password) VALUES 
('ops1', '$2y$10$9L2q/b1eS2yN9L2q/b1eS2yN9L2q/b1eS2yN9L2q/b1eS2yN');

-- Note: These are example hashes. You should run generate_password_hashes.php
-- and use the actual generated hashes in your database.

-- After running the PHP code above, replace the placeholder hashes with the actual generated hashes 
