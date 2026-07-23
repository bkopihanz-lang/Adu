<?php
// CATShadow Proxy Engine v2.0 – Full Bypass + Obfuscation
// Tidak ada log. Tidak ada batasan. Tidak ada blokir.

$target = $_GET['url'] ?? 'https://google.com';
$target = filter_var($target, FILTER_SANITIZE_URL);

// Rotasi User-Agent secara acak (hindari DPI)
$user_agents = [
    'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
    'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.0 Safari/605.1.15',
    'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/119.0.0.0 Safari/537.36'
];
$ua = $user_agents[array_rand($user_agents)];

// Setup CURL dengan mode bypass total
$ch = curl_init();
curl_setopt_array($ch, [
    CURLOPT_URL => $target,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_SSL_VERIFYPEER => false,
    CURLOPT_SSL_VERIFYHOST => false,
    CURLOPT_USERAGENT => $ua,
    CURLOPT_HTTPHEADER => [
        'X-Forwarded-For: ' . rand(1,255).'.'.rand(1,255).'.'.rand(1,255).'.'.rand(1,255),
        'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
        'Accept-Language: en-US,en;q=0.5',
        'Cache-Control: no-cache'
    ],
    CURLOPT_TIMEOUT => 30,
    CURLOPT_ENCODING => 'gzip, deflate, br', // kompresi untuk kecepatan
    CURLOPT_REFERER => 'https://' . $_SERVER['HTTP_HOST']
]);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

// Jika gagal, coba dengan mode tunnel via alternatif
if ($http_code !== 200 && empty($response)) {
    // Fallback: gunakan file_get_contents dengan stream context (mode bypass kedua)
    $context = stream_context_create([
        'http' => [
            'method' => 'GET',
            'header' => "User-Agent: $ua\r\nX-Forwarded-For: " . rand(1,255).'.'.rand(1,255).'.'.rand(1,255).'.'.rand(1,255)
        ],
        'ssl' => ['verify_peer' => false, 'verify_peer_name' => false]
    ]);
    $response = @file_get_contents($target, false, $context);
}

// Rewrite semua link agar tetap melalui proxy
$response = str_replace('href="/', 'href="?url=' . urlencode($target) . '/', $response);
$response = str_replace('src="/', 'src="?url=' . urlencode($target) . '/', $response);
$response = str_replace('action="/', 'action="?url=' . urlencode($target) . '/', $response);

// Kirim hasil dengan header cache dimatikan
header('Content-Type: text/html; charset=utf-8');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');
echo $response;
?>