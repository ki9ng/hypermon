<?php
// Auto-detect node number from config
$nodeNumber = '';
$allmonUrl = '/allmon3';

// Try to read node from config file
if (file_exists('config.php')) {
    include 'config.php';
    if (isset($HYPERMON_NODE)) {
        $nodeNumber = $HYPERMON_NODE;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HyperMon</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
            min-height: 100vh;
            padding: 20px;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
        }

        header {
            background: rgba(255, 255, 255, 0.95);
            padding: 20px 30px;
            border-radius: 12px;
            margin-bottom: 20px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        h1 {
            color: #1e3c72;
            margin-bottom: 10px;
            font-size: 28px;
        }

        .subtitle {
            color: #666;
            font-size: 14px;
        }

        .button-group {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        button {
            padding: 10px 20px;
            border: none;
            border-radius: 6px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
        }

        .btn-primary {
            background: #2a5298;
            color: white;
        }

        .btn-primary:hover {
            background: #1e3c72;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }

        .btn-secondary {
            background: #6c757d;
            color: white;
        }

        .btn-secondary:hover {
            background: #545b62;
        }

        .tabs {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
        }

        .tab {
            padding: 12px 24px;
            background: rgba(255, 255, 255, 0.7);
            border: none;
            border-radius: 8px 8px 0 0;
            cursor: pointer;
            font-weight: 600;
            color: #1e3c72;
            transition: all 0.3s;
        }

        .tab.active {
            background: rgba(255, 255, 255, 0.95);
            box-shadow: 0 -2px 6px rgba(0, 0, 0, 0.1);
        }

        .tab-content {
            display: none;
            background: rgba(255, 255, 255, 0.95);
            padding: 30px;
            border-radius: 0 12px 12px 12px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .tab-content.active {
            display: block;
        }

        .node-list {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 15px;
            margin-top: 20px;
        }

        .node-card {
            background: white;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            padding: 15px;
            transition: all 0.3s;
            cursor: pointer;
        }

        .node-card:hover {
            border-color: #2a5298;
            box-shadow: 0 4px 12px rgba(42, 82, 152, 0.2);
            transform: translateY(-2px);
        }

        .node-number {
            font-size: 24px;
            font-weight: bold;
            color: #1e3c72;
            margin-bottom: 8px;
        }

        .node-callsign {
            font-size: 18px;
            color: #333;
            margin-bottom: 5px;
        }

        .node-info {
            font-size: 14px;
            color: #666;
            margin-bottom: 3px;
        }

        .status-indicator {
            display: inline-block;
            width: 10px;
            height: 10px;
            border-radius: 50%;
            margin-right: 5px;
            background: #28a745;
        }

        .search-results {
            margin-top: 20px;
        }

        .loading {
            text-align: center;
            padding: 40px;
            color: #666;
            font-size: 18px;
        }

        .error {
            background: #f8d7da;
            color: #721c24;
            padding: 15px;
            border-radius: 6px;
            margin-top: 15px;
        }

        .success {
            background: #d4edda;
            color: #155724;
            padding: 15px;
            border-radius: 6px;
            margin-top: 15px;
        }

        .info-box {
            background: #d1ecf1;
            color: #0c5460;
            padding: 15px;
            border-radius: 6px;
            margin-bottom: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }

        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        th {
            background: #f8f9fa;
            font-weight: 600;
            color: #1e3c72;
        }

        tr:hover {
            background: #f8f9fa;
        }

        .connect-btn {
            padding: 6px 12px;
            font-size: 14px;
        }

        input[type="text"], input[type="number"] {
            width: 100%;
            padding: 10px 15px;
            border: 2px solid #ddd;
            border-radius: 6px;
            font-size: 16px;
        }

        input:focus {
            outline: none;
            border-color: #2a5298;
        }

        label {
            display: block;
            margin-bottom: 5px;
            color: #333;
            font-weight: 500;
        }

        .input-group {
            margin-bottom: 15px;
        }

        @media (max-width: 768px) {
            .node-list {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <header>
            <h1>HyperMon</h1>
            <p class="subtitle">Node Connection Manager <?php if($nodeNumber) echo "- Node $nodeNumber"; ?></p>
        </header>

        <div class="tabs">
            <button class="tab active" onclick="switchTab('keyed')">Currently Keyed</button>
            <button class="tab" onclick="switchTab('search')">Search by Callsign</button>
            <button class="tab" onclick="switchTab('manual')">Manual Entry</button>
        </div>

        <div id="keyed-tab" class="tab-content active">
            <h2>Currently Active (Keyed) Nodes</h2>
            <div class="info-box">
                Click "Refresh" to load currently keyed nodes. Click any node to connect.
            </div>
            <div class="button-group">
                <button class="btn-primary" onclick="loadKeyedNodes()">Refresh Keyed Nodes</button>
                <button class="btn-secondary" onclick="toggleAutoRefresh()"><span id="auto-refresh-text">Enable Auto-Refresh</span></button>
            </div>
            <div id="keyed-status"></div>
            <div id="keyed-nodes" class="node-list"></div>
        </div>

        <div id="search-tab" class="tab-content">
            <h2>Search by Callsign or Node</h2>
            <div class="info-box">
                Enter a callsign or node number to search the AllStarLink database.
            </div>
            <div class="input-group">
                <label for="search-input">Callsign or Node Number:</label>
                <input type="text" id="search-input" placeholder="Enter callsign (e.g., W1ABC) or node number">
            </div>
            <button class="btn-primary" onclick="searchNodes()">Search</button>
            <div id="search-status"></div>
            <div id="search-results" class="search-results"></div>
        </div>

        <div id="manual-tab" class="tab-content">
            <h2>Manual Node Connection</h2>
            <div class="info-box">
                Directly enter a node number to connect.
            </div>
            <div class="input-group">
                <label for="manual-node">Node Number:</label>
                <input type="number" id="manual-node" placeholder="Enter node number">
            </div>
            <button class="btn-primary" onclick="connectManualNode()">Connect to Node</button>
            <div id="manual-status"></div>
        </div>
    </div>

    <script>
        const YOUR_NODE = '<?php echo $nodeNumber; ?>';
        const ALLMON_URL = '<?php echo $allmonUrl; ?>';
        const API_URL = './api.php';

        let autoRefreshInterval = null;

        function switchTab(tabName) {
            const tabs = document.querySelectorAll('.tab');
            const contents = document.querySelectorAll('.tab-content');
            
            tabs.forEach(tab => tab.classList.remove('active'));
            contents.forEach(content => content.classList.remove('active'));
            
            document.querySelector(`.tab:nth-child(${tabName === 'keyed' ? 1 : tabName === 'search' ? 2 : 3})`).classList.add('active');
            document.getElementById(`${tabName}-tab`).classList.add('active');
        }

        function connectToNode(nodeNumber) {
            if (!YOUR_NODE) {
                alert('Node number not configured. Please contact administrator.');
                return;
            }

            const connectUrl = `${ALLMON_URL}/link.php?node=${YOUR_NODE}&links=${nodeNumber}`;
            window.open(connectUrl, '_blank');
            
            const statusDiv = document.getElementById('keyed-status') || document.getElementById('search-status') || document.getElementById('manual-status');
            statusDiv.innerHTML = `<div class="success">Connection request sent to node ${nodeNumber}.</div>`;
            setTimeout(() => {
                statusDiv.innerHTML = '';
            }, 5000);
        }

        async function loadKeyedNodes() {
            const statusDiv = document.getElementById('keyed-status');
            const nodesDiv = document.getElementById('keyed-nodes');
            
            statusDiv.innerHTML = '<div class="loading">Loading keyed nodes...</div>';
            
            try {
                const response = await fetch(`${API_URL}?action=keyed-nodes`);
                const data = await response.json();
                
                if (data.success && data.nodes.length > 0) {
                    displayNodes(data.nodes, nodesDiv);
                    statusDiv.innerHTML = `<div class="success">Loaded ${data.count} keyed node(s)</div>`;
                    setTimeout(() => {
                        statusDiv.innerHTML = '';
                    }, 3000);
                } else if (data.success && data.nodes.length === 0) {
                    nodesDiv.innerHTML = '<p style="text-align: center; color: #666; padding: 40px;">No nodes currently keyed</p>';
                    statusDiv.innerHTML = '<div class="success">No keyed nodes at this time</div>';
                    setTimeout(() => {
                        statusDiv.innerHTML = '';
                    }, 3000);
                } else {
                    throw new Error(data.error || 'Failed to load nodes');
                }
            } catch (error) {
                console.error('Error loading keyed nodes:', error);
                statusDiv.innerHTML = `<div class="error">Error: ${error.message}</div>`;
            }
        }

        async function searchNodes() {
            const searchTerm = document.getElementById('search-input').value.trim().toUpperCase();
            const statusDiv = document.getElementById('search-status');
            const resultsDiv = document.getElementById('search-results');
            
            if (!searchTerm) {
                statusDiv.innerHTML = '<div class="error">Please enter a callsign or node number.</div>';
                return;
            }
            
            statusDiv.innerHTML = '<div class="loading">Searching...</div>';
            
            try {
                const response = await fetch(`${API_URL}?action=search-nodes&q=${encodeURIComponent(searchTerm)}`);
                const data = await response.json();
                
                if (data.success && data.results.length > 0) {
                    let html = '<table><thead><tr><th>Node</th><th>Callsign</th><th>Location</th><th>Description</th><th>Action</th></tr></thead><tbody>';
                    
                    data.results.forEach(node => {
                        html += `
                            <tr>
                                <td><strong>${node.node}</strong></td>
                                <td>${node.callsign}</td>
                                <td>${node.location}</td>
                                <td>${node.description || '-'}</td>
                                <td><button class="btn-primary connect-btn" onclick="connectToNode('${node.node}')">Connect</button></td>
                            </tr>
                        `;
                    });
                    
                    html += '</tbody></table>';
                    resultsDiv.innerHTML = html;
                    statusDiv.innerHTML = `<div class="success">Found ${data.count} result(s)</div>`;
                    setTimeout(() => {
                        statusDiv.innerHTML = '';
                    }, 3000);
                } else if (data.success && data.results.length === 0) {
                    resultsDiv.innerHTML = '';
                    statusDiv.innerHTML = '<div class="error">No results found.</div>';
                } else {
                    throw new Error(data.error || 'Search failed');
                }
            } catch (error) {
                console.error('Error searching nodes:', error);
                resultsDiv.innerHTML = '';
                statusDiv.innerHTML = `<div class="error">Error: ${error.message}</div>`;
            }
        }

        function connectManualNode() {
            const nodeNumber = document.getElementById('manual-node').value;
            const statusDiv = document.getElementById('manual-status');
            
            if (!nodeNumber) {
                statusDiv.innerHTML = '<div class="error">Please enter a node number.</div>';
                return;
            }
            
            connectToNode(nodeNumber);
        }

        function displayNodes(nodes, container) {
            let html = '';
            
            nodes.forEach(node => {
                html += `
                    <div class="node-card" onclick="connectToNode('${node.node}')">
                        <div class="node-number">
                            <span class="status-indicator"></span>
                            ${node.node}
                        </div>
                        <div class="node-callsign">${node.callsign}</div>
                        <div class="node-info">Location: ${node.location}</div>
                        ${node.description ? `<div class="node-info">${node.description}</div>` : ''}
                    </div>
                `;
            });
            
            container.innerHTML = html;
        }

        function toggleAutoRefresh() {
            if (autoRefreshInterval) {
                clearInterval(autoRefreshInterval);
                autoRefreshInterval = null;
                document.getElementById('auto-refresh-text').textContent = 'Enable Auto-Refresh';
            } else {
                autoRefreshInterval = setInterval(loadKeyedNodes, 30000);
                document.getElementById('auto-refresh-text').textContent = 'Disable Auto-Refresh';
                loadKeyedNodes();
            }
        }

        document.addEventListener('keydown', function(e) {
            if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
                e.preventDefault();
                switchTab('search');
                document.getElementById('search-input').focus();
            }
        });

        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById('search-input').addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    searchNodes();
                }
            });
            
            document.getElementById('manual-node').addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    connectManualNode();
                }
            });
        });
    </script>
</body>
</html>
