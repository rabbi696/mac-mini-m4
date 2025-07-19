<?php
// Script to fetch the official PipraPay class from GitHub
echo "<h2>Fetching Official PipraPay Class</h2>";
echo "<hr>";

$github_url = 'https://raw.githubusercontent.com/PipraPay/php-library-piprapay-gateway/main/PipraPay.php';

echo "Fetching from: " . $github_url . "<br><br>";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $github_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 30);

$content = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

if ($error) {
    echo "<p style='color: red;'>❌ cURL Error: " . $error . "</p>";
} elseif ($http_code !== 200) {
    echo "<p style='color: red;'>❌ HTTP Error: " . $http_code . "</p>";
} elseif ($content) {
    echo "<p style='color: green;'>✅ Successfully fetched official PipraPay class!</p>";
    echo "<h3>Content Preview (first 500 characters):</h3>";
    echo "<pre>" . htmlspecialchars(substr($content, 0, 500)) . "...</pre>";
    
    // Save to file
    $filename = 'PipraPay-official.php';
    if (file_put_contents($filename, $content)) {
        echo "<p style='color: green;'>✅ Saved to: " . $filename . "</p>";
        echo "<p>File size: " . strlen($content) . " bytes</p>";
    } else {
        echo "<p style='color: red;'>❌ Failed to save file</p>";
    }
} else {
    echo "<p style='color: red;'>❌ Empty response received</p>";
}
?>
