<?php
declare(strict_types=1);

// Evita acceso directo vía URL a este archivo de inicialización
if (PHP_SAPI !== 'cli') {
    $_entryScript = $_SERVER['SCRIPT_FILENAME'] ?? '';
    if (!empty($_entryScript) && realpath($_entryScript) === __FILE__) {
        http_response_code(404);

        // Muestra la página 404 de IIS configurada en el servidor
        $_iis404File = 'C:\\inetpub\\custerr\\es-ES\\404.htm';
        if (is_file($_iis404File) && is_readable($_iis404File)) {
            if (!headers_sent()) {
                header('Content-Type: text/html; charset=UTF-8');
                header_remove('Content-Length');
            }
            readfile($_iis404File);
            exit;
        }

        // Sin cuerpo: permite que el servidor web aplique su manejo de 404
        if (!headers_sent()) header('Content-Length: 0');
        exit;
    }
}

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

require_once __DIR__ . '/vendor/autoload.php';
// Detecta el nombre del proyecto desde el path físico
$_pryNm = "invoice";
$_project_name = $_pryNm;
// $_SERVER["CONTEXT _ PREFIX"]=""; // $_pryNm."/";

// Ruta al archivo .env correspondiente
$_envPth = "C:/PHP/includes/.env.$_pryNm";

// Carga y valida el archivo .env
require_once __DIR__."/clases/Config.php";
Config::init($_envPth);

// Configura entorno si el archivo fue válido
if (Config::get("error")!==null) {
    $_config_error=Config::get("error");
    echo "<!-- BOOTSTRAP: CONFIG ERROR: $_config_error -->\n";
    echo "<script>console.error('BOOTSTRAP: CONFIG ERROR: $_config_error');</script>\n";
} else {
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
    // Ruta de proyecto
    $_pryPth = Config::get("project","path");
    $_webPth = rtrim(str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $_pryPth ?? __DIR__), DIRECTORY_SEPARATOR);
    if (!in_array(realpath($_webPth), array_map('realpath', explode(PATH_SEPARATOR, get_include_path())))) {
        set_include_path(get_include_path() . PATH_SEPARATOR . $_webPth);
    }

    require_once "configuracion/meta.php";
    $_browser = getBrowser();

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
    foreach (["Y","y","m","n","d","j","t","H","i","s","now","ym","ymd","md","1MY","DMY","ebY","eBY","tMY"] as $char) {
        if (!isset($char[1])) $_now[$char]=date($char); // Solo los de un caracter, los demás se calculan en el switch
        else switch($char) {
            case "now": $_now["now"]="$_now[Y]-$_now[m]-$_now[d] $_now[H]:$_now[i]:$_now[s]"; break;
            case "ym" : $_now["ym"]="$_now[y]$_now[m]"; break;
            case "ymd": $_now["ymd"]="$_now[y]$_now[m]$_now[d]"; break;
            case "md" : $_now["md"]="$_now[m]$_now[d]"; break;
            case "1MY": $_now["1MY"]="01/$_now[m]/$_now[Y]"; break;
            case "DMY": $_now["DMY"]="$_now[d]/$_now[m]/$_now[Y]"; break;
            case "ebY": $_now["ebY"]="$_now[j] ".ucfirst(mesesMexico(true)[($_now["n"]-1)]).", $_now[Y]"; break;
            case "eBY": $_now["eBY"]="$_now[j] ".ucfirst(mesesMexico()[($_now["n"]-1)]).", $_now[Y]"; break;
            case "tMY": $_now["tMY"]="$_now[t]/$_now[m]/$_now[Y]"; break;
        }
    }

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
    // Configuracion inicial de la base de datos
    if ($_doDB && $habilitado && file_exists($_webPth . "\clases\DBi.php")) {
        doclog("BOOTSTRAP: CONNECTION INIT","connection");
        require_once "clases/DBi.php";
        $dbConnKey = DBi::connect();
        require_once "clases/DAOFactory.php";
        //if ($dbConnKey===null) echo "<!-- BOOTSTRAP: NULL CONNECTION -->\n";
        //else if (empty($dbConnKey)) echo "<!-- BOOTSTRAP: EMPTY CONNECTION -->\n";
        //else echo "<!-- BOOTSTRAP: CONNECTED $dbConnKey(".DBi::getCount($dbConnKey).") -->\n";
    } //else if (!$_doDB) echo "<!-- BOOTSTRAP: DB DISABLED -->\n";
    //else if (!$habilitado) echo "<!-- BOOTSTRAP: DESHABILITADO -->\n";
    //else echo "<!-- BOOTSTRAP: NOT FOUND $_webPth\clases\DBi.php -->\n";

    // Configuracion inicial de la sesión
    sessionInit();
    setUser();
    global $_project_name, $hasUser, $user, $userid, $username;
    // Identificar y validar usuario
    if ($_doLogin && file_exists($_webPth . "\configuracion\login.php")) {
        include "configuracion/login.php";
    } //else cleanUser();

    if ($_esPruebas) {
        ini_set('display_errors', 1);
        ini_set('display_startup_errors', 1);
        error_reporting(E_ALL);
    }

    // Cambios GUI de Temporada
    $waitPth=Config::get("interface", "waitPth")??"";
    $waitImg=Config::get("interface", "waitImg");
    $waitExt=Config::get("interface", "waitExt");
    $bkgdPth=Config::get("interface", "bkgdPth")??"";
    $lghtPth=Config::get("interface", "lightPath")??"";
    $bkgdImg=Config::get("interface", "bkgdImg");
    $bkgdExt=Config::get("interface", "bkgdExt");
    $isSeason=Config::getBool("interface", "season");
    if ($isSeason) {
        $md = $_now["md"];
        global $hasUser, $username;
        $VIPList=Config::get("season", "byUser")??[];
        $isVIP=$hasUser && in_array($username, $VIPList);
        $seasonNoList=Config::get("season", "exUser")??[];
        $isIgnore=$hasUser && in_array($username, $seasonNoList);
        if ($isVIP || !$isIgnore) {
            if ($md<102) $seasonImageBlock="newyear";
            else if ($md<107) $seasonImageBlock="wisemen";
            else if ($md<203) $seasonImageBlock= "candelaria";
            else if ($md<216) $seasonImageBlock= "valentine";
            else if ($md<1229) $seasonImageBlock= "newyear";
            else if ($md>1212) $seasonImageBlock= "navidad";
            if (isset($seasonImageBlock)) {
                $seasonOptions=Config::get("season", $seasonImageBlock)??[];
                if (!empty($seasonOptions)) {
                    $waitPth="icons/seasons";
                    $waitImg=$seasonOptions[array_rand($seasonOptions)];
                }
            }
            $num = rand(0, 3);
            if ($num<3) {
                if (($md>211 || ($num>0 && $md>205)) && $md<215) { // ESPECIAL: DIA DEL AMOR Y LA AMISTAD: 0210-0214
                    $seasonBkgdImgBlk="bglove";
                } else if ($num>0 && ($md>1200 || $md<102)) { // ESPECIAL: NAVIDAD
                    $seasonBkgdImgBlk= "bgchristmas";
                } else if ($md>320 && $md<600) { // TEMPORADA: PRIMAVERA
                    $seasonBkgdImgBlk= "bgspring";
                } else if ($md>620 && $md<900) { // TEMPORADA: VERANO
                    $seasonBkgdImgBlk= "bgsummer";
                } else if ($md>920 && $md<1200) { // TEMPORADA: OTOÑO
                    $seasonBkgdImgBlk= "bgautumn";
                } else if ($md>1220 || $md<300) { // TEMPORADA: INVIERNO
                    $seasonBkgdImgBlk= "bgwinter";
                }
                if (isset($seasonBkgdImgBlk)) {
                    $seasonOptions=Config::get("season", $seasonBkgdImgBlk)??[];
                    if (!empty($seasonOptions)) {
                        $bkgdPth="seasons";
                        $bkgdImg=$seasonOptions[array_rand($seasonOptions)];
                    }
                }
            }
        }
    }
    if (isset($bkgdPth[0])) $bkgdPth.="/";
    if (isset($waitPth[0])) $waitPth.="/";
    if (isset($lghtPth[0])) $lghtPth.="/";
    if (isset($bkgdExt[0])) $bkgdExt=".$bkgdExt";
    if (isset($waitExt[0])) $waitExt=".$waitExt";
    $waitImgName="imagenes/".$waitPth.$waitImg.$waitExt;
    $bkgdImgName="imagenes/fondos/".$bkgdPth.$lghtPth.$bkgdImg.$bkgdExt;
}
