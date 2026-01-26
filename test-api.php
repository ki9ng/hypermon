<?php
// Test script to diagnose API issues
header('Content-Type: text/plain');

echo "=== HyperMon API Diagnostics ===\n\n";

// Check PHP version
echo "PHP Version: " . phpversion() . "\n";

// Check if curl is available
if (function_exists('curl_init')) {
    echo "CURL: Available\n";
} else {
    echo "CURL: NOT AVAILABLE - Install with: apt install php-curl\n";
}

// Test fetching AllStarLink
echo "\n=== Testing AllStarLink Connection ===\n";

$url = "https://stats.allstarlink.org/stats/keyed";
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0');
curl_setopt($ch, CURLOPT_TIMEOUT, 15);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

$result = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

echo "HTTP Code: $httpCode\n";
if ($error) {
    echo "CURL Error: $error\n";
}
if ($result) {
    echo "Response Length: " . strlen($result) . " bytes\n";
    echo "Response Preview: " . substr(strip_tags($result), 0, 200) . "...\n";
} else {
    echo "No response received\n";
}

echo "\n=== Test Complete ===\n";
?>
