#!/bin/bash

echo "================================"
echo "HyperMon Installation Script"
echo "================================"
echo ""

# Detect web root
if [ -d "/var/www/html" ]; then
    WEB_ROOT="/var/www/html"
elif [ -d "/srv/http" ]; then
    WEB_ROOT="/srv/http"
else
    echo "Error: Could not detect web root directory"
    echo "Please install manually to your web server directory"
    exit 1
fi

INSTALL_DIR="$WEB_ROOT/hypermon"

echo "Web root detected: $WEB_ROOT"
echo "Installation directory: $INSTALL_DIR"
echo ""

# Check if already installed
if [ -d "$INSTALL_DIR" ]; then
    echo "HyperMon is already installed at $INSTALL_DIR"
    read -p "Do you want to overwrite it? (y/N) " -n 1 -r
    echo
    if [[ ! $REPLY =~ ^[Yy]$ ]]; then
        echo "Installation cancelled"
        exit 1
    fi
    echo "Backing up existing installation..."
    mv "$INSTALL_DIR" "$INSTALL_DIR.backup.$(date +%Y%m%d-%H%M%S)"
fi

# Check for PHP
if ! command -v php &> /dev/null; then
    echo "Error: PHP is not installed"
    echo "Please install PHP: sudo apt install php"
    exit 1
fi

# Check for curl extension
if ! php -m | grep -q curl; then
    echo "Warning: PHP curl extension not found"
    echo "Installing php-curl..."
    if command -v apt &> /dev/null; then
        sudo apt install -y php-curl
    else
        echo "Please install php-curl manually"
        exit 1
    fi
fi

# Create installation directory
echo "Creating installation directory..."
sudo mkdir -p "$INSTALL_DIR"

# Copy files
echo "Copying files..."
sudo cp index.html "$INSTALL_DIR/"
sudo cp api.php "$INSTALL_DIR/"
sudo cp README.md "$INSTALL_DIR/"

# Set permissions
echo "Setting permissions..."
sudo chown -R www-data:www-data "$INSTALL_DIR" 2>/dev/null || sudo chown -R http:http "$INSTALL_DIR" 2>/dev/null
sudo chmod -R 755 "$INSTALL_DIR"

# Create cache directory
sudo mkdir -p /tmp
sudo chmod 1777 /tmp

echo ""
echo "================================"
echo "Installation Complete!"
echo "================================"
echo ""
echo "Access HyperMon at:"
echo "  http://$(hostname -I | awk '{print $1}')/hypermon"
echo ""
echo "First-time setup:"
echo "  1. Click 'Config' button"
echo "  2. Enter your AllMon3 URL"
echo "  3. Enter your node number"
echo "  4. Click 'Save Configuration'"
echo ""
echo "Enjoy HyperMon!"
