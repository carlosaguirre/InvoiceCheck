<?php
echo "<h2>Validación de extensiones PHP</h2>";

$funciones = [
    'curl_init',
    'ftp_connect',
    'openssl_random_pseudo_bytes'
];

foreach ($funciones as $func) {
    echo "<p>$func: " . (function_exists($func) ? "✅ Disponible" : "❌ No disponible") . "</p>";
}
