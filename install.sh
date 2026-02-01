#!/bin/bash
#
# HyperMon Installer
#

set -e

echo "=============================="
echo "HyperMon Installer"
echo "=============================="
echo ""

# Detect web root
WEB_ROOT=""
if [ -d "/var/www/html" ]; then
    WEB_ROOT="/var/www/html"
elif [ -d "/srv/http" ]; then
    WEB_ROOT="/srv/http"
elif [ -d "/usr/share/nginx/html" ]; then
    WEB_ROOT="/usr/share/nginx/html"
else
    echo "Could not detect web root directory."
    read -p "Enter web root path (e.g., /var/www/html): " WEB_ROOT
fi

if [ ! -d "$WEB_ROOT" ]; then
    echo "Error: Directory $WEB_ROOT does not exist"
    exit 1
fi

echo "Web root: $WEB_ROOT"
echo ""

# Detect node number
NODE_NUMBER=""
if [ -f "/etc/asterisk/rpt.conf" ]; then
    NODE_NUMBER=$(grep -E '^\[[0-9]+\]' /etc/asterisk/rpt.conf | head -1 | tr -d '[]')
fi

if [ -n "$NODE_NUMBER" ]; then
    echo "Detected node number: $NODE_NUMBER"
    read -p "Use this node? (Y/n): " CONFIRM
    if [[ "$CONFIRM" =~ ^[Nn]$ ]]; then
        read -p "Enter your node number: " NODE_NUMBER
    fi
else
    read -p "Enter your node number: " NODE_NUMBER
fi

if [ -z "$NODE_NUMBER" ]; then
    echo "Error: Node number is required"
    exit 1
fi

echo ""
echo "Installing HyperMon to $WEB_ROOT/hypermon..."
echo ""

# Create hypermon directory
sudo mkdir -p "$WEB_ROOT/hypermon"

# Copy files
echo "Copying files..."
sudo cp index.php "$WEB_ROOT/hypermon/"
sudo cp api.php "$WEB_ROOT/hypermon/"
[ -f "test-api.php" ] && sudo cp test-api.php "$WEB_ROOT/hypermon/" || true

# Create config file
echo "Creating config..."
sudo sh -c "echo '<?php' > '$WEB_ROOT/hypermon/config.php'"
sudo sh -c "echo '// HyperMon Configuration' >> '$WEB_ROOT/hypermon/config.php'"
sudo sh -c "echo '\$HYPERMON_NODE = \"$NODE_NUMBER\";' >> '$WEB_ROOT/hypermon/config.php'"
sudo sh -c "echo '?>' >> '$WEB_ROOT/hypermon/config.php'"

# Set permissions
echo "Setting permissions..."
sudo chown -R www-data:www-data "$WEB_ROOT/hypermon" 2>/dev/null || \
sudo chown -R http:http "$WEB_ROOT/hypermon" 2>/dev/null || \
sudo chown -R apache:apache "$WEB_ROOT/hypermon" 2>/dev/null || \
sudo chmod -R 755 "$WEB_ROOT/hypermon"

# Check if php-curl is installed
echo ""
echo "Checking dependencies..."
if php -m 2>/dev/null | grep -q curl; then
    echo "PHP CURL: Installed"
else
    echo ""
    echo "WARNING: PHP CURL is not installed!"
    echo "HyperMon requires php-curl to fetch data from AllStarLink."
    echo ""
    echo "Install it with:"
    echo "  sudo apt update"
    echo "  sudo apt install php-curl"
    echo "  sudo systemctl restart apache2"
    echo ""
    read -p "Would you like to install php-curl now? (Y/n): " INSTALL_CURL
    if [[ ! "$INSTALL_CURL" =~ ^[Nn]$ ]]; then
        sudo apt update
        sudo apt install -y php-curl
        sudo systemctl restart apache2 2>/dev/null || sudo systemctl restart nginx 2>/dev/null || true
        echo "PHP CURL installed!"
    fi
fi

echo ""
echo "=============================="
echo "Installation Complete!"
echo "=============================="
echo ""
echo "HyperMon is now installed!"
echo ""
echo "Access it at:"
echo "  http://your-server/hypermon"
echo "  http://$(hostname -I | awk '{print $1}')/hypermon"
echo ""
echo "Configuration:"
echo "  Node Number: $NODE_NUMBER"
echo "  AllMon3 URL: /allmon3"
echo ""
echo "Test the API at:"
echo "  http://your-server/hypermon/test-api.php"
echo ""
echo "73!"
echo ""
