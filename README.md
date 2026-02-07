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
- No squinting at tiny buttons

**Active Node Discovery**
- Shows currently keyed (transmitting) nodes from AllStarLink's live stats
- See what's happening right now across the network
- One tap to connect to any active node

**Zero Configuration**
- Works immediately after install
- No user accounts or databases required
- Favorites and recent connections stored in your browser

**Lightweight**
- Runs on your Raspberry Pi alongside ASL3
- Minimal resource usage
- Fast page loads

## Current Status

**This project is in early development.** The basic architecture is planned, but implementation is just beginning.

What works:
- Research into AllStarLink APIs
- Architecture planning

What's needed:
- Everything else

## Planned Features

**v1.0 Core Features:**
- View currently keyed nodes from AllStarLink network
- One-tap connect/disconnect
- Visual status indicators (connected, transmitting, idle)
- Favorites list
- Recent connections history
- Your node status display

**Future Considerations:**
- Multiple node support
- Audio level indicators
- Connection notifications
- Custom node groups

## Technical Approach

**Backend:** PHP (like Supermon/AllScan)
- Communicates with Asterisk Manager Interface (AMI)
- Fetches active node data from AllStarLink stats API
- Minimal server-side logic

**Frontend:** Vanilla JavaScript + Modern CSS
- No heavy frameworks
- Fast and responsive
- Progressive Web App capabilities

**Data Storage:**
- Browser localStorage for favorites and recents
- No database required for basic functionality

## Installation

Installation instructions will be provided once the initial release is ready.

## Contributing

This project needs help from the AllStarLink community. If you have experience with:

- PHP and AMI integration
- Mobile-responsive web design
- AllStarLink node operation
- Testing on different devices

Your contributions are welcome.

**How to Contribute:**
1. Check the Issues tab for tasks that need doing
2. Fork the repository
3. Make your changes
4. Submit a pull request

Please keep contributions focused on the core goal: a simple, fast, mobile-friendly interface for connecting to active AllStar nodes.

## API Information

HyperMon uses AllStarLink's public APIs:

- **Node List:** `http://stats.allstarlink.org/api/stats/mapData`
- **Keyed Nodes:** `http://stats.allstarlink.org/stats/keyed` (investigating endpoint)
- **Node Stats:** `http://stats.allstarlink.org/stats.php?node=[NODE]`

API rate limit: 30 requests per minute per IP address.

## Relationship to Other Projects

**AllScan** by davidgsd is an excellent project with comprehensive features, favorites management, and stats integration. If you need a full-featured dashboard, use AllScan.

HyperMon focuses specifically on mobile use and active node discovery. It's not trying to replace AllScan or Supermon—it's solving a different problem.

## Development Roadmap

**Phase 1: Basic Functionality**
- [ ] Fetch and display keyed nodes
- [ ] AMI integration for connect/disconnect
- [ ] Basic status display
- [ ] Mobile-responsive layout

**Phase 2: Essential Features**
- [ ] Favorites management
- [ ] Recent connections
- [ ] Search/filter nodes
- [ ] One-tap connect with auto-disconnect option

**Phase 3: Polish**
- [ ] Progressive Web App features
- [ ] Installation script
- [ ] Configuration options
- [ ] Documentation

## License

GPL-3.0 (same as Supermon and AllScan)

## Contact

- GitHub Issues for bugs and feature requests
- Discussions tab for questions and ideas

## Acknowledgments

Thanks to the AllStarLink developers and community, and to the creators of Supermon, Allmon, and AllScan for showing what's possible with ASL web interfaces.
