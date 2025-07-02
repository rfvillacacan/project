# AUTHMAN Dashboard Project Documentation

## Project Overview
AUTHMAN is a comprehensive IT asset management dashboard that monitors and manages various types of servers, network devices, and URLs. The system provides real-time monitoring, status tracking, and compliance metrics for IT infrastructure.

## Database Structure

### 1. Servers Table
```sql
CREATE TABLE servers (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    domain VARCHAR(255),
    ip VARCHAR(45) NOT NULL,
    operating_system VARCHAR(255),
    application_name VARCHAR(255),
    type ENUM('Application', 'IT', 'OT') NOT NULL,
    status ENUM('Online', 'Offline') DEFAULT 'Offline',
    agent_online ENUM('Yes', 'No') DEFAULT 'No',
    siem_monitored ENUM('Yes', 'No') DEFAULT 'No',
    penetration_tested ENUM('Yes', 'No') DEFAULT 'No',
    user_access_review ENUM('Yes', 'No') DEFAULT 'No',
    vapt ENUM('Yes', 'No') DEFAULT 'No',
    availability ENUM('Yes', 'No') DEFAULT 'No',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
```

### 2. Network Devices Table
```sql
CREATE TABLE network_devices (
    id INT PRIMARY KEY AUTO_INCREMENT,
    hostname VARCHAR(255) NOT NULL,
    domain VARCHAR(255),
    ip_address VARCHAR(45) NOT NULL,
    operating_system VARCHAR(255),
    role VARCHAR(255),
    criticality ENUM('Low', 'Medium', 'High') DEFAULT 'Low',
    status ENUM('Online', 'Offline') DEFAULT 'Offline',
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
```

### 3. URLs Table
```sql
CREATE TABLE urls (
    id INT PRIMARY KEY AUTO_INCREMENT,
    url VARCHAR(255) NOT NULL,
    category VARCHAR(255),
    status ENUM('Active', 'Inactive') DEFAULT 'Active',
    last_checked DATETIME,
    application_id INT,
    agent_online ENUM('Yes', 'No') DEFAULT 'No',
    siem_monitored ENUM('Yes', 'No') DEFAULT 'No',
    penetration_tested ENUM('Yes', 'No') DEFAULT 'No',
    user_access_review ENUM('Yes', 'No') DEFAULT 'No',
    vapt ENUM('Yes', 'No') DEFAULT 'No',
    availability ENUM('Yes', 'No') DEFAULT 'No',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (application_id) REFERENCES servers(id)
);
```

### 4. Daily Tasks Table

The `daily_tasks` table stores routine tasks and now includes several
additional fields for scheduling and time management:

| Column | Purpose |
|--------|---------|
| `due_date` | Date when the task should be completed. |
| `priority` | Indicates urgency (`Low`, `Medium`, or `High`). |

These columns are added via the `daily_tasks_alter_overhaul.sql` migration.
Apply it with the MySQL client or through phpMyAdmin:

```bash
mysql -u <user> -p <database> < daily_tasks_alter_overhaul.sql
```

## Key Features

### 1. Dashboard Overview
- Overall compliance metrics
- Total assets count
- Agent online status
- SIEM monitoring status
- Penetration testing status
- User access review status
- VAPT status
- Availability metrics

### 2. Server Management
- Categorized into Application, IT, and OT servers
- Status monitoring (Online/Offline)
- Agent status tracking
- Security compliance tracking
- Real-time updates

### 3. Network Device Management
- Device status monitoring
- Criticality levels (Low/Medium/High)
- Role-based categorization
- IP address management
- Operating system tracking

### 4. URL Management
- Status monitoring (Active/Inactive)
- Category-based organization
- Last checked timestamp
- Security compliance tracking
- Application association

## Frontend Structure

### 1. Main Components
- Dashboard overview
- Server management tabs
- Network device management
- URL management
- Real-time clock (KSA timezone)

### 2. DataTables Integration
- Server-side processing
- Pagination
- Search functionality
- Sorting capabilities
- Responsive design

### 3. Modal Forms
- Edit server details
- Edit network device details
- Edit URL details
- Form validation
- Real-time updates

## API Endpoints

### 1. Data Retrieval
- `servers_datatable.php` - Server data with type filtering
- `network_devices_datatable.php` - Network device data
- `urls_datatable.php` - URL data
- `project_management/api/project_management_api.php?endpoint=projects` - List projects
- `project_management/api/project_management_api.php?endpoint=tasks&project_id=ID` - List tasks (optional project filter)

### 2. Statistics
- `dashboard_summary_stats.php` - Overall dashboard statistics
- `sub_dashboard_stats.php?type=Application` - Application server statistics
- `sub_dashboard_stats.php?type=IT` - IT server statistics
- `sub_dashboard_stats.php?type=OT` - OT server statistics
- `sub_dashboard_stats.php?type=Network` - Network device statistics
- `urls_sub_dashboard_stats.php` - URL statistics

### 3. Data Modification
- `edit_server.php` - Update server details
- `edit_network_device.php` - Update network device details
- `edit_url.php` - Update URL details

## Security Features

### 1. Data Validation
- IP address validation
- Status value standardization
- Criticality level enforcement
- Timestamp tracking

### 2. Database Constraints
- Foreign key relationships
- Enum value restrictions
- Required field enforcement
- Default value handling

## UI/UX Features

### 1. Dark Mode
- Custom dark theme
- High contrast elements
- Readable typography
- Consistent color scheme

### 2. Interactive Elements
- Auto-switching tabs
- Collapsible navigation
- Real-time updates
- Responsive design

### 3. Data Visualization
- Status badges
- Compliance metrics
- Performance indicators
- Health monitoring

## Setup Requirements

### 1. Server Requirements
- PHP 7.4 or higher
- MySQL 5.7 or higher
- Apache/Nginx web server
- mod_rewrite enabled

### 2. Dependencies
- Bootstrap 5.3.5
- jQuery 3.7.0
- DataTables 1.13.6
- Font Awesome (for icons)

### 3. Database Configuration
- Database name: dashboard
- Default user: root
- Default password: (empty)
- Host: localhost

## Common Issues and Solutions

### 1. Status Dropdown Issues
- Clean HTML tags from status values
- Set appropriate default values
- Handle null/empty cases

### 2. DataTable Loading
- Ensure proper server-side processing
- Handle AJAX errors gracefully
- Implement proper error messages

### 3. Real-time Updates
- Implement proper refresh intervals
- Handle connection issues
- Maintain data consistency

## Future Improvements

### 1. Planned Features
- User authentication
- Role-based access control
- Audit logging
- API documentation
- Automated testing

### 2. Performance Optimization
- Query optimization
- Caching implementation
- Asset minification
- Lazy loading

### 3. Security Enhancements
- Input sanitization
- CSRF protection
- Rate limiting
- Session management 
