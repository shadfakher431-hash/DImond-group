# Diamond Money Exchange System

A comprehensive money exchange management system with multi-language support (Kurdish Sorani, English, Arabic) and multi-currency transactions.

## Features

### Core Functionality
1. **Offices Management** - Manage exchange offices with commission rates and balances
2. **Transaction Owners** - Track customers who send and receive money
3. **Owner Safe** - Individual ledger for each transaction owner
4. **Expenses Management** - Track daily and monthly expenses
5. **Users Management** - Role-based access control with PIN authentication
6. **Send Transfer** - Create outgoing money transfers
7. **Incoming Transfers** - Track and complete incoming transfers
8. **Currency Exchange Log** - Record currency exchange transactions
9. **Cash Management** - Manage office safe deposits and withdrawals
10. **Reports** - Comprehensive reporting system

### Technical Features
- **Multi-Language Support**: Kurdish Sorani (default), English, Arabic
- **Multi-Currency Support**: 100+ currencies including IQD and USD
- **Modern UI/UX**: Gradient design with golden/yellow accent colors
- **Responsive Design**: Mobile, tablet, and desktop optimized
- **RTL Support**: Right-to-left layout for Kurdish and Arabic
- **PIN-Based Authentication**: iPhone-style numeric PIN login
- **Database Backup/Restore**: Export and import functionality
- **Print Support**: Print receipts and reports
- **Role-Based Permissions**: Admin, Manager, and User roles

## Installation

### Requirements
- PHP 7.4 or higher
- MySQL/MariaDB 5.7 or higher
- Web server (Apache/Nginx)
- mod_rewrite enabled (for clean URLs)

### Setup Instructions

1. **Clone or download** the repository to your web server directory:
   ```bash
   cd /var/www/html/
   git clone [repository-url] exchange_system
   ```

2. **Create the database**:
   ```bash
   mysql -u root -p
   ```
   ```sql
   CREATE DATABASE exchange_system CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   ```

3. **Import the database schema**:
   ```bash
   mysql -u root -p exchange_system < exchange_system/database.sql
   ```

4. **Configure database connection**:
   Edit `config/config.php` and update:
   ```php
   define('DB_HOST', 'localhost');
   define('DB_USER', 'root');
   define('DB_PASS', 'your_password');
   define('DB_NAME', 'exchange_system');
   ```

5. **Set permissions**:
   ```bash
   chmod -R 755 exchange_system/
   chmod -R 777 exchange_system/exports/
   chmod -R 777 exchange_system/uploads/
   ```

6. **Access the system**:
   Open your browser and navigate to:
   ```
   http://localhost/exchange_system/login.php
   ```

### Default Login Credentials

**Default Admin PIN**: `123456`

**Note**: Change this immediately after first login!

## Usage

### First Time Setup

1. **Login** with the default PIN (123456)
2. **Change your PIN** in Settings → Users → Edit Admin
3. **Add your offices** in Offices Management
4. **Add transaction owners** in Transaction Owners
5. **Configure settings** in Settings page
6. **Add additional users** with appropriate permissions

### Daily Operations

#### Sending a Transfer
1. Go to "Send Transfer"
2. Fill in sender and receiver information
3. Select office, currency, and amount
4. System automatically calculates commission
5. Submit to create transfer receipt

#### Receiving a Transfer
1. Go to "Incoming Transfers"
2. View pending transfers
3. Mark as completed when money is delivered

#### Currency Exchange
1. Go to "Currency Exchange Log"
2. Enter exchange details (from/to currency)
3. System tracks profit/loss automatically

#### Managing Expenses
1. Go to "Expenses"
2. Add daily expenses with categories
3. View daily/monthly summaries

### Managing Users

Administrators can:
- Add/edit/delete users
- Assign roles (Admin, Manager, User)
- Set granular permissions
- Change user PINs

### Reports

Generate various reports:
- Daily/Monthly financial reports
- Office performance reports
- Owner transaction reports
- Expense reports
- Currency exchange reports

## Database Backup & Restore

### Backup
1. Go to Settings
2. Click "Backup Database"
3. SQL file will be downloaded

### Restore
1. Go to Settings
2. Click "Import Database"
3. Select your SQL backup file
4. Confirm restore

**Alternatively**, use command line:
```bash
# Backup
mysqldump -u root -p exchange_system > backup_$(date +%Y%m%d).sql

# Restore
mysql -u root -p exchange_system < backup_20240115.sql
```

## Multi-Language Support

The system supports three languages:
- **Kurdish Sorani (کوردی)** - Default
- **English**
- **Arabic (عربي)**

Switch languages using the language dropdown in the navigation bar.

### Adding New Languages

1. Create a new language file in `languages/` directory:
   ```php
   // languages/fr.php
   <?php
   return [
       'app_name' => 'Système d\'échange Diamond',
       // Add all translations...
   ];
   ```

2. Update `includes/language_helper.php`:
   ```php
   function getAvailableLanguages() {
       return [
           'en' => 'English',
           'ku' => 'کوردی',
           'ar' => 'عربي',
           'fr' => 'Français'  // Add new language
       ];
   }
   ```

## Currency Support

The system supports 100+ currencies including:
- Iraqi Dinar (IQD)
- US Dollar (USD)
- Euro (EUR)
- British Pound (GBP)
- And many more...

Exchange rates can be updated in the `currencies` table.

## Security Features

- **Password Hashing**: All PINs are hashed using bcrypt
- **SQL Injection Protection**: Prepared statements used throughout
- **Session Management**: Secure session handling
- **Activity Logging**: All actions are logged
- **Role-Based Access**: Granular permission system
- **XSS Protection**: Input sanitization and output escaping

## Troubleshooting

### Common Issues

**Database Connection Error**
- Check `config/config.php` credentials
- Ensure MySQL service is running
- Verify database exists

**Permission Denied**
- Check file permissions (755 for directories, 644 for files)
- Ensure web server has write access to `exports/` and `uploads/`

**Language Not Displaying Correctly**
- Ensure UTF-8 encoding in database
- Check browser encoding settings
- Verify language files exist

**Login Issues**
- Default PIN is `123456`
- Clear browser cookies/cache
- Check `users` table in database

## Development

### File Structure
```
exchange_system/
├── assets/
│   ├── css/
│   │   └── style.css          # Main stylesheet
│   └── js/
│       └── main.js             # JavaScript utilities
├── config/
│   └── config.php              # Configuration file
├── exports/                    # Database exports directory
├── includes/
│   ├── header.php              # Common header
│   ├── footer.php              # Common footer
│   └── language_helper.php     # Language functions
├── languages/
│   ├── ku.php                  # Kurdish translations
│   ├── en.php                  # English translations
│   └── ar.php                  # Arabic translations
├── uploads/                    # File uploads directory
├── database.sql                # Database schema
├── login.php                   # Login page
├── logout.php                  # Logout handler
├── index.php                   # Dashboard
├── offices.php                 # Offices management
├── owners.php                  # Owners management
├── owner_safe.php              # Owner safe/ledger
├── expenses.php                # Expenses management
├── users.php                   # Users management
├── send_transfer.php           # Send transfer
├── incoming_transfers.php      # Incoming transfers
├── exchange_log.php            # Currency exchange
├── cash_management.php         # Cash management
├── reports.php                 # Reports
├── settings.php                # System settings
└── README.md                   # This file
```

### Extending the System

**Adding New Features**:
1. Create new PHP file in root directory
2. Include header and footer templates
3. Use existing helper functions
4. Follow existing code patterns

**Modifying UI**:
- Edit `assets/css/style.css` for styling
- Use existing CSS classes for consistency
- Add custom JavaScript in `assets/js/main.js`

## Support

For issues, questions, or contributions:
- Create an issue in the repository
- Contact: Diamond Group

## License

© 2024 Diamond Money Exchange System. All Rights Reserved.

## Credits

Developed by Diamond Group
