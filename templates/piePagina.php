<?php
require_once dirname(__DIR__)."/bootstrap.php";
clog2ini("piePagina");
clog1seq(1);

if (isset($_REQUEST["refreshFooter"]))
    $refreshFooter = $_REQUEST["refreshFooter"];
if (!isset($habilitado)) $habilitado=TRUE;

if (!isset($refreshFooter) || $refreshFooter!="true") {
    $configClass="vAlignParent noprint";
    if(isset($_REQUEST["pie_pagina"][0])) $configClass.=" $_REQUEST[pie_pagina]";
    echo "<div id='pie_pagina' class='$configClass'>";
} else if ($habilitado) {
    if (!isset($systemTitle))
        $initializeFooter = include_once "configuracion/inicializacion.php";
} else if (!isset($systemTitle)) include_once "configuracion/inicializacionSinBD.php";

if(hasUser()) {
    $user = getUser();
    $usrPrfTitle = "";
    $usrPerfiles = "";
    if ($habilitado) {
        require_once "clases/Usuarios.php";
        $usrObj = new Usuarios();
        $usrPerfiles = $usrObj->getPerfiles($user->nombre);
        if (isset($usrPerfiles[20])) {
            $usrPrfIdx=strpos($usrPerfiles, ",", 20);
            if (isset($usrPerfiles[40])&&($usrPrfIdx===false||$usrPrfIdx>40)) $usrPrfIdx=40;
            if ($usrPrfIdx!==false) {
                $usrPrfTitle=" title='$usrPerfiles'";
                $usrPerfiles=substr($usrPerfiles, 0, $usrPrfIdx)."...";
            }
        }
    }
    $esDesarrollo=in_array(getUser()->nombre, ["admin","SISTEMAS"]);
    //$esPruebas = true; //in_array(getUser()->nombre, ["admin","test"]);
    $cloud=$esDesarrollo?"<img src=\"imagenes/icons/refresh.png\" class=\"marL2 btn12 btnOI op80\" onclick=\"reloadUser();\">":"";
    echo "<div id='pie_usuario' class='pie_element'>Usuario: <span class='footValue' id='caption_usuario'>$user->nombre</span>$cloud</div>";
    echo "<div id='pie_perfil' class='pie_element'>Perfil: <span class='footValue'{$usrPrfTitle}>$usrPerfiles</span></div>";

}

$ip = (empty($_SERVER['HTTP_CLIENT_IP'])?(empty($_SERVER['HTTP_X_FORWARDED_FOR'])?$_SERVER['REMOTE_ADDR']:$_SERVER['HTTP_X_FORWARDED_FOR']):$_SERVER['HTTP_CLIENT_IP']);
//clog2("HTTP CLIENT IP : ".(empty($_SERVER['HTTP_CLIENT_IP'])?"vacio":$_SERVER['HTTP_CLIENT_IP']));
//clog2("HTTP X FORWARDED FOR : ".(empty($_SERVER['HTTP_X_FORWARDED_FOR'])?"vacio":$_SERVER['HTTP_X_FORWARDED_FOR']));
//clog2("REMOTE ADDR : ".(empty($_SERVER['REMOTE_ADDR'])?"vacio":$_SERVER['REMOTE_ADDR']));
//clog2("IP sin filtro: $ip");
$ip = filter_var ($ip, FILTER_SANITIZE_ADD_SLASHES); // FILTER_SANITIZE_ENCODED, FILTER_FLAG_STRIP_LOW, FILTER_SANITIZE_MAGIC_QUOTES
//clog2("IP con filtro: $ip");
if (strlen($ip)>25) $ip = substr($ip, 0, 25);
echo "<div id='pie_ip' class='pie_element'>IP: $ip</div>";
if (validaPerfil("Administrador")) {
    $_useragent=getBrowser("useragent");
    echo "<div id='pie_kb' class='pie_element'><img src='imagenes/icons/statusError16.png' alt='Prueba' class='aslink' onclick='showAutoCloseLine(\"$_useragent\");'></div>";
}
if (validaPerfil("Sistemas") && $menu_accion=="Reporte Facturas") {
    $fixImg="process200.png";//$esDesarrollo?"process200.png":"process200g.jpg";
    echo "<div class=\"pie_element pie_quickSys\"><img src=\"imagenes/icons/{$fixImg}\" alt=\"Repara Complementos\" title=\"Repara Pagos Viejos\" class=\"btn16 aslink\" onclick=\"fixOldPagos();\"></div><div class=\"pie_element pie_quickSys\"><img src=\"imagenes/icons/{$fixImg}\" alt=\"Repara Complementos 2\" title=\"Repara Pagos Incompletos\" class=\"btn16 aslink\" onclick=\"fixEmptyPagos();\"></div>";
}
//    if(!hasUser()) echo "        <div id='pie_agent' class='pie_element'>".$_SERVER['HTTP_USER_AGENT']."</div>\n";

echo "<div id='pie_space' class='pie_space'></div>";

if (hasUser()&&isset($_SESSION['MENSAJE_NOTICIA'])) {
    echo "<div id='pie_alerta' class='pie_element'><img src='imagenes/icons/notice16.png' alt='Noticia' class='top aslink' onclick='fillMessage(\"noticia\");console.log(\"filling message noticia\");'></div>";
}
if (isset($_SESSION['MENSAJE_CORONAVIRUS'])) {
    $showCOVID=false;
    $procSwitch="";
    if (validaPerfil("Administrador")) $procSwitch="<DIV id=\\'procSwitch\\' class=\\'chartSwitch\\' onclick=\\'viewChartOptions(3);\\'><DIV id=\\'procDetail\\' class=\\'switchName\\'>Consultas</DIV></DIV>";
    $customSwitch="<DIV class=\\'hidden chartButtons\\'><DIV id=\\'chartSwitch1\\' class=\\'chartSwitch\\' onclick=\\'viewChartOptions(1);\\'><DIV id=\\'switchColor1\\' class=\\'switchColor\\'></DIV><DIV id=\\'switchName1\\' class=\\'switchName\\' style=\\'min-width:95px;\\'></DIV></DIV><DIV id=\\'zoneSwitch1\\' class=\\'chartSwitch\\' onclick=\\'viewChartOptions(2);\\'><DIV id=\\'zoneName1\\' class=\\'switchName\\' style=\\'min-width:170px;\\'></DIV></DIV>$procSwitch</DIV>";
    if ($showCOVID) echo "<div id=\"pie_covid\" class=\"pie_element\"><img src=\"imagenes/icons/covid19.png\" alt=\"Noticia\" class=\"top aslink\" onclick=\"overlayClose();overlayMessage('$customSwitch<iframe id=\\'frame1All\\' style=\\'width:49%;height:420px;border:0;\\' frameborder=\\'0\\' allow=\\'accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture\\'></iframe><iframe id=\\'frame1New\\' style=\\'width:49%;height:420px;border:0;\\' frameborder=\\'0\\' allow=\\'accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture\\'></iframe>','Propagación Coronavirus Actual');viewChart('infected','MX');adjustDialogBoxHeight();const dra1=ebyid('dialog_resultarea').firstElementChild;dra1.style.position='relative';dra1.appendChild(ecrea({eName:'DIV',className:'abs_nw all_space centered padt4',eChilds:[{eName:'A',href:'https://coronavirus.app/tracking/mexico',className:'fontBig boldValue alink',target:'coronavirus',eText:'CoronavirusApp/Mexico'}]}));\"></div>";
    //echo "<div id='pie_covid' class='pie_element'><img src='imagenes/icons/covid19.png' alt='Noticia' class='top aslink' onclick='overlayMessage(\"<iframe style=\'width:100%\' width=\'560\' height=\'380\' src=\'https://coronavirus.app/map?embed=true\' frameborder=\'0\' allow=\'accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture\' allowfullscreen></iframe>\",\"Propagación Coronavirus\");'></div>";
    // {eName:\"IFRAME\", style:\"width:100%,border:0\", width:\"560\", height:\"380\", src:\"https://coronavirus.app/chart/I4OJ6z9Vjs1byOcbsJrd/infected/new?embed=true\", frameborder:\"0\", allow:\"accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture\", allowfullscreen:1, allowFullScreen:true}
    // <iframe style="width:100%"; width="560" height="380" src="https://coronavirus.app/map?embed=true" frameborder="0" allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
}
echo "<div id='pie_contacto' class='pie_element'><a href='mailto:desarrollo@glama.com.mx?Subject=Solicitud%20de%20Asesoría%20Técnica' target='_top'>caah&copy;2016</a></div>";
echo "<div id='pie_version' class='pie_element boldValue'>V3.0</div>";
echo "<div id='pie_clock' class='pie_element'>".date('H:i:s')."</div>";
if(hasUser()) {
    echo "<div id='pie_logout' class='pie_element'><form name='forma_logout' target='_self' method='post'><input type='image' src='imagenes/logout.png' alt='SALIR' title='SALIR' name='logout' value='SALIR'/><input type='hidden' name='logout' value='SALIR'></form></div>";
    echo "<img src=\"data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7\" onload=\"let cu=ebyid('caption_usuario');cu.ondblclick=function(event){postService('consultas/Usuarios.php',{accion:'itch',original:'1'},function(text,params,state,status){if(state<4&&status<=200)return;if(state>4||status>200){console.log('ERROR.STATE:'+state+',STATUS:'+status+',TEXT:'+text);return;}if(text.length>0){try{let jo=JSON.parse(text);if(jo.original)console.log(jo.original);else console.log('ERROR.NO REASON: '+text);}catch(ex){console.log('ERROR.EXCEPTION:',ex,text);}}else console.log('SIN RESPUESTA');});};ekil(this);\">";
}
if (!isset($refreshFooter) || $refreshFooter!="true") {
    echo "</div>";
} else if (isset($initializeFooter) && $initializeFooter!==true && $habilitado) {
    include_once ("configuracion/finalizacion.php");
}
clog1seq(-1);
clog2end("piePagina");
