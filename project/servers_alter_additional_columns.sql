ALTER TABLE servers
  ADD COLUMN domain VARCHAR(100) NULL AFTER name,
  ADD COLUMN ipaddresslist TEXT NULL AFTER ip,
  ADD COLUMN operating_system VARCHAR(100) NULL AFTER ipaddresslist,
  ADD COLUMN application_name VARCHAR(100) NULL AFTER operating_system,
  ADD COLUMN notes TEXT NULL AFTER application_name; 
