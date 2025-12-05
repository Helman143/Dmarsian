# DigitalOcean Deployment Guide

This guide will help you deploy the Martial Arts School Management System to DigitalOcean.

## Prerequisites

- DigitalOcean Droplet (Ubuntu 20.04 or later recommended)
- Domain name (optional but recommended)
- SSH access to your droplet
- Basic knowledge of Linux commands

## Quick Start

1. **Clone the repository:**
   ```bash
   cd /var/www
   git clone https://github.com/Dashersd/Dmarsian.git
   cd Dmarsian
   ```

2. **Install Composer dependencies:**
   ```bash
   composer install --no-dev --optimize-autoloader
   ```

3. **Set up environment variables:**
   ```bash
   cp env.example .env
   nano .env  # Edit with your actual values
   ```

4. **Set proper permissions:**
   ```bash
   chown -R www-data:www-data .
   chmod -R 755 .
   chmod -R 777 uploads/
   chmod 600 .env
   ```

5. **Import database:**
   ```bash
   mysql -u your_user -p your_database < Database/db.sql
   ```

6. **Configure Apache virtual host** (see DEPLOYMENT_GUIDE.md for details)

## Files Overview

### composer.json
- Defines PHP dependencies
- Includes PHPMailer, PHPUnit, and required PHP extensions
- Run `composer install` to install dependencies

### .htaccess
- Apache configuration for security, performance, and URL rewriting
- Includes security headers, compression, and caching rules
- Blocks access to sensitive files

### env.example
- Template for environment variables
- Copy to `.env` and fill in your actual values
- Never commit `.env` to version control

### robots.txt
- Search engine crawler instructions
- Blocks access to admin areas and sensitive directories

### deploy.sh
- Automated deployment script
- Installs dependencies, sets permissions, creates directories
- Run with: `bash deploy.sh`

## Environment Variables

Required environment variables (set in `.env` file):

```env
DB_HOST=localhost
DB_USER=your_database_user
DB_PASS=your_database_password
DB_NAME=capstone_db
DB_PORT=3306

SMTP2GO_API_KEY=your_api_key
SMTP2GO_SENDER_EMAIL=your_email@example.com
SMTP2GO_SENDER_NAME=D'Marsians Taekwondo Gym
ADMIN_BCC_EMAIL=admin@example.com
```

## Security Checklist

- [ ] Change default database passwords
- [ ] Set strong passwords for admin accounts
- [ ] Enable HTTPS/SSL certificate
- [ ] Update `.env` file with production values
- [ ] Set proper file permissions (755 for directories, 644 for files)
- [ ] Restrict `.env` file access (chmod 600)
- [ ] Enable firewall (UFW)
- [ ] Keep system packages updated
- [ ] Regular database backups
- [ ] Monitor error logs

## Troubleshooting

### Composer Issues
```bash
# If composer command not found
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer
```

### Permission Issues
```bash
sudo chown -R www-data:www-data /var/www/Dmarsian
sudo chmod -R 755 /var/www/Dmarsian
sudo chmod -R 777 /var/www/Dmarsian/uploads
```

### Database Connection Issues
- Verify database credentials in `.env`
- Check MySQL service is running: `systemctl status mysql`
- Verify database exists: `mysql -u user -p -e "SHOW DATABASES;"`

### Apache Issues
```bash
# Check Apache error logs
sudo tail -f /var/log/apache2/error.log

# Restart Apache
sudo systemctl restart apache2

# Check Apache status
sudo systemctl status apache2
```

## Maintenance

### Regular Updates
```bash
# Update system packages
sudo apt update && sudo apt upgrade -y

# Update Composer dependencies
composer update --no-dev

# Backup database
mysqldump -u user -p database > backup_$(date +%Y%m%d).sql
```

### Monitoring
- Check Apache logs: `/var/log/apache2/`
- Check PHP error logs: `/var/log/php_errors.log`
- Monitor disk space: `df -h`
- Monitor memory: `free -h`

## Support

For detailed deployment instructions, see `DEPLOYMENT_GUIDE.md`.

For issues or questions, please open an issue on GitHub.







