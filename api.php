<?php
/**
 * HyperMon API Backend
 * 
 * Provides endpoints for fetching AllStarLink node data by scraping
 * the stats.allstarlink.org website. Includes caching to reduce load
 * and improve response times.
 */

error_reporting(0);
ini_set('display_errors', 0);

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

$action = isset($_GET['action']) ? $_GET['action'] : '';

try {
    switch ($action) {
        case 'keyed-nodes':
            getKeyedNodes();
            break;
        case 'search-nodes':
            searchNodes();
            break;
        case 'health':
            healthCheck();
            break;
        default:
            echo json_encode([
                'error' => 'Invalid action',
                'success' => false
            ]);
            break;
    }
} catch (Exception $e) {
    echo json_encode([
        'error' => $e->getMessage(),
        'success' => false
    ]);
}

/**
 * Fetch currently keyed (transmitting) nodes
 * Uses 30-second cache to reduce load on AllStarLink servers
 */
function getKeyedNodes() {
    $cacheFile = '/tmp/hypermon_keyed_cache.json';
    $cacheTimeout = 30;
    
    // Check cache first
    if (file_exists($cacheFile)) {
        $cacheData = json_decode(file_get_contents($cacheFile), true);
        if (time() - $cacheData['timestamp'] < $cacheTimeout) {
            echo json_encode([
                'success' => true,
                'nodes' => $cacheData['nodes'],
                'count' => count($cacheData['nodes']),
                'cached' => true
            ]);
            return;
        }
    }
    
    // Fetch fresh data from AllStarLink
    $url = "https://stats.allstarlink.org/stats/keyed";
    $html = fetchUrl($url);
    
    if (!$html) {
        // Return stale cache if fetch fails
        if (isset($cacheData) && !empty($cacheData['nodes'])) {
            echo json_encode([
                'success' => true,
                'nodes' => $cacheData['nodes'],
                'count' => count($cacheData['nodes']),
                'cached' => true,
                'stale' => true
            ]);
            return;
        }
        
        echo json_encode([
            'success' => false,
            'error' => 'Failed to fetch data from AllStarLink',
            'nodes' => []
        ]);
        return;
    }
    
    $nodes = parseNodesTable($html);
    
    // Mark all nodes as keyed
    foreach ($nodes as &$node) {
        $node['status'] = 'keyed';
    }
    
    // Save to cache
    file_put_contents($cacheFile, json_encode([
        'timestamp' => time(),
        'nodes' => $nodes
    ]));
    
    echo json_encode([
        'success' => true,
        'nodes' => $nodes,
        'count' => count($nodes),
        'cached' => false
    ]);
}

/**
 * Search for nodes by callsign or node number
 */
function searchNodes() {
    $query = isset($_GET['q']) ? trim($_GET['q']) : '';
    
    if (empty($query)) {
        echo json_encode([
            'success' => false,
            'error' => 'Search term required',
            'results' => []
        ]);
        return;
    }
    
    $url = "https://www.allstarlink.org/nodelist/?search=" . urlencode($query);
    $html = fetchUrl($url);
    
    if (!$html) {
        echo json_encode([
            'success' => false,
            'error' => 'Failed to fetch data from AllStarLink',
            'results' => []
        ]);
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
 * Health check endpoint
 */
function healthCheck() {
    echo json_encode([
        'status' => 'healthy',
        'service' => 'HyperMon API',
        'version' => '1.0.0',
        'success' => true,
        'timestamp' => time()
    ]);
}

/**
 * Fetch URL using cURL
 */
function fetchUrl($url) {
    if (!function_exists('curl_init')) {
        return false;
    }
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36');
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    
    $result = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode >= 200 && $httpCode < 400 && $result) {
        return $result;
    }
    
    return false;
}

/**
 * Parse HTML table into array of nodes
 * Uses DOMDocument for robust parsing with regex fallback
 */
function parseNodesTable($html) {
    $nodes = [];
    
    // Try DOMDocument first (most robust)
    if (class_exists('DOMDocument')) {
        $dom = new DOMDocument();
        @$dom->loadHTML($html);
        $xpath = new DOMXPath($dom);
        
        $rows = $xpath->query('//table//tr');
        
        $headerSkipped = false;
        foreach ($rows as $row) {
            $cells = $xpath->query('.//td', $row);
            
            // Skip header row
            if (!$headerSkipped) {
                $headerSkipped = true;
                continue;
            }
            
            if ($cells->length >= 2) {
                $nodeData = [];
                foreach ($cells as $cell) {
                    $nodeData[] = trim($cell->textContent);
                }
                
                $nodeNum = $nodeData[0];
                
                if (!empty($nodeNum) && is_numeric($nodeNum)) {
                    $nodes[] = [
                        'node' => $nodeNum,
                        'callsign' => isset($nodeData[1]) ? $nodeData[1] : '',
                        'location' => isset($nodeData[2]) ? $nodeData[2] : '',
                        'description' => isset($nodeData[3]) ? $nodeData[3] : ''
                    ];
                }
            }
        }
        
        if (!empty($nodes)) {
            return $nodes;
        }
    }
    
    // Fallback to regex parsing
    preg_match_all('/<tr[^>]*>(.*?)<\/tr>/is', $html, $rows);
    
    if (!isset($rows[1]) || count($rows[1]) < 2) {
        return $nodes;
    }
    
    // Skip first row (header)
    for ($i = 1; $i < count($rows[1]); $i++) {
        $row = $rows[1][$i];
        
        preg_match_all('/<td[^>]*>(.*?)<\/td>/is', $row, $cells);
        
        if (!isset($cells[1]) || count($cells[1]) < 2) {
            continue;
        }
        
        $cellData = array_map(function($cell) {
            return trim(strip_tags($cell));
        }, $cells[1]);
        
        $nodeNum = $cellData[0];
        
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
