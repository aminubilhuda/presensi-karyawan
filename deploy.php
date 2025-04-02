<?php
$secret = "Smkan798@"; // Ganti dengan secret GitHub Webhook
$signature = $_SERVER['HTTP_X_HUB_SIGNATURE_256'] ?? '';
$payload = file_get_contents('php://input');
$hash = "sha256=" . hash_hmac('sha256', $payload, $secret);

if (hash_equals($hash, $signature)) {
    exec("bash /home3/abdinega/absensi.abdinegara.com/deploy.sh 2>&1", $output);
    echo implode("\n", $output);
} else {
    http_response_code(403);
    die("Unauthorized");
}
?>
