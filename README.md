# Admin Login System

This directory contains the admin authentication system for the Student Voting Management System.

## Files Created

### HTML & CSS
- `AdminLogin.html` - Admin login form with modern UI
- `AdminLogin.css` - Styling for admin login form

### PHP Backend
- `admin_auth.php` - Main authentication system
- `check_session.php` - Session validation
- `admin_logout.php` - Logout functionality
- `admin_management.php` - Admin account management
- `setup_admin.php` - Database initialization script

### Database
- `../../config/admin_database.sql` - Database schema for admin tables

## Setup Instructions

### 1. Database Setup
1. Make sure your MySQL database is running
2. Update database credentials in `../../config/database.php`
3. Run the setup script: `http://yoursite.com/Admin/Admin%20login/setup_admin.php`

### 2. Default Admin Accounts
After setup, you can login with these default accounts:

**Super Admin:**
- Username: `admin`
- Password: `admin123`

**Moderator:**
- Username: `moderator`
- Password: `moderator123`

### 3. Features

#### Admin Login Form
- Modern, responsive design similar to user login
- Form validation
- Remember me functionality
- Error/success message display
- Secure password handling

#### Authentication System
- Secure password hashing using PHP's `password_hash()`
- Session management with database storage
- CSRF protection
- Audit logging for all admin actions
- Role-based access control

#### Database Tables
- `admins` - Admin account information
- `admin_sessions` - Active session management
- `admin_logs` - Audit trail for admin actions

#### Security Features
- Password hashing with bcrypt
- Session timeout (24 hours)
- IP address tracking
- User agent logging
- SQL injection prevention
- XSS protection

## Usage

### Access Admin Login
Navigate to: `Admin/Admin login/AdminLogin.html`

### Admin Roles
- **super_admin**: Full system access
- **admin**: Standard admin privileges
- **moderator**: Limited admin privileges

### Session Management
- Sessions are stored in database for security
- Automatic cleanup of expired sessions
- Remember me option extends session duration

## Customization

### Styling
Edit `AdminLogin.css` to customize the appearance:
- Colors are defined in CSS variables at the top
- Responsive design for mobile devices
- Admin-specific styling with red accent colors

### Database Schema
Modify `admin_database.sql` to add additional fields or tables as needed.

### Authentication
Extend `admin_auth.php` to add additional security features like:
- Two-factor authentication
- Account lockout after failed attempts
- Password complexity requirements

## Troubleshooting

### Common Issues
1. **Database connection failed**: Check credentials in `database.php`
2. **Session not working**: Ensure PHP sessions are enabled
3. **Login not working**: Check if database tables exist and are populated

### Debug Mode
Enable error reporting in PHP to see detailed error messages:
```php
error_reporting(E_ALL);
ini_set('display_errors', 1);
```

## Security Notes

- Change default passwords immediately after setup
- Use HTTPS in production
- Regularly update admin passwords
- Monitor admin logs for suspicious activity
- Keep PHP and MySQL updated
