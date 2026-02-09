<?php
declare(strict_types=1);
header('charset=UTF-8');

if (!isset($_SERVER["APPL_PHYSICAL_PATH"][0])) $_SERVER["APPL_PHYSICAL_PATH"]=__DIR__."\\";
$_SERVER["CONTEXT_DOCUMENT_ROOT"]=$_SERVER["APPL_PHYSICAL_PATH"];
$_SERVER["DOCUMENT_ROOT"]=$_SERVER["APPL_PHYSICAL_PATH"];
if ((!empty($_SERVER['REQUEST_SCHEME']) && $_SERVER['REQUEST_SCHEME'] == 'https') ||
     (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') ||
     (! empty($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == '443')) $_SERVER['REQUEST_SCHEME'] = 'https';
else $_SERVER['REQUEST_SCHEME'] = 'http';
$_SERVER["WEB_MD_PATH"]="/invoice/";
$_SERVER["HTTP_ORIGIN"]="$_SERVER[REQUEST_SCHEME]://$_SERVER[HTTP_HOST]";

// Ruta de proyecto
set_include_path(get_include_path() . PATH_SEPARATOR . __DIR__);
$_webPth=__DIR__;
//$_pryPth = Config::get("project","path");
//$_webPth = rtrim(str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $_pryPth ?? __DIR__), DIRECTORY_SEPARATOR);
//if (!in_array(realpath($_webPth), array_map('realpath', explode(PATH_SEPARATOR, get_include_path())))) {
//    set_include_path(get_include_path() . PATH_SEPARATOR . $_webPth);
//}

require_once 'vendor/autoload.php';
// Detecta el nombre del proyecto desde el path físico
$_pryNm = "invoice";
$_project_name = $_pryNm;
// $_SERVER["CONTEXT _ PREFIX"]=""; // $_pryNm."/";

// Ruta al archivo .env correspondiente
$_envPth = "C:/PHP/includes/.env.$_pryNm";

// Carga y valida el archivo .env
//echo "<!-- BOOTSTRAP INI: '$_envPth' -->\n";
require_once "clases/Config.php";
Config::init($_envPth);

// Configura entorno si el archivo fue válido
if (Config::get("error")!==null) {
    $_config_error=Config::get("error");
    echo "<!-- BOOTSTRAP: CONFIG ERROR: $_config_error -->\n";
} else {
    //echo "<!-- BOOTSTRAP: CONFIG SET -->\n";
    // Zona horaria
    $_tzOld=date_default_timezone_get();
    $_tz=Config::get("gral","timezone");
    if (isset($_tz)) date_default_timezone_set($_tz);
    // Locale
    $_lctm=Config::get("locale","time");
    if (isset($_lctm)) $_lc = setlocale(LC_TIME, $_lctm);
    // Server
    $_cliIP = $_SERVER['REMOTE_ADDR']??'UNKNOWN';
    $_currScr = $_SERVER['SCRIPT_NAME']??'UNKNOWN';
    // d = DAY OF MONTH 2 DIGITS          01 - 31
    // j = DAY OF MONTH NO ZEROES          1 - 31
    // S = ORDINAL SUFFIX MONTH, 2 char   st, nd, rd or th
    // D = DAY OF WEEK SHORT TEXT        Mon - Sun 
    // l = DAY OF WEEK FULL TEXT      Sunday - Saturday
    // N = DAY OF Week ISO    (for Monday) 1 - 7 (for Sunday)
    // w = DAY OF WEEK NUM    (for Sunday) 0 - 6 (for Saturday)
    // z = DAY OF YEAR (starting from 0)   0 - 365
    // W = WEEK OF YEAR (starts on monday) 1 - 55
    // F = MONTH FULL TEXT           January - December
    // M = MONTH SHORT TEXT              Jan - Dec
    // m = MONTH 2 DIGITS                 01 - 12
    // n = MONTH NO LEADING ZEROES         1 - 12
    // t = DAYS IN GIVEN MONTH            28 - 31
    // L = IS LEAP YEAR               (No) 0 - 1 (Yes)
    // o = YEAR (corresponds to week num) 1999, 2025, etc
    // X = EXPANDED FULL YEAR +/-4 DIGITS -0055, +0786, +1999, +10931
    // x = EXPANDED FULL YEAR 4 DIGITS    -0055, 0786, 1999, +10931
    // Y = FULL YEAR 4 DIGITS             -0055, 0787, 1999, 10191
    // y = YEAR 2 DIGITS                  99, 03
    // a = LWRCASE MERIDIEMS              am, pm
    // A = UPPCASE MERIDIEMS              AM, PM
    // B = SWATCH INTERNET TIME          000 - 999
    // g = 12 HOUR NO ZEROES               1 - 12
    // G = 24 HOUR NO ZEROES               0 - 23
    // h = 12 HOUR LEADING ZEROS          01 - 12
    // H = 24 HOUR LEADING ZEROS          00 - 23
    // i = MINUTES LEADING ZEROS          00 - 59
    // s = SECONDS LEADING ZEROS          00 - 59
    // u = MICROSECONDS (date vs DateTimeInterface) 000000 vs 654321
    // v = MILISECONDS (same as u)                     000 vs 321
    // e = TIMEZONE IDENTIFIER          UTC, GMT, Atlantic/Azores
    // I = DAYLING SAVING TIME        (No) 0 - 1 (Yes)
    // O = GREENWICH TIME DIFF NO COLON   vg +0200
    // P = SAME AS O WITH COLON           vg +02:00
    // p = SAME AS P (Z instead of 00:00) vg Z OR +02:00
    // T = TIMEZONE SHORT TEXT            vg EST, MDT, +05
    // Z = TIMEZONE OFFSET SECS, BASE UTC vg -43200 - 50400
    // c = ISO 8601 (up to year 9999)     2004-02-12T15:19:21+00:00
    // r = RFC 2822/5322                  Thu, 21 Dec 2000 16:01:07 +0200
    // U = SECONDS SINCE UNIX EPOCH (January 1 1970 00:00:00 GMT)
    $_now = [];

    foreach (["Y","y","m","n","d","j","t","H","i","s","now","ym","ymd","1MY","DMY","tMY"] as $char) {
        if (!isset($char[1])) $_now[$char]=date($char);
        else switch($char) {
            case "now": $_now["now"]="$_now[Y]-$_now[m]-$_now[d] $_now[H]:$_now[i]:$_now[s]"; break;
            case "ym" : $_now["ym"]="$_now[y]$_now[m]"; break;
            case "ymd": $_now["ymd"]="$_now[y]$_now[m]$_now[d]"; break;
            case "1MY": $_now["1MY"]="01/$_now[m]/$_now[Y]"; break;
            case "DMY": $_now["DMY"]="$_now[d]/$_now[m]/$_now[Y]"; break;
            case "tMY": $_now["tMY"]="$_now[t]/$_now[m]/$_now[Y]"; break;
        }
    }
    $monStrs=["Enero","Febrero","Marzo","Abril","Mayo","Junio","Julio","Agosto","Septiembre","Octubre","Noviembre","Diciembre"];

    //echo "<!-- BOOTSTRAP: META BEGIN -->\n";
    require_once "configuracion/meta.php";
    $_browser = getBrowser();

    // Inicializa sesión
    if (!array_key_exists("_doLogin",$GLOBALS)) $GLOBALS["_doLogin"]=Config::getBool("auth","login_required");
    // Conexión a base de datos si existe DBi
    if (!array_key_exists("_doDB",$GLOBALS)) $GLOBALS["_doDB"]=Config::getBool("db","enable");
    // Modo del proyecto
    if (!array_key_exists("habilitado",$GLOBALS)) $GLOBALS["habilitado"] = Config::getBool("project","habilitado");
    if (!array_key_exists("modoActualizacion",$GLOBALS)) $GLOBALS["modoActualizacion"] = Config::getBool("project","actualizando");
    if (!array_key_exists("modoPruebas",$GLOBALS)) $GLOBALS["modoPruebas"] = Config::getBool("project","fasePruebas");

    // Solo Index. Contemplar quitarlas de config y de bootstrap
    $lstPfx=Config::get("gral","lstPfx")??"";
    $lpLen=strlen($lstPfx);

    // Permisos precalculados
    $_esAdministrador=$_esSistemas=$_esSistemasX=$_esDesarrollo=$_esPruebas=$_esCompras=$_esComprasB=$_esProveedor=false;

    // Otras constantes
    require_once "configuracion/constantes_del_sistema.php";

    // seguimiento de errores
    require_once "configuracion/error.php";
    echo "<!-- Proyect Name: $_project_name -->\n";

    // Configuracion inicial de la base de datos
    if ($_doDB && $habilitado && file_exists($_webPth . "\clases\DBi.php")) {
        echo "<!-- BOOTSTRAP: CONNECTION INIT -->\n";
//        doclog("BOOTSTRAP: CONNECTION INIT","connection");
    /*
        require_once "clases/DBi.php";
        $dbConnKey = DBi::connect();
        if ($dbConnKey===null) echo "<!-- BOOTSTRAP: NULL CONNECTION -->\n";
        else if (empty($dbConnKey)) echo "<!-- BOOTSTRAP: EMPTY CONNECTION -->\n";
        else echo "<!-- BOOTSTRAP: CONNECTED $dbConnKey(".DBi::getCount($dbConnKey).") -->\n";
    */
    } else if (!$_doDB) echo "<!-- BOOTSTRAP: DB DISABLED -->\n";
    else if (!$habilitado) echo "<!-- BOOTSTRAP: DESHABILITADO -->\n";
    else echo "<!-- BOOTSTRAP: NOT FOUND $_webPth\clases\DBi.php -->\n";
}