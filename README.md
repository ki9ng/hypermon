# HyperMon

A mobile-first web interface for AllStarLink nodes, designed for quick discovery and connection to active nodes.

## The Problem

Current AllStarLink web interfaces (Supermon, Allmon) work well on desktop but aren't optimized for mobile use. If you want to find and connect to an active node while away from your desk, you typically need to:

1. Open the AllStarLink stats page on your phone
2. Find a node that's currently active
3. Switch to your Supermon page
4. Type in the node number
5. Hit connect

HyperMon simplifies this to: tap the active node, you're connected.

## What Makes HyperMon Different

**Mobile-First Design**
- Touch-optimized interface
- Works on phones, tablets, and desktops
- Compact list view optimized for small screens

**Active Node Discovery**
- Shows currently keyed (transmitting) nodes from AllStarLink's live stats
- See what's happening right now across the network
- One tap to connect to any active node

**Zero Configuration**
- Works immediately after install
- Minimal setup required
- Settings stored in your browser

**Lightweight**
- Runs on your Raspberry Pi alongside ASL3
- Minimal resource usage
- Fast page loads with intelligent caching

## Installation

```bash
cd ~
tar -xzf hypermon-v1.0.0.tar.gz
sudo cp -r hypermon /var/www/html/
```

Then visit `http://your-node-ip/hypermon` in your browser.

### First-Time Setup

1. Click the "Config" button in the top right
2. Enter your AllMon3 URL (e.g., `http://your-node-ip/allmon3`)
3. Enter your node number
4. Click "Save Configuration"

That's it. Your settings are saved in your browser's local storage.

## How It Works

### Data Collection

HyperMon uses a simple but effective approach to get the list of currently active nodes:

1. The PHP backend scrapes the HTML from `https://stats.allstarlink.org/stats/keyed`
2. Parses the node table to extract node numbers, callsigns, and locations
3. Returns clean JSON data to the frontend
4. Results are cached for 30 seconds to reduce load on AllStarLink's servers

This scraping approach was chosen because:
- No official JSON API exists for keyed nodes
- It gets the exact same data users see on the stats page
- PHP backend avoids CORS issues
- Caching reduces server load

### Connection Process

When you tap a node:
1. HyperMon constructs an AllMon3 link.php URL
2. Opens it in a new tab to trigger the connection
3. AllMon3 handles the actual AMI commands to your node

This leverages your existing AllMon3 installation rather than reimplementing AMI communication.

## Current Status - v1.0.0

**What's Working:**
- Fetch and display currently keyed nodes
- 30-second auto-refresh
- Connect to nodes via AllMon3
- Mobile-responsive list layout
- Configuration panel
- 30-second caching to reduce server load
- Robust HTML parsing with fallback methods

**What's Not Yet Implemented:**
- Direct disconnect functionality
- Favorites management
- Recent connections history
- Connection status indicators
- Direct AMI integration (currently uses AllMon3)
- Search/filter nodes

## Technical Details

**Backend:** PHP
- Scrapes AllStarLink stats pages
- Parses HTML tables using DOMDocument (with regex fallback)
- 30-second file-based cache
- Returns clean JSON to frontend

**Frontend:** Vanilla JavaScript + CSS
- No frameworks or dependencies
- Responsive design for mobile
- LocalStorage for settings
- Auto-refresh every 30 seconds

**Requirements:**
- Web server (Apache/Nginx) with PHP
- PHP curl extension
- AllMon3 installed and configured
- Internet access to reach stats.allstarlink.org

## API Endpoints

The api.php backend provides these endpoints:

- `?action=keyed-nodes` - Returns currently keyed nodes
- `?action=search-nodes&q=CALLSIGN` - Search for nodes by callsign
- `?action=health` - Health check

All endpoints return JSON.

## Roadmap

**v1.1 - Essential Features**
- Disconnect button functionality
- Favorites list (stored in localStorage)
- Recent connections history
- Better error messages

**v1.2 - Enhanced Discovery**
- Search/filter nodes in keyed list
- Show node connection count
- Display how long node has been active

**v2.0 - Direct Integration**
- Direct AMI communication (no AllMon3 dependency)
- Real connection status indicators
- Auto-disconnect option
- Multiple node support

## Known Limitations

1. **Requires AllMon3** - Currently uses AllMon3's link.php for connections
2. **No disconnect** - Can only connect, not disconnect (coming in v1.1)
3. **Scraping-based** - Depends on AllStarLink's HTML structure staying consistent
4. **No real-time updates** - Polls every 30 seconds rather than WebSocket
5. **Cache delays** - May show stale data for up to 30 seconds

## Troubleshooting

**Nothing shows up / "Failed to load active nodes"**
- Check that your web server has internet access
- Verify PHP curl extension is installed: `php -m | grep curl`
- Check `/tmp/hypermon_keyed_cache.json` permissions
- Try accessing `https://stats.allstarlink.org/stats/keyed` directly

**"Please configure AllMon3 URL and node number first"**
- Click the Config button and enter your AllMon3 URL
- Make sure AllMon3 is installed and accessible
- Settings are stored in browser localStorage (not cookies)

**Connection doesn't work**
- Verify your AllMon3 URL is correct
- Check that AllMon3's link.php is working
- Make sure your node number is correct
- Check AllMon3 logs for errors

## Development

Want to contribute? Great! Here's how:

1. Fork the repository
2. Make your changes
3. Test on mobile devices
4. Submit a pull request

Please keep contributions focused on the core goal: simple, fast, mobile-friendly interface for connecting to active AllStar nodes.

## Relationship to Other Projects

**AllScan** by davidgsd is an excellent project with comprehensive features, favorites management, and detailed stats integration. If you need a full-featured desktop dashboard, use AllScan.

HyperMon focuses specifically on mobile use and active node discovery with minimal setup. It's not trying to replace AllScan or Supermon - it's solving a different problem: quick mobile access to active nodes.

## License

GPL-3.0 (same as Supermon and AllScan)

## Contact

- GitHub Issues for bugs and feature requests
- Discussions tab for questions and ideas

## Acknowledgments

Thanks to the AllStarLink developers and community, and to the creators of Supermon, Allmon, and AllScan for showing what's possible with ASL web interfaces.

Special thanks to the maintainers of stats.allstarlink.org for providing the public stats pages that make this project possible.
