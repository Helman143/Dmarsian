#!/bin/bash
# Deployment script for DigitalOcean
# Usage: ./deploy.sh

set -e

echo "Starting deployment..."

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Check if composer is installed
if ! command -v composer &> /dev/null; then
    echo -e "${RED}Composer is not installed. Please install Composer first.${NC}"
    exit 1
fi

# Install/Update Composer dependencies
echo -e "${YELLOW}Installing Composer dependencies...${NC}"
composer install --no-dev --optimize-autoloader

# Set proper permissions
echo -e "${YELLOW}Setting file permissions...${NC}"
find . -type d -exec chmod 755 {} \;
find . -type f -exec chmod 644 {} \;
chmod 755 deploy.sh
chmod -R 777 uploads/

# Check if .env file exists
if [ ! -f .env ]; then
    echo -e "${YELLOW}Creating .env file from template...${NC}"
    if [ -f env.example ]; then
        cp env.example .env
        echo -e "${RED}Please update .env file with your actual configuration values!${NC}"
    else
        echo -e "${RED}env.example file not found. Please create .env file manually.${NC}"
    fi
fi

# Create uploads directory if it doesn't exist
if [ ! -d "uploads" ]; then
    echo -e "${YELLOW}Creating uploads directory...${NC}"
    mkdir -p uploads/posts
    chmod -R 777 uploads/
fi

# Clear any caches (if applicable)
echo -e "${YELLOW}Clearing caches...${NC}"
# Add cache clearing commands here if needed

echo -e "${GREEN}Deployment completed successfully!${NC}"
echo -e "${YELLOW}Don't forget to:${NC}"
echo "  1. Update .env file with your actual configuration"
echo "  2. Import database schema: mysql -u user -p database < Database/db.sql"
echo "  3. Set proper ownership: chown -R www-data:www-data ."
echo "  4. Restart Apache: systemctl restart apache2"







