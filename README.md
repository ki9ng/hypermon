# HyperMon

A streamlined web interface for connecting to AllStarLink nodes via AllMon3. This tool eliminates the tedious workflow of checking multiple websites and manually typing node numbers.

## Overview

Traditional workflow:
1. Visit stats.allstarlink.org to check keyed nodes
2. Search www.allstarlink.org/nodelist for callsigns
3. Manually type node numbers into AllMon3

With HyperMon:
1. Click a node to connect instantly

## Features

- **Currently Keyed Nodes**: View and connect to active nodes in real-time
- **Callsign Search**: Search for nodes by callsign or node number
- **Manual Entry**: Quick manual node connection
- **Auto-Refresh**: Optional automatic refresh of keyed nodes every 30 seconds
- **Settings Persistence**: Configuration saved in browser localStorage
- **Keyboard Shortcuts**: Ctrl/Cmd+K for quick search access

## Prerequisites

- Python 3.7 or higher
- Modern web browser (Chrome, Firefox, Edge, Safari)
- AllMon3 installation with network access
- Your AllStarLink node number

## Quick Install

Smart one-line installation with auto-detection:

```bash
curl -sL https://github.com/ki9ng/hypermon/archive/main.tar.gz | tar xz && cd hypermon-main && chmod +x auto-install.sh && ./auto-install.sh
```

**What it auto-detects:**
- ✓ AllMon3 installation location
- ✓ Your node number from Asterisk config
- ✓ Python and pip (installs if missing)

**What it does:**
1. Detects and confirms your configuration
2. Installs dependencies
3. Sets up as a systemd service
4. Starts HyperMon automatically

You'll only be asked to confirm or provide info if auto-detection fails.

## Manual Installation

### Step 1: Install Dependencies

```bash
pip install -r requirements.txt --break-system-packages
```

Or install packages individually:

```bash
pip install Flask==3.0.0 flask-cors==4.0.0 requests==2.31.0 beautifulsoup4==4.12.2 lxml==5.1.0 --break-system-packages
```

### Step 2: Start the Proxy Server

The proxy server fetches data from AllStarLink and bypasses CORS restrictions:

```bash
python3 hypermon-proxy.py
```

Expected output:
```
AllStarLink Proxy Server Starting...
Listening on http://localhost:5000
```

### Step 3: Open the Web Interface

Open `hypermon.html` in your web browser:

```bash
# Linux
xdg-open hypermon.html

# macOS
open hypermon.html

# Windows
start hypermon.html
```

Or simply double-click the HTML file.

## Configuration

### After Installation

If you installed as a service, HyperMon is already running! Check with:

```bash
sudo systemctl status hypermon
```

### Managing the Service

```bash
# Check status
sudo systemctl status hypermon

# Stop service
sudo systemctl stop hypermon

# Start service
sudo systemctl start hypermon

# Restart service
sudo systemctl restart hypermon

# View logs
sudo journalctl -u hypermon -f
```

### Web Interface Setup

The installer creates `hypermon.html` which you can access by:

1. **Download to your computer:**
   ```bash
   scp username@your-node-ip:/path/to/hypermon-main/hypermon.html .
   ```

2. **Open in your browser:**
   ```bash
   xdg-open hypermon.html
   ```

3. **Configuration is already set!**
   - The installer pre-configured your AllMon3 URL, node number, and proxy server
   - Settings are stored in your browser
   - You can change them anytime in the Configuration section

### Manual Configuration (if needed)

1. Open the web interface in your browser
2. Navigate to the Configuration section
3. Enter the following information:
   - **AllMon3 URL**: Your AllMon3 server URL (e.g., `http://192.168.1.100/allmon3`)
   - **Your Node Number**: Your AllStarLink node number (e.g., `12345`)
   - **Proxy Server URL**: Your node's IP with port 5000 (e.g., `http://192.168.1.188:5000`)
4. Click "Save Settings"

Settings are stored in browser localStorage and persist between sessions.

## Usage

### Currently Keyed Nodes Tab

1. Click "Refresh Keyed Nodes" to load currently active nodes
2. Click any node card to instantly connect via AllMon3
3. Optional: Click "Enable Auto-Refresh" to refresh every 30 seconds

### Search by Callsign Tab

1. Enter a callsign or node number in the search box
2. Press Enter or click "Search"
3. Click "Connect" next to any search result

### Manual Entry Tab

1. Enter a node number directly
2. Click "Connect to Node"

### Keyboard Shortcuts

- `Ctrl/Cmd + K`: Jump to search tab and focus search box
- `Enter`: Submit search or manual entry

## Architecture

```
┌─────────────────┐      ┌──────────────────┐      ┌─────────────────────┐
│   Web Browser   │─────▶│  Python Proxy    │─────▶│  AllStarLink.org    │
│  (HTML/JS UI)   │◀─────│   (Flask API)    │◀─────│  (Stats & Nodelist) │
└─────────────────┘      └──────────────────┘      └─────────────────────┘
         │
         │ Direct connection
         ▼
┌─────────────────┐
│    AllMon3      │
│  (Node Control) │
└─────────────────┘
```

### Components

1. **hypermon.html**: Frontend web interface
   - Responsive UI with tab-based navigation
   - Manages settings in browser localStorage
   - Makes AJAX calls to proxy server
   - Opens AllMon3 connection URLs in new tabs

2. **hypermon-proxy.py**: Backend proxy server
   - Fetches data from AllStarLink websites
   - Parses HTML tables with BeautifulSoup
   - Provides RESTful JSON API endpoints
   - Bypasses CORS restrictions

### API Endpoints

The proxy server provides the following endpoints:

- `GET /api/keyed-nodes` - Fetch currently keyed nodes
- `GET /api/search-nodes?q=CALLSIGN` - Search for nodes by callsign or number
- `GET /api/node-info/<node_number>` - Get specific node details
- `GET /health` - Health check endpoint

## Troubleshooting

### Proxy Server Not Running

**Symptom**: "Error: Make sure the proxy server is running"

**Solution**:
- Verify the proxy is running: `python3 hypermon-proxy.py`
- Check that it's listening on port 5000
- Ensure no firewall is blocking localhost:5000

### Configuration Missing

**Symptom**: "Please configure your AllMon3 URL"

**Solution**:
- Go to Configuration section
- Enter your AllMon3 URL and node number
- Click "Save Settings"

### Connection Issues

**Symptom**: Clicking a node doesn't connect

**Solution**:
- Verify your AllMon3 URL is correct and accessible
- Check that your node number is correct
- Ensure AllMon3 is running and accessible from your browser

### No Data Showing

**Symptom**: No keyed nodes or search results appear

**Solution**:
- Check browser console (F12) for JavaScript errors
- Verify proxy server is running and accessible
- Check proxy server terminal for error messages
- Ensure AllStarLink websites are accessible

## Advanced Configuration

### Custom Proxy URL

If running the proxy on a different machine:

1. Edit the proxy server to listen on all interfaces:
   ```python
   app.run(host='0.0.0.0', port=5000)
   ```

2. In the web interface, update "Proxy Server URL" to:
   ```
   http://your-server-ip:5000
   ```

### Running as a Systemd Service (Linux)

Create a systemd service file `/etc/systemd/system/hypermon-proxy.service`:

```ini
[Unit]
Description=HyperMon Proxy Server
After=network.target

[Service]
Type=simple
User=youruser
WorkingDirectory=/path/to/hypermon
ExecStart=/usr/bin/python3 /path/to/hypermon-proxy.py
Restart=always

[Install]
WantedBy=multi-user.target
```

Enable and start the service:

```bash
sudo systemctl enable hypermon-proxy
sudo systemctl start hypermon-proxy
sudo systemctl status hypermon-proxy
```

### Hosting on a Web Server

To make the interface accessible from other devices:

1. Copy `hypermon.html` to your web server document root
2. Update the proxy URL in settings to your proxy server's address
3. Ensure the proxy server is accessible from your network
4. Consider using HTTPS for secure connections

## Security Considerations

- This tool makes connections to your AllMon3 instance on your behalf
- Settings are stored in browser localStorage (client-side only)
- The proxy server only fetches public AllStarLink data
- No credentials or sensitive data are transmitted through the proxy
- AllMon3 connection URLs are opened in new tabs for transparency
- Consider running the proxy server behind a firewall
- Use HTTPS in production environments

## AllMon3 Integration

This tool uses AllMon3's link.php interface to connect nodes:

```
http://your-allmon3/link.php?node=YOUR_NODE&links=TARGET_NODE
```

Verify connections by checking your AllMon3 interface after clicking a node.

## Development

### File Structure

```
hypermon/
├── hypermon.html           # Frontend web interface
├── hypermon-proxy.py       # Backend proxy server
├── requirements.txt        # Python dependencies
├── install.sh              # Installation script
├── README.md               # This file
└── LICENSE                 # License file
```

### Contributing

Contributions are welcome! Please feel free to submit a Pull Request. For major changes:

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/YourFeature`)
3. Commit your changes (`git commit -m 'Add YourFeature'`)
4. Push to the branch (`git push origin feature/YourFeature`)
5. Open a Pull Request

## Known Limitations

- Requires the proxy server to be running for data fetching
- Depends on AllStarLink website HTML structure (may break if they change)
- CORS restrictions prevent direct browser access to AllStarLink sites
- Search results limited by AllStarLink's search functionality

## License

This project is licensed under the MIT License - see the LICENSE file for details.

## Acknowledgments

- Built for the AllStarLink community
- Uses data from allstarlink.org
- Designed to work with AllMon3

## Support

For issues, questions, or suggestions:

1. Check the Troubleshooting section above
2. Review browser console (F12) for errors
3. Check proxy server terminal for error messages
4. Open an issue on GitHub

---

**73!** Happy operating!
