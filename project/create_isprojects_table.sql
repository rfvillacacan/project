CREATE TABLE IF NOT EXISTS `tbl_isprojects` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `service` varchar(255) NOT NULL,
  `due_date` date NOT NULL,
  `progress` varchar(50) NOT NULL,
  `comments` text,
  `assign_to_team` enum('Prep', 'GRC', 'SD', 'SecOPS', 'OT', 'IS') NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4; 
