# Diamond Money Exchange System - Installation Guide

## Quick Start Installation

### Step 1: Prerequisites Check
Make sure you have:
- PHP 7.4 or higher
- MySQL/MariaDB 5.7 or higher  
- Apache/Nginx web server
- mod_rewrite enabled (for Apache)

### Step 2: Upload Files
Upload the entire `exchange_system` folder to your web server:
```
/var/www/html/exchange_system/
```

### Step 3: Create Database
```sql
CREATE DATABASE exchange_system CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

### Step 4: Import Database Schema
```bash
mysql -u root -p exchange_system < exchange_system/database.sql
```

Or use phpMyAdmin:
1. Open phpMyAdmin
2. Select `exchange_system` database
3. Go to Import tab
4. Choose `database.sql` file
5. Click Go

### Step 5: Configure Database Connection
Edit `config/config.php`:
```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');           // Your MySQL username
define('DB_PASS', 'your_password');  // Your MySQL password
define('DB_NAME', 'exchange_system');
```

### Step 6: Set Directory Permissions
```bash
chmod -R 755 exchange_system/
chmod -R 777 exchange_system/exports/
chmod -R 777 exchange_system/uploads/
```

### Step 7: Access the System
Open your browser:
```
http://localhost/exchange_system/login.php
```

Or if using a domain:
```
http://yourdomain.com/exchange_system/login.php
```

### Step 8: First Login
**Default Admin PIN**: `123456`

**IMPORTANT**: Change this PIN immediately after login!

## Post-Installation Steps

### 1. Change Admin PIN
- Go to Users Management
- Click edit on admin user
- Click "Change PIN" button
- Enter new 6-digit PIN

### 2. Configure Company Settings
- Go to Settings
- Update company name in all languages
- Set default language
- Set default commission rate

### 3. Add Your Data
- Add Offices
- Add Transaction Owners
- Add Users with appropriate permissions

### 4. Backup Configuration
- Go to Settings
- Click "Backup Database"
- Save the file securely

## Troubleshooting

### Cannot connect to database
```
Solution: Check config/config.php credentials
```

### Permission denied errors
```bash
chmod -R 755 exchange_system/
chown -R www-data:www-data exchange_system/  # For Ubuntu/Debian
```

### Page not found (404 errors)
```
Solution: Enable mod_rewrite in Apache
sudo a2enmod rewrite
sudo service apache2 restart
```

### Blank page or PHP errors
```
Solution: Enable error reporting temporarily in config.php:
error_reporting(E_ALL);
ini_set('display_errors', 1);
```

### Characters not displaying correctly
```
Solution: Ensure UTF-8 encoding:
- Database: utf8mb4_unicode_ci
- Browser: UTF-8
- Files: Save as UTF-8
```

## Security Recommendations

1. **Change Default PIN Immediately**
2. **Use Strong Database Password**
3. **Enable HTTPS (SSL Certificate)**
4. **Regular Database Backups**
5. **Keep PHP and MySQL Updated**
6. **Restrict Database Access**
7. **Use Different Database User (not root)**

### Creating Secure Database User
```sql
CREATE USER 'exchange_user'@'localhost' IDENTIFIED BY 'strong_password_here';
GRANT ALL PRIVILEGES ON exchange_system.* TO 'exchange_user'@'localhost';
FLUSH PRIVILEGES;
```

Then update config.php:
```php
define('DB_USER', 'exchange_user');
define('DB_PASS', 'strong_password_here');
```

## System Requirements Details

### Minimum Requirements
- PHP 7.4+
- MySQL 5.7+ or MariaDB 10.2+
- 50MB disk space
- 128MB RAM

### Recommended Requirements
- PHP 8.0+
- MySQL 8.0+ or MariaDB 10.6+
- 500MB disk space
- 512MB RAM
- SSL Certificate

### Required PHP Extensions
- PDO
- PDO_MySQL
- mbstring
- json
- session

## For Production Deployment

### 1. Disable Error Display
In config.php:
```php
error_reporting(0);
ini_set('display_errors', 0);
```

### 2. Enable HTTPS
Uncomment in .htaccess:
```apache
RewriteCond %{HTTPS} off
RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]
```

### 3. Setup Automated Backups
Create cron job:
```bash
crontab -e
# Add this line (daily backup at 2 AM):
0 2 * * * mysqldump -u exchange_user -p'password' exchange_system > /backups/exchange_$(date +\%Y\%m\%d).sql
```

### 4. Monitor Logs
Check Apache/Nginx error logs regularly:
```bash
tail -f /var/log/apache2/error.log
```

## Support

For issues or questions:
- Check README.md
- Review this INSTALL.md
- Contact system administrator

## License

© 2024 Diamond Money Exchange System
All Rights Reserved
