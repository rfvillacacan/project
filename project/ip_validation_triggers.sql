DELIMITER //

-- Trigger for network_devices table
CREATE TRIGGER validate_network_device_ip
BEFORE INSERT ON network_devices
FOR EACH ROW
BEGIN
    IF NEW.ip_address NOT REGEXP '^([0-9]{1,3}\.){3}[0-9]{1,3}$|^([0-9a-fA-F]{1,4}:){7}[0-9a-fA-F]{1,4}$' THEN
        SIGNAL SQLSTATE '45000' 
        SET MESSAGE_TEXT = 'Invalid IP address format in network_devices.ip_address';
    END IF;
END//

CREATE TRIGGER validate_network_device_ip_update
BEFORE UPDATE ON network_devices
FOR EACH ROW
BEGIN
    IF NEW.ip_address NOT REGEXP '^([0-9]{1,3}\.){3}[0-9]{1,3}$|^([0-9a-fA-F]{1,4}:){7}[0-9a-fA-F]{1,4}$' THEN
        SIGNAL SQLSTATE '45000' 
        SET MESSAGE_TEXT = 'Invalid IP address format in network_devices.ip_address';
    END IF;
END//

-- Trigger for servers table
CREATE TRIGGER validate_server_ip
BEFORE INSERT ON servers
FOR EACH ROW
BEGIN
    IF NEW.ip NOT REGEXP '^([0-9]{1,3}\.){3}[0-9]{1,3}$|^([0-9a-fA-F]{1,4}:){7}[0-9a-fA-F]{1,4}$' THEN
        SIGNAL SQLSTATE '45000' 
        SET MESSAGE_TEXT = 'Invalid IP address format in servers.ip';
    END IF;
END//

CREATE TRIGGER validate_server_ip_update
BEFORE UPDATE ON servers
FOR EACH ROW
BEGIN
    IF NEW.ip NOT REGEXP '^([0-9]{1,3}\.){3}[0-9]{1,3}$|^([0-9a-fA-F]{1,4}:){7}[0-9a-fA-F]{1,4}$' THEN
        SIGNAL SQLSTATE '45000' 
        SET MESSAGE_TEXT = 'Invalid IP address format in servers.ip';
    END IF;
END//

DELIMITER ; 
