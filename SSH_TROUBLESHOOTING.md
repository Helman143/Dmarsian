# SSH into DigitalOcean and Check Error Logs

## Step 1: Connect to Your DigitalOcean Server

### On Windows (PowerShell/CMD):
```bash
ssh root@YOUR_DROPLET_IP
```

### On Mac/Linux:
```bash
ssh root@YOUR_DROPLET_IP
```

Replace `YOUR_DROPLET_IP` with your actual DigitalOcean droplet IP address.

## Step 2: Navigate to Your Application Directory

```bash
cd /var/www/your-app-directory
# Or wherever you deployed the app
```

## Step 3: Check Apache Error Log (MOST IMPORTANT)

```bash
# View last 50 lines
sudo tail -50 /var/log/apache2/error.log

# Or watch in real-time
sudo tail -f /var/log/apache2/error.log
```

**This will show you the EXACT error causing the 500!**

## Step 4: Run the Error Check Script

```bash
# Make it executable
chmod +x check_errors.sh
./check_errors.sh

# Or get server logs
chmod +x get_server_logs.sh
./get_server_logs.sh
```

## Step 5: Common Commands

### Check PHP Syntax
```bash
php -l config.php
php -l env-loader.php
php -l get_posts.php
php -l webpage.php
```

### Check File Permissions
```bash
ls -la config.php env-loader.php .env
```

### Check if .env exists
```bash
ls -la .env
```

### Check Apache Status
```bash
sudo systemctl status apache2
```

### Restart Apache
```bash
sudo systemctl restart apache2
```

### Check PHP Version
```bash
php -v
```

### Test Database Connection
```bash
php -r "require 'config.php'; var_dump(\$conn->connect_error);"
```

## Step 6: Quick Fixes

### If .env is missing:
```bash
cp env.example .env
nano .env  # Edit with your credentials
chmod 600 .env
```

### If vendor folder is missing:
```bash
composer install --no-dev --optimize-autoloader
```

### If permissions are wrong:
```bash
sudo chown -R www-data:www-data .
sudo chmod -R 755 .
sudo chmod -R 777 uploads/
```

### If Apache mod_rewrite not enabled:
```bash
sudo a2enmod rewrite
sudo systemctl restart apache2
```

## Step 7: Enable Error Display (Temporary)

Edit `webpage.php` or `index.php` and add at the top:
```php
<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
?>
```

Then visit the page and you'll see the error directly.

## Step 8: Test Files

### Test basic PHP:
Visit: `https://yourdomain.com/simple_test.php`

### Test full diagnostic:
Visit: `https://yourdomain.com/test.php`

### Test API endpoint:
Visit: `https://yourdomain.com/get_posts.php?category=achievement`

## What to Look For in Error Logs

Common errors you might see:

1. **Parse error: syntax error** - PHP syntax issue
2. **Fatal error: require_once()** - Missing file
3. **Call to undefined function** - Missing PHP extension
4. **Access denied for user** - Database connection issue
5. **Permission denied** - File permission issue
6. **Class not found** - Missing Composer dependency

## After Finding the Error

1. Fix the issue based on the error message
2. Restart Apache: `sudo systemctl restart apache2`
3. Test the page again
4. Remove error display code for production

## Need Help?

Share the error log output and I can help you fix it!












