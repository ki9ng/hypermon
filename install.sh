#!/bin/bash
#
# HyperMon - Installation Script
# This script installs dependencies and starts the proxy server
#

set -e  # Exit on error

echo "======================"
echo "HyperMon Setup"
echo "======================"
echo ""

# Check if Python 3 is installed
if ! command -v python3 &> /dev/null; then
    echo "Error: Python 3 is not installed"
    echo "Please install Python 3.7 or higher and try again"
    exit 1
fi

echo "Python version: $(python3 --version)"
echo ""

# Check if pip is installed
if ! command -v pip3 &> /dev/null && ! command -v pip &> /dev/null; then
    echo "Error: pip is not installed"
    echo "Please install pip and try again"
    exit 1
fi

# Determine pip command
PIP_CMD="pip3"
if ! command -v pip3 &> /dev/null; then
    PIP_CMD="pip"
fi

echo "Installing Python dependencies..."
$PIP_CMD install -r requirements.txt --break-system-packages 2>/dev/null || \
$PIP_CMD install -r requirements.txt

echo ""
echo "Installation complete!"
echo ""
echo "To start the proxy server, run:"
echo "  python3 hypermon-proxy.py"
echo ""
echo "Then open hypermon.html in your web browser"
echo ""
