# HyperMon Architecture

This document outlines the technical architecture of HyperMon.

## Directory Structure

```
hypermon/
├── index.php           # Main entry point
├── api/                # Backend API endpoints
│   ├── ami.php        # AMI connection and commands
│   ├── keyed.php      # Fetch keyed nodes from ASL stats
│   ├── status.php     # Get node connection status
│   └── nodes.php      # Node information utilities
├── css/
│   └── style.css      # Main stylesheet
├── js/
│   ├── app.js         # Main application logic
│   └── api.js         # API communication
├── docs/
│   └── API.md         # API documentation
└── config/
    └── config.sample.php  # Sample configuration
```

## Data Flow

### Displaying Keyed Nodes

1. Browser loads `index.php`
2. JavaScript calls `api/keyed.php` every 10 seconds
3. `keyed.php` fetches from AllStarLink stats API (with caching)
4. Returns JSON to browser
5. JavaScript updates UI with active nodes

### Connecting to a Node

1. User taps a node
2. JavaScript calls `api/ami.php?action=connect&node=12345`
3. `ami.php` connects to Asterisk Manager Interface
4. Sends `rpt cmd [your_node] ilink 3 12345` command
5. Returns success/failure to browser
6. JavaScript updates UI to show connection

### Status Updates

1. JavaScript calls `api/status.php` every 2 seconds
2. `status.php` queries AMI for current connections
3. Returns JSON with connected nodes
4. JavaScript highlights connected nodes in UI

## AMI Integration

Uses Asterisk Manager Interface to control the node:

**Connect to AMI:**
```php
$socket = fsockopen('localhost', 5038);
fputs($socket, "Action: Login\r\n");
fputs($socket, "Username: admin\r\n");
fputs($socket, "Secret: password\r\n\r\n");
```

**Send Command:**
```php
fputs($socket, "Action: Command\r\n");
fputs($socket, "Command: rpt cmd [node] ilink 3 [remote_node]\r\n\r\n");
```

**Disconnect:**
```php
fputs($socket, "Action: Command\r\n");
fputs($socket, "Command: rpt cmd [node] ilink 1 [remote_node]\r\n\r\n");
```

## AllStarLink API Integration

### Keyed Nodes API

**Endpoint:** `http://stats.allstarlink.org/stats/keyed`

Returns list of currently transmitting nodes. Response format needs investigation.

**Caching Strategy:**
- Cache results for 10 seconds
- Store in PHP session or temp file
- Reduces load on ASL stats server
- Stays under 30 req/min rate limit

### Node Information API

**Endpoint:** `http://stats.allstarlink.org/api/stats/mapData`

Returns all registered nodes with:
- Node number
- Callsign
- Location
- Coordinates
- Description

**Usage:**
- Download once at startup
- Cache for 5-10 minutes
- Use for node lookups (number to callsign/location)

## Browser Storage

Uses `localStorage` for client-side persistence:

```javascript
// Favorites
localStorage.setItem('hypermon_favorites', JSON.stringify([12345, 67890]));

// Recent connections
localStorage.setItem('hypermon_recent', JSON.stringify([
  {node: 12345, timestamp: Date.now()},
  {node: 67890, timestamp: Date.now()}
]));
```

## Mobile Optimization

### Touch Targets
- Minimum 44x44px touch targets
- Adequate spacing between interactive elements
- Large, easy-to-tap buttons

### Performance
- Minimize DOM manipulation
- Use CSS transforms for animations
- Debounce search/filter inputs
- Lazy load node lists if needed

### Responsive Design
- Mobile-first CSS
- Flexbox/Grid layouts
- Works from 320px width up

## Security Considerations

### AMI Access
- AMI credentials stored outside web root
- Read from config file with restricted permissions
- Never expose in client-side code

### Rate Limiting
- Respect ASL API 30 req/min limit
- Implement request throttling
- Cache aggressively

### Input Validation
- Validate node numbers (numeric only)
- Sanitize all inputs before AMI commands
- Prevent command injection

## Configuration

`config/config.php` (not in git):
```php
<?php
define('AMI_HOST', 'localhost');
define('AMI_PORT', 5038);
define('AMI_USER', 'admin');
define('AMI_PASS', 'your_password');
define('LOCAL_NODE', 12345);
?>
```

## Error Handling

- API endpoints return JSON with status and message
- JavaScript displays user-friendly error messages
- Log errors server-side for debugging
- Graceful degradation if APIs unavailable

## Performance Targets

- Initial page load: < 2 seconds
- API response time: < 500ms
- UI interactions: < 100ms response
- Works smoothly on Raspberry Pi 3+
