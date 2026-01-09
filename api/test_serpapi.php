<?php
/**
 * File test SerpAPI connection
 * Chạy file này để kiểm tra API key có hoạt động không
 * 
 * Truy cập: http://localhost/shop/api/test_serpapi.php
 */

require_once __DIR__ . '/config.php';

// Tọa độ mẫu (New York)
$latitude = 40.7455096;
$longitude = -74.0083012;
$query = 'Coffee';
$ll = "@{$latitude},{$longitude},14z";

echo "<h2>Test SerpAPI Connection</h2>";
echo "<p><strong>API Key:</strong> " . substr(SERPAPI_KEY, 0, 20) . "...</p>";
echo "<p><strong>Query:</strong> {$query}</p>";
echo "<p><strong>Location:</strong> {$latitude}, {$longitude}</p>";
echo "<hr>";

// Gọi SerpAPI
$url = "https://serpapi.com/search.json";
$params = [
    'engine' => 'google_maps',
    'q' => $query,
    'll' => $ll,
    'api_key' => SERPAPI_KEY,
    'hl' => 'vi'
];

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url . '?' . http_build_query($params));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlError = curl_error($ch);
curl_close($ch);

echo "<h3>Response Status:</h3>";
echo "<p><strong>HTTP Code:</strong> {$httpCode}</p>";

if ($curlError) {
    echo "<p style='color: red;'><strong>cURL Error:</strong> {$curlError}</p>";
}

if ($httpCode === 200 && $response) {
    $data = json_decode($response, true);
    
    if (isset($data['error'])) {
        echo "<p style='color: red;'><strong>API Error:</strong> " . htmlspecialchars($data['error']) . "</p>";
    } else {
        echo "<p style='color: green;'><strong>✓ Kết nối thành công!</strong></p>";
        
        if (isset($data['local_results']) && is_array($data['local_results'])) {
            $count = count($data['local_results']);
            echo "<p><strong>Số địa điểm tìm thấy:</strong> {$count}</p>";
            
            echo "<h3>Top 5 địa điểm gần nhất:</h3>";
            echo "<ol>";
            foreach (array_slice($data['local_results'], 0, 5) as $index => $place) {
                $title = $place['title'] ?? $place['tiêu_đề'] ?? 'N/A';
                $address = $place['address'] ?? $place['Địa_chỉ'] ?? 'N/A';
                $rating = $place['rating'] ?? $place['đánh_giá'] ?? 'N/A';
                
                echo "<li>";
                echo "<strong>{$title}</strong><br>";
                echo "Địa chỉ: {$address}<br>";
                echo "Đánh giá: {$rating}/5.0<br>";
                echo "</li><br>";
            }
            echo "</ol>";
        } else {
            echo "<p style='color: orange;'>Không tìm thấy địa điểm nào trong kết quả.</p>";
        }
        
        echo "<h3>Raw Response (first 500 chars):</h3>";
        echo "<pre>" . htmlspecialchars(substr($response, 0, 500)) . "...</pre>";
    }
} else {
    echo "<p style='color: red;'><strong>✗ Kết nối thất bại!</strong></p>";
    echo "<p><strong>Response:</strong></p>";
    echo "<pre>" . htmlspecialchars($response) . "</pre>";
}

echo "<hr>";
echo "<p><a href='../checkout.php?step=2'>← Quay lại Checkout</a></p>";
?>














