# HyperMon

Streamlined web interface for connecting to AllStarLink nodes via AllMon3.

## Installation

One-line install:

```bash
curl -sL https://github.com/ki9ng/hypermon/archive/main.tar.gz | tar xz && cd hypermon-main && chmod +x install.sh && sudo ./install.sh
```

## What It Does

- Installs to `/var/www/html/hypermon` (or your web root)
- Auto-detects your node number
- Works like AllMon3 - just visit `/hypermon` in your browser
- No Python, no services, no port forwarding needed

## Access

After installation, access HyperMon at:

```
http://your-server-ip/hypermon
http://your-hostname/hypermon
```

For example:
```
http://604010.ki9ng.com/hypermon
```

## Features

- View currently keyed nodes
- Search nodes by callsign
- One-click connection to nodes via AllMon3
- Auto-refresh keyed nodes
- No configuration needed - works out of the box

## Requirements

- Web server (Apache/Nginx) with PHP
- AllMon3 installed
- curl PHP extension (usually installed by default)

## Uninstall

```bash
sudo rm -rf /var/www/html/hypermon
```

## How It Works

HyperMon is a simple web application that:
1. Sits in your web server alongside AllMon3
2. Uses PHP to fetch data from AllStarLink (no CORS issues)
3. Provides a clean interface to search and connect to nodes
4. Integrates with your existing AllMon3 installation

## License

MIT License

---

**73!**
