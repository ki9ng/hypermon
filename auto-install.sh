#!/bin/bash
#
# HyperMon v3 - Smart Auto-Install Script
# Auto-detects AllMon3 and node configuration
#

set -e

echo "=============================="
echo "HyperMon v3 Installer"
echo "=============================="
echo ""

# Check for Python/pip and install if needed
if ! command -v python3 &> /dev/null; then
    echo "Installing Python 3..."
    sudo apt update && sudo apt install -y python3
fi

if ! command -v pip3 &> /dev/null && ! command -v pip &> /dev/null; then
    echo "Installing pip..."
    sudo apt update && sudo apt install -y python3-pip
fi

PIP_CMD=$(command -v pip3 || command -v pip)
echo "Installing dependencies..."
$PIP_CMD install -r requirements.txt --break-system-packages 2>/dev/null || \
$PIP_CMD install -r requirements.txt --user 2>/dev/null || \
$PIP_CMD install -r requirements.txt

echo ""
echo "=============================="
echo "Auto-Detection"
echo "=============================="
echo ""

# Detect AllMon3 installation
ALLMON_URL=""
if [ -d "/var/www/html/allmon3" ]; then
    ALLMON_URL="http://localhost/allmon3"
    echo "✓ Found AllMon3: $ALLMON_URL"
elif [ -d "/usr/local/share/allmon3" ]; then
    ALLMON_URL="http://localhost/allmon3"
    echo "✓ Found AllMon3: $ALLMON_URL"
else
    echo "✗ AllMon3 not detected on this system"
fi

# Detect node number from rpt.conf or asl.conf
NODE_NUMBER=""
if [ -f "/etc/asterisk/rpt.conf" ]; then
    NODE_NUMBER=$(grep -E '^\[' /etc/asterisk/rpt.conf | grep -v general | head -1 | tr -d '[]')
    if [ -n "$NODE_NUMBER" ]; then
        echo "✓ Found node number: $NODE_NUMBER (from rpt.conf)"
    fi
fi

if [ -z "$NODE_NUMBER" ] && [ -f "/etc/asterisk/asl.conf" ]; then
    NODE_NUMBER=$(grep -E '^\[' /etc/asterisk/asl.conf | grep -v general | head -1 | tr -d '[]')
    if [ -n "$NODE_NUMBER" ]; then
        echo "✓ Found node number: $NODE_NUMBER (from asl.conf)"
    fi
fi

echo ""
echo "=============================="
echo "Configuration"
echo "=============================="
echo ""

# Ask for AllMon3 URL if not detected
if [ -z "$ALLMON_URL" ]; then
    read -p "AllMon3 not detected. Enter AllMon3 URL (or leave blank to skip): " ALLMON_URL
fi

# Ask for node number if not detected
if [ -z "$NODE_NUMBER" ]; then
    read -p "Node number not detected. Enter your node number: " NODE_NUMBER
else
    read -p "Use node $NODE_NUMBER? (Y/n): " CONFIRM
    if [[ "$CONFIRM" =~ ^[Nn]$ ]]; then
        read -p "Enter your node number: " NODE_NUMBER
    fi
fi

# Validate node number is provided
if [ -z "$NODE_NUMBER" ]; then
    echo "Error: Node number is required"
    exit 1
fi

echo ""
echo "Configuration:"
echo "  Node Number: $NODE_NUMBER"
if [ -n "$ALLMON_URL" ]; then
    echo "  AllMon3 URL: $ALLMON_URL"
else
    echo "  AllMon3: Not configured (proxy-only mode)"
fi
echo ""

INSTALL_DIR=$(pwd)
CURRENT_USER=$(whoami)
SERVER_IP=$(hostname -I | awk '{print $1}')

# Install as service
echo "=============================="
echo "Service Installation"
echo "=============================="
echo ""

sudo tee /etc/systemd/system/hypermon.service > /dev/null <<EOF
[Unit]
Description=HyperMon Proxy Server
After=network.target

[Service]
Type=simple
User=$CURRENT_USER
WorkingDirectory=$INSTALL_DIR
ExecStart=/usr/bin/python3 $INSTALL_DIR/hypermon-proxy.py
Restart=always
RestartSec=10

[Install]
WantedBy=multi-user.target
EOF

sudo systemctl daemon-reload
sudo systemctl enable hypermon
sudo systemctl start hypermon

echo "✓ Service installed and started"
echo ""

# Wait for service to start
sleep 2

if systemctl is-active --quiet hypermon; then
    echo "✓ HyperMon is running on http://$SERVER_IP:5000"
else
    echo "✗ Service failed to start. Check: sudo journalctl -u hypermon -xe"
    exit 1
fi

echo ""
echo "=============================="
echo "Installation Complete!"
echo "=============================="
echo ""
echo "HyperMon Proxy: http://$SERVER_IP:5000"
echo ""
echo "Service commands:"
echo "  Status: sudo systemctl status hypermon"
echo "  Logs:   sudo journalctl -u hypermon -f"
echo ""
echo "Access web interface:"
echo "  1. Download hypermon.html from this directory"
echo "  2. Open in browser and configure:"
if [ -n "$ALLMON_URL" ]; then
    echo "     - AllMon3 URL: $ALLMON_URL"
fi
echo "     - Node Number: $NODE_NUMBER"
echo "     - Proxy URL: http://$SERVER_IP:5000"
echo ""
echo "73!"
