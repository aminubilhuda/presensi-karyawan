@extends('layouts.app')

@section('title', 'Absen Pulang')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-sign-out-alt me-2"></i> Absen Pulang</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('attendance.check-out') }}" method="POST" id="attendanceForm">
                        @csrf
                        
                        <!-- Camera Preview -->
                        <div class="mb-4 text-center">
                            <div class="camera-container mb-3">
                                <video id="camera" class="rounded border w-100" style="max-height: 300px; display: none;"></video>
                                <canvas id="canvas" class="rounded border w-100" style="max-height: 300px; display: none;"></canvas>
                                <img id="photoPreview" class="rounded border w-100" style="max-height: 300px; display: none;">
                            </div>
                            
                            <div class="d-flex justify-content-center gap-2">
                                <button type="button" id="startCamera" class="btn btn-outline-primary">
                                    <i class="fas fa-camera me-2"></i> Buka Kamera
                                </button>
                                <button type="button" id="takePhoto" class="btn btn-primary" style="display: none;">
                                    <i class="fas fa-camera me-2"></i> Ambil Foto
                                </button>
                                <button type="button" id="retakePhoto" class="btn btn-outline-secondary" style="display: none;">
                                    <i class="fas fa-redo me-2"></i> Ambil Ulang
                                </button>
                            </div>
                        </div>
                        
                        <!-- Hidden inputs for photo and location -->
                        <input type="hidden" name="photo" id="photoInput">
                        <input type="hidden" name="latitude" id="latitude">
                        <input type="hidden" name="longitude" id="longitude">
                        
                        <!-- Map -->
                        <div class="mb-4">
                            <label class="form-label">Lokasi Anda</label>
                            <div id="map" class="rounded" style="height: 300px;"></div>
                            <div class="mt-2">
                                <div id="locationStatus" class="text-muted">Mendapatkan lokasi Anda...</div>
                            </div>
                        </div>
                        
                        <div class="d-grid">
                            <button type="button" id="submitBtn" class="btn btn-primary" disabled>
                                <i class="fas fa-sign-out-alt me-2"></i> Absen Pulang
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
    let map, marker, circle;
    let userLocation = null;
    let photoTaken = false;
    let locationFound = false;
    let cameraStream = null;
    let allowedLocations = @json($locations);
    
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize map
        map = L.map('map').setView([-6.2088, 106.8456], 13); // Default to Jakarta
        
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
        }).addTo(map);
        
        // Get user location
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(
                function(position) {
                    userLocation = {
                        lat: position.coords.latitude,
                        lng: position.coords.longitude
                    };
                    
                    // Set form values
                    document.getElementById('latitude').value = userLocation.lat;
                    document.getElementById('longitude').value = userLocation.lng;
                    
                    // Update map
                    map.setView([userLocation.lat, userLocation.lng], 15);
                    
                    // Add marker
                    if (marker) map.removeLayer(marker);
                    marker = L.marker([userLocation.lat, userLocation.lng]).addTo(map)
                        .bindPopup('Lokasi Anda').openPopup();
                    
                    // Check if user is within allowed locations
                    let inAllowedLocation = checkIfInAllowedLocation(userLocation);
                    locationFound = true;
                    
                    let statusText = inAllowedLocation 
                        ? 'Anda berada di area yang diizinkan untuk absensi.'
                        : 'Anda berada di luar area yang diizinkan untuk absensi.';
                    
                    let statusClass = inAllowedLocation ? 'text-success' : 'text-danger';
                    
                    document.getElementById('locationStatus').className = statusClass;
                    document.getElementById('locationStatus').innerText = statusText;
                    
                    // Display allowed locations on map
                    displayAllowedLocations();
                    
                    checkSubmitButtonState();
                },
                function(error) {
                    document.getElementById('locationStatus').innerText = 'Gagal mendapatkan lokasi Anda: ' + error.message;
                    document.getElementById('locationStatus').className = 'text-danger';
                }
            );
        } else {
            document.getElementById('locationStatus').innerText = 'Browser Anda tidak mendukung geolokasi.';
            document.getElementById('locationStatus').className = 'text-danger';
        }
        
        // Camera handling
        document.getElementById('startCamera').addEventListener('click', startCamera);
        document.getElementById('takePhoto').addEventListener('click', takePhoto);
        document.getElementById('retakePhoto').addEventListener('click', retakePhoto);
        document.getElementById('submitBtn').addEventListener('click', submitForm);
    });
    
    function startCamera() {
        const video = document.getElementById('camera');
        const startBtn = document.getElementById('startCamera');
        const takeBtn = document.getElementById('takePhoto');
        
        // Get user media
        navigator.mediaDevices.getUserMedia({ video: true })
            .then(function(stream) {
                video.srcObject = stream;
                cameraStream = stream;
                video.style.display = 'block';
                video.play();
                
                startBtn.style.display = 'none';
                takeBtn.style.display = 'inline-block';
            })
            .catch(function(err) {
                alert('Tidak dapat mengakses kamera: ' + err.message);
            });
    }
    
    function takePhoto() {
        const video = document.getElementById('camera');
        const canvas = document.getElementById('canvas');
        const photo = document.getElementById('photoPreview');
        const takeBtn = document.getElementById('takePhoto');
        const retakeBtn = document.getElementById('retakePhoto');
        
        // Set canvas dimensions to match video
        canvas.width = video.videoWidth;
        canvas.height = video.videoHeight;
        
        // Draw video frame to canvas
        const context = canvas.getContext('2d');
        context.drawImage(video, 0, 0, canvas.width, canvas.height);
        
        // Convert canvas to data URL
        const dataURL = canvas.toDataURL('image/jpeg');
        
        // Set the photo input value
        document.getElementById('photoInput').value = dataURL;
        
        // Show preview
        photo.src = dataURL;
        photo.style.display = 'block';
        video.style.display = 'none';
        
        // Update buttons
        takeBtn.style.display = 'none';
        retakeBtn.style.display = 'inline-block';
        
        // Stop camera stream
        if (cameraStream) {
            cameraStream.getTracks().forEach(track => track.stop());
        }
        
        photoTaken = true;
        checkSubmitButtonState();
    }
    
    function retakePhoto() {
        const video = document.getElementById('camera');
        const photo = document.getElementById('photoPreview');
        const takeBtn = document.getElementById('takePhoto');
        const retakeBtn = document.getElementById('retakePhoto');
        
        // Reset photo
        photo.style.display = 'none';
        document.getElementById('photoInput').value = '';
        
        // Restart camera
        photoTaken = false;
        checkSubmitButtonState();
        startCamera();
    }
    
    function displayAllowedLocations() {
        // Remove previous circles
        if (circle) {
            map.removeLayer(circle);
        }
        
        // Add allowed locations as circles
        allowedLocations.forEach(function(location) {
            let locationCircle = L.circle([location.latitude, location.longitude], {
                color: 'green',
                fillColor: '#0d6efd',
                fillOpacity: 0.2,
                radius: location.radius
            }).addTo(map);
            
            locationCircle.bindPopup(location.name);
        });
    }
    
    function checkIfInAllowedLocation(userLocation) {
        if (!userLocation) return false;
        
        // Check each allowed location
        for (let i = 0; i < allowedLocations.length; i++) {
            let location = allowedLocations[i];
            let distance = calculateDistance(
                userLocation.lat, 
                userLocation.lng, 
                location.latitude, 
                location.longitude
            );
            
            if (distance <= location.radius) {
                return true;
            }
        }
        
        return false;
    }
    
    function calculateDistance(lat1, lon1, lat2, lon2) {
        const R = 6371e3; // Earth radius in meters
        const φ1 = lat1 * Math.PI/180;
        const φ2 = lat2 * Math.PI/180;
        const Δφ = (lat2-lat1) * Math.PI/180;
        const Δλ = (lon2-lon1) * Math.PI/180;
        
        const a = Math.sin(Δφ/2) * Math.sin(Δφ/2) +
                  Math.cos(φ1) * Math.cos(φ2) *
                  Math.sin(Δλ/2) * Math.sin(Δλ/2);
        const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
        
        return R * c; // Distance in meters
    }
    
    function checkSubmitButtonState() {
        const submitBtn = document.getElementById('submitBtn');
        submitBtn.disabled = !(photoTaken && locationFound);
    }
    
    function submitForm() {
        if (photoTaken && locationFound) {
            // Create a FormData object
            const form = document.getElementById('attendanceForm');
            const formData = new FormData(form);
            
            // Convert base64 string to blob
            const base64String = document.getElementById('photoInput').value;
            const byteString = atob(base64String.split(',')[1]);
            const mimeString = base64String.split(',')[0].split(':')[1].split(';')[0];
            const ab = new ArrayBuffer(byteString.length);
            const ia = new Uint8Array(ab);
            
            for (let i = 0; i < byteString.length; i++) {
                ia[i] = byteString.charCodeAt(i);
            }
            
            const blob = new Blob([ab], {type: mimeString});
            formData.set('photo', blob, 'photo.jpg');
            
            // Remove the photoInput value from the form data
            formData.delete('photoInput');
            
            // Submit the form
            form.submit();
        }
    }
</script>
@endsection 