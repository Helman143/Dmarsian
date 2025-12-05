# Quick Fix Guide for 500 Internal Server Error

## Immediate Steps on DigitalOcean Server

### Step 1: Check Apache Error Log
```bash
sudo tail -50 /var/log/apache2/error.log
```
This will show you the exact error causing the 500 error.

### Step 2: Run Error Check Script
```bash
chmod +x check_errors.sh
./check_errors.sh
```

### Step 3: Test PHP Directly
Access in browser: `https://yourdomain.com/test.php`

This will show you:
- If PHP is working
- If config.php loads
- If database connects
- What's missing

### Step 4: Common Fixes

#### Fix 1: Missing .env File
```bash
cd /var/www/your-app-directory
cp env.example .env
nano .env  # Edit with your database credentials
chmod 600 .env
```

#### Fix 2: Missing Composer Dependencies
```bash
cd /var/www/your-app-directory
composer install --no-dev --optimize-autoloader
```

#### Fix 3: File Permissions
```bash
cd /var/www/your-app-directory
sudo chown -R www-data:www-data .
sudo chmod -R 755 .
sudo chmod -R 777 uploads/
sudo chmod 600 .env
```

#### Fix 4: Apache mod_rewrite
```bash
sudo a2enmod rewrite
sudo systemctl restart apache2
```

#### Fix 5: Check PHP Syntax
```bash
php -l config.php
php -l env-loader.php
php -l index.php
```

#### Fix 6: Restart Services
```bash
sudo systemctl restart apache2
# If using PHP-FPM:
sudo systemctl restart php8.2-fpm
```

### Step 5: Temporarily Disable .htaccess
If .htaccess is causing issues:
```bash
mv .htaccess .htaccess.bak
sudo systemctl restart apache2
# Test if site loads
# If it works, the issue is in .htaccess
```

### Step 6: Enable Error Display (Temporary)
Add to top of `index.php`:
```php
<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
?>
```

## Most Common Issues

1. **Missing .env file** - Create from env.example
2. **Database connection failed** - Check .env credentials
3. **Missing vendor folder** - Run composer install
4. **File permissions** - Set proper ownership and permissions
5. **PHP extensions missing** - Install required extensions
6. **Apache mod_rewrite not enabled** - Enable and restart

## After Fixing

1. Remove test.php: `rm test.php`
2. Remove error display from index.php (for production)
3. Restore .htaccess if disabled
4. Run optimize.sh to clear caches





