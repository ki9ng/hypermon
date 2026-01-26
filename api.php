<?php
/**
 * HyperMon API - PHP Backend
 * Provides API endpoints for fetching AllStarLink data
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

// Get the action from the request
$action = isset($_GET['action']) ? $_GET['action'] : '';

switch ($action) {
    case 'keyed-nodes':
        getKeyedNodes();
        break;
    case 'search-nodes':
        searchNodes();
        break;
    case 'node-info':
        getNodeInfo();
        break;
    case 'health':
        healthCheck();
        break;
    default:
        echo json_encode(['error' => 'Invalid action']);
        break;
}

/**
 * Fetch currently keyed nodes
 */
function getKeyedNodes() {
    $url = "https://stats.allstarlink.org/stats/keyed";
    $html = fetchUrl($url);
    
    if (!$html) {
        echo json_encode(['success' => false, 'error' => 'Failed to fetch data', 'nodes' => []]);
        return;
    }
    
    $nodes = parseNodesTable($html);
    
    // Mark all as keyed
    foreach ($nodes as &$node) {
        $node['status'] = 'keyed';
    }
    
    echo json_encode([
        'success' => true,
        'nodes' => $nodes,
        'count' => count($nodes)
    ]);
}

/**
 * Search for nodes
 */
function searchNodes() {
    $query = isset($_GET['q']) ? trim($_GET['q']) : '';
    
    if (empty($query)) {
        echo json_encode(['success' => false, 'error' => 'Search term required', 'results' => []]);
        return;
    }
    
    $url = "https://www.allstarlink.org/nodelist/?search=" . urlencode($query);
    $html = fetchUrl($url);
    
    if (!$html) {
        echo json_encode(['success' => false, 'error' => 'Failed to fetch data', 'results' => []]);
        return;
    }
    
    $nodes = parseNodesTable($html);
    
    echo json_encode([
        'success' => true,
        'results' => $nodes,
        'count' => count($nodes),
        'search_term' => $query
    ]);
}

/**
 * Get specific node info
 */
function getNodeInfo() {
    $nodeNumber = isset($_GET['node']) ? trim($_GET['node']) : '';
    
    if (empty($nodeNumber)) {
        echo json_encode(['success' => false, 'error' => 'Node number required']);
        return;
    }
    
    $url = "https://www.allstarlink.org/nodelist/?search=" . urlencode($nodeNumber);
    $html = fetchUrl($url);
    
    if (!$html) {
        echo json_encode(['success' => false, 'error' => 'Failed to fetch data']);
        return;
    }
    
    $nodes = parseNodesTable($html);
    
    if (count($nodes) > 0) {
        echo json_encode([
            'success' => true,
            'node' => $nodes[0]
        ]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Node not found']);
    }
}

/**
 * Health check
 */
function healthCheck() {
    echo json_encode([
        'status' => 'healthy',
        'service' => 'HyperMon API'
    ]);
}

/**
 * Fetch URL content
 */
function fetchUrl($url) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36');
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    
    $result = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode == 200) {
        return $result;
    }
    
    return false;
}

/**
 * Parse HTML table into node array
 */
function parseNodesTable($html) {
    $nodes = [];
    
    // Create DOMDocument
    $dom = new DOMDocument();
    @$dom->loadHTML($html);
    
    // Find tables
    $tables = $dom->getElementsByTagName('table');
    
    if ($tables->length == 0) {
        return $nodes;
    }
    
    // Get first table
    $table = $tables->item(0);
    $rows = $table->getElementsByTagName('tr');
    
    // Skip header row
    for ($i = 1; $i < $rows->length; $i++) {
        $row = $rows->item($i);
        $cols = $row->getElementsByTagName('td');
        
        if ($cols->length >= 3) {
            $node = [
                'node' => trim($cols->item(0)->textContent),
                'callsign' => $cols->length > 1 ? trim($cols->item(1)->textContent) : '',
                'location' => $cols->length > 2 ? trim($cols->item(2)->textContent) : '',
                'description' => $cols->length > 3 ? trim($cols->item(3)->textContent) : ''
            ];
            
            // Only add if node number is valid
            if (!empty($node['node']) && is_numeric($node['node'])) {
                $nodes[] = $node;
            }
        }
    }
    
    return $nodes;
}
?>
