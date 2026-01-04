/**
 * Location Verification System
 * Sử dụng browser geolocation API để xác nhận vị trí khách hàng
 * và cập nhật thông tin giao hàng dựa trên SerpAPI Google Maps data
 */

class LocationVerification {
    constructor(options = {}) {
        this.apiEndpoint = options.apiEndpoint || 'api/update_location.php';
        this.serpApiKey = options.serpApiKey || '';
        this.onSuccess = options.onSuccess || null;
        this.onError = options.onError || null;
        this.onLocationFound = options.onLocationFound || null;
    }

    /**
     * Lấy vị trí hiện tại của người dùng
     */
    getCurrentLocation() {
        return new Promise((resolve, reject) => {
            if (!navigator.geolocation) {
                reject(new Error('Trình duyệt không hỗ trợ định vị'));
                return;
            }

            const options = {
                enableHighAccuracy: true,
                timeout: 15000, // Tăng timeout lên 15s
                maximumAge: 60000 // Cache 1 phút
            };

            // Timeout backup để đảm bảo cleanup
            const timeoutId = setTimeout(() => {
                reject(new Error('Yêu cầu lấy vị trí hết thời gian chờ'));
            }, 15000);

            navigator.geolocation.getCurrentPosition(
                (position) => {
                    clearTimeout(timeoutId);
                    const location = {
                        latitude: position.coords.latitude,
                        longitude: position.coords.longitude,
                        accuracy: position.coords.accuracy
                    };
                    resolve(location);
                },
                (error) => {
                    clearTimeout(timeoutId);
                    let errorMessage = 'Không thể lấy vị trí';
                    switch(error.code) {
                        case error.PERMISSION_DENIED:
                            errorMessage = 'Người dùng từ chối quyền truy cập vị trí';
                            break;
                        case error.POSITION_UNAVAILABLE:
                            errorMessage = 'Thông tin vị trí không khả dụng';
                            break;
                        case error.TIMEOUT:
                            errorMessage = 'Yêu cầu lấy vị trí hết thời gian chờ';
                            break;
                    }
                    reject(new Error(errorMessage));
                },
                options
            );
        });
    }

    /**
     * Tìm địa điểm gần nhất dựa trên tọa độ GPS
     * Sử dụng cấu trúc dữ liệu SerpAPI
     */
    findNearbyPlaces(latitude, longitude, searchQuery = '') {
        return new Promise((resolve, reject) => {
            // Tạo URL tìm kiếm Google Maps với tọa độ
            const ll = `@${latitude},${longitude},14z`;
            const query = searchQuery || 'Coffee'; // Mặc định tìm quán cà phê
            
            // Gọi API để tìm địa điểm gần nhất
            fetch(this.apiEndpoint, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    action: 'find_nearby',
                    latitude: latitude,
                    longitude: longitude,
                    query: query,
                    ll: ll
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    resolve(data.places);
                } else {
                    reject(new Error(data.message || 'Không tìm thấy địa điểm gần đây'));
                }
            })
            .catch(error => {
                reject(error);
            });
        });
    }

    /**
     * Cập nhật địa chỉ giao hàng dựa trên vị trí
     */
    updateShippingAddress(locationData) {
        return new Promise((resolve, reject) => {
            fetch(this.apiEndpoint, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    action: 'update_address',
                    location: locationData
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    if (this.onSuccess) {
                        this.onSuccess(data);
                    }
                    resolve(data);
                } else {
                    if (this.onError) {
                        this.onError(data.message);
                    }
                    reject(new Error(data.message));
                }
            })
            .catch(error => {
                if (this.onError) {
                    this.onError(error.message);
                }
                reject(error);
            });
        });
    }

    /**
     * Xác nhận và cập nhật vị trí tự động
     */
    async verifyAndUpdate() {
        try {
            // Hiển thị loading
            this.showLoading('Đang lấy vị trí của bạn...');

            // Lấy vị trí hiện tại
            const location = await this.getCurrentLocation();
            
            this.showLoading('Đang tìm địa điểm gần nhất...');

            // Tìm địa điểm gần nhất
            const places = await this.findNearbyPlaces(
                location.latitude, 
                location.longitude
            );

            if (places && places.length > 0) {
                // Lấy địa điểm gần nhất (đầu tiên trong danh sách)
                const nearestPlace = places[0];
                
                if (this.onLocationFound) {
                    this.onLocationFound(nearestPlace, location);
                }

                // Hiển thị dialog xác nhận
                this.showLocationConfirmation(nearestPlace, location);
            } else {
                throw new Error('Không tìm thấy địa điểm gần đây');
            }
        } catch (error) {
            // Đảm bảo cleanup overlay khi có lỗi
            this.hideLoading();
            // Đợi một chút để đảm bảo overlay được remove
            setTimeout(() => {
                this.hideLoading();
            }, 100);
            this.showError(error.message);
            if (this.onError) {
                this.onError(error.message);
            }
        }
    }

    /**
     * Hiển thị dialog xác nhận vị trí
     */
    showLocationConfirmation(place, location) {
        this.hideLoading();
        
        const modal = document.createElement('div');
        modal.className = 'location-modal';
        modal.innerHTML = `
            <div class="location-modal-content">
                <div class="location-modal-header">
                    <h3><i class="fas fa-map-marker-alt"></i> Xác Nhận Vị Trí</h3>
                    <button class="location-modal-close" onclick="this.closest('.location-modal').remove()">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <div class="location-modal-body">
                    <div class="location-info">
                        <p><strong>Vị trí được phát hiện:</strong></p>
                        <div class="location-details">
                            <p><i class="fas fa-map-pin"></i> <strong>${place.title || place.tiêu_đề || 'Địa điểm'}</strong></p>
                            <p><i class="fas fa-location-dot"></i> ${place.address || place.Địa_chỉ || 'Không có địa chỉ'}</p>
                            ${place.phone || place.điện_thoại ? `<p><i class="fas fa-phone"></i> ${place.phone || place.điện_thoại}</p>` : ''}
                            ${place.rating ? `<p><i class="fas fa-star"></i> Đánh giá: ${place.rating || place.đánh_giá} / 5.0</p>` : ''}
                        </div>
                        <div class="location-coordinates">
                            <small>Tọa độ: ${location.latitude.toFixed(6)}, ${location.longitude.toFixed(6)}</small>
                        </div>
                    </div>
                    <div class="location-actions">
                        <button class="btn btn-secondary" onclick="this.closest('.location-modal').remove()">
                            <i class="fas fa-times"></i> Hủy
                        </button>
                        <button class="btn btn-primary" onclick="window.locationVerificationInstance.confirmLocation(${JSON.stringify(place).replace(/"/g, '&quot;')}, ${JSON.stringify(location).replace(/"/g, '&quot;')})">
                            <i class="fas fa-check"></i> Xác Nhận & Cập Nhật
                        </button>
                    </div>
                </div>
            </div>
        `;
        
        document.body.appendChild(modal);
        
        // Thêm styles nếu chưa có
        if (!document.getElementById('location-modal-styles')) {
            const styles = document.createElement('style');
            styles.id = 'location-modal-styles';
            styles.textContent = `
                .location-modal {
                    position: fixed;
                    top: 0;
                    left: 0;
                    width: 100%;
                    height: 100%;
                    background: rgba(0, 0, 0, 0.5);
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    z-index: 10000;
                }
                .location-modal-content {
                    background: white;
                    border-radius: 12px;
                    max-width: 500px;
                    width: 90%;
                    max-height: 90vh;
                    overflow-y: auto;
                    box-shadow: 0 10px 40px rgba(0,0,0,0.2);
                }
                .location-modal-header {
                    display: flex;
                    justify-content: space-between;
                    align-items: center;
                    padding: 20px;
                    border-bottom: 1px solid #e0e0e0;
                }
                .location-modal-header h3 {
                    margin: 0;
                    display: flex;
                    align-items: center;
                    gap: 10px;
                }
                .location-modal-close {
                    background: none;
                    border: none;
                    font-size: 20px;
                    cursor: pointer;
                    color: #666;
                }
                .location-modal-body {
                    padding: 20px;
                }
                .location-info {
                    margin-bottom: 20px;
                }
                .location-details p {
                    margin: 10px 0;
                    display: flex;
                    align-items: center;
                    gap: 10px;
                }
                .location-coordinates {
                    margin-top: 15px;
                    padding-top: 15px;
                    border-top: 1px solid #e0e0e0;
                    color: #666;
                }
                .location-actions {
                    display: flex;
                    gap: 10px;
                    justify-content: flex-end;
                }
            `;
            document.head.appendChild(styles);
        }
    }

    /**
     * Xác nhận và cập nhật địa chỉ
     */
    async confirmLocation(place, location) {
        try {
            this.showLoading('Đang cập nhật địa chỉ...');

            // Chuẩn hóa dữ liệu từ SerpAPI format
            const addressData = {
                latitude: location.latitude,
                longitude: location.longitude,
                address: place.address || place.Địa_chỉ || '',
                title: place.title || place.tiêu_đề || '',
                phone: place.phone || place.điện_thoại || '',
                city: this.extractCity(place.address || place.Địa_chỉ || ''),
                district: this.extractDistrict(place.address || place.Địa_chỉ || ''),
                gps_coordinates: {
                    latitude: place.gps_coordinates?.latitude || place['tọa độ GPS']?.vĩ_độ || location.latitude,
                    longitude: place.gps_coordinates?.longitude || place['tọa độ GPS']?.kinh_độ || location.longitude
                }
            };

            // Cập nhật địa chỉ
            const result = await this.updateShippingAddress(addressData);
            
            this.hideLoading();
            document.querySelector('.location-modal')?.remove();
            this.showSuccess('Đã cập nhật địa chỉ giao hàng thành công!');
            
            // Reload trang để hiển thị địa chỉ mới
            setTimeout(() => {
                window.location.reload();
            }, 1500);
        } catch (error) {
            this.hideLoading();
            this.showError(error.message);
        }
    }

    /**
     * Trích xuất thành phố từ địa chỉ
     */
    extractCity(address) {
        // Logic đơn giản để trích xuất thành phố
        const parts = address.split(',');
        if (parts.length > 0) {
            return parts[parts.length - 1].trim();
        }
        return '';
    }

    /**
     * Trích xuất quận/huyện từ địa chỉ
     */
    extractDistrict(address) {
        const parts = address.split(',');
        if (parts.length > 1) {
            return parts[parts.length - 2].trim();
        }
        return '';
    }

    /**
     * Hiển thị loading
     */
    showLoading(message) {
        let loading = document.getElementById('location-loading');
        if (!loading) {
            loading = document.createElement('div');
            loading.id = 'location-loading';
            loading.className = 'location-loading';
            document.body.appendChild(loading);
        }
        loading.innerHTML = `
            <div class="location-loading-content">
                <div class="location-loading-spinner"></div>
                <p>${message}</p>
            </div>
        `;
        loading.style.display = 'flex';
    }

    /**
     * Ẩn loading
     */
    hideLoading() {
        const loading = document.getElementById('location-loading');
        if (loading) {
            loading.style.display = 'none';
        }
    }

    /**
     * Hiển thị thông báo lỗi
     */
    showError(message) {
        this.showNotification(message, 'error');
    }

    /**
     * Hiển thị thông báo thành công
     */
    showSuccess(message) {
        this.showNotification(message, 'success');
    }

    /**
     * Hiển thị thông báo
     */
    showNotification(message, type = 'info') {
        const notification = document.createElement('div');
        notification.className = `location-notification location-notification-${type}`;
        notification.innerHTML = `
            <i class="fas fa-${type === 'success' ? 'check-circle' : type === 'error' ? 'exclamation-circle' : 'info-circle'}"></i>
            <span>${message}</span>
        `;
        document.body.appendChild(notification);
        
        setTimeout(() => {
            notification.classList.add('show');
        }, 100);
        
        setTimeout(() => {
            notification.classList.remove('show');
            setTimeout(() => notification.remove(), 300);
        }, 3000);
    }
}

// Thêm styles cho loading và notification
if (!document.getElementById('location-verification-styles')) {
    const styles = document.createElement('style');
    styles.id = 'location-verification-styles';
    styles.textContent = `
        .location-loading {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 9999;
        }
        .location-loading-content {
            background: white;
            padding: 30px;
            border-radius: 12px;
            text-align: center;
        }
        .location-loading-spinner {
            border: 4px solid #f3f3f3;
            border-top: 4px solid #6c5ce7;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            animation: spin 1s linear infinite;
            margin: 0 auto 15px;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        .location-notification {
            position: fixed;
            top: 20px;
            right: 20px;
            background: white;
            padding: 15px 20px;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            display: flex;
            align-items: center;
            gap: 10px;
            z-index: 10001;
            transform: translateX(400px);
            transition: transform 0.3s;
        }
        .location-notification.show {
            transform: translateX(0);
        }
        .location-notification-success {
            border-left: 4px solid #27ae60;
        }
        .location-notification-error {
            border-left: 4px solid #e74c3c;
        }
        .location-notification i {
            font-size: 20px;
        }
        .location-notification-success i {
            color: #27ae60;
        }
        .location-notification-error i {
            color: #e74c3c;
        }
    `;
    document.head.appendChild(styles);
}

// Export for use
if (typeof module !== 'undefined' && module.exports) {
    module.exports = LocationVerification;
}

(function(){
    'use strict';

    function _hideLocationLoading(){
        try{
            var loading = document.getElementById('location-loading');
            if(loading){ loading.style.display = 'none'; loading.remove(); }
            var modal = document.querySelector('.location-modal');
            if(modal) modal.remove();
            document.body.classList.remove('location-modal-open');
            document.body.style.overflow = '';
        }catch(e){ console.warn('hideLocationLoading error', e); }
    }

    function _showLocationLoading(msg){
        try{
            var loading = document.getElementById('location-loading');
            if(!loading){
                loading = document.createElement('div');
                loading.id = 'location-loading';
                loading.className = 'location-loading';
                loading.style.cssText = 'position:fixed;left:0;top:0;width:100%;height:100%;display:flex;align-items:center;justify-content:center;background:rgba(0,0,0,0.45);z-index:1050;';
                document.body.appendChild(loading);
            }
            loading.innerHTML = '<div class="location-loading-content" style="background:#fff;padding:18px;border-radius:10px;box-shadow:0 8px 30px rgba(0,0,0,.2);min-width:220px;text-align:center;"><div class="location-loading-spinner" style="width:36px;height:36px;border-radius:50%;border:4px solid #eee;border-top-color:var(--primary);"></div><p style="margin-top:12px;font-weight:600;color:#333;">'+ (msg||'Đang xử lý...') +'</p></div>';
            loading.style.display='flex';
            document.body.classList.add('location-modal-open');
            document.body.style.overflow = 'hidden';
        }catch(e){ console.warn('showLocationLoading error', e); }
    }

    document.addEventListener('keydown', function(e){
        if(e.key === 'Escape' || e.key === 'Esc'){
            _hideLocationLoading();
        }
    }, false);

    window.addEventListener('beforeunload', function(){
        _hideLocationLoading();
    });

    if(typeof LocationVerification !== 'undefined'){
        var p = LocationVerification.prototype;
        p.hideLoading = function(){ _hideLocationLoading(); };
        p.showLoading = function(msg){ _showLocationLoading(msg); };
        var origShowLocationConfirmation = p.showLocationConfirmation;
        if(typeof origShowLocationConfirmation === 'function'){
            p.showLocationConfirmation = function(place, location){
                origShowLocationConfirmation.call(this, place, location);
                setTimeout(function(){
                    var closeBtn = document.querySelector('.location-modal .location-modal-close');
                    if(closeBtn){
                        closeBtn.addEventListener('click', function(){
                            _hideLocationLoading();
                        });
                    }
                    document.querySelectorAll('.location-modal').forEach(function(m){
                        m.addEventListener('click', function(evt){
                            if(evt.target === m){
                                m.remove();
                                _hideLocationLoading();
                            }
                        });
                    });
                }, 50);
            };
        }
    }
})();

