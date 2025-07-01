DELIMITER //

CREATE TRIGGER validate_ip_list
BEFORE INSERT ON servers
FOR EACH ROW
BEGIN
    IF NEW.ipaddresslist IS NOT NULL THEN
        -- Split the IP list and validate each IP
        SET @ip_list = NEW.ipaddresslist;
        SET @valid = 1;
        
        WHILE LENGTH(@ip_list) > 0 DO
            SET @ip = SUBSTRING_INDEX(@ip_list, ',', 1);
            SET @ip_list = SUBSTRING(@ip_list, LENGTH(@ip) + 2);
            
            IF @ip NOT REGEXP '^([0-9]{1,3}\.){3}[0-9]{1,3}$|^([0-9a-fA-F]{1,4}:){7}[0-9a-fA-F]{1,4}$' THEN
                SET @valid = 0;
                SIGNAL SQLSTATE '45000' 
                SET MESSAGE_TEXT = 'Invalid IP address format in ipaddresslist';
            END IF;
        END WHILE;
        
        IF @valid = 0 THEN
            SIGNAL SQLSTATE '45000' 
            SET MESSAGE_TEXT = 'Invalid IP address format in ipaddresslist';
        END IF;
    END IF;
END//

DELIMITER ; 
