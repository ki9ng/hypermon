#!/bin/bash
#
# HyperMon PHP Installer
# Installs HyperMon as a web application alongside AllMon3
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
sudo cp index.html "$WEB_ROOT/hypermon/"
sudo cp api.php "$WEB_ROOT/hypermon/"

# Create config file with node number
sudo tee "$WEB_ROOT/hypermon/config.js" > /dev/null <<EOF
// HyperMon auto-generated configuration
window.HYPERMON_CONFIG = {
    yourNode: '$NODE_NUMBER',
    allmonUrl: '/allmon3',
    apiUrl: './api.php'
};
EOF

# Set permissions
sudo chown -R www-data:www-data "$WEB_ROOT/hypermon" 2>/dev/null || \
sudo chown -R http:http "$WEB_ROOT/hypermon" 2>/dev/null || \
sudo chown -R apache:apache "$WEB_ROOT/hypermon" 2>/dev/null || \
sudo chmod -R 755 "$WEB_ROOT/hypermon"

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
echo "73!"
echo ""
