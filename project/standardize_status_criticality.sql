-- Standardize status fields
ALTER TABLE servers 
MODIFY COLUMN status ENUM('Online', 'Offline', 'Maintenance') NOT NULL DEFAULT 'Offline';

-- Standardize criticality fields
ALTER TABLE servers 
MODIFY COLUMN criticality ENUM('Low', 'Medium', 'High', 'Critical') NOT NULL DEFAULT 'Low';

-- Add indexes for frequently searched fields
ALTER TABLE network_devices
ADD INDEX idx_hostname (hostname),
ADD INDEX idx_ip_address (ip_address),
ADD INDEX idx_status (status),
ADD INDEX idx_criticality (criticality);

ALTER TABLE servers
ADD INDEX idx_name (name),
ADD INDEX idx_ip (ip),
ADD INDEX idx_status (status),
ADD INDEX idx_criticality (criticality),
ADD INDEX idx_type (type);

ALTER TABLE urls
ADD INDEX idx_url (url),
ADD INDEX idx_status (status),
ADD INDEX idx_application_id (application_id);

-- Add foreign key constraint for urls table
ALTER TABLE urls
ADD CONSTRAINT fk_urls_servers
FOREIGN KEY (application_id) REFERENCES servers(id)
ON DELETE CASCADE
ON UPDATE CASCADE;

-- Add created_at and updated_at timestamps to servers table
ALTER TABLE servers
ADD COLUMN created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
ADD COLUMN updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP;

-- Add updated_at to urls table
ALTER TABLE urls
ADD COLUMN created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
ADD COLUMN updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP;

-- Add NOT NULL constraints to important fields
ALTER TABLE servers
MODIFY COLUMN name VARCHAR(50) NOT NULL,
MODIFY COLUMN ip VARCHAR(45) NOT NULL,
MODIFY COLUMN type VARCHAR(50) NOT NULL;

ALTER TABLE urls
MODIFY COLUMN category VARCHAR(50) NOT NULL DEFAULT 'General',
MODIFY COLUMN status VARCHAR(20) NOT NULL DEFAULT 'Active'; 
