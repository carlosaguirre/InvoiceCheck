<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once dirname(__DIR__)."/clases/Config.php";
$_pryNm = "invoice";
$_envPth = "C:/PHP/includes/.env.$_pryNm";
Config::init($_envPth);

require_once dirname(__DIR__)."/clases/DBi.php";
$dbKey = DBi::connect("invoice2");
if (is_null($dbKey) || $dbKey===false) {
    echo json_encode(['error' => 'Error de conexión']);
    exit;
}

// DBi::query();

// Obtener RFC del POST
$data = json_decode(file_get_contents('php://input'), true);
$rfc = $data['rfc'] ?? '';

if (empty($rfc)) {
    echo json_encode(['error' => 'RFC es requerido']);
    exit;
}

// Buscar en la base de datos usando los campos proporcionados
$stmt = DBi::query("SELECT alias, rfc, conSitFis FROM grupo WHERE rfc = '$rfc' LIMIT 1", DBObject::getByTable("grupo"), $dbKey);
$resultado = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$resultado) {
    echo json_encode(['error' => 'RFC no encontrado']);
    exit;
}

$alias = $resultado['alias'];
$conSitFis = $resultado['conSitFis'];

// Dominio base donde se alojan las constancias
$dominio = 'http://globaltycloud.com.mx/docs/csf/';

// Normalizar el nombre de archivo: convertir el nombre (sin extensión) a mayúsculas
$name = pathinfo($alias, PATHINFO_FILENAME);
$ext = pathinfo($alias, PATHINFO_EXTENSION);
$aliasNormalized = $name !== '' ? strtoupper($name) . ($ext ? '.' . $ext : '') : $alias;

// Construir la URL final asegurando barras correctas
$finalUrl = rtrim($dominio, '/') . '/' . trim($conSitFis, '/') . '/' . ltrim($aliasNormalized, '/');
if (!preg_match('/\.pdf$/i', $finalUrl)) { $finalUrl .= '.pdf'; }

// Intentar obtener el archivo remoto (timeout corto)
$context = stream_context_create(['http' => ['timeout' => 10]]);
$contenido = @file_get_contents($finalUrl, false, $context);

if ($contenido === false) {
    // No se pudo obtener el archivo remoto — devolvemos la URL para que el cliente lo use
    echo json_encode([
        'success' => false,
        'error' => 'No se pudo obtener el archivo remoto',
        'rfc' => $rfc,
        'alias' => $aliasNormalized,
        'conSitFis' => $conSitFis,
        'url' => $finalUrl
    ]);
    exit;
}

// Detectar tipo MIME y devolver el archivo en base64
$finfo = new finfo(FILEINFO_MIME_TYPE);
$mime = $finfo ? $finfo->buffer($contenido) : 'application/octet-stream';
$base64 = base64_encode($contenido);

echo json_encode([
    'success' => true,
    'rfc' => $rfc,
    'alias' => $aliasNormalized,
    'conSitFis' => $conSitFis,
    'url' => $finalUrl,
    'archivo' => $base64,
    'tipo' => $mime
]);
?>