<?php
/**
 * API Endpoint để xử lý xác nhận vị trí và cập nhật thông tin giao hàng
 * Sử dụng cấu trúc dữ liệu SerpAPI Google Maps
 */

require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/db.php';
header('Content-Type: application/json');

// Load config nếu có
if (file_exists(__DIR__ . '/config.php')) {
    require_once __DIR__ . '/config.php';
}

// Kiểm tra đăng nhập
if (!isset($_SESSION['user_id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Vui lòng đăng nhập để sử dụng tính năng này'
    ]);
    exit();
}

$user_id = $_SESSION['user_id'];
$input = json_decode(file_get_contents('php://input'), true);

if (!$input || !isset($input['action'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Thiếu thông tin yêu cầu'
    ]);
    exit();
}

$action = $input['action'];

try {
    switch ($action) {
        case 'find_nearby':
            // Tìm địa điểm gần nhất dựa trên tọa độ GPS
            $latitude = floatval($input['latitude']);
            $longitude = floatval($input['longitude']);
            $query = isset($input['query']) ? $input['query'] : 'Coffee';
            $ll = isset($input['ll']) ? $input['ll'] : "@{$latitude},{$longitude},14z";
            
            // Gọi SerpAPI để tìm địa điểm gần nhất
            $places = findNearbyPlaces($latitude, $longitude, $query, $ll);
            
            echo json_encode([
                'success' => true,
                'places' => $places
            ]);
            break;

        case 'update_address':
            // Cập nhật địa chỉ giao hàng dựa trên vị trí
            $location = $input['location'];
            
            // Kiểm tra bảng shipping_addresses có tồn tại không
            $check_table = $conn->query("SHOW TABLES LIKE 'shipping_addresses'");
            if (!$check_table || $check_table->num_rows === 0) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Bảng địa chỉ chưa được tạo. Vui lòng chạy file database_wishlist_addresses.sql'
                ]);
                exit();
            }

            // Trích xuất thông tin từ location data
            $address = isset($location['address']) ? trim($location['address']) : '';
            $city = isset($location['city']) ? trim($location['city']) : extractCityFromAddress($address);
            $district = isset($location['district']) ? trim($location['district']) : extractDistrictFromAddress($address);
            $title = isset($location['title']) ? trim($location['title']) : '';
            
            // Lấy thông tin người dùng hiện tại
            $user_query = $conn->prepare("SELECT name, phone FROM user WHERE id = ?");
            $user_query->bind_param('i', $user_id);
            $user_query->execute();
            $user_result = $user_query->get_result();
            $user = $user_result->fetch_assoc();
            
            $full_name = $user['name'] ?? '';
            $phone = $user['phone'] ?? (isset($location['phone']) ? $location['phone'] : '');
            
            // Kiểm tra xem đã có địa chỉ mặc định chưa
            $check_default = $conn->prepare("SELECT id FROM shipping_addresses WHERE user_id = ? AND is_default = 1");
            $check_default->bind_param('i', $user_id);
            $check_default->execute();
            $default_result = $check_default->get_result();
            $is_default = ($default_result->num_rows === 0) ? 1 : 0;
            
            // Nếu đã có địa chỉ mặc định, bỏ is_default của các địa chỉ khác
            if ($is_default) {
                $unset_default = $conn->prepare("UPDATE shipping_addresses SET is_default = 0 WHERE user_id = ?");
                $unset_default->bind_param('i', $user_id);
                $unset_default->execute();
            }
            
            // Thêm địa chỉ mới
            $stmt = $conn->prepare("INSERT INTO shipping_addresses 
                (user_id, full_name, phone, address, ward, district, city, postal_code, is_default) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
            
            $ward = ''; // Có thể trích xuất từ address nếu cần
            $postal_code = ''; // Có thể lấy từ API nếu có
            
            $stmt->bind_param('isssssssi', 
                $user_id, 
                $full_name, 
                $phone, 
                $address, 
                $ward, 
                $district, 
                $city, 
                $postal_code, 
                $is_default
            );
            
            if ($stmt->execute()) {
                // Lưu tọa độ GPS nếu có bảng riêng (tùy chọn)
                $address_id = $conn->insert_id;
                
                // Có thể lưu tọa độ GPS vào bảng riêng nếu cần
                if (isset($location['gps_coordinates'])) {
                    saveGPSCoordinates($conn, $address_id, $location['gps_coordinates']);
                }
                
                echo json_encode([
                    'success' => true,
                    'message' => 'Đã cập nhật địa chỉ giao hàng thành công',
                    'address_id' => $address_id
                ]);
            } else {
                throw new Exception('Lỗi khi lưu địa chỉ: ' . $conn->error);
            }
            break;

        default:
            echo json_encode([
                'success' => false,
                'message' => 'Hành động không hợp lệ'
            ]);
            break;
    }
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

/**
 * Tìm địa điểm gần nhất sử dụng SerpAPI
 * Nếu không có API key, sử dụng dữ liệu mẫu từ cấu trúc đã cung cấp
 */
function findNearbyPlaces($latitude, $longitude, $query = 'Coffee', $ll = '') {
    // Lấy API key từ config hoặc environment variable
    $serpApiKey = defined('SERPAPI_KEY') ? SERPAPI_KEY : 'YOUR_SERPAPI_KEY_HERE';
    $defaultQuery = defined('DEFAULT_SEARCH_QUERY') ? DEFAULT_SEARCH_QUERY : 'Coffee';
    $query = $query ?: $defaultQuery;
    
    // Nếu không có API key, sử dụng dữ liệu mẫu
    if ($serpApiKey === 'YOUR_SERPAPI_KEY_HERE') {
        return getSamplePlaces($latitude, $longitude);
    }
    
    // Gọi SerpAPI
    $url = "https://serpapi.com/search.json";
    $params = [
        'engine' => 'google_maps',
        'q' => $query,
        'll' => $ll ?: "@{$latitude},{$longitude},14z",
        'api_key' => $serpApiKey,
        'hl' => 'vi'
    ];
    
    $timeout = defined('API_TIMEOUT') ? API_TIMEOUT : 10;
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url . '?' . http_build_query($params));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode === 200 && $response) {
        $data = json_decode($response, true);
        
        if (isset($data['local_results']) && is_array($data['local_results'])) {
            // Chuẩn hóa dữ liệu từ SerpAPI
            $places = [];
            foreach ($data['local_results'] as $place) {
                $places[] = normalizePlaceData($place);
            }
            return $places;
        }
    }
    
    // Fallback: sử dụng dữ liệu mẫu
    return getSamplePlaces($latitude, $longitude);
}

/**
 * Chuẩn hóa dữ liệu địa điểm từ SerpAPI
 */
function normalizePlaceData($place) {
    return [
        'title' => $place['title'] ?? $place['tiêu_đề'] ?? '',
        'address' => $place['address'] ?? $place['Địa_chỉ'] ?? '',
        'phone' => $place['phone'] ?? $place['điện_thoại'] ?? '',
        'rating' => $place['rating'] ?? $place['đánh_giá'] ?? null,
        'reviews' => $place['reviews'] ?? $place['đánh_giá'] ?? 0,
        'gps_coordinates' => [
            'latitude' => $place['gps_coordinates']['latitude'] ?? $place['tọa độ GPS']['vĩ độ'] ?? null,
            'longitude' => $place['gps_coordinates']['longitude'] ?? $place['tọa độ GPS']['kinh_độ'] ?? null
        ],
        'type' => $place['type'] ?? $place['kiểu'] ?? '',
        'place_id' => $place['place_id'] ?? ''
    ];
}

/**
 * Dữ liệu mẫu dựa trên cấu trúc SerpAPI đã cung cấp
 */
function getSamplePlaces($latitude, $longitude) {
    // Tạo danh sách địa điểm mẫu dựa trên tọa độ
    // Trong thực tế, bạn nên sử dụng SerpAPI thật
    return [
        [
            'title' => 'Địa điểm gần bạn',
            'address' => 'Vị trí hiện tại của bạn',
            'phone' => '',
            'rating' => null,
            'reviews' => 0,
            'gps_coordinates' => [
                'latitude' => $latitude,
                'longitude' => $longitude
            ],
            'type' => 'Vị trí',
            'place_id' => ''
        ]
    ];
}

/**
 * Trích xuất thành phố từ địa chỉ
 */
function extractCityFromAddress($address) {
    if (empty($address)) return '';
    
    $parts = explode(',', $address);
    if (count($parts) > 0) {
        $city = trim(end($parts));
        // Loại bỏ mã bưu chính nếu có
        $city = preg_replace('/\d{5,6}/', '', $city);
        return trim($city);
    }
    return '';
}

/**
 * Trích xuất quận/huyện từ địa chỉ
 */
function extractDistrictFromAddress($address) {
    if (empty($address)) return '';
    
    $parts = explode(',', $address);
    if (count($parts) > 1) {
        return trim($parts[count($parts) - 2]);
    }
    return '';
}

/**
 * Lưu tọa độ GPS (tùy chọn - nếu có bảng riêng)
 */
function saveGPSCoordinates($conn, $address_id, $coordinates) {
    // Kiểm tra xem có bảng address_coordinates không
    $check_table = $conn->query("SHOW TABLES LIKE 'address_coordinates'");
    
    if ($check_table && $check_table->num_rows > 0) {
        $stmt = $conn->prepare("INSERT INTO address_coordinates (address_id, latitude, longitude) 
                                VALUES (?, ?, ?) 
                                ON DUPLICATE KEY UPDATE latitude = ?, longitude = ?");
        $lat = floatval($coordinates['latitude']);
        $lng = floatval($coordinates['longitude']);
        $stmt->bind_param('idddd', $address_id, $lat, $lng, $lat, $lng);
        $stmt->execute();
    }
}

$conn->close();
?>

