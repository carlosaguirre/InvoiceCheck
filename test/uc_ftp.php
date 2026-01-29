<?php
echo "<h2>Prueba FTP</h2>";

$ftp_server = "ftp.gnu.org"; // Servidor público de prueba
$conn_id = ftp_connect($ftp_server);

if (!$conn_id) {
    echo "Error: No se pudo conectar al servidor FTP.";
} else {
    echo "Conexión FTP exitosa con $ftp_server.";
    ftp_close($conn_id);
}
