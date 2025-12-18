# DigitalOcean Deployment Guide
## D'MARSIANS Taekwondo System - PHP Application

This guide will walk you through deploying your native PHP application to DigitalOcean.

---

## 📋 Table of Contents

1. [Prerequisites](#prerequisites)
2. [DigitalOcean Droplet Setup](#digitalocean-droplet-setup)
3. [Server Configuration](#server-configuration)
4. [Application Deployment](#application-deployment)
5. [Database Setup](#database-setup)
6. [Security Configuration](#security-configuration)
7. [SSL/HTTPS Setup](#sslhttps-setup)
8. [Domain Configuration](#domain-configuration)
9. [Environment Variables](#environment-variables)
10. [File Permissions](#file-permissions)
11. [Testing & Verification](#testing--verification)
12. [Backup Strategy](#backup-strategy)
13. [Troubleshooting](#troubleshooting)

---

## Prerequisites

- DigitalOcean account
- Domain name (optional but recommended)
- SSH client (PuTTY on Windows, Terminal on Mac/Linux)
- Basic knowledge of Linux commands

---

## DigitalOcean Droplet Setup

### Step 1: Create a Droplet

1. **Log in to DigitalOcean** and click "Create" → "Droplets"

2. **Choose Configuration:**
   - **Image**: Ubuntu 22.04 LTS (recommended)
   - **Plan**: 
     - **Minimum**: $6/month (1GB RAM, 1 vCPU) - for testing
     - **Recommended**: $12/month (2GB RAM, 1 vCPU) - for production
     - **Better**: $18/month (2GB RAM, 2 vCPU) - for better performance
   - **Datacenter Region**: Choose closest to your users
   - **Authentication**: SSH keys (recommended) or Password
   - **Hostname**: `taekwondo-app` (or your preferred name)

3. **Click "Create Droplet"**

4. **Note your Droplet's IP address** (e.g., `157.230.123.45`)

### Step 2: Connect to Your Droplet

**Windows (PowerShell/CMD):**
```bash
ssh root@YOUR_DROPLET_IP
```

**Mac/Linux:**
```bash
ssh root@YOUR_DROPLET_IP
```

Replace `YOUR_DROPLET_IP` with your actual droplet IP address.

---

## Server Configuration

### Step 1: Update System Packages

```bash
apt update && apt upgrade -y
```

### Step 2: Install LAMP Stack

#### Install Apache Web Server
```bash
apt install apache2 -y
systemctl start apache2
systemctl enable apache2
```

#### Install MySQL Database
```bash
apt install mysql-server -y
systemctl start mysql
systemctl enable mysql
```

**Secure MySQL Installation:**
```bash
mysql_secure_installation
```

Follow the prompts:
- Set root password (choose a strong password)
- Remove anonymous users: **Y**
- Disallow root login remotely: **Y**
- Remove test database: **Y**
- Reload privilege tables: **Y**

#### Install PHP 8.2 and Required Extensions
```bash
apt install software-properties-common -y
add-apt-repository ppa:ondrej/php -y
apt update
apt install php8.2 php8.2-cli php8.2-common php8.2-mysql php8.2-zip php8.2-gd php8.2-mbstring php8.2-curl php8.2-xml php8.2-bcmath php8.2-fpm libapache2-mod-php8.2 -y
```

#### Verify PHP Installation
```bash
php -v
```

Should show PHP 8.2.x

### Step 3: Enable Apache Modules

```bash
a2enmod rewrite
a2enmod ssl
a2enmod headers
systemctl restart apache2
```

### Step 4: Configure PHP Settings

Edit PHP configuration:
```bash
nano /etc/php/8.2/apache2/php.ini
```

Find and update these settings:
```ini
upload_max_filesize = 10M
post_max_size = 10M
max_execution_time = 300
memory_limit = 256M
display_errors = Off
error_reporting = E_ALL & ~E_DEPRECATED & ~E_STRICT
log_errors = On
error_log = /var/log/php_errors.log
date.timezone = Asia/Manila
```

Save and exit (Ctrl+X, then Y, then Enter)

Restart Apache:
```bash
systemctl restart apache2
```

---

## Application Deployment

### Step 1: Install Git (if not already installed)

```bash
apt install git -y
```

### Step 2: Clone or Upload Your Application

**Option A: Using Git (Recommended)**
```bash
cd /var/www
git clone YOUR_REPOSITORY_URL Capstone
cd Capstone
```

**Option B: Using SCP (from your local machine)**

On your **local Windows machine** (PowerShell):
```powershell
# Navigate to your project directory
cd C:\xampp\htdocs

# Upload files to server
scp -r Capstone root@YOUR_DROPLET_IP:/var/www/
```

**Option C: Using SFTP Client**
- Use FileZilla, WinSCP, or similar
- Connect to your droplet via SFTP
- Upload the entire `Capstone` folder to `/var/www/`

### Step 3: Set Proper Permissions

```bash
cd /var/www/Capstone
chown -R www-data:www-data /var/www/Capstone
chmod -R 755 /var/www/Capstone
chmod -R 777 /var/www/Capstone/uploads
```

### Step 4: Configure Apache Virtual Host

Create a new virtual host configuration:
```bash
nano /etc/apache2/sites-available/taekwondo.conf
```

Add the following configuration:
```apache
<VirtualHost *:80>
    ServerName yourdomain.com
    ServerAlias www.yourdomain.com
    DocumentRoot /var/www/Capstone

    <Directory /var/www/Capstone>
        Options -Indexes +FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>

    ErrorLog ${APACHE_LOG_DIR}/taekwondo_error.log
    CustomLog ${APACHE_LOG_DIR}/taekwondo_access.log combined
</VirtualHost>
```

**If you don't have a domain yet**, use your droplet IP:
```apache
<VirtualHost *:80>
    ServerName YOUR_DROPLET_IP
    DocumentRoot /var/www/Capstone

    <Directory /var/www/Capstone>
        Options -Indexes +FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>

    ErrorLog ${APACHE_LOG_DIR}/taekwondo_error.log
    CustomLog ${APACHE_LOG_DIR}/taekwondo_access.log combined
</VirtualHost>
```

Enable the site:
```bash
a2ensite taekwondo.conf
a2dissite 000-default.conf
systemctl reload apache2
```

---

## Database Setup

### Step 1: Create Database and User

Log into MySQL:
```bash
mysql -u root -p
```

Enter your MySQL root password when prompted.

Run these SQL commands:
```sql
CREATE DATABASE capstone_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'capstone_user'@'localhost' IDENTIFIED BY 'YOUR_STRONG_PASSWORD_HERE';
GRANT ALL PRIVILEGES ON capstone_db.* TO 'capstone_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

**Important:** Replace `YOUR_STRONG_PASSWORD_HERE` with a strong password. Save this password!

### Step 2: Import Database Schema

```bash
cd /var/www/Capstone
mysql -u capstone_user -p capstone_db < Database/db.sql
```

Enter the password you set for `capstone_user`.

### Step 3: Verify Database Import

```bash
mysql -u capstone_user -p capstone_db -e "SHOW TABLES;"
```

You should see all your tables listed.

---

## Environment Variables

### Step 1: Create Environment File

Create a `.env` file (optional but recommended for production):
```bash
nano /var/www/Capstone/.env
```

Add your configuration:
```env
DB_HOST=localhost
DB_USER=capstone_user
DB_PASSWORD=YOUR_DATABASE_PASSWORD
DB_NAME=capstone_db

SMTP2GO_API_KEY=api-DB88D1F1E4B74779BDB77FC2895D8325
SMTP2GO_SENDER_EMAIL=helmandashelle.dacuma@sccpag.edu.ph
SMTP2GO_SENDER_NAME=D'Marsians Taekwondo Gym
ADMIN_BCC_EMAIL=helmandacuma5@gmail.com
```

**Note:** Your `config.php` already supports environment variables, so this will work automatically.

### Step 2: Secure the .env File

```bash
chmod 600 /var/www/Capstone/.env
chown www-data:www-data /var/www/Capstone/.env
```

### Alternative: Update config.php Directly

If you prefer not to use `.env`, edit `config.php`:
```bash
nano /var/www/Capstone/config.php
```

Update the database credentials:
```php
define('DB_SERVER', 'localhost');
define('DB_USERNAME', 'capstone_user');
define('DB_PASSWORD', 'YOUR_DATABASE_PASSWORD');
define('DB_NAME', 'capstone_db');
```

---

## Security Configuration

### Step 1: Configure Firewall (UFW)

```bash
ufw allow OpenSSH
ufw allow 'Apache Full'
ufw enable
ufw status
```

### Step 2: Secure PHP Configuration

Already done in [Server Configuration](#step-4-configure-php-settings), but verify:
- `display_errors = Off`
- `expose_php = Off` (add this if not present)

### Step 3: Create .htaccess for Security (Optional but Recommended)

```bash
nano /var/www/Capstone/.htaccess
```

Add security headers:
```apache
# Prevent directory listing
Options -Indexes

# Protect sensitive files
<FilesMatch "\.(env|log|sql|json)$">
    Order allow,deny
    Deny from all
</FilesMatch>

# Security headers
<IfModule mod_headers.c>
    Header set X-Content-Type-Options "nosniff"
    Header set X-Frame-Options "SAMEORIGIN"
    Header set X-XSS-Protection "1; mode=block"
</IfModule>
```

### Step 4: Remove Development Files (Production)

```bash
cd /var/www/Capstone
rm -f test_*.php
rm -f ngrok.exe
# Keep tests/ directory if you want, but consider removing in production
```

---

## SSL/HTTPS Setup

### Step 1: Install Certbot

```bash
apt install certbot python3-certbot-apache -y
```

### Step 2: Obtain SSL Certificate

**If you have a domain:**
```bash
certbot --apache -d yourdomain.com -d www.yourdomain.com
```

Follow the prompts:
- Enter your email address
- Agree to terms
- Choose whether to redirect HTTP to HTTPS (recommended: Yes)

**If you don't have a domain yet:**
- You can skip SSL for now
- Access via HTTP: `http://YOUR_DROPLET_IP`
- Set up SSL later when you have a domain

### Step 3: Auto-Renewal

Certbot sets up auto-renewal automatically. Test it:
```bash
certbot renew --dry-run
```

---

## Domain Configuration

### Step 1: Point Domain to Droplet

1. **Log into your domain registrar** (GoDaddy, Namecheap, etc.)

2. **Add/Edit DNS A Record:**
   - **Type**: A
   - **Host**: @ (or leave blank)
   - **Value**: Your Droplet IP address
   - **TTL**: 3600 (or default)

3. **Add WWW Record (optional):**
   - **Type**: A
   - **Host**: www
   - **Value**: Your Droplet IP address
   - **TTL**: 3600

4. **Wait for DNS propagation** (can take 5 minutes to 48 hours)

### Step 2: Update Apache Virtual Host

If you set up SSL with Certbot, it should have already updated your virtual host. Verify:
```bash
cat /etc/apache2/sites-available/taekwondo-le-ssl.conf
```

---

## File Permissions

Ensure proper permissions are set:

```bash
cd /var/www/Capstone

# Set ownership
chown -R www-data:www-data .

# Set directory permissions
find . -type d -exec chmod 755 {} \;

# Set file permissions
find . -type f -exec chmod 644 {} \;

# Set uploads directory to writable
chmod -R 777 uploads

# Make specific files executable if needed
chmod 755 *.php
```

---

## Testing & Verification

### Step 1: Test Apache

Visit in browser:
- `http://YOUR_DROPLET_IP` or `http://yourdomain.com`

You should see your application.

### Step 2: Test Database Connection

Create a test file temporarily:
```bash
nano /var/www/Capstone/test_db.php
```

Add:
```php
<?php
require_once 'db_connect.php';
$conn = connectDB();
if ($conn) {
    echo "Database connection successful!";
    $conn->close();
} else {
    echo "Database connection failed!";
}
?>
```

Visit: `http://YOUR_DROPLET_IP/test_db.php`

If successful, **delete the test file**:
```bash
rm /var/www/Capstone/test_db.php
```

### Step 3: Test Application Features

1. **Admin Login**: `http://YOUR_DROPLET_IP/admin_login.php`
2. **User Signup**: `http://YOUR_DROPLET_IP/signup.php`
3. **File Upload**: Test uploading images in post management
4. **Email Functionality**: Test sending emails (if configured)

---

## Backup Strategy

### Step 1: Database Backup Script

Create a backup script:
```bash
nano /root/backup_database.sh
```

Add:
```bash
#!/bin/bash
BACKUP_DIR="/root/backups"
DATE=$(date +%Y%m%d_%H%M%S)
DB_NAME="capstone_db"
DB_USER="capstone_user"
DB_PASS="YOUR_DATABASE_PASSWORD"

mkdir -p $BACKUP_DIR
mysqldump -u $DB_USER -p$DB_PASS $DB_NAME > $BACKUP_DIR/db_backup_$DATE.sql

# Keep only last 7 days of backups
find $BACKUP_DIR -name "db_backup_*.sql" -mtime +7 -delete

echo "Backup completed: db_backup_$DATE.sql"
```

Make it executable:
```bash
chmod +x /root/backup_database.sh
```

### Step 2: Application Files Backup

```bash
nano /root/backup_files.sh
```

Add:
```bash
#!/bin/bash
BACKUP_DIR="/root/backups"
DATE=$(date +%Y%m%d_%H%M%S)
APP_DIR="/var/www/Capstone"

mkdir -p $BACKUP_DIR
tar -czf $BACKUP_DIR/files_backup_$DATE.tar.gz -C /var/www Capstone

# Keep only last 7 days of backups
find $BACKUP_DIR -name "files_backup_*.tar.gz" -mtime +7 -delete

echo "Files backup completed: files_backup_$DATE.tar.gz"
```

Make it executable:
```bash
chmod +x /root/backup_files.sh
```

### Step 3: Automated Daily Backups (Cron)

```bash
crontab -e
```

Add these lines:
```cron
# Daily database backup at 2 AM
0 2 * * * /root/backup_database.sh >> /var/log/backup.log 2>&1

# Daily files backup at 3 AM
0 3 * * * /root/backup_files.sh >> /var/log/backup.log 2>&1
```

### Step 4: Off-Server Backup (Recommended)

Consider using DigitalOcean Spaces or another cloud storage:
- Upload backups to DigitalOcean Spaces
- Or use rsync to another server
- Or use cloud storage services (AWS S3, Google Cloud Storage)

---

## Troubleshooting

### Issue: "500 Internal Server Error"

**Check Apache error logs:**
```bash
tail -f /var/log/apache2/error.log
```

**Common causes:**
- File permissions
- PHP syntax errors
- Missing PHP extensions

### Issue: "Database Connection Failed"

**Check:**
1. MySQL service is running: `systemctl status mysql`
2. Database credentials in `config.php`
3. Database user has proper permissions
4. Firewall allows MySQL connections (if remote)

### Issue: "File Upload Not Working"

**Check:**
1. Uploads directory permissions: `chmod -R 777 uploads`
2. PHP upload settings: `php.ini`
3. Apache error logs

### Issue: "Permission Denied"

**Fix:**
```bash
chown -R www-data:www-data /var/www/Capstone
chmod -R 755 /var/www/Capstone
chmod -R 777 /var/www/Capstone/uploads
```

### Issue: "SSL Certificate Not Working"

**Check:**
1. Domain DNS is pointing to correct IP
2. Port 443 is open: `ufw allow 443`
3. Apache SSL module is enabled: `a2enmod ssl`

### View Application Logs

```bash
# Apache access logs
tail -f /var/log/apache2/taekwondo_access.log

# Apache error logs
tail -f /var/log/apache2/taekwondo_error.log

# PHP error logs
tail -f /var/log/php_errors.log
```

---

## Quick Reference Commands

```bash
# Restart Apache
systemctl restart apache2

# Restart MySQL
systemctl restart mysql

# Check Apache status
systemctl status apache2

# Check MySQL status
systemctl status mysql

# View Apache error logs
tail -f /var/log/apache2/error.log

# Test Apache configuration
apache2ctl configtest

# Reload Apache (without downtime)
systemctl reload apache2
```

---

## Post-Deployment Checklist

- [ ] Application is accessible via domain/IP
- [ ] Database connection working
- [ ] Admin login functional
- [ ] File uploads working
- [ ] SSL certificate installed (if using domain)
- [ ] Firewall configured
- [ ] Backups scheduled
- [ ] Error logging enabled
- [ ] Development files removed
- [ ] Environment variables configured
- [ ] File permissions set correctly

---

## Support & Resources

- **DigitalOcean Documentation**: https://docs.digitalocean.com/
- **Apache Documentation**: https://httpd.apache.org/docs/
- **PHP Documentation**: https://www.php.net/docs.php
- **MySQL Documentation**: https://dev.mysql.com/doc/

---

## Security Best Practices

1. **Keep system updated:**
   ```bash
   apt update && apt upgrade -y
   ```

2. **Use strong passwords** for all accounts

3. **Disable root SSH login** (create a sudo user):
   ```bash
   adduser deploy
   usermod -aG sudo deploy
   # Then disable root login in /etc/ssh/sshd_config
   ```

4. **Regular backups** (automated)

5. **Monitor logs** regularly

6. **Keep PHP and extensions updated**

7. **Use HTTPS** in production

8. **Limit file upload sizes** in php.ini

---

**Congratulations!** Your application should now be deployed and running on DigitalOcean! 🎉





