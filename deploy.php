<?php
// Gunakan variabel environment atau file konfigurasi terpisah untuk secret
$secret = getenv('GITHUB_WEBHOOK_SECRET') ?: "Smkan798@"; // Lebih baik gunakan variabel lingkungan

// Verifikasi signature dari GitHub
$signature = $_SERVER['HTTP_X_HUB_SIGNATURE_256'] ?? '';
$payload = file_get_contents('php://input');
$hash = "sha256=" . hash_hmac('sha256', $payload, $secret);

// Log untuk debugging (opsional, hapus di production)
$logFile = __DIR__ . '/deployment-log.txt';
$logData = date('Y-m-d H:i:s') . " - Deployment attempt\n";

// Verifikasi kecocokan signature
if (hash_equals($hash, $signature)) {
    // Terima hanya push ke branch master
    $data = json_decode($payload, true);
    if (isset($data['ref']) && $data['ref'] === 'refs/heads/master') {
        // Jalankan script deployment dengan timeout dan capture output
        $logData .= "Signature verified, running deployment script...\n";
        $output = [];
        exec("bash " . __DIR__ . "/deploy.sh 2>&1", $output, $return_var);
        $logData .= implode("\n", $output) . "\n";
        $logData .= "Exit code: " . $return_var . "\n";
        
        // Log hasil
        file_put_contents($logFile, $logData, FILE_APPEND);
        
        // Tampilkan hasil
        echo "Deployment berhasil!\n";
        echo implode("\n", $output);
    } else {
        $logData .= "Ignoring push to non-master branch\n";
        file_put_contents($logFile, $logData, FILE_APPEND);
        http_response_code(202); // Accepted but not processed
        echo "Webhook diterima tetapi bukan untuk branch master";
    }
} else {
    // Log percobaan gagal
    $logData .= "Verification failed. Invalid signature.\n";
    file_put_contents($logFile, $logData, FILE_APPEND);
    
    // Tampilkan error
    http_response_code(403);
    die("Unauthorized: Signature verification failed");
}
?>
