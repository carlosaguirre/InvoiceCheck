<?php
echo "<h2>Prueba OpenSSL</h2>";

$bytes = openssl_random_pseudo_bytes(16, $crypto_strong);

if ($bytes === false) {
    echo "Error: No se pudo generar bytes aleatorios.";
} else {
    echo "Bytes generados: " . bin2hex($bytes) . "<br>";
    echo "¿Criptográficamente fuerte?: " . ($crypto_strong ? "Sí" : "No");
}
