ALTER TABLE users
ADD COLUMN google_auth_secret VARCHAR(32) NULL,
ADD COLUMN two_factor_enabled TINYINT(1) DEFAULT 0; 
