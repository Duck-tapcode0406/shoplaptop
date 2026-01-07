<?php
/**
 * API Endpoint để xử lý xác nhận vị trí và cập nhật thông tin giao hàng
 * Sử dụng cấu trúc dữ liệu SerpAPI Google Maps
 */

require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/db.php';
header('Content-Type: application/json');

// Load config - prefer api/config.php, fallback to includes/config.php
if (file_exists(__DIR__ . '/config.php')) {
    require_once __DIR__ . '/config.php';
} elseif (file_exists(__DIR__ . '/../includes/config.php')) {
    require_once __DIR__ . '/../includes/config.php';
}

// Lấy SERPAPI_KEY từ env hoặc constant
$serpApiKey = getenv('SERPAPI_KEY') ?: (defined('SERPAPI_KEY') ? SERPAPI_KEY : '');

// Kiểm tra key có hợp lệ không
if (empty($serpApiKey) || $serpApiKey === 'YOUR_SERPAPI_KEY_HERE' || $serpApiKey === 'cdabba37434cb5ff0d99dc4ea1addfcf137eef3fcbb6ee1c0e705f7ba2dd3ab6') {
    // Log lỗi nếu DEBUG_MODE
    if (defined('DEBUG_MODE') && DEBUG_MODE) {
        error_log('SERPAPI_KEY not configured or using placeholder');
    }
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
            // Validate input
            if (!isset($input['latitude']) || !isset($input['longitude'])) {
                throw new Exception('Thiếu tọa độ GPS (latitude, longitude)');
            }
            
            $latitude = floatval($input['latitude']);
            $longitude = floatval($input['longitude']);
            
            // Validate coordinates
            if (!is_numeric($latitude) || !is_numeric($longitude)) {
                throw new Exception('Tọa độ GPS không hợp lệ');
            }
            
            if ($latitude < -90 || $latitude > 90 || $longitude < -180 || $longitude > 180) {
                throw new Exception('Tọa độ GPS nằm ngoài phạm vi hợp lệ');
            }
            
            $query = isset($input['query']) ? $input['query'] : 'Coffee';
            $ll = isset($input['ll']) ? $input['ll'] : "@{$latitude},{$longitude},14z";
            
            // Kiểm tra SERPAPI_KEY trước khi gọi
            global $serpApiKey;
            if (empty($serpApiKey) || $serpApiKey === 'YOUR_SERPAPI_KEY_HERE' || $serpApiKey === 'cdabba37434cb5ff0d99dc4ea1addfcf137eef3fcbb6ee1c0e705f7ba2dd3ab6') {
                http_response_code(500);
                echo json_encode([
                    'success' => false,
                    'message' => 'SERPAPI_KEY chưa được cấu hình. Vui lòng tạo file api/config.php và thêm SERPAPI_KEY.',
                    'places' => []
                ]);
                exit();
            }
            
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

            // Trích xuất thông tin từ location data - CHỈ LẤY VỊ TRÍ
            $address = isset($location['address']) ? trim($location['address']) : '';
            $city = isset($location['city']) ? trim($location['city']) : extractCityFromAddress($address);
            $district = isset($location['district']) ? trim($location['district']) : extractDistrictFromAddress($address);
            $ward = isset($location['ward']) ? trim($location['ward']) : extractWardFromAddress($address);
            
            // Tách địa chỉ thành address_line1 (địa chỉ nhỏ) và address (địa chỉ bổ sung)
            $address_line1 = '';
            $address_remaining = '';
            if (!empty($address)) {
                $addressParts = explode(',', $address);
                if (count($addressParts) > 0) {
                    $address_line1 = trim($addressParts[0]); // Phần đầu: số nhà, tên đường
                    if (count($addressParts) > 1) {
                        // Phần còn lại (bỏ phần cuối là thành phố/quận)
                        $address_remaining = trim(implode(', ', array_slice($addressParts, 1, -2)));
                    }
                }
            }
            
            // Lấy thông tin người dùng hiện tại (tên và số điện thoại từ thông tin cá nhân)
            $user_query = $conn->prepare("SELECT familyname, firstname, phone FROM user WHERE id = ?");
            $user_query->bind_param('i', $user_id);
            $user_query->execute();
            $user_result = $user_query->get_result();
            $user = $user_result->fetch_assoc();
            
            // Tên và số điện thoại lấy từ thông tin cá nhân, không từ location
            $full_name = trim(($user['familyname'] ?? '') . ' ' . ($user['firstname'] ?? ''));
            $phone = $user['phone'] ?? '';
            
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
                (user_id, full_name, phone, address_line1, address, ward, district, city, postal_code, is_default) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            
            $postal_code = isset($location['postal_code']) ? trim($location['postal_code']) : ''; // Có thể lấy từ API nếu có
            
            $stmt->bind_param('issssssssi', 
                $user_id, 
                $full_name, 
                $phone, 
                $address_line1, 
                $address_remaining, 
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
    // Đảm bảo luôn trả về JSON, ngay cả khi có lỗi
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
    exit();
}

/**
 * Tìm địa điểm gần nhất sử dụng SerpAPI Google Maps
 * 
 * Tham số API SerpAPI:
 * - engine: "google_maps" (bắt buộc)
 * - q: truy vấn tìm kiếm (ví dụ: "Coffee", "Restaurant")
 * - ll: tọa độ GPS format "@latitude,longitude,zoom" (ví dụ: "@40.7455096,-74.0083012,14z")
 * - type: "search" (mặc định) hoặc "place"
 * - api_key: SerpAPI key (bắt buộc)
 * - hl: ngôn ngữ (vi, en, ...)
 * - gl: quốc gia (vn, us, ...)
 * - nearby: true (khuyến nghị khi dùng "near me" trong query)
 * 
 * Response structure:
 * - local_results: mảng các địa điểm
 *   - title: tên địa điểm
 *   - address: địa chỉ đầy đủ
 *   - gps_coordinates: {latitude, longitude}
 *   - place_id: Google Place ID
 *   - phone: số điện thoại
 *   - rating: điểm đánh giá
 *   - reviews: số lượng đánh giá
 */
function findNearbyPlaces($latitude, $longitude, $query = 'Coffee', $ll = '') {
    global $serpApiKey;
    
    // Lấy cấu hình từ config
    if (empty($serpApiKey)) {
        $serpApiKey = getenv('SERPAPI_KEY') ?: (defined('SERPAPI_KEY') ? SERPAPI_KEY : '');
    }
    
    $endpoint = defined('SERPAPI_ENDPOINT') ? SERPAPI_ENDPOINT : 'https://serpapi.com/search.json';
    $defaultQuery = defined('DEFAULT_SEARCH_QUERY') ? DEFAULT_SEARCH_QUERY : 'Coffee';
    $language = defined('SERPAPI_LANGUAGE') ? SERPAPI_LANGUAGE : 'vi';
    $country = defined('SERPAPI_COUNTRY') ? SERPAPI_COUNTRY : 'vn';
    $timeout = defined('API_TIMEOUT') ? API_TIMEOUT : 10;
    
    $query = $query ?: $defaultQuery;
    
    // Validate coordinates
    if (!is_numeric($latitude) || !is_numeric($longitude)) {
        error_log('Invalid coordinates: ' . $latitude . ', ' . $longitude);
        return [];
    }
    
    // Nếu không có API key hợp lệ, trả về lỗi thay vì dữ liệu mẫu
    if (empty($serpApiKey) || $serpApiKey === 'YOUR_SERPAPI_KEY_HERE' || $serpApiKey === 'cdabba37434cb5ff0d99dc4ea1addfcf137eef3fcbb6ee1c0e705f7ba2dd3ab6') {
        error_log('SERPAPI_KEY not configured or using placeholder');
        if (defined('DEBUG_MODE') && DEBUG_MODE) {
            // Chỉ trả về sample data trong DEBUG_MODE
            return getSamplePlaces($latitude, $longitude);
        }
        return [];
    }
    
    // Tạo tham số ll nếu chưa có
    if (empty($ll)) {
        $ll = "@{$latitude},{$longitude},14z";
    }
    
    // Chuẩn bị tham số API
    $params = [
        'engine' => 'google_maps',
        'q' => $query,
        'll' => $ll,
        'type' => 'search',
        'api_key' => $serpApiKey,
        'hl' => $language,
        'gl' => $country
    ];
    
    // Nếu query có "near me", thêm tham số nearby
    if (stripos($query, 'near me') !== false) {
        $params['nearby'] = 'true';
    }
    
    // Gọi SerpAPI
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $endpoint . '?' . http_build_query($params));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    curl_close($ch);
    
    if ($httpCode === 200 && $response) {
        $data = json_decode($response, true);
        
        // Kiểm tra lỗi từ SerpAPI
        if (isset($data['error'])) {
            error_log('SerpAPI Error: ' . json_encode($data['error']));
            if (defined('DEBUG_MODE') && DEBUG_MODE) {
                return getSamplePlaces($latitude, $longitude);
            }
            return [];
        }
        
        if (isset($data['local_results']) && is_array($data['local_results']) && count($data['local_results']) > 0) {
            // Chuẩn hóa dữ liệu từ SerpAPI
            $places = [];
            foreach ($data['local_results'] as $place) {
                $places[] = normalizePlaceData($place);
            }
            return $places;
        }
    } else {
        error_log("SerpAPI HTTP Error: {$httpCode}, cURL Error: {$curlError}, Response: " . substr($response, 0, 200));
        if (defined('DEBUG_MODE') && DEBUG_MODE) {
            return getSamplePlaces($latitude, $longitude);
        }
    }
    
    // Không fallback mặc định - trả về mảng rỗng
    return [];
}

/**
 * Chuẩn hóa dữ liệu địa điểm từ SerpAPI
 * 
 * Các trường quan trọng từ SerpAPI response:
 * - title: Tên địa điểm
 * - address: Địa chỉ đầy đủ
 * - gps_coordinates: {latitude, longitude}
 * - place_id: Google Place ID
 * - phone: Số điện thoại
 * - rating: Điểm đánh giá (0-5)
 * - reviews: Số lượng đánh giá
 * - type: Loại địa điểm
 * - data_id: Data ID của Google
 * - data_cid: Customer ID của Google
 */
function normalizePlaceData($place) {
    // Xử lý GPS coordinates
    $gps = $place['gps_coordinates'] ?? [];
    $latitude = $gps['latitude'] ?? null;
    $longitude = $gps['longitude'] ?? null;
    
    return [
        'title' => $place['title'] ?? '',
        'address' => $place['address'] ?? '',
        'phone' => $place['phone'] ?? '',
        'rating' => isset($place['rating']) ? floatval($place['rating']) : null,
        'reviews' => isset($place['reviews']) ? intval($place['reviews']) : 0,
        'gps_coordinates' => [
            'latitude' => $latitude ? floatval($latitude) : null,
            'longitude' => $longitude ? floatval($longitude) : null
        ],
        'type' => $place['type'] ?? '',
        'place_id' => $place['place_id'] ?? '',
        'data_id' => $place['data_id'] ?? '',
        'data_cid' => $place['data_cid'] ?? '',
        'website' => $place['website'] ?? '',
        'price' => $place['price'] ?? '',
        'open_state' => $place['open_state'] ?? '',
        'hours' => $place['hours'] ?? ''
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
 * Trích xuất phường/xã từ địa chỉ
 */
function extractWardFromAddress($address) {
    if (empty($address)) return '';
    
    $parts = explode(',', $address);
    if (count($parts) > 2) {
        return trim($parts[count($parts) - 3]);
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

