<?php
/**
 * Ejemplo práctico obtenido por IA para corregir archivos XML con caracteres raros
 * Al parecer nada más borra los caracteres
 * */
function limpiarTexto($texto) {
    // Convertir a UTF-8 si el texto tiene otro encoding
    $texto = mb_convert_encoding($texto, 'UTF-8', mb_detect_encoding($texto, 'UTF-8, ISO-8859-1, WINDOWS-1252', true));

    // Reemplazar caracteres no compatibles con UTF-8
    $texto = preg_replace('/[^\x20-\x7E\xA0-\xFF]/', '', $texto);

    return $texto;
}

// Cargar un archivo XML con posible problema de encoding
$archivo = 'archivo.xml';
$contenido = file_get_contents($archivo);

// Limpiar el texto
$contenidoLimpio = limpiarTexto($contenido);

// Guardar el archivo corregido
file_put_contents('archivo_corregido.xml', $contenidoLimpio);

echo "Archivo XML corregido guardado correctamente.";
