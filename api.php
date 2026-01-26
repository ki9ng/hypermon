<?php
/**
 * HyperMon API - PHP Backend
 * Provides API endpoints for fetching AllStarLink data
 */

// Error handling
error_reporting(0);
ini_set('display_errors', 0);

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

// Get the action from the request
$action = isset($_GET['action']) ? $_GET['action'] : '';

try {
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
            echo json_encode(['error' => 'Invalid action', 'success' => false]);
            break;
    }
} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage(), 'success' => false]);
}

/**
 * Fetch currently keyed nodes
 */
function getKeyedNodes() {
    $url = "https://stats.allstarlink.org/stats/keyed";
    $html = fetchUrl($url);
    
    if (!$html) {
        echo json_encode(['success' => false, 'error' => 'Failed to fetch data from AllStarLink', 'nodes' => []]);
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
        echo json_encode(['success' => false, 'error' => 'Failed to fetch data from AllStarLink', 'results' => []]);
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
        echo json_encode(['success' => false, 'error' => 'Failed to fetch data from AllStarLink']);
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
        'service' => 'HyperMon API',
        'success' => true
    ]);
}

/**
 * Fetch URL content
 */
function fetchUrl($url) {
    // Check if curl is available
    if (!function_exists('curl_init')) {
        return false;
    }
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36');
    curl_setopt($ch, CURLOPT_TIMEOUT, 15);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
    
    $result = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    if ($httpCode >= 200 && $httpCode < 300) {
        return $result;
    }
    
    return false;
}

/**
 * Parse HTML table into node array
 */
function parseNodesTable($html) {
    $nodes = [];
    
    // Simple regex parsing instead of DOM for better compatibility
    // Look for table rows
    preg_match_all('/<tr[^>]*>(.*?)<\/tr>/is', $html, $rows);
    
    if (!isset($rows[1]) || count($rows[1]) < 2) {
        return $nodes;
    }
    
    // Skip first row (header)
    for ($i = 1; $i < count($rows[1]); $i++) {
        $row = $rows[1][$i];
        
        // Extract table cells
        preg_match_all('/<td[^>]*>(.*?)<\/td>/is', $row, $cells);
        
        if (!isset($cells[1]) || count($cells[1]) < 3) {
            continue;
        }
        
        // Clean cell content
        $cellData = array_map(function($cell) {
            return trim(strip_tags($cell));
        }, $cells[1]);
        
        $nodeNum = $cellData[0];
        
        // Only add if node number is valid
        if (!empty($nodeNum) && is_numeric($nodeNum)) {
            $nodes[] = [
                'node' => $nodeNum,
                'callsign' => isset($cellData[1]) ? $cellData[1] : '',
                'location' => isset($cellData[2]) ? $cellData[2] : '',
                'description' => isset($cellData[3]) ? $cellData[3] : ''
            ];
        }
    }
    
    return $nodes;
}
?>
