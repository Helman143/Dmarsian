#!/bin/bash
# Quick Error Check Script for DigitalOcean
# This script checks Apache error logs and PHP configuration

echo "=========================================="
echo "  Error Check Script"
echo "=========================================="
echo ""

# Check Apache error log
echo "1. Recent Apache Errors:"
echo "----------------------------------------"
if [ -f /var/log/apache2/error.log ]; then
    echo "Last 20 lines of Apache error log:"
    sudo tail -20 /var/log/apache2/error.log
elif [ -f /var/log/httpd/error_log ]; then
    echo "Last 20 lines of HTTPD error log:"
    sudo tail -20 /var/log/httpd/error_log
else
    echo "Apache error log not found in standard locations"
fi
echo ""

# Check PHP error log
echo "2. PHP Error Log:"
echo "----------------------------------------"
PHP_ERROR_LOG=$(php -i 2>/dev/null | grep "error_log" | head -1 | awk '{print $3}')
if [ ! -z "$PHP_ERROR_LOG" ] && [ -f "$PHP_ERROR_LOG" ]; then
    echo "PHP error log location: $PHP_ERROR_LOG"
    echo "Last 20 lines:"
    sudo tail -20 "$PHP_ERROR_LOG"
else
    echo "PHP error log not configured or not found"
fi
echo ""

# Check PHP syntax
echo "3. PHP Syntax Check:"
echo "----------------------------------------"
if [ -f config.php ]; then
    echo "Checking config.php..."
    php -l config.php
else
    echo "config.php not found!"
fi

if [ -f env-loader.php ]; then
    echo "Checking env-loader.php..."
    php -l env-loader.php
else
    echo "env-loader.php not found!"
fi

if [ -f index.php ]; then
    echo "Checking index.php..."
    php -l index.php
else
    echo "index.php not found!"
fi
echo ""

# Check file permissions
echo "4. File Permissions:"
echo "----------------------------------------"
ls -la config.php env-loader.php index.php 2>/dev/null | head -5
echo ""

# Check if .env exists
echo "5. Environment File:"
echo "----------------------------------------"
if [ -f .env ]; then
    echo "✓ .env file exists"
    echo "File permissions:"
    ls -la .env
else
    echo "✗ .env file NOT FOUND"
    echo "Create it with: cp env.example .env"
fi
echo ""

# Check Apache status
echo "6. Apache Status:"
echo "----------------------------------------"
sudo systemctl status apache2 --no-pager -l | head -10
echo ""

# Check PHP version
echo "7. PHP Version:"
echo "----------------------------------------"
php -v
echo ""

# Check if mod_rewrite is enabled
echo "8. Apache Modules:"
echo "----------------------------------------"
if command -v apache2ctl &> /dev/null; then
    apache2ctl -M 2>/dev/null | grep rewrite || echo "mod_rewrite not enabled"
elif command -v httpd &> /dev/null; then
    httpd -M 2>/dev/null | grep rewrite || echo "mod_rewrite not enabled"
fi
echo ""

echo "=========================================="
echo "  Check Complete"
echo "=========================================="
echo ""
echo "Next steps:"
echo "1. Review the errors above"
echo "2. Access test.php in browser: https://yourdomain.com/test.php"
echo "3. Fix any syntax errors found"
echo "4. Ensure .env file is configured"
echo "5. Run: sudo systemctl restart apache2"







