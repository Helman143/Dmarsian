# Fix 500 Internal Server Error - Even simple_test.php Fails

Since even `simple_test.php` is returning 500, this indicates a **server-level configuration issue**, not a code issue.

## Immediate Steps

### Step 1: Test if Server Responds at All

1. Visit: `https://dmarsians-taekwondo-zkcmy.ondigitalocean.app/minimal_test.txt`
   - If this works: Server is responding, issue is with PHP
   - If this fails: Server configuration problem

2. Visit: `https://dmarsians-taekwondo-zkcmy.ondigitalocean.app/minimal_test.php`
   - If this works: PHP is working, issue is with other files
   - If this fails: PHP configuration problem

### Step 2: Temporarily Disable .htaccess

SSH into your server and run:
```bash
cd /var/www/your-app-directory
mv .htaccess .htaccess.bak
sudo systemctl restart apache2
```

Then test `simple_test.php` again. If it works, the issue is in `.htaccess`.

### Step 3: Check Apache Error Log (CRITICAL)

SSH into server:
```bash
sudo tail -100 /var/log/apache2/error.log
```

Look for errors related to:
- PHP syntax errors
- Permission denied
- Module not loaded
- .htaccess syntax errors

### Step 4: Check PHP is Enabled

```bash
# Check if PHP module is loaded
apache2ctl -M | grep php

# If not, enable it
sudo a2enmod php8.2
sudo systemctl restart apache2
```

### Step 5: Check PHP Configuration

```bash
# Test PHP directly
php -r "echo 'PHP CLI works';"

# Check PHP-FPM status (if using)
sudo systemctl status php8.2-fpm
```

### Step 6: Check File Permissions

```bash
ls -la simple_test.php minimal_test.php
# Should show readable by www-data

# Fix if needed
sudo chown www-data:www-data simple_test.php
sudo chmod 644 simple_test.php
```

### Step 7: Check Apache Virtual Host Configuration

```bash
# Check your site configuration
sudo nano /etc/apache2/sites-available/your-site.conf
# Or
sudo nano /etc/apache2/sites-available/000-default.conf
```

Make sure it has:
```apache
<Directory /var/www/your-app-directory>
    Options -Indexes +FollowSymLinks
    AllowOverride All
    Require all granted
</Directory>
```

### Step 8: Test PHP Info Directly

Create `info.php`:
```php
<?php phpinfo(); ?>
```

Then:
```bash
sudo chown www-data:www-data info.php
sudo chmod 644 info.php
```

Visit: `https://yourdomain.com/info.php`

## Common Causes When Even Simple PHP Fails

### 1. PHP Module Not Loaded
```bash
sudo a2enmod php8.2
sudo systemctl restart apache2
```

### 2. Wrong PHP Handler
Check `/etc/apache2/mods-enabled/php*.conf` exists

### 3. .htaccess Syntax Error
Disable it temporarily:
```bash
mv .htaccess .htaccess.bak
```

### 4. PHP-FPM Not Running
```bash
sudo systemctl start php8.2-fpm
sudo systemctl enable php8.2-fpm
```

### 5. SELinux Blocking (if enabled)
```bash
sudo setsebool -P httpd_can_network_connect 1
```

### 6. File Ownership Wrong
```bash
sudo chown -R www-data:www-data /var/www/your-app-directory
```

## Quick Diagnostic Commands

Run these on your server:

```bash
# 1. Check Apache status
sudo systemctl status apache2

# 2. Check PHP version
php -v

# 3. Check PHP module
apache2ctl -M | grep php

# 4. Check error log
sudo tail -50 /var/log/apache2/error.log

# 5. Test PHP file directly
php simple_test.php

# 6. Check file permissions
ls -la *.php | head -5

# 7. Check Apache configuration
sudo apache2ctl configtest
```

## Most Likely Issue

Since even `simple_test.php` fails, the most common causes are:

1. **PHP module not enabled in Apache**
2. **.htaccess blocking PHP execution**
3. **File permissions preventing Apache from reading files**
4. **PHP-FPM not configured correctly**

## Solution Priority

1. **First**: Disable `.htaccess` and test
2. **Second**: Check Apache error log
3. **Third**: Verify PHP module is loaded
4. **Fourth**: Check file permissions

## After Fixing

1. Re-enable `.htaccess` if it was the issue
2. Test `simple_test.php` again
3. Test `test.php` for full diagnostic
4. Test `webpage.php`








