/**
 * File JavaScript untuk deteksi Fake GPS dan validasi lokasi
 */

// Fungsi untuk mendeteksi kemungkinan mock location
function checkMockLocation(position) {
    // Di Android, mock location biasanya memiliki nilai altitude dan accuracy yang aneh
    if (position.coords.altitude === 0 && position.coords.accuracy === 0) {
        return true; // Kemungkinan fake GPS
    }
    
    // Periksa juga nilai altitudeAccuracy yang tidak wajar
    if (position.coords.altitudeAccuracy === 0 || position.coords.altitudeAccuracy === null) {
        return true;
    }
    
    // Kecepatan 0 tapi accuracy sempurna juga mencurigakan
    if (position.coords.speed === 0 && position.coords.accuracy < 10) {
        return true;
    }
    
    return false;
}

// Fungsi untuk mengumpulkan informasi perangkat
function collectDeviceInfo() {
    const deviceInfo = {
        userAgent: navigator.userAgent,
        screenResolution: `${screen.width}x${screen.height}`,
        timezone: Intl.DateTimeFormat().resolvedOptions().timeZone,
        language: navigator.language,
        platform: navigator.platform,
        cores: navigator.hardwareConcurrency || 'unknown',
        devicePixelRatio: window.devicePixelRatio || 'unknown'
    };
    
    return deviceInfo;
}

// Fungsi untuk mendapatkan lokasi dengan opsi keamanan tinggi
function getLocationSecure(onSuccess, onError) {
    if (!navigator.geolocation) {
        if (onError) onError(new Error('Browser tidak mendukung geolokasi'));
        return;
    }
    
    navigator.geolocation.getCurrentPosition(
        (position) => {
            // Periksa apakah lokasi palsu
            const isMockLocation = checkMockLocation(position);
            
            // Kumpulkan info perangkat
            const deviceInfo = collectDeviceInfo();
            
            if (onSuccess) {
                onSuccess({
                    position: position,
                    isMockLocation: isMockLocation,
                    deviceInfo: deviceInfo
                });
            }
        },
        (error) => {
            if (onError) onError(error);
        },
        {
            enableHighAccuracy: true,
            timeout: 10000,
            maximumAge: 0
        }
    );
}

// Export fungsi-fungsi untuk digunakan di halaman lain
export {
    checkMockLocation,
    collectDeviceInfo,
    getLocationSecure
}; 