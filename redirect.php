<?php
// The official URI for which we would like the corresponding OntoME URI, if exists. This could be a class, property or project
// You can modify this if it is not $_SERVER[“REQUEST_URI”] (i.e. the current page) and thus rewrite it with the correct URI to be queried
$officialURI = rawurlencode($_SERVER['REQUEST_URI']);

// the API (do not modify)
$apiUrl = "https://ontome.net/api/get-ontome-uri";
$finalApiUrl = $apiUrl . '?officialUri=' . $officialURI;

$ch = curl_init($finalApiUrl);

curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => [
        'Accept: application/json',
    ],
    CURLOPT_TIMEOUT => 10,
    CURLOPT_CONNECTTIMEOUT => 5,
    CURLOPT_FOLLOWLOCATION => false,
]);

$response = curl_exec($ch);
$curlError = curl_error($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

if ($response === false) {
    http_response_code(502);
    echo "Erreur cURL : " . $curlError;
    exit;
}

if ($httpCode !== 200) {
    http_response_code($httpCode ?: 502);
    echo "Erreur API (HTTP $httpCode) : " . $response;
    exit;
}

$data = json_decode($response, true);
$target = $data['ontome_uri'] ?? null;

if ($target && filter_var($target, FILTER_VALIDATE_URL)) {
    header("Location: " . $target, true, 302);
    exit;
}

http_response_code(502);
exit;