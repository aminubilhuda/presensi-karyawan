/**
 * Script untuk Face Recognition dengan face-api.js
 */

const FaceRecognition = (function () {
    // Konfigurasi
    const config = {
        modelsPath: '/models',
        minConfidence: 0.5,
        inputSize: 320,
        scoreThreshold: 0.5,
        maxResults: 1
    };

    // Status
    let modelsLoaded = false;
    let stream = null;
    let videoElement = null;
    let canvasElement = null;
    let isCameraRunning = false;

    /**
     * Inisialisasi face-api.js
     * @returns {Promise}
     */
    async function initFaceAPI() {
        if (modelsLoaded) return Promise.resolve();

        try {
            // Ubah path model menjadi relatif terhadap root URL
            const MODEL_URL = '/models';

            // Load model face detection, landmark, dan recognition
            await faceapi.nets.tinyFaceDetector.loadFromUri(MODEL_URL);
            await faceapi.nets.faceLandmark68Net.loadFromUri(MODEL_URL);
            await faceapi.nets.faceRecognitionNet.loadFromUri(MODEL_URL);
            await faceapi.nets.faceExpressionNet.loadFromUri(MODEL_URL);

            modelsLoaded = true;
            console.log('Model face-api.js berhasil dimuat');
            return Promise.resolve();
        } catch (error) {
            console.error('Error loading models:', error);
            return Promise.reject(error);
        }
    }

    /**
     * Memulai kamera untuk face recognition
     * @param {HTMLVideoElement} video - Elemen video untuk menampilkan stream kamera
     * @param {HTMLCanvasElement} canvas - Elemen canvas untuk menampilkan deteksi
     * @returns {Promise}
     */
    async function startCamera(video, canvas) {
        if (!modelsLoaded) {
            await initFaceAPI();
        }

        // Simpan elemen
        videoElement = video;
        canvasElement = canvas;

        // Stop kamera jika sudah berjalan
        if (stream) {
            stopCamera();
        }

        try {
            // Dapatkan akses ke kamera
            stream = await navigator.mediaDevices.getUserMedia({
                video: {}
            });

            // Tampilkan stream ke elemen video
            video.srcObject = stream;

            // Tunggu video siap
            await new Promise(resolve => {
                video.onloadedmetadata = () => {
                    video.play();
                    resolve(true);
                };
            });

            isCameraRunning = true;
            return Promise.resolve();
        } catch (error) {
            console.error('Error starting camera:', error);
            return Promise.reject(error);
        }
    }

    /**
     * Menghentikan kamera
     */
    function stopCamera() {
        if (stream) {
            stream.getTracks().forEach(track => track.stop());
            stream = null;

            if (videoElement) {
                videoElement.srcObject = null;
            }

            isCameraRunning = false;
        }
    }

    /**
     * Mendeteksi wajah dari elemen video
     * @returns {Promise} - Promise yang berisi hasil deteksi
     */
    async function detectFace() {
        if (!isCameraRunning || !videoElement) {
            return Promise.reject('Camera is not running');
        }

        try {
            const options = new faceapi.TinyFaceDetectorOptions({ inputSize: 512, scoreThreshold: 0.5 });

            const detections = await faceapi.detectAllFaces(videoElement, options)
                .withFaceLandmarks()
                .withFaceDescriptors()
                .withFaceExpressions();

            // Tampilkan hasil deteksi di canvas
            const displaySize = { width: videoElement.videoWidth, height: videoElement.videoHeight };
            faceapi.matchDimensions(canvasElement, displaySize);

            const resizedDetections = faceapi.resizeResults(detections, displaySize);
            canvasElement.getContext('2d').clearRect(0, 0, canvasElement.width, canvasElement.height);
            faceapi.draw.drawDetections(canvasElement, resizedDetections);
            faceapi.draw.drawFaceLandmarks(canvasElement, resizedDetections);
            faceapi.draw.drawFaceExpressions(canvasElement, resizedDetections);

            return detections;
        } catch (error) {
            console.error('Error detecting faces:', error);
            return Promise.reject(error);
        }
    }

    /**
     * Mengambil gambar wajah dari video
     * @returns {Promise} - Promise yang berisi data gambar wajah
     */
    async function captureFace() {
        if (!isCameraRunning || !videoElement) {
            return Promise.reject('Camera is not running');
        }

        try {
            // Deteksi wajah
            const detection = await detectFace();

            if (!detection) {
                return Promise.reject('No face detected');
            }

            // Ambil region wajah
            const { box } = detection[0];

            // Buat canvas untuk crop wajah
            const tempCanvas = document.createElement('canvas');
            const context = tempCanvas.getContext('2d');

            // Tambahkan margin 20px
            const margin = 20;
            tempCanvas.width = box.width + (margin * 2);
            tempCanvas.height = box.height + (margin * 2);

            // Gambar wajah ke canvas
            context.drawImage(
                videoElement,
                box.x - margin,
                box.y - margin,
                box.width + (margin * 2),
                box.height + (margin * 2),
                0, 0,
                tempCanvas.width,
                tempCanvas.height
            );

            // Convert ke base64
            const imageData = tempCanvas.toDataURL('image/jpeg', 0.9);

            // Ambil face descriptor (untuk verifikasi)
            const descriptor = detection[0].descriptor;

            return Promise.resolve({
                imageData,
                descriptor: Array.from(descriptor), // Convert TypedArray ke Array biasa
                box
            });
        } catch (error) {
            console.error('Error capturing face:', error);
            return Promise.reject(error);
        }
    }

    /**
     * Verifikasi wajah yang terdeteksi dengan wajah yang tersimpan
     * @param {Float32Array} storedDescriptor - Descriptor wajah yang tersimpan
     * @param {Float32Array} capturedDescriptor - Descriptor wajah yang diambil
     * @returns {Object} - Hasil verifikasi { match: boolean, distance: number }
     */
    function verifyFace(storedDescriptor, capturedDescriptor) {
        if (!storedDescriptor || !capturedDescriptor) {
            return { match: false, distance: 1, message: 'Missing face descriptors' };
        }

        try {
            // Konversi ke Float32Array jika dibutuhkan
            const stored = storedDescriptor instanceof Float32Array
                ? storedDescriptor
                : new Float32Array(storedDescriptor);

            const captured = capturedDescriptor instanceof Float32Array
                ? capturedDescriptor
                : new Float32Array(capturedDescriptor);

            // Hitung jarak euclidean
            const distance = faceapi.euclideanDistance(stored, captured);

            // Jarak di bawah 0.6 biasanya cocok, semakin kecil semakin mirip
            const threshold = 0.6;
            const match = distance < threshold;

            return {
                match,
                distance,
                message: match ? 'Face verified' : 'Face does not match'
            };
        } catch (error) {
            console.error('Error verifying face:', error);
            return { match: false, distance: 1, message: 'Error during verification' };
        }
    }

    /**
     * Menggambar hasil deteksi ke canvas
     * @param {Object} detection - Hasil deteksi dari face-api.js
     */
    function drawDetection(detection) {
        if (!canvasElement || !detection) return;

        // Reset canvas
        const context = canvasElement.getContext('2d');
        context.clearRect(0, 0, canvasElement.width, canvasElement.height);

        // Sesuaikan ukuran canvas dengan video
        canvasElement.width = videoElement.videoWidth;
        canvasElement.height = videoElement.videoHeight;

        // Gambar hasil deteksi
        faceapi.draw.drawDetections(canvasElement, [detection]);
        faceapi.draw.drawFaceLandmarks(canvasElement, [detection]);
    }

    // API publik
    return {
        init: initFaceAPI,
        startCamera,
        stopCamera,
        detectFace,
        captureFace,
        verifyFace,
        drawDetection
    };
})(); 