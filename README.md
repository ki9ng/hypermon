# HyperMon

A mobile-first web interface for AllStarLink nodes, designed for quick discovery and connection to active nodes.

## Quick Install

**One-line install for AllStarLink nodes:**

```bash
cd /var/www/html && sudo wget https://github.com/ki9ng/hypermon/archive/main.tar.gz && sudo tar -xzf main.tar.gz && sudo mv hypermon-main hypermon && sudo chown -R www-data:www-data hypermon && sudo rm main.tar.gz && echo "HyperMon installed! Access at http://$(hostname -I | awk '{print $1}')/hypermon/"
```

Then access at `http://your-node-ip/hypermon/`

## The Problem

Current AllStarLink web interfaces (Supermon, Allmon) work well on desktop but aren't optimized for mobile use. If you want to find and connect to an active node while away from your desk, you typically need to:

1. Open the AllStarLink stats page on your phone
2. Find a node that's currently active
3. Switch to your Supermon page
4. Type in the node number
5. Hit connect

HyperMon simplifies this to: tap the active node, you're connected.

## Features

**Mobile-First Design**
- Touch-optimized interface designed for phones
- One-line layout: Callsign | Connections | Icon | Location
- Large touch targets, no tiny buttons
- Works on phones, tablets, and desktops

**Active Node Discovery**
- Shows currently keyed (transmitting) nodes from AllStarLink's live stats
- See what's happening right now across the network
- Connection count displayed for each node
- Auto-refreshes every 30 seconds

**Connection Management**
- One-tap connect to any active node
- Connected nodes panel at top with disconnect buttons
- Integrates with AllMon3 for authentication
- Auto-refresh connections every 15 seconds

**Lightweight**
- Runs on your Raspberry Pi alongside ASL3
- Minimal resource usage
- 30-second caching to reduce server load
- Fast page loads

## Setup

After installation, click "Config" and enter:

- **AllMon3 URL**: Your AllMon3 installation URL (e.g., `http://your-ip/allmon3/`)
- **Username**: Your AllMon3 username
- **Password**: Your AllMon3 password
- **Your Node Number**: Your node number

Configuration is saved in your browser's local storage.

## Requirements

- AllStarLink node (ASL3 recommended)
- AllMon3 installed and configured
- Apache/nginx with PHP support
- PHP cURL extension

## Current Status

**Working:**
- Active node discovery from AllStarLink stats
- AllMon3 integration for connections
- Mobile-responsive layout
- Connection/disconnection management
- Auto-refresh and caching

**Roadmap:**
- Favorites management
- Recent connections history
- Search/filter nodes
- PWA features (install to home screen)
- Installation script

## Technical Details

**Backend:** PHP
- Scrapes AllStarLink stats page for keyed nodes
- Integrates with AllMon3 API for connections
- Implements caching to respect rate limits

**Frontend:** Vanilla JavaScript + CSS
- No frameworks or dependencies
- Browser localStorage for configuration
- Responsive design with CSS Grid/Flexbox

**Data Flow:**
1. PHP backend fetches keyed nodes from stats.allstarlink.org
2. Caches results for 30 seconds
3. Frontend displays and auto-refreshes
4. Connections made via AllMon3's link.php endpoint

## API Information

HyperMon uses:
- AllStarLink stats page: `https://stats.allstarlink.org/stats/keyed`
- AllMon3 API for connections and node status
- 30-second caching to respect rate limits

## Contributing

Contributions welcome! Areas that need work:

- Testing on different devices and browsers
- Installation script development
- PWA implementation
- Favorites/history features
- UI/UX improvements

**How to Contribute:**
1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Submit a pull request

Please keep contributions focused on the core goal: a simple, fast, mobile-friendly interface for connecting to active AllStar nodes.

## Relationship to Other Projects

**AllScan** by davidgsd is an excellent full-featured dashboard with comprehensive stats and favorites management.

**Supermon/Allmon** are established web interfaces with many advanced features.

HyperMon focuses specifically on **mobile use** and **active node discovery**. It's not trying to replace these projects—it's solving a different problem: making it easy to find and connect to active nodes from your phone.

## License

GPL-3.0 (same as Supermon and AllScan)

## Acknowledgments

Thanks to:
- AllStarLink developers and community
- Supermon, Allmon, and AllScan creators
- Everyone testing and contributing to HyperMon

## Support

- GitHub Issues for bugs and feature requests
- Discussions tab for questions and ideas
- Pull requests welcome
