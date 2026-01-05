/**
 * Google Maps Address Picker
 * Cho phép chọn địa chỉ trên Google Maps với Places Autocomplete
 */

class GoogleMapPicker {
    constructor(options = {}) {
        this.mapContainer = options.mapContainer || 'map-picker-container';
        this.searchInput = options.searchInput || 'map-search-input';
        this.onAddressSelected = options.onAddressSelected || null;
        this.map = null;
        this.marker = null;
        this.geocoder = null;
        this.autocomplete = null;
        this.currentLocation = null;
        this.apiKey = options.apiKey || '';
    }

    /**
     * Khởi tạo Google Maps
     */
    async init() {
        // Kiểm tra Google Maps API đã được load chưa
        if (typeof google === 'undefined' || !google.maps) {
            await this.loadGoogleMapsAPI();
        }

        // Lấy vị trí hiện tại
        try {
            this.currentLocation = await this.getCurrentLocation();
        } catch (error) {
            console.warn('Không thể lấy vị trí hiện tại:', error);
            // Mặc định là Hồ Chí Minh
            this.currentLocation = {
                latitude: 10.762622,
                longitude: 106.660172
            };
        }

        // Khởi tạo map
        this.initMap();
        
        // Khởi tạo Places Autocomplete
        this.initAutocomplete();
        
        // Khởi tạo Geocoder
        this.geocoder = new google.maps.Geocoder();
    }

    /**
     * Load Google Maps API
     */
    loadGoogleMapsAPI() {
        return new Promise((resolve, reject) => {
            if (typeof google !== 'undefined' && google.maps) {
                resolve();
                return;
            }

            if (!this.apiKey || this.apiKey === '') {
                reject(new Error('Google Maps API Key không được cấu hình'));
                return;
            }

            const script = document.createElement('script');
            script.src = `https://maps.googleapis.com/maps/api/js?key=${this.apiKey}&libraries=places&language=vi&region=VN`;
            script.async = true;
            script.defer = true;
            script.onload = () => {
                console.log('Google Maps API đã được load thành công');
                resolve();
            };
            script.onerror = () => {
                console.error('Lỗi khi load Google Maps API. Kiểm tra API Key và network.');
                reject(new Error('Không thể load Google Maps API. Vui lòng kiểm tra API Key và kết nối mạng.'));
            };
            document.head.appendChild(script);
        });
    }

    /**
     * Lấy vị trí hiện tại
     */
    getCurrentLocation() {
        return new Promise((resolve, reject) => {
            if (!navigator.geolocation) {
                reject(new Error('Trình duyệt không hỗ trợ định vị'));
                return;
            }

            navigator.geolocation.getCurrentPosition(
                (position) => {
                    resolve({
                        latitude: position.coords.latitude,
                        longitude: position.coords.longitude
                    });
                },
                (error) => {
                    reject(error);
                },
                {
                    enableHighAccuracy: true,
                    timeout: 10000,
                    maximumAge: 0
                }
            );
        });
    }

    /**
     * Khởi tạo bản đồ
     */
    initMap() {
        const container = document.getElementById(this.mapContainer);
        if (!container) {
            console.error('Không tìm thấy container map:', this.mapContainer);
            return;
        }

        // Kiểm tra container có kích thước không
        if (container.offsetWidth === 0 || container.offsetHeight === 0) {
            console.warn('Container map chưa có kích thước, đợi 100ms...');
            setTimeout(() => this.initMap(), 100);
            return;
        }

        const center = {
            lat: this.currentLocation.latitude,
            lng: this.currentLocation.longitude
        };

        console.log('Khởi tạo map tại:', center, 'Container size:', container.offsetWidth, 'x', container.offsetHeight);

        // Tạo map
        try {
            this.map = new google.maps.Map(container, {
                center: center,
                zoom: 15,
                mapTypeControl: true,
                streetViewControl: true,
                fullscreenControl: true
            });

            console.log('Map đã được tạo thành công');

            // Tạo marker ban đầu tại vị trí hiện tại
            this.marker = new google.maps.Marker({
                map: this.map,
                position: center,
                draggable: true,
                animation: google.maps.Animation.DROP,
                title: 'Vị trí của bạn'
            });

            // Lấy địa chỉ tại vị trí hiện tại
            this.geocodePosition(center);

            // Xử lý khi click vào map
            this.map.addListener('click', (event) => {
                this.setMarkerPosition(event.latLng);
                this.geocodePosition(event.latLng);
            });

            // Xử lý khi kéo marker
            this.marker.addListener('dragend', (event) => {
                this.geocodePosition(event.latLng);
            });
        } catch (error) {
            console.error('Lỗi khi tạo map:', error);
            throw error;
        }
    }

    /**
     * Khởi tạo Places Autocomplete
     */
    initAutocomplete() {
        const input = document.getElementById(this.searchInput);
        if (!input) {
            console.error('Không tìm thấy input search:', this.searchInput);
            return;
        }

        // Tạo Autocomplete
        this.autocomplete = new google.maps.places.Autocomplete(input, {
            componentRestrictions: { country: 'vn' },
            fields: ['geometry', 'formatted_address', 'address_components', 'place_id'],
            types: ['address']
        });

        // Xử lý khi chọn địa chỉ từ autocomplete
        this.autocomplete.addListener('place_changed', () => {
            const place = this.autocomplete.getPlace();
            if (!place.geometry) {
                console.warn('Không tìm thấy geometry cho địa chỉ này');
                return;
            }

            // Di chuyển map và marker đến địa chỉ đã chọn
            this.map.setCenter(place.geometry.location);
            this.map.setZoom(17);
            this.setMarkerPosition(place.geometry.location);
            
            // Xử lý địa chỉ
            this.handleAddressSelection(place);
        });
    }

    /**
     * Đặt vị trí marker
     */
    setMarkerPosition(latLng) {
        if (this.marker) {
            this.marker.setPosition(latLng);
            this.map.setCenter(latLng);
        }
    }

    /**
     * Geocode vị trí thành địa chỉ
     */
    geocodePosition(latLng) {
        if (!this.geocoder) {
            console.warn('Geocoder chưa được khởi tạo');
            return;
        }

        this.geocoder.geocode({ location: latLng }, (results, status) => {
            if (status === 'OK' && results[0]) {
                const place = {
                    geometry: {
                        location: latLng
                    },
                    formatted_address: results[0].formatted_address,
                    address_components: results[0].address_components,
                    place_id: results[0].place_id
                };
                this.handleAddressSelection(place);
            } else if (status === 'REQUEST_DENIED') {
                console.error('Geocoder REQUEST_DENIED - Có thể do:');
                console.error('1. Geocoding API chưa được bật trong Google Cloud Console');
                console.error('2. API Key restrictions chưa đúng');
                console.error('3. Billing account chưa được kích hoạt');
                // Vẫn cho phép chọn vị trí dù không có địa chỉ
                const place = {
                    geometry: {
                        location: latLng
                    },
                    formatted_address: latLng.lat() + ', ' + latLng.lng(),
                    address_components: [],
                    place_id: null
                };
                this.handleAddressSelection(place);
            } else {
                console.warn('Geocoder failed:', status);
                // Vẫn cho phép chọn vị trí dù không có địa chỉ
                const place = {
                    geometry: {
                        location: latLng
                    },
                    formatted_address: latLng.lat() + ', ' + latLng.lng(),
                    address_components: [],
                    place_id: null
                };
                this.handleAddressSelection(place);
            }
        });
    }

    /**
     * Xử lý khi chọn địa chỉ
     */
    handleAddressSelection(place) {
        if (!place) return;

        // Parse address components
        const addressData = this.parseAddressComponents(place.address_components || []);
        
        // Tạo address_line1 từ street_number và route
        let address_line1 = '';
        if (addressData.street_number && addressData.route) {
            address_line1 = (addressData.street_number + ' ' + addressData.route).trim();
        } else if (addressData.route) {
            address_line1 = addressData.route.trim();
        } else if (addressData.street_number) {
            address_line1 = addressData.street_number.trim();
        }
        
        const addressInfo = {
            formatted_address: place.formatted_address || '',
            address_line1: address_line1,
            address: addressData.sublocality || addressData.neighborhood || '',
            ward: addressData.ward || addressData.sublocality_level_1 || '',
            district: addressData.administrative_area_level_2 || addressData.sublocality_level_2 || '',
            city: addressData.administrative_area_level_1 || addressData.locality || '',
            postal_code: addressData.postal_code || '',
            latitude: place.geometry.location.lat(),
            longitude: place.geometry.location.lng()
        };

        // Gọi callback
        if (this.onAddressSelected) {
            this.onAddressSelected(addressInfo);
        }
    }

    /**
     * Parse address components từ Google Places
     */
    parseAddressComponents(components) {
        const addressData = {
            street_number: '',
            route: '',
            ward: '',
            district: '',
            city: '',
            postal_code: '',
            sublocality: '',
            neighborhood: '',
            administrative_area_level_1: '',
            administrative_area_level_2: '',
            locality: '',
            sublocality_level_1: '',
            sublocality_level_2: ''
        };

        components.forEach(component => {
            const types = component.types;
            
            if (types.includes('street_number')) {
                addressData.street_number = component.long_name;
            } else if (types.includes('route')) {
                addressData.route = component.long_name;
            } else if (types.includes('postal_code')) {
                addressData.postal_code = component.long_name;
            } else if (types.includes('administrative_area_level_1')) {
                addressData.administrative_area_level_1 = component.long_name;
            } else if (types.includes('administrative_area_level_2')) {
                addressData.administrative_area_level_2 = component.long_name;
            } else if (types.includes('locality')) {
                addressData.locality = component.long_name;
            } else if (types.includes('sublocality') || types.includes('sublocality_level_1')) {
                addressData.sublocality = component.long_name;
                addressData.sublocality_level_1 = component.long_name;
            } else if (types.includes('sublocality_level_2')) {
                addressData.sublocality_level_2 = component.long_name;
            } else if (types.includes('neighborhood')) {
                addressData.neighborhood = component.long_name;
            }
        });

        return addressData;
    }

    /**
     * Đặt vị trí hiện tại làm center
     */
    setCurrentLocation() {
        if (this.currentLocation && this.map) {
            const center = {
                lat: this.currentLocation.latitude,
                lng: this.currentLocation.longitude
            };
            this.map.setCenter(center);
            this.map.setZoom(15);
            this.setMarkerPosition(center);
            this.geocodePosition(center);
        }
    }
}

// Export
if (typeof module !== 'undefined' && module.exports) {
    module.exports = GoogleMapPicker;
}

