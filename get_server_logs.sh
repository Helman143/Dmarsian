#!/bin/bash
# Script to get error logs from DigitalOcean server
# Run this ON YOUR DIGITALOCEAN SERVER via SSH

echo "=========================================="
echo "  DigitalOcean Server Error Logs"
echo "=========================================="
echo ""

# Check Apache error log
echo "1. Recent Apache Errors (Last 50 lines):"
echo "----------------------------------------"
if [ -f /var/log/apache2/error.log ]; then
    sudo tail -50 /var/log/apache2/error.log
elif [ -f /var/log/httpd/error_log ]; then
    sudo tail -50 /var/log/httpd/error_log
else
    echo "Apache error log not found in standard locations"
    echo "Searching for error logs..."
    sudo find /var/log -name "*error*" -type f 2>/dev/null | head -5
fi
echo ""

# Check PHP error log
echo "2. PHP Error Log:"
echo "----------------------------------------"
PHP_ERROR_LOG=$(php -i 2>/dev/null | grep "error_log" | head -1 | awk '{print $3}')
if [ ! -z "$PHP_ERROR_LOG" ] && [ "$PHP_ERROR_LOG" != "no value" ] && [ -f "$PHP_ERROR_LOG" ]; then
    echo "PHP error log location: $PHP_ERROR_LOG"
    echo "Last 30 lines:"
    sudo tail -30 "$PHP_ERROR_LOG"
else
    echo "PHP error log not configured or not found"
    echo "Checking common locations..."
    if [ -f /var/log/php_errors.log ]; then
        sudo tail -30 /var/log/php_errors.log
    elif [ -f /var/log/php8.2-fpm.log ]; then
        sudo tail -30 /var/log/php8.2-fpm.log
    else
        echo "No PHP error log found"
    fi
fi
echo ""

# Check for PHP fatal errors in Apache log
echo "3. PHP Fatal Errors in Apache Log:"
echo "----------------------------------------"
if [ -f /var/log/apache2/error.log ]; then
    sudo grep -i "fatal\|parse error\|syntax error" /var/log/apache2/error.log | tail -20
elif [ -f /var/log/httpd/error_log ]; then
    sudo grep -i "fatal\|parse error\|syntax error" /var/log/httpd/error_log | tail -20
fi
echo ""

# Check recent access log for 500 errors
echo "4. Recent 500 Errors from Access Log:"
echo "----------------------------------------"
if [ -f /var/log/apache2/access.log ]; then
    sudo grep " 500 " /var/log/apache2/access.log | tail -10
elif [ -f /var/log/httpd/access_log ]; then
    sudo grep " 500 " /var/log/httpd/access_log | tail -10
fi
echo ""

echo "=========================================="
echo "  Log Check Complete"
echo "=========================================="
echo ""
echo "To monitor errors in real-time, run:"
echo "  sudo tail -f /var/log/apache2/error.log"





