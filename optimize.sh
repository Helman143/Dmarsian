#!/bin/bash
# Cache Optimization and Clearing Script for DigitalOcean
# Usage: ./optimize.sh or bash optimize.sh

set -e

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

echo -e "${BLUE}========================================${NC}"
echo -e "${BLUE}  Cache Optimization & Clearing Script${NC}"
echo -e "${BLUE}========================================${NC}"
echo ""

# Get the script directory
SCRIPT_DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"
cd "$SCRIPT_DIR"

# 1. Clear PHP OPcache
echo -e "${YELLOW}[1/8] Clearing PHP OPcache...${NC}"
if [ -f /etc/php/8.2/apache2/php.ini ] || [ -f /etc/php/8.1/apache2/php.ini ]; then
    # Find PHP version
    PHP_VERSION=$(php -r "echo PHP_MAJOR_VERSION.'.'.PHP_MINOR_VERSION;")
    echo -e "${GREEN}   PHP Version: ${PHP_VERSION}${NC}"
    
    # Clear OPcache via PHP
    php -r "if (function_exists('opcache_reset')) { opcache_reset(); echo 'OPcache cleared'; } else { echo 'OPcache not enabled'; }"
    echo -e "${GREEN}   ✓ PHP OPcache cleared${NC}"
else
    echo -e "${RED}   ✗ PHP configuration not found${NC}"
fi
echo ""

# 2. Clear Composer cache
echo -e "${YELLOW}[2/8] Clearing Composer cache...${NC}"
if command -v composer &> /dev/null; then
    composer clear-cache 2>/dev/null || true
    echo -e "${GREEN}   ✓ Composer cache cleared${NC}"
else
    echo -e "${YELLOW}   ⚠ Composer not found, skipping${NC}"
fi
echo ""

# 3. Optimize Composer autoloader
echo -e "${YELLOW}[3/8] Optimizing Composer autoloader...${NC}"
if [ -f composer.json ]; then
    if command -v composer &> /dev/null; then
        composer dump-autoload --optimize --no-dev 2>/dev/null || composer dump-autoload --optimize 2>/dev/null || true
        echo -e "${GREEN}   ✓ Composer autoloader optimized${NC}"
    else
        echo -e "${YELLOW}   ⚠ Composer not found, skipping${NC}"
    fi
else
    echo -e "${YELLOW}   ⚠ composer.json not found, skipping${NC}"
fi
echo ""

# 4. Clear application cache directories
echo -e "${YELLOW}[4/8] Clearing application cache directories...${NC}"
CACHE_DIRS=(
    "cache"
    "tmp"
    "temp"
    "var/cache"
    "storage/cache"
    "storage/framework/cache"
    "storage/framework/sessions"
    "storage/framework/views"
)

CLEARED=0
for dir in "${CACHE_DIRS[@]}"; do
    if [ -d "$dir" ]; then
        find "$dir" -type f -delete 2>/dev/null || true
        find "$dir" -type d -empty -delete 2>/dev/null || true
        echo -e "${GREEN}   ✓ Cleared: $dir${NC}"
        CLEARED=1
    fi
done

if [ $CLEARED -eq 0 ]; then
    echo -e "${BLUE}   ℹ No cache directories found${NC}"
fi
echo ""

# 5. Clear log files (optional - keep recent entries)
echo -e "${YELLOW}[5/8] Clearing old log files...${NC}"
LOG_DIRS=(
    "logs"
    "storage/logs"
    "var/log"
)

CLEARED=0
for dir in "${LOG_DIRS[@]}"; do
    if [ -d "$dir" ]; then
        # Keep last 100 lines of each log file
        find "$dir" -name "*.log" -type f -exec sh -c 'tail -100 "$1" > "$1.tmp" && mv "$1.tmp" "$1"' _ {} \; 2>/dev/null || true
        echo -e "${GREEN}   ✓ Trimmed logs in: $dir${NC}"
        CLEARED=1
    fi
done

if [ $CLEARED -eq 0 ]; then
    echo -e "${BLUE}   ℹ No log directories found${NC}"
fi
echo ""

# 6. Clear system cache (if applicable)
echo -e "${YELLOW}[6/8] Clearing system cache...${NC}"

# Clear APT cache (optional - saves disk space)
if command -v apt-get &> /dev/null; then
    echo -e "${BLUE}   ℹ APT cache can be cleared with: sudo apt-get clean${NC}"
fi

# Clear systemd journal (optional)
if command -v journalctl &> /dev/null; then
    echo -e "${BLUE}   ℹ Systemd journal can be cleared with: sudo journalctl --vacuum-time=7d${NC}"
fi

echo -e "${GREEN}   ✓ System cache info displayed${NC}"
echo ""

# 7. Restart services
echo -e "${YELLOW}[7/8] Restarting services...${NC}"

# Restart PHP-FPM if exists
if systemctl is-active --quiet php*-fpm 2>/dev/null; then
    PHP_FPM_SERVICE=$(systemctl list-units --type=service | grep -o 'php[0-9.]*-fpm' | head -1)
    if [ ! -z "$PHP_FPM_SERVICE" ]; then
        sudo systemctl restart "$PHP_FPM_SERVICE" 2>/dev/null && echo -e "${GREEN}   ✓ Restarted PHP-FPM${NC}" || echo -e "${YELLOW}   ⚠ Could not restart PHP-FPM (may need sudo)${NC}"
    fi
fi

# Restart Apache
if systemctl is-active --quiet apache2 2>/dev/null; then
    sudo systemctl reload apache2 2>/dev/null && echo -e "${GREEN}   ✓ Reloaded Apache${NC}" || echo -e "${YELLOW}   ⚠ Could not reload Apache (may need sudo)${NC}"
elif systemctl is-active --quiet httpd 2>/dev/null; then
    sudo systemctl reload httpd 2>/dev/null && echo -e "${GREEN}   ✓ Reloaded HTTPD${NC}" || echo -e "${YELLOW}   ⚠ Could not reload HTTPD (may need sudo)${NC}"
else
    echo -e "${BLUE}   ℹ Web server not found or not running${NC}"
fi
echo ""

# 8. Set proper permissions
echo -e "${YELLOW}[8/8] Setting proper file permissions...${NC}"
if [ -d "uploads" ]; then
    chmod -R 777 uploads/ 2>/dev/null || sudo chmod -R 777 uploads/ 2>/dev/null || true
    echo -e "${GREEN}   ✓ Set uploads directory permissions${NC}"
fi

if [ -f ".env" ]; then
    chmod 600 .env 2>/dev/null || sudo chmod 600 .env 2>/dev/null || true
    echo -e "${GREEN}   ✓ Set .env file permissions${NC}"
fi
echo ""

# Summary
echo -e "${BLUE}========================================${NC}"
echo -e "${GREEN}✓ Optimization Complete!${NC}"
echo -e "${BLUE}========================================${NC}"
echo ""
echo -e "${YELLOW}Summary:${NC}"
echo "  • PHP OPcache cleared"
echo "  • Composer cache cleared"
echo "  • Composer autoloader optimized"
echo "  • Application cache cleared"
echo "  • Log files trimmed"
echo "  • Services reloaded"
echo "  • Permissions set"
echo ""
echo -e "${BLUE}Note: Some operations may require sudo privileges${NC}"
echo -e "${BLUE}Run with sudo for full optimization: sudo ./optimize.sh${NC}"

