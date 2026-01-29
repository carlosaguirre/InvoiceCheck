<?php
echo "<h2>Prueba CURL</h2>";
$url = "https://www.example.com/";
$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
$response = curl_exec($ch);
if ($response === false) {
    echo "Error CURL: " . curl_error($ch);
} else {
    echo "CURL ejecutado correctamente. Longitud de respuesta: " . strlen($response);
}
curl_close($ch);
