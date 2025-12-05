# Troubleshooting Guide - Internal Server Error (500)

## Quick Diagnostic Steps

### 1. Check Apache Error Logs
```bash
sudo tail -f /var/log/apache2/error.log
```
This will show you the actual error causing the 500 error.

### 2. Run Diagnostic Script
Access `diagnostic.php` in your browser:
```
https://yourdomain.com/diagnostic.php
```
This will show you:
- PHP configuration
- Missing extensions
- File permissions
- Environment variables
- Database connection status

**⚠️ IMPORTANT: Delete diagnostic.php after troubleshooting!**

### 3. Common Issues and Fixes

#### Issue: Syntax Error in config.php
**Error:** `Parse error: syntax error, unexpected '?' in config.php`
**Fix:** 
- Check for `_DIR_` (should be `__DIR__` with double underscore)
- Check for duplicate `<?php` tags
- Check for unclosed PHP tags

#### Issue: Missing Composer Dependencies
**Error:** `Fatal error: require_once(): Failed opening required 'vendor/autoload.php'`
**Fix:**
```bash
cd /var/www/your-app-directory
composer install --no-dev --optimize-autoloader
```

#### Issue: Missing .env File
**Error:** Database connection fails or environment variables not set
**Fix:**
```bash
cp env.example .env
nano .env  # Edit with your actual values
chmod 600 .env
```

#### Issue: File Permissions
**Error:** `Permission denied` or files not accessible
**Fix:**
```bash
sudo chown -R www-data:www-data /var/www/your-app-directory
sudo chmod -R 755 /var/www/your-app-directory
sudo chmod -R 777 /var/www/your-app-directory/uploads
```

#### Issue: Missing PHP Extensions
**Error:** `Call to undefined function mysqli_connect()`
**Fix:**
```bash
sudo apt install php8.2-mysqli php8.2-mbstring php8.2-curl php8.2-gd php8.2-zip php8.2-xml
sudo systemctl restart apache2
```

#### Issue: Apache mod_rewrite Not Enabled
**Error:** `.htaccess` rules not working
**Fix:**
```bash
sudo a2enmod rewrite
sudo systemctl restart apache2
```

#### Issue: Database Connection Failed
**Error:** `Database connection failed: Access denied`
**Fix:**
1. Check `.env` file has correct credentials
2. Verify database exists: `mysql -u user -p -e "SHOW DATABASES;"`
3. Check MySQL service: `sudo systemctl status mysql`
4. Verify user permissions:
```sql
GRANT ALL PRIVILEGES ON capstone_db.* TO 'your_user'@'localhost';
FLUSH PRIVILEGES;
```

#### Issue: .htaccess Causing Errors
**Error:** 500 error after adding .htaccess
**Fix:**
1. Temporarily rename `.htaccess` to `.htaccess.bak`
2. Check if site loads
3. If it loads, the issue is in .htaccess
4. Check Apache error log for specific .htaccess error
5. Fix the specific rule causing the issue

### 4. Step-by-Step Debugging

#### Step 1: Enable Error Display (Temporary)
Add to the top of `index.php` or your main entry file:
```php
<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
?>
```

#### Step 2: Test config.php
Create `test_config.php`:
```php
<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once 'config.php';
echo "Config loaded successfully!";
if (isset($conn) && $conn->connect_error) {
    echo "Database error: " . $conn->connect_error;
} else {
    echo "Database connected!";
}
?>
```

#### Step 3: Check File Paths
Verify all `require_once` and `include` statements use correct paths:
- Use `__DIR__` for relative paths
- Check file exists before including

### 5. Server-Side Checks

#### Check PHP Version
```bash
php -v
```
Should be PHP 7.4 or higher.

#### Check Apache Status
```bash
sudo systemctl status apache2
```

#### Check MySQL Status
```bash
sudo systemctl status mysql
```

#### Check Disk Space
```bash
df -h
```

#### Check Memory
```bash
free -h
```

### 6. Common Configuration Issues

#### PHP.ini Settings
Edit `/etc/php/8.2/apache2/php.ini`:
```ini
display_errors = Off  # Off for production
log_errors = On
error_log = /var/log/php_errors.log
upload_max_filesize = 10M
post_max_size = 10M
memory_limit = 256M
```

#### Apache Virtual Host
Check `/etc/apache2/sites-available/your-site.conf`:
```apache
<VirtualHost *:80>
    ServerName yourdomain.com
    DocumentRoot /var/www/your-app-directory
    
    <Directory /var/www/your-app-directory>
        Options -Indexes +FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>
    
    ErrorLog ${APACHE_LOG_DIR}/error.log
    CustomLog ${APACHE_LOG_DIR}/access.log combined
</VirtualHost>
```

### 7. Emergency Fixes

If nothing works, try this minimal setup:

1. **Disable .htaccess temporarily:**
```bash
mv .htaccess .htaccess.bak
```

2. **Create minimal config.php:**
```php
<?php
$conn = new mysqli('localhost', 'user', 'password', 'database');
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
```

3. **Test basic PHP:**
Create `test.php`:
```php
<?php phpinfo(); ?>
```

### 8. Getting Help

When asking for help, provide:
1. Full error message from Apache error log
2. PHP version: `php -v`
3. Output from `diagnostic.php`
4. Relevant sections from `.env` (without passwords)
5. Apache error log snippet

### 9. Security Reminders

After fixing issues:
- ✅ Remove `diagnostic.php`
- ✅ Remove `test.php` or `test_config.php`
- ✅ Set `display_errors = Off` in php.ini
- ✅ Restore `.htaccess` if disabled
- ✅ Verify `.env` has correct permissions (600)







