<?php
header("Content-type: text/css; charset: UTF-8");
$GLOBALS["_doDB"]=false;
$GLOBALS["season"]=false;
require_once dirname(__DIR__)."/bootstrap.php";
$isChrome = $_browser=="Chrome";
$isIE = $_browser=="IE";
$isFF = $_browser=="Firefox";

$enableHiddenMenu=$hasUser&&!isMobile();
//$isMe=( ($_SERVER["REMOTE_ADDR"]==="192.168.1.254") && in_array($_SERVER["HTTP_USER_AGENT"],["Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36","Mozilla/5.0 (Linux; Android 8.0.0; SM-G955U Build/R16NW) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Mobile Safari/537.36"]) );
/*
REMOTE_ADDR='192.168.1.254'
HTTP_USER_AGENT='Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36'
HTTP_ACCEPT_LANGUAGE='es-419,es;q=0.9'
REMOTE_PORT='25953'
REQUEST_METHOD='GET'
*/
?>
@charset "utf-8";
/* CSS Document */

* {
    font-family: Tahoma, Arial, sans-serif !important;
}
body:not(.blank) {
    background-image: url(<?= "/{$_pryNm}/{$bkgdImgName}" ?>);
    background-repeat: repeat;
    color: #008;
    font-size: 14px;
    overflow-y: hidden;
    margin-left: 0px;
    margin-right: 0px;
}
body.basefont {
    font-size: 14px;
}
body.scrollable {
    overflow: auto;
    margin: 0;
    padding: 0;
    height: 100vh; /* Ensures the body takes the full height of the viewport */
    box-sizing: border-box; /* Ensures padding and border are included in the element's total width and height */
}
.txtstrk {
    /* -webkit-text-stroke: 1px white; */
    text-shadow: -1px 0 white, 0 1px white, 1px 0 white, 0 -1px white;
}
#firstPageCover {
    display: block !important;
    position: absolute;
    top: 0;
    right: 0;
    width: 100px;
    height: 100px;
    background-color: white;
    /*
    aliceblue     240,248,255
    azure         240,255,255
    floralwhite   255,250,240
    ghostwhite    248,248,255
    honeydew      240,255,240
    ivory         255,255,240
    lavenderblush 255,240,245
    mintcream     245,255,250
    seashell      255,245,238
    snow          255,250,250
    whitesmoke    245,245,245
    */
    font-size: 24px;
    font-weight: bold;
    text-align: center;
    vertical-align: middle;
    margin: 0 auto;
}
body.ORIGINAL, table.ORIGINAL, div.contrablock.ORIGINAL {
    background-image: url(<?= "/{$_pryNm}/imagenes/fondos/originalWatermark1.png" ?>);
    background-repeat: repeat;
}
body.ORIGINALPUE, table.ORIGINALPUE, div.contrablock.ORIGINALPUE {
    background-image: url(<?= "/{$_pryNm}/imagenes/fondos/originalPUEWatermark1.png" ?>);
    background-repeat: repeat;
}
body.COPIA, table.COPIA, div.contrablock.COPIA {
    background-image: url(<?= "/{$_pryNm}/imagenes/fondos/copyWatermark1.png" ?>);
    background-repeat: repeat;
}
body.COPIAPUE, table.COPIAPUE, div.contrablock.COPIAPUE {
    background-image: url(<?= "/{$_pryNm}/imagenes/fondos/copyPUEWatermark1.png" ?>);
    background-repeat: repeat;
}
body.PROVEEDOR, table.PROVEEDOR, div.contrablock.PROVEEDOR {
    background-image: url(<?= "/{$_pryNm}/imagenes/fondos/proveedorWatermark1.png" ?>);
    background-repeat: repeat;
}
body.PROVEEDORPUE, table.PROVEEDORPUE, div.contrablock.PROVEEDORPUE {
    background-image: url(<?= "/{$_pryNm}/imagenes/fondos/proveedorPUEWatermark1.png" ?>);
    background-repeat: repeat;
}
div.contrablock {
    width: 100%;
    min-height: 98vh;
    position: relative;
    break-inside: auto;
    page-break-after: always;
}
body h1, body h2, body h3 {
    font-weight: 700;
    word-break: break-word;
}
body h1 {
    font-size: 28px;
}
body h2 {
    font-size: 24px;
}
body h3 {
    font-size: 20px;
}
h4 {
    font-size: 16px;
}
.pre {
    white-space: pre;
    white-space: -moz-pre; /* Mozilla, since 1999 */
    white-space: -pre;     /* Opera 4-6 */
    white-space: -o-pre;   /* Opera 7 */
}
.preline {
    white-space: pre-line;
    white-space: -moz-pre-line; /* Mozilla, since 1999 */
    white-space: -pre-line;     /* Opera 4-6 */
    white-space: -o-pre-line;   /* Opera 7 */
}
pre.wrapped0, code.wrapped0, .prewrap {
    white-space: pre-wrap;      /* css-3 */
    white-space: -moz-pre-wrap; /* Mozilla, since 1999 */
    white-space: -pre-wrap;     /* Opera 4-6 */
    white-space: -o-pre-wrap;   /* Opera 7 */
    word-wrap: break-word;      /* Internet Explorer 5.5+ */
}
pre.wrapped1, code.wrapped1, .prewrap1 {
    white-space: pre-wrap;
    word-wrap: break-word;
}
pre.wrapped2, code.wrapped2, .prewrap2 {
    padding: 30px;
    -webkit-box-sizing: border-box;
    -moz-box-sizing: border-box;
    box-sizing: border-box;
    display: block;
    white-space: pre-wrap;
    white-space: -moz-pre-wrap;
    white-space: -pre-wrap;
    white-space: -o-pre-wrap;
    word-wrap: break-word;
    width: 100;
    overflow-x: auto;
}
pre.wrapped3, code.wrapped3, .prewrap3 {
    word-break: break-all; /* webkit */
    word-wrap: break-word;
    white-space: pre;
    white-space: -moz-pre-wrap: /* fennec */
    white-space: pre-wrap;
    white-space: pre\9; /* IE7+ */
}
pre.wrapped4, code.wrapped4, .prewrap4 {
    word-break: keep-all; /* webkit */
    word-wrap: break-word;
    white-space: pre;
    white-space: -moz-pre-wrap: /* fennec */
    white-space: pre-wrap;
    white-space: pre\9; /* IE7+ */
}
pre.wrapped5, .prewrap5 {
    overflow-wrap: break-word;
    margin: 5px;
    text-align: left;
    align: left;
    width: 580px;
}
pre.wrapped6, prewrap6 {
    white-space: pre-wrap;      /* css-3 */
    white-space: -moz-pre-wrap; /* Mozilla, since 1999 */
    white-space: -pre-wrap;     /* Opera 4-6 */
    white-space: -o-pre-wrap;   /* Opera 7 */
    word-wrap: break-word;      /* Internet Explorer 5.5+ */
    margin: 5px;
    text-align: left;
    align: left;
    width: 580px;
}
#contenedor {
    width: 100%;
    height: 100%;
}
#encabezado {
    z-index: 10001;
    background-image: url(<?= "/{$_pryNm}/{$bkgdImgName}" ?>);
    background-repeat: repeat;
    width: 100%;
    height: 98px;
}
#bloque_central {
    width: 100%;
}
#bloque_central:not(.noHeader):not(.noEncabezado) {
    height: calc(100% - 128px);
}
#bloque_central.noHeader {
    height: 100%;
}
#bloque_central.noEncabezado {
    height: calc(100% - 98px);
    overflow: auto;
}
#pie_pagina {
    position: fixed;
    vertical-align: middle;
    left: 0px;
    bottom: 0px;
    width: 100%;
    height: 30px;
    border-top: 2px groove gray;
    background-image: url(<?= "/{$_pryNm}/imagenes/image1.png" ?>);
    background-repeat: repeat;
}
/*
* html #pie_pagina {
    position: absolute;
    top: expression((0-(pie_pagina.offsetHeight)+(document.documentElement.clientHeight ? document.documentElement.clientHeight : document.body.clientHeight)+(ignoreMe = document.documentElement.scrollTop ? document.documentElement.scrollTop : document.body.scrollTop))+'px');
}
*/
#head_logo, #head_logo>a, #head_logo>a>img, .logoSpace {
    height: 96px;
}
#head_logo>a, #head_logo>a>img {
    width: 96px;
}
#head_logo {
    float: left;
    width: 200px;
    padding: 0px 0px 2px 0px;
}
#head_logo>a {
    display: inline-block;
}
#head_main {
    float: left;
    width: calc(100% - 200px);
    height: 88px;
    margin-top: 10px;
    position: relative;
    z-index: 10;
}
#head_main>h1 {
    text-align: center;
}
#pie_pagina>div {
    height: 100%;
    border-right: 2px groove gray;
    display: table-cell;
    white-space: nowrap;
}
#repLogo {
    display: none;
    height: 100px;
    margin: 10px;
    background-position: center !important;
    background-size: contain !important;
    background-repeat: no-repeat !important;
}
/* #pie_pagina>div:not(.pad1):not(.pad2):not(.pad3):not(.pad4):not(.pad5):not(.pad6) / - * #pie_pagina>div.pad7 * - / {
    vertical-align: middle;
    padding: 7px;
} */
.right1BG  {
    background-image: url(<?= "/{$_pryNm}/imagenes/fondos/right01.png" ?>);
    background-repeat: repeat;
}
.right2BG  {
    background-image: url(<?= "/{$_pryNm}/imagenes/fondos/right02.png" ?>);
    background-repeat: repeat;
}
.right3BG  {
    background-image: url(<?= "/{$_pryNm}/imagenes/fondos/right03.png" ?>);
    background-repeat: repeat;
}
.wrong1BG {
    background-image: url(<?= "/{$_pryNm}/imagenes/fondos/wrong01.png" ?>);
    background-repeat: repeat;
}
.wrong2BG {
    background-image: url(<?= "/{$_pryNm}/imagenes/fondos/wrong02.png" ?>);
    background-repeat: repeat;
}
.wrong3BG {
    background-image: url(<?= "/{$_pryNm}/imagenes/fondos/wrong03.png" ?>);
    background-repeat: repeat;
}
.pie_element, .basicBG {
    background-image: url(<?= "/{$_pryNm}/{$bkgdImgName}" ?>);
    background-repeat: repeat;
    padding: 4px;
}
.lightBG {
    background-image: radial-gradient(rgba(255,255,255,0.8), rgba(255,255,255,0.3)),
                    url(<?= "/{$_pryNm}/{$bkgdImgName}" ?>);
    background-repeat: repeat;
}
.curtain {
    background-image: url(<?= "/{$_pryNm}/imagenes/fondos/corrugatedboxSemiT0.png" ?>);
    background-repeat: repeat;
}
.curtain1 {
    background-image: url(<?= "/{$_pryNm}/imagenes/fondos/corrugatedboxSemiT1.png" ?>);
    background-repeat: repeat;
}
#pie_kb>img {
    vertical-align: text-bottom;
}
#pie_version, #pie_contacto {
    font-size: 10px;
    width: 1px;
}
#pie_clock {
    width: 1px;
    font-weight: bold;
    font-size: 12px;
    color: #008;
    -webkit-touch-callout: none;
    -webkit-user-select: none;
    -khtml-user-select: none;
    -moz-user-select: none;
    -ms-user-select: none;
    -o-user-select: none;
    user-select: none;
    pointer-events: none;
    cursor: not-allowed;
}
#pie_logout>form>input[type="image"] {
    width: 16px;
    height: 16px;
    vertical-align: text-bottom;
}
.pie_space, .all_space {
    width: 100%;
}
.vw_space {
    width: 100vw;
}
.vw_content {
    /* width: calc(100vw - (100vw - 100% + 78px) - 200px ); */
    width: calc(100% - 278px );
}
.vh_space {
    height: 100vh;
}
.vhbt200 {
    height: calc(100vh - 200px);
}
.shortBy3 {
    height: calc(100% - 3px);
}

<?php if ($enableHiddenMenu) { ?>
#lado_izquierdo {
      position: fixed;
      left: -169px; /* Oculta el menú dejando solo 20px visibles */
      top: 0;
      bottom: 0;
      padding-right: 0px; /* 15px;*/
      width: 170px;
      transition: left 0.3s ease;
      z-index: 2000;
      background: transparent; / rgba(100,100,255,0.1); */
      overflow-y: auto;
}
#lado_izquierdo:hover:not(.noApply) {
  left: 0; /* Muestra completamente el menú al hacer hover */
}
#pie_pagina {
    z-index: 1000;
}
<?php } ?>
#lado_izquierdo {
    float: left;
<?php if (!$enableHiddenMenu) { ?>
    width: 199px;
<?php } ?>
}
#lado_izquierdo:not(.shortBy3) {
    height: 100%;
}
#lado_izquierdo.shortBy3 {
    height: calc(100% - 3px);
}
#principal.shortBy3 {
    height: calc(100% - 3px);
}
#lado_izquierdo form {
    background-color: #e8e8e8;
    height: <?= $enableHiddenMenu ?"calc(100% - 126px)":"100%" ?>;
    margin: 0 auto;
<?php if ($enableHiddenMenu) { ?>
    box-shadow: -1px 1px 0 0 rgba(0,0,0,0.5) inset;
<?php } ?>
}
#mensaje_inicial, #mensaje_noticia, .noticia {
    font-size: 18px;
    font-weight: bold;
    text-align: justify;
    padding: 15px 15px 5px;
}
.menu_izquierdo ul {
    margin: 0px;
    padding: 0px;
    list-style-type: none;
    width: <?= $enableHiddenMenu ?"100%":"calc(100% - 10px)" ?>;
}
.menu_izquierdo ul.floating {
    position: absolute;
    top: 32px;
    left: 0px;
    z-index: 100;
    <?= $_esDesarrollo&&false?"animation: expandSubmenu 3s;":"" ?>
    <?= $_esDesarrollo&&false?"animation-delay: 1s;":"" ?>
    <?= $enableHiddenMenu?"width: calc(89%);":"" ?>
}
.menu_izquierdo ul.submenu>li {
    position: relative;
}
.menu_izquierdo ul.submenu>li>button:after {
    display: block;
    content: "";
    width: 12px;
    height: 6.75px;
    position: absolute;
    cursor: pointer;
    top: 0px;
    right: 0px;
    background: contain transparent url(<?= "/{$_pryNm}/imagenes/icons/upArrow.png" ?>) no-repeat;
} /* <img class="abs_ne" src="imagenes/icons/upArrow.png" width="12" style="cursor: pointer;"> */
.menu_izquierdo ul>li {
    margin-bottom: 10px;
    width: 100%;
}
.menu_izquierdo ul>li>ul>li {
    margin-bottom: 0px;
}
.menu_izquierdo ul>li .navSelected {
    background-color: rgba(180, 180, 230, 0.3);
}
.menu_izquierdo ul>li>ul>li .navSelected {
    background-color: rgba(200, 200, 255, 0.3);
}
.menu_izquierdo a:not(.noApply), .menu_izquierdo input[type="submit"], .menu_izquierdo button {
    font-size: 13px;
    font-weight: bold;
    font-variant: small-caps;
    color: #008;
    text-decoration: none;
    outline: none;
    text-align: center;
    display: block;
    cursor: pointer;
    background-color: rgba(0, 0, 0, 0.1);
    background: -webkit-radial-gradient(circle, rgba(0, 0, 0, 0.05), rgba(0, 0, 0, 0.1));
    background: -o-radial-gradient(circle, rgba(0, 0, 0, 0.05), rgba(0, 0, 0, 0.1));
    background: -moz-radial-gradient(circle, rgba(0, 0, 0, 0.05), rgba(0, 0, 0, 0.1));
    background: radial-gradient(circle, rgba(0, 0, 0, 0.05), rgba(0, 0, 0, 0.1)); 
    white-space: nowrap;
    padding: 5px;
    margin: 5px;
}
.menu_izquierdo input[type="submit"], .menu_izquierdo button {
    width: calc(100% - 10px);
    border: outset 2px;
}
.menu_izquierdo input[type="submit"]:active, .menu_izquierdo button:active {
    border: inset 2px;
    padding: 3px 7px 7px 3px;
    opacity: 0.6;
}
.menu_izquierdo ul>li>ul.floating input[type="submit"]:active, .menu_izquierdo ul>li>ul.floating button:active {
    opacity: 1;
}
.menu_izquierdo ul>li>ul>li input[type="submit"], .menu_izquierdo ul>li>ul>li button {
    margin-left: 12px;
    width: calc(100% - 24px);
    font-size: 90%;
    box-shadow: -3px 0px 0px 3px rgba(220,215,240,0.8), 3px 0px 0px 3px rgba(220,215,240,0.8);
}
.menu_izquierdo ul>li>ul.floating>li input[type="submit"], .menu_izquierdo ul>li>ul.floating>li button {
    background-image: url(<?= "/{$_pryNm}/imagenes/fondos/corrugatedbox7wm.jpg" ?>);
    background-repeat: repeat;
}
.menu_izquierdo a:hover, .menu_izquierdo input[type="submit"]:hover, .menu_izquierdo button:hover {
    background-color: rgba(100, 100, 255, 0.1);
}
.menu_izquierdo ul>li>ul>li a:hover, .menu_izquierdo ul>li>ul>li input[type="submit"]:hover, .menu_izquierdo ul>li>ul>li button:hover {
    background-color: rgba(200, 200, 255, 0.25);
}
.menu_izquierdo ul>li>ul.floating>li a:hover, .menu_izquierdo ul>li>ul.floating>li input[type="submit"]:hover, .menu_izquierdo ul>li>ul.floating>li button:hover {
    background-image: url(<?= "/{$_pryNm}/imagenes/fondos/corrugatedboxhwm.jpg" ?>);
    background-repeat: repeat;
}
div.scrollablewrapper {
    overflow: hidden;
    height: 100%;
    width: <?= $enableHiddenMenu?"100%":"calc(100% - 15px)" ?>;
    
}
div.scrollablewrapper div.scrollablediv {
    border: none;
    overflow-y: scroll;
    overflow-x: hidden;
    height: 100%;
    width: calc(100% + 17px);
}
#principal {
    float: left;
    padding-left: <?= $enableHiddenMenu?"20":"1" ?>px;
}
#principal:not(.shortBy3) {
    height: 100%;
}
<?php if ($enableHiddenMenu) { ?>
#principal {
    width: 100%;
}
<?php } else { ?>
#principal:not(.noMenu) {
    width: calc(100% - 199px);
}
#principal.noMenu {
    width: 100%;
}
<?php } ?>
#esasa_foot {
    height: 38px;
    overflow: auto;
}
#esasa_frame {
    width: 100%;
    height: inherit;
}
#area_acceso {
    text-align: left;
    width: 225px;
}
#area_acceso form {
    width: 100%;
    height: 100%;
}
#area_acceso fieldset {
    width: 220px;
    border: groove gray 2px;
    background-color: rgba(0, 0, 0, 0.1);
    min-width: 0;
}
#area_acceso legend {
    color: #008;
    font-size: 14px;
    width: auto;
    margin: auto;
    border: 0;
}
#area_acceso table {
    border-spacing: 0px;
    border-collapse: separate;
    width: 100%;
}
#area_acceso td {
    padding: 0px;
    padding-bottom: 2px;
    vertical-align: top;
}
#area_acceso td:first-child {
    width: 80px;
    padding-left: 2px;
    width: 1%;
    white-space: nowrap;
    text-align: left;
}
#area_acceso input[type="submit"] {
    font-size: 14px;
}
#area_usuario3 {
    text-align: left;
    width: 325px;
}
#area_usuario3 form {
    width: 100%;
    height: 100%;
}
#area_usuario3 fieldset {
    width: 300px;
    border: groove gray 2px;
    background-color: rgba(0, 0, 0, 0.1);
    min-width: 0;
}
#area_usuario3 legend {
    color: #008;
    font-size: 14px;
    width: auto;
    margin: auto;
    border: 0;
}
#area_usuario3 table {
    border-spacing: 0px;
    border-collapse: separate;
    width: 100%;
}
#area_usuario3 td {
    padding: 0px;
    padding-bottom: 2px;
    vertical-align: top;
}
#area_usuario3 td:first-child {
    width: 80px;
    padding-left: 2px;
    width: 1%;
    white-space: nowrap;
    text-align: left;
}
#area_usuario3 input[type="submit"] {
    font-size: 14px;
}
#area_usuario2 {
    height: calc(100% - 90px);
    width: 100%;
    top: 0px;
    /*display: inline-block;*/
    /*vertical-align: top;*/
    min-width: 500px;
    overflow-x: hidden;
    overflow-y: auto;
    position: relative;
}
#area_usuario2>p {
    margin: 0 auto;
    text-align: left;
    width: 80%;
    z-index: 100;
    position:  relative;
    background-color: rgba(230, 230, 230, 0.8);
    border-radius: 10px;
}
#area_usuario2_desc {
    width: 520px;
    /* margin: 0 auto; */
    left: calc(50% - 260px);
    overflow: auto;
    height: auto !important;
    position: absolute;
    top:0px;
    z-index: 0;
}
#area_usuario2_desc>div {
    width: 171px;
    margin: 1px;
    float: left;
    z-index: 0;
}
#area_usuario2_desc>div>* {
    margin: 7px;
    z-index: 1;
}
div.3col, div.col3 {
    padding-left:2%;
    padding-right:2%;
}
div.3col>div, div.col3>div {
    width: 30%;
    margin: 1%;
    float: left;
}
input[type="button"].fix1 {
    padding-block: 0px;
    padding-inline: 3px;
}
#area_usuario {
    width: 500px;
    overflow: hidden;
}
#area_usuario h1 {
    height: 92px;
}
#area_usuario_desc {
    overflow-x: hidden;
    overflow-y: scroll;
    width: 520px;
    height: calc(100% - 130px);
    top: 0px;
    left: 0px;
    text-align: left;
}
div.scrollmaxdiv {
    width: 100%;
    height: 100%;
    overflow: auto;
}
div.central.base div.scrolldiv {
    width: calc(100% - 2px);
    height: calc(100% - 71.52px);
    overflow: auto;
}
div.central>div.contenido {
    width: calc(100% - 2px);
    height: calc(100% - 71.52px);
}
.subh21>div {
    height: 20.8px;
}
.oneLine, .footOneLine {
    height: 25px;
}
.oneLineI {
    height: 25px !important;
}
.afterOneLineForm, .lessOneLine {
    height: calc(100% - 25px);
}
.lessTwoLines {
    height: calc(100% - 50px);
}
.lessTwoLinesB {
    height: calc(100% - 55px);
}
.oneFootB {
    height: 30px;
}
.afterHeader {
    height: calc(100% - 60px);
}
.subHeadFoot {
    height: calc(100% - 85px);
}
#admin_head {
    overflow: auto;
    height: 60px;
}
#admin_block {
    width: calc(100% - 2px);
    height: calc(100% - 60px);
    overflow: auto;
    margin: 0 auto;
    text-align: center;
}
#admin_block>form, #admin_block>div.blk {
    vertical-align: top;
    width: 460px;
    margin: 0 auto;
    text-align: left;
    background-color: rgba(255, 255, 255, 0.3);
    padding: 5px;
}
#admin_block .searchblock {
    white-space: nowrap;
}
#admin_block .searchblock>input[type="text"] {
    width: calc(100% - 22px);
    padding-right: 20px;
}
#admin_block .searchblock>img.searchicon {
    margin-left: -24px;
    vertical-align: -4px;
}
/*_/ #admin_block td label { /*/ #forma_admon_gpo td label { /**/
    margin-bottom: 0px !important;
    font-weight: normal !important;
}
#repfactform button, #repfactform input, #repfactform select, #repfactform textarea {
    font: 400 13.3333px Tahoma;
}
#repfactform td, #repfactform th {
    padding: 1px;
}
#repfactform button.multiselect {
    border-radius: 0px;
    padding-right: 6px;
    padding-left: 6px;
}
#repfactform ul.dropdown-menu {
    min-width: 120px;
    padding: 1px 0;
    
}
#repfactform ul.dropdown-menu>li>a {
    padding: 1px 3px 0px 3px;
}
#repfactform ul.multiselect-container>li>a>label {
    padding: 1px 3px;
}
#repfactform ul.multiselect-container input {
    display: none;
}

#area_cuentas {
    height: 100%;
    width: 100%;
    top: 0px;
    margin: 0 auto;
    text-align: center;
}
#result_account {
    height: calc(100% - 116px);
    width: 100%;
    overflow-x: hidden;
    overflow-y: auto;
    margin: 0 auto;
} /* allcell */
#summary_account {
    margin-top: 2px;
    vertical-align: middle;
}
#result_table th {
    padding: 3px;
    border: 1px solid rgba(100,100,100,0.1);
    background-color: rgba(100,100,100,0.1);
}
#result_table td {
    padding: 3px;
    border: 1px solid rgba(100,100,100,0.1);
    background-color: rgba(255,255,255,0.2);
}
#log_contents, #log_contents>span {
    font-family: monospace;
    white-space: pre;
    text-align: left;
    margin: 0 auto;
}
pre.code, pre.mono, pre.fixed, .pre.mono, .preline.mono {
    font-family: monospace;
}
.pre.monoi, .preline.monoi {
    font-family: monospace !important;
}
h1.area_header {
    margin: 0 auto;
    text-align: center;
    margin-top: 10px;
    margin-bottom: 10px; 
    width: 100%;
    height: 30px;
}
h1.bs {
    margin-top: 20px;
    margin-bottom: 10px;
    line-height: 1.1;
    width: 100%;
    height: 30px;
    vertical-align: top;
}
h1.bq {
    margin-top: 18px;
    margin-bottom: 10px;
    line-height: 1.1;
    width: 100%;
    height: 30px;
    vertical-align: top;
}
div.central {
    height: 100%;
    width: 100%;
    top: 0px;
    display: inline-block;
    vertical-align: top;
}
div.central h1 {
    width: 100%;
    height: 30px;
    vertical-align: top;
}
.invoiceDoc {
    width: 32px;
    height: 32px;
    background-color: transparent;
    background-image: url(<?= "/{$_pryNm}/imagenes/icons/invChk200.png" ?>);
    background-size: 32px 32px;
}
.nobottompadding {
    padding-bottom: 0px;
    -webkit-padding-after: 0em;
}
.nobottommargin {
    margin-bottom: 0px;
    -webkit-margin-after: 0em;
}
.alignCenter {
    text-align: center;
}
.centered, tbody.centered td, thead.centered th, tfoot.centered td, tfoot.centered th, tr.centered td, tr.centered th {
    margin: 0 auto;
    text-align: center;
    align: center;
}
.textcenter {
    textalign: center;
}
.clear {
    float: none;
    clear: both;
}
.fullWidHigh {
    width: 100% !important;
}
.widmin {
    width: min-content;
}
.widavailableonly {
    width: -webkit-fill-available;
    width: -moz-available;
}
.widavailable {
    width: -webkit-fill-available;
    width: -moz-available;
    width: fit-content;
}
.allWidBut4 {
    top: 2px;
    left: 2px;
    width: calc(100% - 4px);
}
.allWidBut100 {
    width: calc(100% - 100px);
}
.allWidBut120 {
    width: calc(100% - 120px);
}
.allWidBut150 {
    width: calc(100% - 150px);
}
.allWidBut150i {
    width: calc(100% - 150px) !important;
}
.allWidBut200 {
    width: calc(100% - 200px);
}
.allWidBut220 {
    width: calc(100% - 220px);
}
.width50 {
    width: 50%;
}
.wid3rd {
    width: 33.33%;
}
.wid5th {
    width: 20%;
}
.halfwm1 {
    width: calc(50% - 1px);
}
.halfwp1 {
    width: calc(50% + 1px);
}
.halfwm5 {
    width: calc(50% - 5px);
}
.halfwp5 {
    width: calc(50% + 5px);
}
.width40 {
    width: 40%;
}
.width70 {
    width: 70%;
}
.width70i {
    width: 70% !important;
}
.width80 {
    width: 80%;
}
.width80i {
    width: 80% !important;
}
.wid95 {
    width: 95%;
}
.width100 {
    width: 100%;
}
.wid0 {
    width: 0px;
    inline-size: 0px;
}
.wid8px {
    width: 8px;
}
.wid12px {
    width: 12px;
}
.wid15px {
    width: 15px;
}
.wid17px {
    width: 17px;
}
.wid20px {
    width: 20px;
}
.wid24px {
    width: 24px;
}
.wid26px {
    width: 26px;
}
.wid30px {
    width: 30px;
}
.wid33px {
    width: 33px;
}
.wid35px {
    width: 35px;
}
.wid40, .wid40px {
    width: 40px;
}
.wid42 {
    width: 42px;
}
.wid45px {
    width: 45px;
}
.wid48px {
    width: 48px;
}
.wid50px {
    width: 50px;
}
.wid55px {
    width: 55px;
}
.wid70px {
    width: 70px;
}
.wid77px {
    width: 77px;
}
.wid85px {
    width: 85px;
}
.wid90px {
    width: 90px;
}
.wid100px, .wrap100, input[type="text"].pedido, input[type="text"].concepto, input[type="text"].remision {
    width: 100px;
}
.wid105px {
    width: 105px;
}
.wid108px {
    width: 108px;
}
.wid110px {
    width: 110px;
}
.wid119px {
    width: 119px;
}
.wid120px {
    width: 120px;
}
.wid125px {
    width: 125px;
}
.wid135px {
    width: 135px;
}
.wid140px {
    width: 140px;
}
.wid146px {
    width: 146.66px;
}
.wid150px {
    width: 150px;
}
.wid150pxi {
    width: 150px !important;
}
.wid151px {
    width: 151px;
}
.wid200px {
    width: 200px;
}
.wid205px {
    width: 205px;
}
.wid220px {
    width: 220px;
}
.wid220pxi {
    width: 220px !important;
}
.wid222px {
    width: 222.5px;
}
.wid225px {
    width: 225px;
}
.wid240px {
    width: 240px;
}
.wid245px {
    width: 245px;
}
.wid245pxi {
    width: 245px !important;
}
.wid270px {
    width: 270px;
}
.wid270pxi {
    width: 270px !important;
}
.wid300i {
    width: 300px !important;
}
.wid335px {
    width: 335px;
}
.wid350px {
    width: 350px;
}
.wid400px {
    width: 400px;
}
.wid450px {
    width: 450px;
}
.wid500px {
    width: 500px;
}
.wid834px {
    width: 834px;
}
.widRazSocH {
    width: calc(100% - 448px);
}
.widRazSoc {
    width: calc(100% - 463px);
}
.width100px {
    min-width: 100px;
}
.width120px {
    min-width: 120px;
}
.width240px {
    min-width: 240px;
}
.wid5em {
    width: 5em;
}
.wid10em {
    width: 10em;
}
.wid20em {
    width: 20em;
}
.wid30em {
    width: 30em;
}
.wid50em {
    width: 50em;
}
.wid50em2 {
    width: calc(50em + 12px);
}
.widMin4 {
    width: calc(100% - 4px);
}
.widMin31em {
    width: calc(100% - 31em);
}
.widMin36px {
    width: calc(100% - 36px);
}
.hgt100px {
    height: 100px;
}
.inlineblock, .inblock {
    display: inline-block;
}
.block, .shown {
    display: block;
}
.blocki {
    display: block !important;
}
.whiteshown {
    display: block;
    height: 0.1px;
}
.showntr {
    display: table-row;
}
/*
.box.acceso, .bodega1, .box.puertos {
    width: 300px;
}
*/

h1 select.likeh1 {
    border: 0px;
    font-size: 1em;
    font-weight: bold;
    background-color: transparent;
    box-shadow: 1px 1px 1px 1px gray;
}
h1 select.likeh1 option {
    font-size: 1em;
    font-weight: bold;
    background-color: transparent;
}
th>select.liketh {
    border: 0px;
    font-size: 14px;
    font-weight: bold;
    background-color: transparent;
    text-align-last: center;
    color: #008;
    box-shadow: 1px 1px 1px 1px lightgray;
}
h1 select.likebh1 {
    border: 0px;
    font-size: 1em;
    font-weight: bold;
    background-color: transparent;
    text-align-last: center;
    color: #008;
    box-shadow: 1px 1px 1px 1px lightgray;
}
h1 select.likebh1 option {
    font-size: 1em;
    font-weight: bold;
    color: #008;
    background-color: transparent;
}
button.likeTH {
  /* padding: 3px; */
  /* border: 0px 0px 1px 0px */
  /* vertical-align: top */
  padding: 0px;
  border: 0px;
  margin: 0 auto;
  background-color: transparent;
}
table.contrafacturas {
    margin: 0 auto;
    text-align: center;
    width: 100%;
    background-color: transparent;
    table-layout: fixed;
}
table.contrafacturas * {
    font-family: Book, Bookman, "Book Antigua", Times, serif;
    font-size: 12px;
}
table.contrafacturas>thead>tr {
    background-color: rgba(0,0,0,0.03);
}
table.contrafacturas>thead>tr>th {
    margin: 0 auto;
    text-align: center;
}
table.contrafacturas>thead>tr>th>*, thead.vAlignMiddle>tr>th>* {
    vertical-align: middle;
}
table.contrafacturas>*>tr>[cell="Folio"],table.contrafacturas>*>tr>[cell="Fecha"] {
    width: 80px;
}
table.contrafacturas>*>tr>[cell="MP"] {
    width: 30px;
}
table.contrafacturas>*>tr>[cell="TC"] {
    width: 24px;
}
table.contrafacturas>*>tr>[cell="Peek"] {
    width: 30px;
}
table.contrafacturas>*>tr>[cell="Auth"] {
    width: 50px;
}
/*
table.contrafacturas tr>*:nth-child(1), table.contrafacturas tr>*:nth-child(2) {
    width: 80px;
}
table.contrafacturas tr>*:nth-child(3) {
    width: 30px;
}
table.contrafacturas tr>*:nth-child(4) {
    width: 24px;
}
table.contrafacturas tr>*:nth-child(7) {
    width: 50px;
}
*/
#contrafooter {
    width: calc(100% - 2px);
    border: 1px solid transparent;
    text-align: left;
}
#contrafoottb {
    /* min-width: 658.4px; */
    overflow: hidden;
    width: calc(100% - 2px);
}
#contraRscpBtn, #contraViewBtn {
    font-size: 9px;
    padding: 0px 2px;
    width: 18.4px;
}
.contrarrecibo, .contrarrecibo th, .contrarrecibo td,  {
    font-family: Book, Bookman, "Book Antigua", Times, serif;
}
.contrarrecibo table:not(.noApply) {
    border-spacing: 3px;
    border-collapse: separate;
}
.contrarrecibo td:not(.noApply) {
    border: 1px solid black;
    padding: 3px;
}
#area_central, #area_central2, #area_central3, #area_central4, #area_central_gencr {
    height: 100%;
    width: 100%;
    top: 0px;
    display: inline-block;
    vertical-align: top;
}
#area_central.formapago {
    height: calc(100% - 88.4px);
}
#area_central>h1, #area_central2>h1, #area_central3>h1, #area_central4>h1, #area_central_gencr>h1 {
    width: 100%;
    height: 30px;
    vertical-align: top;
}
#area_central>form:not(.noApply) {
    vertical-align: top;
    width: 100%;
    height: calc(100% - 60px);
}
#area_central2>div>form, #area_central3>div>form, #area_central_gencr>div>form {
    vertical-align: top;
    width: 100%;
}
#area_central2>div>form, #area_central3>div>form {
    height: 74px;
}
#area_central_gencr>div>form:not(.gencr) {
    height: calc(100% - 30px); /* 87px; */
}
#area_central_gencr>div>form.gencr {
    height: 87px;
}
#area_central4>div>form {
    vertical-align: top;
    width: 100%;
    height: 99px;
}
#area_central div.scrolldiv {
    width: 770px;
    height: calc(100% - 35px);
    overflow: auto;
    border: 1px outset gray;
    margin: 0 auto;
}
#area_central2 div.scrolldiv {
    width: calc(100% - 2px);
    height: calc(100% - 94px - 2px);
    overflow: auto;
    border: 1px solid lightgray;
}
#area_central3 div.scrolldiv, #area_central_gencr div.scrolldiv {
    width: calc(100% - 2px);
    overflow: auto;
    border: 1px solid lightgray;
}
#area_central3 div.scrolldiv {
    height: calc(100% - 74px - 26px);
}
#area_central4 div.scrolldiv {
    width: calc(100% - 2px);
    height: calc(100% - 99px - 26px);
    overflow: auto;
    border: 1px solid lightgray;
}
#area_central_gencr div.scrolldiv {
    margin-top: 3px;
}
#gencontraform>div.scrolldiv {
    height: calc(100% - 88px);
}
#scrolltablediv_gencr.scrolldiv {
    height: calc(100% - 118px);
}
#area_detalle {
    display: inline-block;
    text-align: left;
    align: left;
    height: calc(100% - 60px);
}
#area_detalle.noboots>fieldset {
    width: fit-content;
}
/* #area_detalle.noboots>fieldset:not(.hasmsg) {
    padding-bottom: 25px;
} */
#area_detalle.noboots>fieldset>legend {
    color: #008;
    font-size: 18px;
    width: auto;
    padding: 0 5px;
    margin-bottom: 4px;
    font-weight: bold;
    letter-spacing: 2px;
    background-color: rgba(0,0,0,0.1);
    position: sticky;
    left: 0;
}

ul.alternate>li:nth-child(even) {
    background-color: rgba(150, 150, 0, 0.1);
}
#admfactura_screen {
    width: calc(100% - 4px);
    height: calc(100% - 55px); /* 34px + 21px */
    background-color: rgba(255, 255, 255, 0.3);
    border: 1px solid lightgray;
    overflow: auto;
}
#admfactura_top {
    width: 100%;
    /* height: 234px; */
    /* eliminar padding bottom */
    /* o agregar padding top,right,-,left */
}
#admfactura_bottom {
    width: 100%;
    /* height: 156px; */
    /* eliminar padding top */
    /* o agregar padding -,right,bottom,left */
}
#admfactura_scroll {
    width: 100%;
    /* height: calc(100% - 390px); */ /* 234px + 156px */
    /* overflow: auto; */
}
#admfactura_extra {
    width: 100%;
}
#admfactura_foot {
    width: 100%;
    height: 21px;
    position: relative;
}
#footerAdminFacturas {
    width: 100%;
    height: 20px;
    vertical-align: middle;
    padding-left: 5px;
    padding-top: 3px;
}
#respaldaFooter {
    position: absolute;
    bottom: 0px;
    width: 100%;
    height: 26px;
}
.footer20 {
    height:20px;
    padding-top: 3px;
    padding-right: 5px;
    padding-left: 5px;
    font-size: 12px;
}
#catalog_menu {
    width: 117px;
    height: calc(100% - 54px);
    overflow-x: hidden;
    overflow-y: auto;
}
#catalog_menu input[type="button"] {
    width: 93px;
    text-align: center;
    padding: 3px;
    border: 1px inset black;
    margin-bottom: 0px;
    font-family: "Courier New", Courier, monospace;
    font-size: 12px;
}
#catalog_menu input[type="button"]:hover {
    border: 1px outset gray;
    background-color: rgba(100, 100, 255, 0.2);
    cursor: pointer;
}
#catalog_content {
    width: calc(100% - 118px);
    height: calc(100% - 54px);
}
#catalog_content>fieldset {
    display: table-cell;
    width: calc(100% - 30px);
    height: calc(100% - 20px);
    min-width: 0;
}

#catalog_column_table th:first-child, #catalog_content_table td:first-child {
    width: 1%;
    /* white-space: nowrap; */
}

#catalog_column_wrapper:not(.nofilter) {
    width: 100%;
    overflow: hidden;
    height: 52px;
}
#catalog_column_section:not(.nofilter) {
    width: 100%;
    overflow-x: auto;
    overflow-y: hidden;
    height: 67px; /* 52 + 15 */
}
#catalog_content_section:not(.nofilter) {
    width: 100%;
    height: calc(100% - 107px);
    overflow: auto;
}
.relative {
    position: relative;
}
#catalog_page_section, .relative100 {
    width: 100%;
    position: relative;
}
#catalog_numRegs {
    position: absolute;
    top: 0px;
    right: 0px;
}
#catalog_commit_section {
    position: absolute;
    top: 0px;
    left: 0px;
}
#providers_section {
    margin: 0 auto;
    text-align: center;
    width: calc(100% - 2px);
    overflow: auto;
}
#providers_section.basicHeight {
    height: calc(100% - 60px);
}
#providers_section:not(.basicHeight) {
    height: calc(100% - 72px);
}
#table_of_invoices {
    margin-top: 2px;
    margin-right: 15px;
    width: calc(100% - 15px);
}
#table_of_invoices>thead>tr {
    white-space: nowrap;
}
#table_of_invoices>thead>tr>th:not(.nopad), #table_of_invoices>tbody>tr>td:not(.nopad) {
    padding-right: 5px;
    padding-left: 5px;
}
#conceptsSection {
    height: inherit;
    overflow: auto;
    vertical-align: top;
}
#paymDocsSection {
    height: inherit;
    overflow: auto;
    vertical-align: top;
    text-align: left;
}
#movimientosbancarios {
    width: calc(100% - 15px);
}
#movimientosbancarios1 {
    width: 100%;
    height: calc(100% - 60px);
}
table.tabla_viaticos, table.tabla_caja {
    margin-bottom: 2px;
}
table.tabla_viaticos th, table.tabla_viaticos td, table.tabla_caja th, table.tabla_caja td, thead.padh1_3>tr>th, tbody.padd1_3>tr>td {
    padding: 1px 3px;
}
table.tabla_viaticos tr:first-child td, table.tabla_viaticos tr:first-child th, table.tabla_caja tr:first-child td, table.tabla_caja tr:first-child th, table.tabla_caja th.filecap {
    padding-top: 3px;
}
table.tabla_viaticos tr:last-child td, table.tabla_viaticos tr:last-child th, table.tabla_caja tr:last-child td, table.tabla_caja tr:last-child th {
    padding-bottom: 3px;
}
table.tabla_caja>thead.centered>tr>th {
    margin: 0 auto;
    text-align: center;
}
table.fixedHeader {
    margin: 0 auto;
    /* width: calc(100% - 2px); */
    border-collapse: collapse;
    border-spacing: 0;
}
table.fixedHeader:not(.noFixHgt) {
    height: calc(100% - 2px);
}
table.fixedHeaderEM {
    width: calc (50em + 19px);
    height: calc(100% - 2px);
    border-collapse: collapse;
    border-spacing: 0;
}
table.fixedHeader td, table.fixedHeader th:not(.scr) {
    border: 1px solid gray;
}
table.fixedHeader:not(.noblk) tbody {
    display: block;
    overflow-y: scroll;
    height: 100%;
    width: 100%;
    background-color:  rgba(255, 255, 255, 0.1);
    /* box-sizing: border-box;
    -moz-box-sizing: border-box;
    -webkit-box-sizing: border-box;
    border: 1px solid gray; */
}
table.fixedHeader tbody tr:nth-child(odd) {
    background-color:  rgba(255, 255, 255, 0.8);
}
table.fixedHeader tbody tr:nth-child(even) {
    background-color:  rgba(250, 250, 200, 0.2);
}
table.fixedHeader:not(.noblk) thead tr {
    display: block;
    width: 100%;
    height: 21px;
    background-color: rgba(0, 0, 0, 0.05);
}
table.fixedHeader tfoot tr:first-child {
    background-color: rgba(0, 0, 0, 0.05);
}
table.fixedHeader tfoot tr {
    display: block;
    width: 100%;
    height:25px;
    /* border-top: 1px solid gray; */
    /* background-color: rgba(0, 0, 0, 0.05); */
}
table.fixedHeader>thead>tr>th>div {
    border-bottom: 2px solid gray;
    padding-left: 3px;
    padding-right: 3px;
}
table.fixedHeader .rowCode {
    width: 56px;
}
table.fixedHeader .rowRazSoc {
    width: 500px;
}
table.fixedHeader .rowRFC {
    width: 112px;
}
table.fixedHeader .rowCuenta {
    width: 97px;
}
table.fixedHeader .rowOpinion {
    width: 97px; /* 97+29 */
}
table.fixedHeader .rowOpPags {
    width: 29px;
}
table.fixedHeader .rowStatus {
    width: 108px;
}
table.fixedHeader th.footLeft {
    width: 150px;
    height:20px;
    vertical-align: middle;
    text-align: left;
    padding-left: 5px;
    padding-bottom: 2px;
    border-right: none;
}
table.fixedHeader th.footCenter {
    width: 661px;
    height:20px;
    vertical-align: middle;
    text-align: center;
    padding-bottom: 2px;
    border-left: none;
    border-right: none;
}
table.fixedHeader th.footRight {
    width: 150px;
    height:20px;
    vertical-align: middle;
    text-align: right;
    padding-right: 5px;
    padding-bottom: 2px;
    border-left: none;
}
table.fixedHeader tfoot tr:first-child th.scr {
    border-bottom: 1px solid rgba(0,0,0,0.05);
}
table.fixedHeader .footForm {
    width: 975px;
    vertical-align: middle;
    padding-top: 2px;
    /* background-color: rgba(255,255,150,0.1); */
    /* background-color: rgba(0,0,0,0.1); */
}
table.pad2cnw td, table.pad2cnw th {
    padding: 2px;
    white-space: nowrap;
}
.catalog_column_hide_button, .catalog_column_show_button {
    position: absolute;
    right: 0px;
    cursor: pointer;
    width: 8px;
    height: 8px;
}
.concept_dropdown_button {
    position: absolute;
    right: 5px;
    top: 8px;
    cursor: pointer;
}
select.concept_dropdown_list {
    position: absolute;
    left: 3px;
    top: 22px;
    -moz-appearance: none;
    -webkit-appearance: none;
    appearance: none;
    overflow-y: auto;
}
select.concept_dropdown_list::-ms-expand {
    display: none;
    padding: 2px 3px 3px 3px;
    box-shadow: 1px 1px 1px 0px gray;  
}
select.concept_dropdown_list option:hover, .invertHoverBG:hover {
    box-shadow: 0 0 10px 100px #1e90FF inset;
    color: white;
}
select.inverse {
    color: #def;
    background-color: #77b;
}
select.inverse>option:hover {
    background-color: yellow;
}
select.inverse>option:not(:checked) {
    color: #def;
    background-color: #57d;
}
select.inverse>option:checked {
    background-color: #fff;
    color: #008;
}
select.inverse>option:checked:after {
    content: attr(title);
    background; #666;
    color: #fff;
    position: absolute;
    width: 100%;
    left: 0;
    border: none;
}
select:not(.inverse)>option:checked {
    color: #def;
    background-color: #57d;
}
div.admon>input[type="checkbox"]:checked {
    background-color: rgba(255, 200, 200, 0.1);
}
label.fixsmall {
    font-size: 9px;
    font-weight: normal !important;
    padding: 0 2px;
    margin-bottom: 0px !important;
}
label.fixsmall>input[type="checkbox"] {
    vertical-align: sub;
    width: 10px;
}
.abs_ne, .abs_nw, .abs_se, .abs_se13, .abs_sw, .abs_n, .abs_s, .abs_w, .abs_e, .abs {
    position: absolute;
}
.abs_n, .abs_ne, .abs_nw, div.toTop {
    top: 0px;
}
.abs_s, .abs_se, .abs_se13, .abs_sw, div.toBottom {
    bottom: 0px;
}
.abs_e, .abs_ne, .abs_se, div.toRight, td.toRight, th.toRight {
    right: 0px;
}
.abs_se13 {
    right: 13px;
}
.abs_w, .abs_nw, .abs_sw, div.toLeft, td.toLeft, th.toLeft {
    left: 0px;
}
.btm2 { bottom: 2px; }
.btm12 { bottom: 12px; }
.btm14 { bottom: 14px; }
.btm20 { bottom: 20px; }
.btm30 { bottom: 30px; }
.btm32 { bottom: 32px; }
.top2 { top: 2px; }
.top4 { top: 4px; }
.top5 { top: 5px; }
.t6 { top: 6px; }
.top8 { top: 8px; }
.top10 { top: 10px; }
.top20 { top: 20px; }
.lft4 { left: 4px; }
.rgt0 { right: 0px; }
.rgt4 { right: 4px; }
.rgt5 { right: 5px; }
.rgt7 { right: 7px; }
.rgt10 { right: 10px; }
.rgtm10 { right: -10px; }
.rgtm11 { right: -11px; }
.catalog_column_hide_button:not(.disabled):hover, .catalog_column_show_button:not(.disabled):hover {
    background-color: rgba(255, 255, 100, 0.4);
    filter: brightness(0.8) contrast(0.8) grayscale(0) hue-rotate(60deg) invert(0) saturate(5) sepia(0);
}
.catalog_column_hide_button:not(.disabled):active, .catalog_column_show_button:not(.disabled):active {
    background-color: rgba(255, 100, 100, 0.4);
    filter: brightness(1.5) contrast(0.8) grayscale(0) hue-rotate(270deg) invert(0) saturate(5) sepia(0);
}
.catalog_column_hide_button {
    top: 0px;
}
.catalog_column_show_button {
    bottom: 0px;
}

.column { float: left; }
.col_5 { width: 5%; }
.col_10 { width: 10%; }
.col_20 { width: 20%; }
.col_25 { width: 25%; }
.col_30 { width: 30%; }
.col_40 { width: 40%; }
.col_50 { width: 50%; }
.col_60 { width: 60%; }
.col_70 { width: 70%; }
.col_80 { width: 80%; }
.col_90 { width: 90%; }
.column2 {
    float: left;
    width: 50%;
}
.row:after {
    content: "";
    display: table;
    clear: both;
}
#column_data {
    border-bottom: 2px solid gray;
}
.catalog_table {
    border-collapse: collapse;
    table-layout: fixed;
    padding: 0px;
    border: 0px;
}
.catalog_table.fit, .widfit {
    width: fit-content;
}
.catalog_table.nice tr:nth-child(odd) {
    background-color:  rgba(255, 255, 255, 0.3);
}
.catalog_table.nice tr:nth-child(even), .litYellow {
    background-color:  rgba(240, 240, 200, 0.3);
}
.catalog_table td, .catalog_table th {
    padding: 2px;
    border: 1px solid lightgray;
}
table.maxfit, td.maxfit {
    width: max-content !important;
}
#page_section {
    padding-top: 1px;
    padding-bottom: 2px;
}
#resultarea_base { /* Remove space for Header H1 (34+37.52). Bootstrap H1 (30+30) */
    width: 100%;
    height: calc(100% - 60px);/* calc(100% - 34px - 37.52px); */
    position:relative;
}
#resultarea_compact {
    width: calc(100% -2px);
    height: calc(100% - 34px);
}
#resultarea_layout3 { /* Remove space for Header H1 (34+37.52) and footer (34) */
    width: 100%;
    height: calc(100% - 34px - 37.52px - 34px);
}
#footer34, .subfoot {
    height: 34px;
    line-height: 34px;
}
#foot0 {
    position: fixed;
    bottom: 0px;
    margin-bottom: 30px;
    right: 0px;
    width: calc(100% - 199px);
}
.lnHgt26 {
    line-height: 26px;
}
.lnh12 {
    line-height: 12px;
}
.baseline {
    line-height: 1.2;
}
.singleline {
    line-height: 1.0;
}
.btnfit20 {
    line-height: 14px;
    height: 20px;
    padding: 0px 2px;
}
.btnview1 {
    border-width: 1px;
    border-style: outset;
    border-color: #008; 
}
.btnview1:hover:not(.disabled) {
    background-color: rgba(128,128,256,0.3);
    color: #66a;
}
.btnview1:active:not(.disabled) {
    background-color: rgba(200,200,80,0.3);
    border-style: inset;
    border-color: #88f;
}
.btnvwTmp:hover {
    background-color: black;
    color: yellow;
    cursor: grab;
}
.btnvwTmp:active {
    cursor: grabbing;
}
/*
#catalog_area div.scrolldiv {
    width: 780px;
    height: 100%;
    overflow: auto;
    border: 1px outset gray;
}
#catalog_area {
    margin: 0 auto;
    width: 902px;
    height: calc(100% - 34px - 38px - 4px);
}
#area_central_cat {
    width: 100%;
    height: 100%;
}
#area_central_cat>thead {
    height: 54px;
}
#area_central_cat>thead>tr>th>h1 {
    margin-top: 10px;
    margin-bottom: 10px; 
}
#area_central_cat>tbody {
    height: calc(100% - 64px);
}
#area_central_cat>tbody>tr, #area_central_cat>tbody>tr>td {
    height: 100%;
}
*/

#reporte_filtros {
    width: 100%;
    height: 48px;
}
#reporte_contenido {
    width: 100%;
    height: calc(100% - 48px);
}
#reporte_accionesH, #reporte_accionesF, #paymDocsSection>h3, .wide {
    width: 100%;
    text-align: center;
    margin-top: 5px;
    margin-bottom: 5px;
}
#reporte_resultado {
    width: calc(100% - 10px);
    height: calc(100% - 90px);
    background-color: rgba(255, 255, 255, 0.3);
    border: 1px solid lightgray;
    overflow: auto;
}
/* ELIMINAR */
#invoice_resultarea, #ack_resultarea, #contra_resultarea {
    height: calc(100% - 34px);
}
.scrolldiv table.datatable:not(.wrapped), .scrolldiv.datatable table:not(.wrapped), table.fullwidth {
    width: calc(100% - 2px);
}
#xml_selector, #xml_insert, .subfoot2 {
    height: 22px;
}
#waitRoll.gencr {
    height: calc(100% - 26px);
    padding-top: 3px;
}
#waitRoll.gencr>img {
    height: calc(100% - 30px);
}
#waiting-roll {
    margin-top: 25px;
}
#waitCentered {
    position: absolute;
    z-index: 9999;
    width: 316px;
    height: 315px;
    top: 50%;
    left: 50%;
    margin-top: -158px;
    margin-left: -158px;
    user-select: none;
    -webkit-user-select: none;
    -moz-user-select: none;
    -khtml-user-select: none;
    -ms-user-select: none;
}
.cartroll {
    background-image: url(<?= "/{$_pryNm}/imagenes/cartrolll.gif" ?>);
    background-repeat: no-repeat;
    background-position: center center;
}
.hgtBtn {
    height: 23px;
    line-height: 17px;
    font-size: 12px;
}
div.calendar_widget {
    position: absolute;
    float: left;
    top: 0px;
    left: 0px;
    width: 221px;
    height: 148px;
    display: none;
    z-index: 1000;
}
div.calendar_month_wrapper, span.calendar_month_wrapper {
    display: inline-block;
    overflow: hidden;
    width: 22px;
    height: 22px;
    vertical-align: bottom;
}
div.calendar_month_wrapper:hover, span.calendar_month_wrapper:hover, .calendarFX:hover {
    opacity: 0.6;
    filter: alpha(opacity=6);
    cursor: pointer;
}
img.calendar_month_01 {
    margin: 0;
}
img.calendar_month_02 {
    margin: 0 0 0 -22px;
}
img.calendar_month_03 {
    margin: 0 0 0 -44px;
}
img.calendar_month_04 {
    margin: 0 0 0 -66px;
}
img.calendar_month_05 {
    margin: -22px 0 0 0;
}
img.calendar_month_06 {
    margin: -22px 0 0 -22px;
}
img.calendar_month_07 {
    margin: -22px 0 0 -44px;
}
img.calendar_month_08 {
    margin: -22px 0 0 -66px;
}
img.calendar_month_09 {
    margin: -44px 0 0 0;
}
img.calendar_month_10 {
    margin: -44px 0 0 -22px;
}
img.calendar_month_11 {
    margin: -44px 0 0 -44px;
}
img.calendar_month_12 {
    margin: -44px 0 0 -66px;
}
input.calendar {
    width: 85px;
    height: 21.2px;
    text-align: center;
    vertical-align: top;
}
input.calendarV {
    width: 98px;
    text-align: center;
}

td.semitransparent, div.semitransparent {
    background-color: rgba(255, 255, 255, 0.7);
}
#textarea_ayuda, .noresize { resize: none; }
#textarea_gastos, #motivos_field {
    resize: none;
    background-color: rgba(255, 255, 255, 0.7);
    height: 45px;
    width: 350px;
}

.boton.bodega td.centered, .box.gastos td.centered.boton, .box.cancelar td.centered.boton {
    margin: 0 auto;
    text-align: center;
}
.box {
    border: groove gray 2px;
    background-color: rgba(0, 0, 0, 0.1);
    height: 85%;
    display: table-cell;
    min-width: 0;
}
.box table {
    border-spacing: 0px;
    border-collapse: separate;
    width: 100%;
}
.box td {
    padding: 0px;
    overflow: hidden;
    padding-bottom: 2px;
    vertical-align: top;
}
.box table td:first-child:not(.noShrink) {
    padding-left: 2px;
    width: 1%;
    white-space: nowrap;
    text-align: left;
}
.box table td:nth-child(2) {
    padding-left: 2px;
    white-space: nowrap;
    text-align: left;
}
.box.bodega:not(.boton) td:nth-child(odd) {
    white-space: nowrap;
    padding-right: 3px;
    text-align: left;
}
.box.bodega:not(.boton) td:first-child {
    width: 54px;
}
.box.bodega:not(.boton) td:nth-child(2) {
    width: 225px;
}
.box.bodega td select {
    width: 217px;
}
.box.bodega:not(.boton) td:nth-child(3) {
    width: 86px;
}

.box.bodega:not(.boton) td:nth-child(even) {
    white-space: nowrap;
    text-align: left;
}
.box.bodega:not(.boton) td:nth-child(even) input[type="text"] {
    width: 95%;
}
ul.hybull {
    text-align: left;
    list-style-type: none;
    padding: 0;
    margin: 0;
}
ul.hybull>li {
    padding-left: 25px;
}
ul.hybull>li:before {
    content: "⁃"; /* &hybull; */
    padding-right: 10px;
    color: brown;
}
ul.dtl {
    text-align: left;
    list-style-type: none;
    margin: 10px;
    padding: 0;
    border: 1px solid #ddd;
}
ul.dtl>li {
    vertical-align: middle;
    margin: 0;
}
ul.dtl>li.oddA {
    background-color: rgba(150, 255, 150, 0.05);
}
ul.dtl>li.oddB {
    background-color: rgba(100, 200, 200, 0.1);
}
ul.dtl>li.oddC {
    background-color: rgba(255, 255, 255, 0.2);
}
ul.dtl>li>table {
    border-spacing: 0px;
    border-collapse: separate;
    width: calc(100% - 2px);
    /*border-left: 1px solid #ddd;*/
    /*border-top: 1px solid #ddd;*/
    /*border-right: 1px solid #ddd;*/
}
ul.dtl>li>table th, ul.dtl>li>table td {
    padding: 2px;
}
ul.dtl>li>table th {
    border-bottom: 2px solid #bbb;
}
ul.dtl>li>table td {
    /* border-bottom: 1px solid #ddd; */
    vertical-align: bottom;
}
ul.dtl>li:nth-child(2)>table td>p {
    padding-top: 2px;
}
ul.dtl>li>table td:first-child>p, ul.dtl>li>table th:first-child>p {
    padding-left: 2px;
}
ul.dtl>li>table td>p {
    -webkit-margin-before: 0px;
    -webkit-margin-after: 0px;
    margin: 0px;
    padding-bottom: 2px;
}

ul.dtl>li>table td:nth-child(2), ul.dtl>li>table th:nth-child(2) {
    width: 65%;
}
ul.dtl>li>table td:nth-child(3), ul.dtl>li>table th:nth-child(3) {
    width: 25px;
    font-size: 8px;
    text-align: center;
}
ul.restaura {
    width: fit-content;
    margin: 0 auto;
    text-align: center;
}
ul.restaura>li {
    text-align: left;
}
ul.restaura>li>span {
    text-align: left;
    display: inline-block;
    width: 135px;
    white-space: nowrap;
}
.fs12, div.fs12 *, table.fs12>tbody>tr>td, table.fs12a * {
    font-size: 12px;
}
.node:not(.expanded), .leaf:not(.expanded) { display: none; }
.node { text-align: left; align: left; margin-before: 0px; -webkit-margin-before: 0px; }
.node > li {
    vertical-align: middle;
}
.node > li {
    border-bottom: 1px solid #ddd;
}
.node > li > table:hover, .node > li > span > table:hover {
    background-color: rgba(255, 255, 127, 0.08);
}
.cfdi-required {
    font-weight: bold;
}
.cfdi-x.cfdi-required {
    color: maroon;
    background-color: rgba(255,100,100,0.1);
}
.cfdi-x.cfdi-optional {
    color: rebeccapurple;
}

#localidadBodega {
    width: 85.5%;
}
#box_bodega.box.bodega {
    text-align: left;
}
.bodega2, .gastos, .cancelar, .wrap600 {
    width: 600px;
}
.wrap800 {
    width: 800px;
}
#top {
    margin: 0 auto;
    align-items: center;
}
#area_titulo {
    clear: both;
    text-align: center;
    margin: 0 auto;
}
#area_folio {
    float: right;
    width: 150px;
    margin-right: 3px;
    text-align: right;
}
#folio_value {
    display: inline-block;
    text-align: center;
    border: solid black 1px;
    background-color: #999;
    width: 100px;
}
.top_area {
    display: inline-block;
    vertical-align: middle;
    white-space: nowrap;
}
.nowrap, table.nowrap td, table.nowrap th, tr.nowrap td, tr.nowrap th, .diamonto, .totalfila {
    white-space: nowrap;
}
.seccion {
    margin: 0 auto;
    display: inline-block;
    padding: 3px;
    margin-top: 0px;
    margin-bottom: 3px;
    overflow: hidden;
    display: table-cell;
    min-width: 0;
}
.hgtEPFP       { height: 121px; }
.hgtSPTP       { height: 136px; }
.hgtETW        { height: 142px; }
.hgtSCP        { height: 189px; }
.hgtSDW        { height: 230px; }
.fullbox       { width: 99%; float: left; }
.fullclearbox  { width: 100%; clear: both; display: block; }
.halfbox       { float: left; overflow: hidden; }

#area_transporte input[type="text"]     { width: 90%; }
#area_peso .pesotable                   { width: 97%; }
#area_peso td.pesofirsttd               { width: 80px; }
#area_peso td:nth-child(3)              { width: 1%; }
#area_botones .box                      { margin-top: -3px; padding-top: 8px; }

#tabla_empleado {
    margin: 0 auto;
    text-align: center;
    border-spacing: 0px 2px;
}
#tabla_empleado td {
    text-align: left;
}
#tabla_empleado td:not(:first-child) {
    white-space: nowrap;
}
#tabla_empleado td:nth-child(odd) {
    padding-right: 0px;
}
#tabla_empleado td:nth-child(even) {
    padding-left: 0px;
}
#tabla_empleado td:nth-child(2n+3) {
    padding-left: 5px;
}
#lista_empleados {
    
}
.tdcol4Name {
    width: 120px;
    padding-right: 10px;
}
.box.usuario #nombreUsuario {
    width: 144px;
}
.box.usuario table td:nth-child(odd), .box.persona table td:nth-child(odd) {
    width: 107px;
    white-space: nowrap;
    padding: 2px;
}
.box.usuario table td, .box.persona table td {
    text-align: left;
}
.box.perfil table {
    border-spacing: 20px 5px;
    border-collapse: separate;
}
.box.perfil table td {
    width: 1%;
    white-space: nowrap;
    text-align: center;
    display: table-cell;
    border: 1px groove lightgray;
    background-color: rgba(255, 255, 255, 0.5);

}
.box.perfil table td div {
    vertical-align: top;
    text-align: left;
    margin: 0 auto;
    padding-left: 5px;
    padding-right: 5px;
    display: inline-block;
    overflow: auto;
}
.upxgBtn, .op80 {
    opacity: 0.8;
    filter: opacity(80%);
}
.hasPxG {
    opacity: 2;
    filter: brightness(120%) opacity(200%) drop-shadow(0 0 5px limegreen);
    background-color: rgba(50,205,50,0.1);
    cursor: pointer;
}
.sharp {
    filter: brightness(120%) contrast(3);
}
.lightOver:hover {
    filter: brightness(0.95) contrast(5) grayscale(0) hue-rotate(40deg) invert(0) saturate(4) sepia(0);
}
.lightOver1:hover {
    filter: brightness(1) contrast(0.8) grayscale(0) hue-rotate(30deg) invert(0) saturate(5) sepia(0);
}
.lightOver2:hover {
    filter: brightness(1) contrast(0.8) grayscale(0) hue-rotate(60deg) invert(0) saturate(5) sepia(0);
}
.lightOver3:hover {
    filter: brightness(1) contrast(0.8) grayscale(0) hue-rotate(90deg) invert(0) saturate(5) sepia(0);
}
.lightOver4:hover {
    filter: brightness(1) contrast(0.8) grayscale(0) hue-rotate(120deg) invert(0) saturate(5) sepia(0);
}
.lightOver5:hover {
    filter: brightness(1) contrast(0.8) grayscale(0) hue-rotate(150deg) invert(0) saturate(5) sepia(0);
}
.lightOver6:hover {
    filter: brightness(1) contrast(0.8) grayscale(0) hue-rotate(180deg) invert(0) saturate(5) sepia(0);
}
.lightOver7:hover {
    filter: brightness(1) contrast(0.8) grayscale(0) hue-rotate(210deg) invert(0) saturate(5) sepia(0);
}
.lightOver8:hover {
    filter: brightness(1) contrast(0.8) grayscale(0) hue-rotate(240deg) invert(0) saturate(5) sepia(0);
}
.lightOver9:hover {
    filter: brightness(1) contrast(0.8) grayscale(0) hue-rotate(270deg) invert(0) saturate(5) sepia(0);
}
.lightOverA:hover {
    filter: brightness(1) contrast(0.8) grayscale(0) hue-rotate(300deg) invert(0) saturate(5) sepia(0);
}
.lightOverB:hover {
    filter: brightness(1) contrast(0.8) grayscale(0) hue-rotate(330deg) invert(0) saturate(5) sepia(0);
}
.greenFilter {
    filter: url('#teal-lightgreen');
    /*filter: url('#spring-grass');*/
}
.redFilter {
    filter: url('#dark-crimson-sepia');
    /*filter: url('#cherry-icecream');*/
    /*filter: url('#red-sunset');*/
}
.yellowFilter {
    filter: url('#golden-x-rays');
}
.ptOver {
    outline: 3px ridge rgba(120,40,20,0.8);
}
.ptOut {
    outline: none;
}
.ptDown {
    outline-style: inset !important;
    background-color: rgba(200,200,150,0.5);
    opacity: 0.6;
}
#accionesCell div p:first-child {
    margin-bottom: 0px;
    -webkit-margin-after: 0px;
}
#accionesCell div p:nth-child(2) {
    margin-top: 0px;
    -webkit-margin-before: 0px;
}
#accionesCell span {
    display: inline-block;
    text-align: center;
    width: 20px;
    margin-top: 0px;
}
.box.botones table td.centered {
    padding-left: 5px;
    padding-right: 5px;
    padding-top: 5px;
    text-align: center;
    margin: 0 auto;
}
.selectable:hover, p.relatedAction, .hoverable:hover, .alink:not(.nobg):hover {
    background-color: rgba(100, 100, 255, 0.2);
}
p.relatedAction span.relatedAction {
    background-color: rgba(0, 255, 0, 0.5);
}
p.msg {
    /* word-break: break-all; */
    text-align: left;
    margin-bottom: 0px;
    border-bottom: 1px solid gray;
}
p.msg.err {
    background-color:rgba(255,100,100,0.2);
    color: darkred;
}
p.stk {
    margin: 0 0 5px;
}
p.stk2 {
    margin: 0 0 5px 3px;
}
p.bts, div.bts {
    margin: 0 0 10px;
}
table.cfdiErrorList.err {
    border-bottom: 1px solid gray;
}
table.cfdiErrorList.err td {
    background-color:rgba(255,100,100,0.2);
    color: darkred;
}
#dialog_tbody td:not(:last-of-type).sumaTipo {
    border-right: solid #ddf 2px;
}
#dialog_tbody td:not(:first-of-type).sumaTipo {
    border-left: solid #ddf 2px;
}
#dialog_tbody td.sumaTipo {
    font-weight: bold;
    background-color: #ddf;
    color: #005;
    white-space: nowrap;
}
#dialog_tbody td:not(:last-of-type).sumaDia {
    border-right: solid #fdd 2px;
}
#dialog_tbody td:not(:first-of-type).sumaDia {
    border-left: solid #fdd 2px;
}
#dialog_tbody td.sumaDia {
    font-weight: bold;
    background-color: #fdd;
    color: #500;
    white-space: nowrap;
}
#dialog_tbody td:not(:last-of-type).sumaTotal {
    border-right: solid #dfd 2px;
}
#dialog_tbody td:not(:first-of-type).sumaTotal {
    border-left: solid #dfd 2px;
}
#dialog_tbody td.sumaTotal {
    font-weight: bold;
    background-color: #dfd;
    color: #050;
    white-space: nowrap;
}
#dialog_tbody td.subtotal, .btopg {
    border-top: solid gray 1px;
}
#transporte_field, .uppercase {
    text-transform: uppercase;
}
#chofer_field {
    text-transform: capitalize;
}
input, button {
    color: #008;
    border-width: 1px;
}
input[type="text"], input[type="number"], input[type="password"], textarea, select {
    color: #008;
    background-color: rgba(255, 255, 255, 0.7);
}
option {
    background-color: #f8f8f8;
}
input[type="text"].longtext, input[type="number"].longtext, input[type="password"].longtext, select.longtext {
    width: calc(100% - 8px);
}
input[type="text"].widfit2, select.widfit2 {
    width: calc(100% - 2px);
}
input[type="text"].widfit20 {
    width: calc(100% - 20px);
}
input[type="text"].widfit24 {
    width: calc(100% - 24px);
}
input[type="text"].middletext, input[type="number"].middletext, input[type="password"].middletext {
    width: calc(50% - 8px);
}
input[type="text"].fullnametext, input[type="number"].fullnametext, input[type="password"].fullnametext {
    width: 350px;
}
input[type="text"].nombreV, input[type="text"].lugar, div.registro, select.nombreV {
    width: 300px;
}
input[type="text"].nombre, select.empresa, .wid250 {
    width: 250px;
}
.widX250 {
    width: calc(100% - 250px);
}
select.conceptoV {
    width: 200px;
}
input[type="text"].cuenta, div.actionId {
    width: 132px;
}
input[type="text"].smalltext, input[type="number"].smalltext, input[type="password"].smalltext {
    width: 120px;
}
input[type="text"].folio, input[type="number"].folio {
    width: 88px;
}
select.folio {
    width: 94px;
}
input[type="text"].folioV, input[type="number"].folioV {
    width: 98px;
}
select.folioV {
    width: 104px;
}
input[type="text"].folioV2 {
    width: 50px;
}
input[type="number"].importe {
    width: 98px;
    text-align: right;
}
input[type="text"].numero,input[type="text"].codigo,select.status {
    width: 70px;
}
input[type="text"].minitext, input[type="number"].minitext, input[type="password"].minitext {
    width: 50px;
}
textarea.query {
    width: calc(90% - 138px);
    vertical-align: top;
    resize: none;
}
.vcenter {
    position: relative;
    top: 50%;
    transform: translateY(-50%);
}
.rot30 {
    transform: rotate(-30deg);
}
.rot5anim {
    animation: keyrotation 5s infinite linear;
}
.rot10anim {
    animation: keyrotation 10s infinite linear;
}
@keyframes keyrotation {
    from {
        transform: rotateZ(0deg);
    }
    to {
        transform: rotateZ(360deg);
    }
}
.creditDays {
    float: right;
    font-size: 36px;
    line-height: 28px;
}
.pesoField {
    margin-top: 3px; 
    padding: 2px 3px;
    width: 128px;
    font-size: 36px;
    text-align: center;
}
.inputtext {
    -moz-appearance: textfield;
    -webkit-appearance: textfield;
    color: #446;
    background-color: #DDDS; /* white; */
    background-color: -moz-field;
    border: 1px solid darkgray;
    box-shadow: 1px 1px 1px 0 lightgray inset;  
    font: -moz-field;
    font: -webkit-small-control;
    font-size: 36px;
    margin-top: 5px;
    padding: 2px 3px;
    width: 130px;
    display: inline;
    text-align: center;
}

.textarea {
    -moz-appearance: textfield-multiline;
    -webkit-appearance: textarea;
    border: 1px solid gray;
    font: medium -moz-fixed;
    font: -webkit-small-control;
    height: 28px;
    overflow: auto;
    padding: 2px;
    resize: both;
    width: 400px;
}
.no_selection {
    user-select: none;
    -webkit-user-select: none; /* webkit (safari, chrome) browsers */
    -moz-user-select: none; /* mozilla browsers */
    -khtml-user-select: none; /* webkit (konqueror) browsers */
    -ms-user-select: none; /* IE10+ */
}
input.no_selection {
    background: transparent;
}
input.no_selection::selection {
    background: transparent;
}
.auto_selection {
    user-select: auto;
    -webkit-user-select: auto; /* webkit (safari, chrome) browsers */
    -moz-user-select: auto; /* mozilla browsers */
    -khtml-user-select: auto; /* webkit (konqueror) browsers */
    -ms-user-select: auto; /* IE10+ */
}
.text_selection {
    user-select: text;
    -webkit-user-select: text; /* webkit (safari, chrome) browsers */
    -moz-user-select: text; /* mozilla browsers */
    -khtml-user-select: text; /* webkit (konqueror) browsers */
    -ms-user-select: text; /* IE10+ */
}
#overlay {
    position: fixed;
    top: 0px;
    left: 0px;
    width: 100%;
    margin: 0 auto;
    height: calc(100% - 30px);
    text-align: center;
    z-index: 2500;
    font-size: 12px;
    background-color:  rgba(240, 240, 240, 0.5);
    vertical-align: middle;
}
#mylog:not(.isAdmin) {
    display: none;
}
#mylog.isAdmin {
    white-space: break-spaces; /* pre-wrap; */
    z-index: 2500;
    position: absolute;
    left: 2.5px;
    bottom: 30px;
    width: calc(100% - 5px);
    pointer-events: none;
    opacity: 0.5;
    color: black;
    font-weight: bold;
    display: flex;
    flex-direction: column-reverse;
    text-shadow: 0 0 3px orange;
}
#backdrop {
    width: 100vw;
    height: 100vh;
    overflow: hidden;
    z-index: 8888;
    position: fixed;
    top: 0px;
    left: 0px;
    background-image: url(<?= "/{$_pryNm}/imagenes/fondos/corrugatedboxSemiT1.png" ?>);
    background-repeat: repeat;
}
#backdrop.transparent {
    background: transparent;
    background-image: unset;
    background-repeat: unset;
    pointer-events: all;
}
#autoCloseWindow {
    position: fixed;
    z-index: 8898;
    left: 200px;
    bottom: 20px;
    background-image: url(<?= "/{$_pryNm}/{$bkgdImgName}" ?>);
    background-repeat: repeat;
    overflow: hidden;
}
#dialogbox {
    margin: 20px auto 0px;
    border: 2px groove #333;
    max-width: calc(100% - 44px);
    max-height: calc(100% - 44px);
    background-image: url(<?= "/{$_pryNm}/{$bkgdImgName}" ?>);
    background-repeat: repeat;
}
#close_row {
    background-color:  rgba(0, 0, 0, 0.1);
    border-bottom: 1px groove darkgray;
    position: relative;
    height: 24px;
}
#dialog_title {
    margin: 0 auto;
    text-align: center;
    font-size: large;
    font-weight: bold;
}
#dialog_corner {
    top: <?= ($isFF?-1:0) ?>px;
    right: 0px;
    position: absolute;
}
#closeOverlay {
    width: 24px;
    height: <?= ($isFF?25:22) ?>px;
    padding: 0px 0px;
}
#dialog_resultarea {
    max-width: 100%;
    overflow-y: auto;
}
#dialog_resultarea.lefted {
    text-align: left;
}
#dialog_resultarea:not(.lefted) {
    text-align: center;
}
#dialog_resultarea.calc {
    min-height: 57px;
}
#dialog_resultarea:not(.twoCloseRows):not(.calc) {
    height: calc(100% - 55px);
}
#dialog_resultarea.twoCloseRows:not(.calc) {
    height: calc(100% - 81px);
}
#dialog_resultarea.calc:not(.twoCloseRows) {
    max-height: calc(100% - 55px);
}
#dialog_resultarea.twoCloseRows.calc {
    max-height: calc(100% - 81px);
}
#dialog_resultarea.reverse {
    display: flex;
    flex-direction: column-reverse;
}
#dialog_resultarea:not(.hScroll) {
    overflow-x: hidden;
}
#dialog_resultarea.hScroll {
    overflow-x: auto;
}
table.tableWithScrollableCells, table.fullyExpanded, .expandAll {
    width: 100%;
    height: 100%;
}
.fullArea {
    border: 5px solid transparent;
    width: calc(100% - 10px);
    height: calc(100% - 10px);
    margin: 0 auto;
}
.fullArea2 {
    border-width: 0px 0px 5px;
    border-style: solid;
    border-color: transparent;
    width: 100%;
    height: 100%;
    margin: 0 auto;
    overflow: auto;
}
#closeButtonArea:not(.twoCloseRows) {
    height: 26px;
}
#closeButtonArea.twoCloseRows {
    height: 52px;
}
#closeButtonArea {
    vertical-align: middle;
    margin-top: 4px;
}
#dialog_resultarea > table:not(.noApply) {
    border-collapse: collapse;
    margin: 0 auto;
    width: 100%;
}
#dialog_resultarea table.cfdiErrorList {
    border-collapse: collapse;
    margin: 0 auto;
}
#recurso_area table:not(.noApply) {
    border-collapse: collapse;
    width: auto;
}
#dialog_resultarea > table:not(.noApply) > tbody:not(.noApply) > tr:not(.noApply) > td:not(.noApply), #dialog_resultarea > table:not(.noApply) > thead:not(.noApply) > tr:not(.noApply) > th:not(.noApply) {
    padding: 3px;
    border-bottom: 1px solid #ddd;
    vertical-align: top;
}
#dialog_resultarea > table:not(.noApply) > tbody:not(.noApply) > tr:not(.noApply) {
    color: #557;
}
#dialog_resultarea > table:not(.noApply) > tbody:not(.noApply) > tr:nth-child(even):not(.noApply) {
    background-color:  rgba(240, 240, 200, 0.3);
}
#dialog_resultarea > table:not(.nohover) > tbody:not(.nohover) > tr:not(.nohover):hover {
    background-color: rgba(255, 255, 255, 0.5);
    color: #008;
}
#dialog_resultarea > table > tbody > tr > td:first-child:not(.noShrink) {
    width: 1%;
    white-space: nowrap;
}
#dialog_resultarea > table > tbody > tr > td.shrinkCol {
    width: 1%;
    white-space: nowrap;
}
#dialog_resultarea > table > tbody > tr > td:not(.nohover):hover {
    background-color: rgba(255, 255, 233, 0.5);
}
#dialog_resultarea > table > thead > th, thead > td, tfoot > th, tfoot > td {
    height: 10;
}
#dialog_resultarea>div {
    width: 100%;
    height: 100%;
}
#load_invoice_structure {
    border-collapse: collapse;
    width: 100%;
    border: 5px solid transparent;
}
#load_invoice_structure > tbody > tr > td:first-child {
    padding-right: 5px;
    vertical-align: top;
}
#load_invoice_structure.facturas>tbody>tr {
    border-bottom: groove white 2px;
}
#load_invoice_structure.facturas>tbody>tr>td:first-child.success, #load_invoice_structure.archivos>tbody>tr.uploadData>td:first-child {
    width: 30%;
}
#load_invoice_structure.facturas>tbody>tr>td:first-child:not(.success), #load_invoice_structure.archivos>tbody>tr.uploadErrorData>td:first-child {
    padding-bottom: 0px;
}
#load_invoice_structure>tbody>tr>td:nth-child(2) {
    width: 65%;
    vertical-align: top;
    line-height: 1.6;
    padding: 5px;
}

#table_of_concepts {
    margin-top: 2px;
    margin-right: 15px;
    width: calc(100% - 15px);
}
#table_of_concepts>thead>tr {
    white-space: nowrap;
}
#table_of_concepts>thead>tr>th:not(.nopad), #table_of_concepts>tbody>tr>td:not(.nopad) {
    padding-right: 5px;
    padding-left: 5px;
}
#pdfviewer_container {
    margin: 0 auto;
    text-align: center;
    width: calc(100% - 10px);
    height: calc(100% - 76px);
    padding-top: 5px;
}
#pdfviewer_containerb {
    margin: 0 auto;
    text-align: center;
    width: calc(100% - 10px);
    height: calc(100% - 60px);
    padding-top: 5px;
}
#pdfv_canvas_container {
    width: 100%;
    height: calc(100% - 25px);
    overflow: auto;
    background-color: rgba(33,33,33,0.33);
    text-align: center;
    border: solid 3px gray;
}
#pdfv_controls {
    width: 600px;
    margin: 0 auto;
}
#pdfv_left_controls {
    float: left;
    width: 100px;
}
#pdfv_zoom_controls {
    float: right;
    width: 110px;
}
#pdfv_alter_container {
    text-align: center;
}
#pdfv_alter_container>* {
    display: inline-block;
    padding: 5px;
    margin: 1px;
    border: 1px solid gray;
    vertical-align: middle;
}
#pdfv_append_container>* {
    vertical-align: middle;
}
#pdfv_new_file {
    display: none;
}
#pdfv_new_label {
    width: 100px;
    height: 20.8px;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
    direction: rtl;
}
.pdfvBtn {
    display: inline-block;
    width: 24px;
    height: 24px;
    border: 1px outset #f0f0f0;
    border-radius: 4px;
    padding: 2px;
    user-select: none;
    -moz-user-select: none;
    -khtml-user-select: none;
    -webkit-user-select: none;
    -o-user-select: none;
    cursor: pointer;
}
.pdfvBtn.disabled {
    background-color: rgba(0,0,0,0.5);
    border: 1px solid transparent;
    cursor: auto;
}
.pdfvBtn:hover:not(.disabled) {
    background-color: rgba(190, 200, 160, 0.5);
    border: 1px solid lightgray;
}
.pdfvBtn:active:not(.disabled) {
    background-color: rgba(190,200,160,0.5);
    border-width: 2px 1px 1px 2px;
    border-style: inset;
    border-color: darkgreen;
}

#pdfv_navigation_controls {
    margin: 0 auto;
    width: calc(100% - 220px);
}
#pdfv_clear_controls {
    clear: both;
}
#pdfv_current_page, #pdfv_del_page, #pdfv_to_page {
    text-align: right;
    width: 43px;
}
#pdfv_controls * {
    vertical-align: middle;
}
#pdfv_message {
    height: 40px;
    overflow: auto;
    padding-top: 5px;
}
div.noccpwarn {
    width: 100%;
    height: 100px;
    padding: 3px;
    z-index: 100;
    position: relative;
    background-color: rgba(255,255,255,0.3);
}
div.noccplist {
    width: 100%;
    padding: 3px;
    z-index: 100;
    position: relative;
    background-color: rgba(255,255,255,0.3);
}
#cancelInvoice {
    display: inline-block;
    position: relative;
}
#cancelReasonBlk {
    position: absolute;
    top: -7px; /* (20.8-32.8)/2 */
    left: -226px; /* (62-365)/2=-151.5 => (62-514)/2=-226 */
    width: 404px; /* falta agregar texto: MOTIVO DE RECHAZO 365+76+11+62*/
                  /* y falta agregar boton: Cancelar */
    padding: 4px;
    background-color: rgb(200,227,255);
    background-image: url(<?= "/{$_pryNm}/imagenes/fondos/corrugatedboxSemiT1.png" ?>);
    background-repeat: repeat;
    border: 2px solid black;
    text-align: center;
}
#cancelReasonBlk:not(.hidden) {
    display: inline-block;
}
#cancelUserCap {
    display: inline-block;
    width: 62px;
    font-weight: bold;
    margin-right: 3px;
    text-align: left;
}
#cancelUserName {
    width: 85px;
    text-align: left;
}
#cancelUserFullName {
    display: inline-block;
    width: 237px;
    margin-left: 3px;
    text-align: left;
}
#cancelReasonCap {
    display: inline-block;
    width: 141px;
    font-weight: bold;
    margin-right: 3px;
    text-align: left;
}
#cancelReasonTxt {
    width: 247px;
    text-align: left;
}
#cancelReasonSnd {
    width: 46px;
    margin-left: 3px;
}
#cancelReasonBkw { /* (Cancelar) */
    width: 59px;
    margin-left: 3px;
}
.briefLog {
    font-size: 9px;
    font-weight: bold;
    color: #500;
}
textarea.softHide {
    position: fixed;
    top: 0;
    left: 0;
    width: 2em;
    height: 2em;
    padding: 0;
    border: none;
    outline: none;
    box-shadow: none;
    background: transparent;
    opacity: 0.1;
    font-size: 4px;
    color: #fff;
}
.softHide::-webkit-scrollbar {
    width: 1px;
    height: 1px;
}
.minScrBar::-webkit-scrollbar {
    width: 4px;
    height: 4px;
}
.minScrBar::-webkit-scrollbar-track {
    background-color: rgba(127,127,127,0.2);
    -webkit-border-radius: 2px;
}
.minScrBar::-webkit-scrollbar-thumb {
    background: rgba(0, 0, 0, 0.2);
    -webkit-border-radius: 2px;
}
.minScrBar::-webkit-scrollbar-thumb:hover {
    background: rgba(0, 0, 0, 0.5); 
}
.scrwid {
    scrollbar-width:thin;
}
.resultarea:not(.nocenter) {
    text-align: center;
    // display: flex;
    align-items: center;
}
.flexCenter {
    display: flex;
    justify-content: center;
    align-items: center;
}
.flexColumn {
    flex-direction: column;
}
.resultarea table:not(.noApply):not(.contrafacturas), .puertosarea table:not(.noApply) {
    border-collapse: collapse;
    margin: 0 auto;
    width: calc(100% - 10px);
}
.resultarea table:not(.contrafacturas)>thead:not(.noBGColor)>tr, thead.darker>tr, tfoot.darker>tr, .darkerBG, input[type="text"].darkerBG {
    background-color: rgba(0, 0, 0, 0.05);
}
.resultarea table:not(.noApply):not(.contrafacturas) tbody:not(.noApply) tr:not(.noApply) {
    color: #557;
}
.resultarea table:not(.contrafacturas)>thead>tr>th, .resultarea table:not(.contrafacturas)>thead>tr>td, .resultarea table:not(.contrafacturas)>tfoot>tr>th, .resultarea table:not(.contrafacturas)>tfoot>tr>td {
    height: 10px;
}
.resultarea table:not(.contrafacturas)>tbody>tr:not(.nohover):hover {
    background-color: rgba(255, 255, 255, 0.3);
}
.resultarea table:not(.contrafacturas):not(.generacontra)>tbody>tr:not(.nohover):hover {
    color: #008;
}
.resultarea table:not(.noApply):not(.contrafacturas)> tbody:not(.noApply) tr:not(.noApply):nth-child(even) {
    background-color: rgba(240, 240, 200, 0.3);
}
.resultarea table:not(.contrafacturas)>tbody>tr:last-child th:not(.noApply) {
    height: 100%;
}
.resultarea table:not(.contrafacturas)>tbody>tr>td:not(.nohover):hover {
    background-color: rgba(200, 255, 200, 0.2);
}
.resultarea table:not(.noApply):not(.contrafacturas) tr:not(.noApply)>td:not(.noApply), .resultarea table:not(.noApply):not(.contrafacturas) tr:not(.noApply) th:not(.noApply) {
    padding: 3px;
    border-bottom: 1px solid #ddd;
    vertical-align: top;
}
.resultarea table:not(.contrafacturas) tr>td.shrinkCol, .shrinkCol {
    width: 1%;
    white-space: nowrap;
}
.resultarea table:not(.contrafacturas) tr:not(.noShrink)>td:first-child:not(.noShrink) {
    width: 1%;
    white-space: nowrap;
}
.resultarea table:not(.contrafacturas) td.middle {
    padding: 3px;
    border-bottom: 1px solid #ddd;
    vertical-align: middle;
}
tr.middle>td>*, tr.middle>th>* {
    vertical-align: middle;
}
td.middle>* {
    vertical-align: middle;
}
.p3bb1d {
    padding: 3px 3px 0px 3px;
    border-bottom: 1px solid #ddd;
}
.btop1d {
    border-top: 1px solid #ddd;
}
.btop2d {
    border-top: 2px solid #ddd;
}
.btopblu {
    border-top: 1px solid #008;
}
.btop2blu {
    border-top: 2px solid #008;
}
.btopdblu {
    border-top: 3px double #008;
}
.btop2dblu {
    border-top: 5px double #008;
}
.bbtm1d {
    border-bottom: 1px solid #ddd;
}
.bbtm2d {
    border-bottom: 2px solid #ddd;
}
.bbtmdblu {
    border-bottom: 3px double #008;
}
.bbtm1_8 {
    border-bottom: 1px solid #888;
}
.br1_0, .brdr1_0 {
    border: 1px solid #000;
}
.br1_8, .brdr1_8 {
    border: 1px solid #888;
}
.brdr1d {
    border: 1px solid #ddd;
}
.br1_x {
    border: 1px solid transparent;
}
.br2_x {
    border: 2px solid transparent;
}
.br2so0 {
    border: 2px solid black;
}
.br1sdr {
    border: 1px solid darkred;
}
.brh1f {
    border-width: 1px 0px;
    border-style: solid;
    border-color: black;
}
.brv1f {
    border-width: 0px 1px;
    border-style: solid;
    border-color: black;
}
.outoff1d {
    outline: 1px solid #ddd;
    outline-offset: -1px;
}
.outoff1a {
    outline: 1px solid #aaa;
    outline-offset: -1px;
}
.outoff10 {
    outline: 1px solid #000;
    outline-offset: -1px;
}
.outoff26 {
    outline: 2px solid #666;
    outline-offset: -2px;
}
.panalBG {
    background-image: url(<?= "/{$_pryNm}/imagenes/fondos/fondo5.png" ?>);
    background-repeat: repeat;
    background-color: transparent;
}
.panalBGLight {
    background-image: url(<?= "/{$_pryNm}/imagenes/fondos/fondo5Light.png" ?>);
    background-repeat: repeat;
    background-color: transparent;
}
.panalBGDark {
    background-image: url(<?= "/{$_pryNm}/imagenes/fondos/fondo5Dark.png" ?>);
    background-repeat: repeat;
    background-color: transparent;
}

td div.scrollableCell, .maxFlowMsg {
  width: 100%;
  height: 100%;
  overflow: auto;
/* Definir estos parametros en el div o en otra clase, pues varian con el contenido
  max-width: 200px;
  max-height: 200px;
*/
}
.maxWidFlow {
    width: 100%;
    overflow: auto;
}
.optionalEditCheck, .hcelr {
    text-align: right;
    vertical-align: middle;
    width: 1%;
    white-space: nowrap;
}
.optionalEditCheck span {
    margin-top: 10px;
    line-height: 20px;
    vertical-align: middle;
    white-space: nowrap;
    display: inline-block;
}
span.currency {
    display: inline-block;
    text-align: right;
    padding-left: 2px;
}
span.curr_code {
    display: inline-block;
    width: 30px;
    overflow: hidden;
    vertical-align: text-bottom;
    text-align: right;
    padding-left: 2px;
}
span.curr_codeb {
    display: inline-block;
    width: 30px;
    overflow: hidden;
    vertical-align: bottom;
    text-align: right;
    padding-left: 2px;
}
.inputCurrency {
    position: relative;
    white-space: nowrap;
}
.inputCurrency input, .inputCurrency.noInput {
    padding-left: 14px !important;
}
span.inputCurrency::before:not(.noInput), span.inputCurrency:before:not(.noInput) {
    position: absolute;
    top: 0;
    content: "$";
    left: 5px;
}
span.inputCurrency.noInput::before, span.inputCurrency.noInput:before {
    position: absolute;
    bottom: 10;
    content: "$";
    left: 5px;
}
.btn16 {
    width: 16px;
    height: 16px;
}
.btn20 {
    display: inline-block;
    width: 20px;
    height: 20px;
}
.btn25 {
    width: 25px;
    height: 25px;
}
.pro16 {
    display: inline-block;
    width: 16px;
    height: 16px;
    line-height: 16px;
    font-size: 9px;
    text-align: center;
    cursor: pointer;
    vertical-align: middle;
}
.pro16b {
    display: inline-block;
    width: 15px;
    height: 15px;
    line-height: 14px;
    font-size: 9px;
    text-align: center;
    cursor: pointer;
    vertical-align: top;
}
.pro12 {
    display: inline-block;
    width: 12px;
    height: 12px;
    line-height: 10px;
    font-size: 9px;
    text-align: center;
    cursor: pointer;
}
.pro11 {
    display: inline-block;
    width: 11px;
    height: 11px;
    line-height: 10px;
    font-size: 9px;
    text-align: center;
    cursor: pointer;
}
.off16 {
    display: inline-block;
    width: 16px;
    height: 16px;
    vertical-align: middle;
}
.pageTxt {
    display: inline-block;
    width: 20px;
    height: 17px;
    line-height: 17px;
    vertical-align: middle;
}
.btnHid {
    border: 1px solid transparent;
    padding: 2px;
}
div>span.tab, td>span.tab {
    width: auto;
    display: inline-block;
    color: #008;
}
.btcb {
    border-top-color: black;
}
.btcg {
    border-top-color: gray;
}
.btcl {
    border-top-color: lightgray;
}
.btnEdg {
    --webkit-appearance: button;
    cursor: pointer;
    text-transform: none;
    margin: 0;
    letter-spacing: normal;
    word-spacing: normal;
    text-ident: 0px;
    text-shadow: none;
    /* background-color: -internal-light-dark(rgb(239,239,239),rgb(59,59,59)); */
    /*background-image: radial-gradient(rgb(239,239,239),rgb(59,59,59));*/
    padding: 1px 6px;
    /*border: 1px outset -internal-light-dark(rgb(118,118,118),rgb(133,133,133));*/
    border-width: 1px;
    border-style: outset;
    border-top-color: rgb(118,118,118);
    border-left-color: rgb(118,118,118);
    border-right-color: rgb(133,133,133);
    border-bottom-color: rgb(133,133,133);
    border-radius: 2px;
}
.btnEdg:active:not(.disabled) {
    border-style: inset;
}
.btnTab {
    border: 1px outset #f0f0f0;
    padding: 1px;
    font-size: 12px;
    user-select: none;
    -moz-user-select: none;
    -khtml-user-select: none;
    -webkit-user-select: none;
    -o-user-select: none;
    cursor: pointer;
    background-image: url(<?= "/{$_pryNm}/{$bkgdImgName}" ?>);
    background-repeat: repeat;
}
.btnTab.disabled {
    background-image: none;
    background-color: rgba(0, 0, 0, 0.1);
    color: #888;
    border: 1px outset lightgray;
    cursor: auto;
}
.btnTab.selected {
    border: 1px inset #f0f0f0;
    background-image: none;
    background-color: rgba(100, 100, 255, 0.2);
    font-weight: bold;
}
.hoverLight1:hover {
    background-color: rgba(255,255,255,0.1);
}
.hoverLight2:hover {
    background-color: rgba(255,255,255,0.2);
}
.hoverLight3:hover {
    background-color: rgba(255,255,255,0.3);
}
.hoverDark1:hover {
    background-color: rgba(0,0,0,0.01);
}
.hoverDark2:hover {
    background-color: rgba(0,0,0,0.03);
}
.hoverDark3:hover {
    background-color: rgba(0,0,0,0.05);
}
.hoverDark4:hover {
    background-color: rgba(0,0,0,0.1);
}
.hoverDark5:hover {
    d(0,0,0,0.2);
}
.hoverDarkF1:hover {
    filter: brightness(99.99%);
}
.hoverDarkF2:hover {
    filter: brightness(99.9%);
}
.hoverDarkF3:hover {
    filter: brightness(99%);
}
.hoverDarkF4:hover {
    filter: brightness(98%);
}
.hoverDarkF5:hover {
    filter: brightness(95%);
}
.btnTab:hover:not(.disabled) {
    background-image: none;
    background-color: rgba(100, 100, 255, 0.1);
    border: 1px solid lightgray;
}
.btnTab:active:not(.disabled) {
    background-color: rgba(190,200,160,0.15);
    border: 1px solid green;
}
.btnLt {
    border: 1px outset #f0f0f0;
    padding: 2px;
    user-select: none;
    -moz-user-select: none;
    -khtml-user-select: none;
    -webkit-user-select: none;
    -o-user-select: none;
}
.btnLt.bRad2 {
    border-radius: 2px;
}
.btnLt.disabled {
    border: 1px outset lightgray;
    background-color: rgba(0, 0, 0, 0.1);
    cursor: auto;
}
.btnLt.selected {
    border: 1px inset #f0f0f0;
    background-color: rgba(140,140,190,0.1);
}
.btnLt:hover:not(.disabled) {
    background-color: rgba(190,200,160,0.15);
}
.btnLt:active:not(.disabled) {
    background-color: rgba(150,150,200,0.1);
    border: 1px inset #f0f0f0;
}
.btnOp {
    border: 1px outset #f0f0f0;
    user-select: none;
    -moz-user-select: none;
    -khtml-user-select: none;
    -webkit-user-select: none;
    -o-user-select: none;
}
.btnOp.disabled {
    border: 1px outset lightgray;
    background-color: rgba(0, 0, 0, 0.1);
    color: #888;
    cursor: auto;
}
.btnOp:hover:not(.disabled):not(:active):not(.mode2) {
    background-color: rgba(128,128,256,0.5);
}
.btnOp.mode2:hover:not(.disabled):not(:active) {
    background-color: rgba(200,180,120,0.2);
}
.btnOp:active:not(.disabled) {
    background-color: rgba(150,150,200,0.2);
    border: 1px inset #f0f0f0;
}
input.withOp {
    text-indent: 16px;
}
.v20_10 {
    width: 10px;
    height: 20px;
}
.v24_12 {
    width: 12px;
    height: 24px;
}
.bxstop {
    box-shadow: 0 -1px 0 #000;
}
.bxslft {
    box-shadow: -1px 0 0 #000;
}
.bxsrgt {
    box-shadow: 1px 0 0 #000;
}
.bxsbtm {
    box-shadow: 0 1px 0 #000;
}
.bxsbrd {
    box-shadow: 0 1px 0 2px rgba(255,0,0,0.1);
}
.btnImgDown {
    background-image: url(<?= "/{$_pryNm}/imagenes/icons/downArrow.png" ?>);
    background-size: contain;
    background-repeat: no-repeat;
    background-position: center center;
}
.btnImgUp {
    background-image: url(<?= "/{$_pryNm}/imagenes/icons/upArrow.png" ?>);
    background-size: contain;
    background-repeat: no-repeat;
    background-position: center center;
}
.btn10 {
    width: 10px;
    height: 10px;
}
.btn12 {
    width: 12px;
    height: 12px;
}
.btnOI {
    user-select: none;
    -moz-user-select: none;
    -khtml-user-select: none;
    -webkit-user-select: none;
    -o-user-select: none;
    border-width: 1px;
    border-style: outset;
    border-color: gray; 
}
.btnOI:hover:not(.disabled), .btnOI.clicked {
    background-color: rgba(0,0,0,0.2);
    border-style: inset;
    cursor: pointer;
}
.btnOI:active:not(.disabled) {
    background-color: rgba(255,255,255,0.2);
}
.btnFX {
    border: 1px outset lightgray;
    padding: 2px;
    user-select: none;
    -moz-user-select: none;
    -khtml-user-select: none;
    -webkit-user-select: none;
    -o-user-select: none;
}
.btnFX.selected {
    border: 1px inset lightgray;
    background-color: rgba(140, 140, 140, 0.2);
}
.btnFX:hover:not(.disabled) {
    background-color: rgba(255, 255, 100, 0.4);
}
.btnFX:active:not(.disabled) {
    background-color: rgba(150, 150, 150, 0.2);
    border: 1px inset lightgray;
}
.buttonLike {
    text-decoration: none;
    color: #008; /* buttontext */
    /* -- -- -- -- */
    padding: 2px 6px 1px;
    /* -- -- -- -- */
    align-items: flex-start;
    text-align: center;
    cursor: default;
    background-color: buttonface;
    box-sizing: border-box;
    border-width: 2px;
    border-style: outset;
    border-color: buttonface;
    border-image: initial;
    /* -- -- -- -- */
    text-rendering: auto;
    letter-spacing: normal;
    word-spacing: normal;
    text-transform: none;
    text-ident: 0px;
    text-shadow: none;
    display: inline-block;
    margin: 0em;
    font: 400 13.3333px Arial;
    /* -- -- -- -- */
    -webkit-writing-mode: horizontal-tb !important;
    /* -- -- -- -- */
    -webkit-appearance: button;
}
.buttonLike:active {
    border-style: inset;
}
.buttonLike:focus {
    outline: -webkit-focus-ring-color auto 5px;
}
.thumbSize:hover {
    transform: scale(1.5);
}
.vAlignParent {
    -webkit-transform-style: preserve-3d;
    -moz-transform-style: preserve-3d;
    transform-style: preserve-3d;
}
.searchicon {
    width: 18px;
    height: 18px;
    background-image: url(<?= "/{$_pryNm}/imagenes/searchicon18.png" ?>);
    cursor: pointer;
    display: inline-block;
    border: none;
    outline: none;
    line-height: 1;
    vertical-align: -3px;
}
.noborder {
    border: none;
}
.noborderi {
    border: none !important;
}
.notBorder {
    border: 0;
    outline: none;
}
.noOutline {
    outline: none;
}
.noPointer {
    pointer-events: none;
}
.noBorder2 {
    border: 0;
    outline: none;
    user-select: none;
    -webkit-user-select: none; /* webkit (safari, chrome) browsers */
    -moz-user-select: none; /* mozilla browsers */
    -khtml-user-select: none; /* webkit (konqueror) browsers */
    -ms-user-select: none; /* IE10+ */
    border-color: transparent;
    outline-color: transparent;
}
.hidBdr {
    border-color: transparent;
    outline-color: transparent;
}
.inactive {
    border: 0;
    outline: none;
    pointer-events: none;
    user-select: none;
    -webkit-user-select: none; /* webkit (safari, chrome) browsers */
    -moz-user-select: none; /* mozilla browsers */
    -khtml-user-select: none; /* webkit (konqueror) browsers */
    -ms-user-select: none; /* IE10+ */
}
.b0333 {
    border-top-width: 0px;
}
.b1111,.b1113,.b1310,.b1311,.b1313,.b1333 {
    border-top-width: 1px;
}
.b3000,.b3003,.b3131,.b3133,.b3303,.b3313,.b3330,.b3331,.b3333 {
    border-top-width: 3px;
}
.b3000,.b3003 {
    border-right-width: 0px;
}
.b1111,.b1113,.b3131,.b3133 {
    border-right-width: 1px;
}
.b0333,.b1310,.b1311,.b1313,.b1333,.b3303,.b3313,.b3330,.b3331,.b3333 {
    border-right-width: 3px;
}
.b3000,.b3003,.b3303 {
    border-bottom-width: 0px;
}
.b1111,.b1113,.b1310,.b1311,.b1313,b3313 {
    border-bottom-width: 1px;
}
.b0333,.b1333,.b3131,.b3133,.b3330,.b3331,.b3333 {
    border-bottom-width: 3px;
}
.b1310,.b3000,.b3330 {
    border-left-width: 0px;
}
.b1111,.b1311,.b3131,.b3331 {
    border-left-width: 1px;
}
.b0333,.b1113,.b1313,.b1333,.b3003,.b3133,.b3303,.b3313,.b3333 {
    border-left-width: 3px;
}
.b0333,.b1111,.b1113,.b1310,.b1311,.b1313,.b1333,.b3000,.b3003,.b3131,.b3133,.b3303,.b3313,.b3330,.b3331,.b3333 {
    border-style: solid;
    border-color: black;
}
.brVanish {
    border-right-color: transparent;
}
.blVanish {
    border-left-color: transparent;
}
.bhVanish {
    border-left-color: transparent;
    border-right-color: transparent;
}
.copyable {
    cursor: copy;
}
.alias {
    cursor: alias;
}
.cellptr {
    cursor: cell;
}
.grabbable {
    cursor: move; /* fallback if grab cursor is unsupported */
    cursor: grab;
    cursor: -moz-grab;
    cursor: -webkit-grab;
}

 /* (Optional) Apply a "closed-hand" cursor during drag operation. */
.grabbable:active { 
    cursor: grabbing;
    cursor: -moz-grabbing;
    cursor: -webkit-grabbing;
}
.nodeco {
    text-decoration: none;
}
.alink.btst {
    color: #337ab7;
    text-decoration: none;
}
.alink:not(.btst) {
    color: #008;
    text-decoration: underline;
}
.alink:hover, .alink:focus {
    text-decoration: underline;
    cursor: pointer;
}
.alink.btst:hover, .alink.btst:focus {
    color: #23527c;
}
.alink:not(.btst):hover, .alink:not(.btst):focus {
    color: currentColor;
}
.alink.btnlike {
    font-weight: bold;
    border: outset lightgrey 2px;
    padding: 2px;
}
.alink.btnlike:active {
    border: inset lightgrey 2px;
}
.aslink {
    box-sizing: border-box;
    cursor: pointer;
    margin: 1px;
    outline: 1px;
    line-height: 1;
}
.aslink:hover {
    box-shadow: 0px 0px 0px 1px lightgray;
}
.aslink:active {
    box-shadow: 0px 0px 0px 1px black;
}
.asLinkH {
    cursor: pointer;
    margin: 1px;
    outline: 1px;
    line-height: 1;
}
.asLinkH:hover {
    color: #7d378c; /* #B46464; */
}
.asLinkH:active {
    color: #B4B4E6;
}
.solBtn {
    width: 34px;
    height: 25.39px;
    cursor: pointer;
    border-radius: 2px;
}
.asBtn {
    background-color: #f0f0f0; /* -internal-light-dark(rgb(255, 255, 255), rgb(59, 59, 59));*/
    padding: 0px 7px 0.5px !important; /* 1px 2px; */
    margin-top: 1px;
    border-radius: 2px;
    border-width: 1px; /* 2px; */
    border-style: solid; /* inset; */
    border-color: gray; /* -internal-light-dark(rgb(118, 118, 118), rgb(133, 133, 133)); */
    /* font-size: 12px; *//*0.8rem; *//* font: 400 13.3333px Arial; */
    font-weight: normal !important;
    color: black;
    height: auto;
}
.asBtn:hover {
    background-color: #e4e4e4;
    border-color: #404040;
}
.resourceAmount {
    display: inline-block;
    width: 170px;
    // border: 1px solid purple;
    text-align: right;
}
input[type="button"]:disabled, button:disabled {
    color: #aaaaaa;
}
img:disabled, img.disabled {
    opacity: 0.5;
    filter: alpha(opacity=5);
}
img.disabled2 {
    opacity: 0.3;
    filter: alpha(opacity=3);
}
.disabled button, .disabled span {
    color: #999999;
    background-color: calc(0,0,0,0.1);
}
.disabled img {
    opacity: 0.5;
    filter: alpha(opacity=5);
}
.altafacturacell {
    white-space: nowrap;
    text-align: left;
    padding: 10px;
}
img.grayscale {
    filter: gray; /* IE6-9 */
    -webkit-filter: grayscale(1); /* Google Chrome, Safari 6+ & Opera 15+ */
    filter: grayscale(1); /* Microsoft Edge and Firefox 35+ */
}
img.grayscale2 {
    filter: gray;
    -webkit-filter: grayscale(1) brightness(0.8) contrast(2.5);
    filter: grayscale(1) brightness(0.8) contrast(2.5);
}
img.searchicon:hover {
    box-sizing: border-box;
    /* box-shadow: 0px 0px 10px yellow; */
    
    background: url(<?= "/{$_pryNm}/imagenes/searchicon18h.png" ?>) left top no-repeat;
}
.xmlerrorcell div {
    background-color: #f6dcdc;
    font-weight: bold;
    margin-right: 5px;
}
span.sign {
    width: 10.2px;
    text-align: center;
    display: inline-block;
}
tr.bottom-border {
    border-bottom: 3px solid red;
}
tr.uploadBottomLine {
    border-bottom: 2px groove lightgray;
}
tr.uploadErrorMessage div:not(.hidden) {
    display: inline-block;
}
tr.strikeout>td {
  position: relative;
}
tr.strikeout>td:before {
  content: "";
  position: absolute;
  top: 50%;
  left: -2.5px;
  padding: 0 2.5px;
  border-top: 2px solid darkred;
  width: 100%;
}
.strikeout,.stroke {
    text-decoration: line-through;
}
.textStroke {
    color: rgba(0,0,0, 0.2);
    -webkit-text-stroke: 1px black;
}
.ttextStroke {
    color: transparent;
    -webkit-text-stroke: 1px black;
}
select.noarrow {
    -moz-appearance: none;
    -webkit-appearance: none;
    appearance: none;
    padding: 1px 5px 1px 3px;
    box-shadow: 1px 1px 1px 0px gray;  
}
select.noarrow::-ms-expand {
    display: none;
    padding: 2px 3px 3px 3px;
    box-shadow: 1px 1px 1px 0px gray;  
}
select.disabled, input[type="text"].disabled, input[type="number"].disabled {
    opacity: 0.7 !important;
    border-color: rgba(118,118,118,0.3) !important;
    color: #666 !important;
}
input[type="number"].noarrow, input[type="number"].importe, , input[type="number"]:hover.noarrow, input[type="number"]:hover.importe {
    -moz-appearance: textfield;
}
input[type="number"].noarrow::-webkit-outer-spin-button, input[type="number"].noarrow::-webkit-inner-spin-button, input[type="number"].importe::-webkit-outer-spin-button, input[type="number"].importe::-webkit-inner-spin-button {
    -webkit-appearance: none !important;
}
div.ulmenu {
    width: 10px;
    height: 10px;
    margin-left: -10px;
    overflow: hidden;
    display: inline-block;
}
div.ulmenu.expanded img {
    margin-top: -10px;
    margin-bottom: 1px;
}
img.ulmenu:not(.expanded) {
    position: relative;
    clip: rect(0px,10px,10px,0px);
    margin-left: -8px;
    margin-top: 2px;
}
img:ulmenu.expanded {
    position: relative;
    clip: rect(10px,10px,20px,0px);
    margin-left: -8px;
    margin-top: 2px;
}
table.rowleaf {
    width: 100%;
    display: inline-table;
}
table.rowleaf td:first-child {
    width: 150px;
    max-width: 150px;
}
td.cfdiname, th.cfdiname {
    width: 150px;
    max-width: 150px;
}
/*
td.cfdidetail, th.cfdidetail {
    width: calc(100% - 200px - 8px - 6px);
}
*/
td.cfdieval, th.cfdieval {
    width: 50px;
    max-width: 50px;
}
.heiOff {
    height: unset;
}
.hei0 {
    height: 0px;
}
.hei1 {
    height: 1px;
}
.hei2 {
    height: 2px;
}
.hei5 {
    height: 5px;
}
.hei10 {
    height: 10px;
}
.hei16 {
    height: 16px;
}
.hei18 {
    height: 18px;
}
.hei19_2 {
    height: 19.2px;
}
.hei20 {
    height: 20px;
}
.hei22 {
    height: 22px;
}
.hei22i {
    height: 22px !important;
}
.hei24 {
    height: 24px;
}
.hei24i {
    height: 24px !important;
}
.hg26, .hei26 {
    height: 26px;
}
.hg30, .hei30 {
    height: 30px;
}
.hg100, .hei100 {
    height: 100px;
}
.hei110 {
    height: 110px;
}
.hei100-h2 {
    height: calc(100% - 29px - 19.92px - 19.92px);
}
.minHei22 {
    min-height: 22px;
}
.minHei25 {
    min-height: 25px;
}
.minHei30 {
    min-height: 30px;
}
.minHeiAll {
    min-height: 100%;
}
.mxHg50 {
    max-height: 50px;
}
.scrolly {
    overflow: auto;
    height: calc(100% - 27px - 27px - 5px);
}
.scrvisi {
    overflow: visible !important;
}
.scrollauto {
    overflow: auto;
}
.scrolli {
    overflow: auto !important;
}
.scroll50-30 {
    overflow: auto;
    height: calc(50% - 30px);
}
.scroll-60 {
    overflow: auto;
    height: calc(100% - 60px);
}
.scroll-64 {
    overflow: auto;
    height: calc(100% - 64px);
}
.scroll-74 {
    overflow: auto;
    height: calc(100% - 74px);
}
.screenBG {
    background-color: rgba(255, 255, 255, 0.3);
}
.screen {
    background-color: rgba(255, 255, 255, 0.3);
    border: 1px solid lightgray;
}
.screen.bgray {
    border: 1px solid gray;
}
.adminSelect {
    width: calc(100% - 8px);
}
.adminInput {
    width: calc(100% - 10px);
    min-width: 20px;
    margin-bottom: 2px;
}
.dateInput {
    width: calc(100% - 28px);
}
span.daySpan {
    display: inline-block;
    width: 16px;
    text-align: right;
}
span.monthSpan {
    display: inline-block;
    /*width: 70.08px;*/
    text-align: center;
}
span.monthSpan.enero {
    letter-spacing:4.3px;
    padding-left:4.3px;
    width: 65.8px;
}
span.monthSpan.febrero {
    letter-spacing:1.8px;
    padding-left:1.8px;
    width: 68.3px;
}
span.monthSpan.marzo {
    letter-spacing:4.1px;
    padding-left:4.1px;
    width: 66px;
}
span.monthSpan.abril {
    letter-spacing:5.3px;
    padding-left:5.3px;
    width: 64.8px;
}
span.monthSpan.mayo {
    letter-spacing:6.2px;
    padding-left:6.2px;
    width: 63.9px;
}
span.monthSpan.junio {
    letter-spacing:4.7px;
    padding-left:4.7px;
    width: 65.4px;
}
span.monthSpan.julio {
    letter-spacing:5.3px;
    padding-left:5.3px;
    width: 64.8px;
}
span.monthSpan.agosto {
    letter-spacing:2.7px;
    padding-left:2.7px;
    width: 67.4px;
}
span.monthSpan.septiembre {
    letter-spacing:0px;
    padding-left:0px;
    width: 70.1px;
}
span.monthSpan.octubre {
    letter-spacing:1.7px;
    padding-left:1.7px;
    width: 68.4px;
}
span.monthSpan.noviembre {
    letter-spacing:0.2px;
    padding-left:0.2px;
    width: 69.9px;
}
span.monthSpan.diciembre {
    letter-spacing:0.5px;
    padding-left:0.5px;
    width: 69.6px;
}

.oldDateInput {
    width: 75px;
    min-width: 75px;
    max-width: 75px;
}
.fixed55 {
    width: 55px;
    min-width: 55px;
    max-width: 55px;
}
.fixed66 {
    width: 66px;
    min-width: 66px;
    max-width: 66px;
}
.fixed77 {
    width: 77px;
    min-width: 77px;
    max-width: 77px;
}
.fixed88 {
    width: 88px;
    min-width: 88px;
    max-width: 88px;
}
.fixed99 {
    width: 99px;
    min-width: 99px;
    max-width: 99px;
}
.fixed550 {
    width: 550px;
    min-width: 550px;
    max-width: 550px;
}
.fixedSelect {
    min-width: 110px;
    max-width: 300px;
}
.maxWid80 {
    max-width: 80px;
}
.maxWid140 {
    max-width: 140px;
}
.maxWid250 {
    max-width: 250px;
}
.maxWid300 {
    max-width: 300px;
}
.pc95 {
    width: 95%;
}
.isUUIDCell {
    white-space: nowrap;
    max-width: 150px;
    overflow: hidden;
}
.noFlow {
    overflow: hidden;
}
.xFlow {
    overflow-x: auto;
    overflow-y: hidden;
}
.yFlow {
    overflow-x: hidden;
    overflow-y: auto;
}
.yFlowi {
    overflow-x: hidden !important;
    overflow-y: auto !important;
}
.selector {
    height: 80%;
}
.vexpand {
    height: 100%;
}
.vmax {
    max-height: 100%;
}
.vhalf {
    height: 50%;
}
.fltL {
    float: left;
}
.fltR {
    float: right;
}
.marginV1 {
    margin-left: 1px;
    margin-right: 1px;
}
.marginV2 {
    margin-left: 2px;
    margin-right: 2px;
}
.marginV3 {
    margin-left: 3px;
    margin-right: 3px;
}
.marginV4 {
    margin-left: 4px;
    margin-right: 4px;
}
.marginV5 {
    margin-left: 5px;
    margin-right: 5px;
}
.marginV7 {
    margin-left: 7px;
    margin-right: 7px;
}
.marginH2 {
    margin-top: 2px;
    margin-bottom: 2px;
}
.marginH3 {
    margin-top: 3px;
    margin-bottom: 3px;
}
.marginH5 {
    margin-top: 5px;
    margin-bottom: 5px;
}
.marginH7 {
    margin-top: 7px;
    margin-bottom: 7px;
}
.marginHSp {
    margin-top: 15px;
    margin-bottom: 5px;
}
.marhtt {
    margin-top: 20px;
    margin-bottom: 10px;
}
.marT0i {
    margin-top: 0px !important;
}
.marginT7 {
    margin-top: 7px;    
}
.marT1 { margin-top: 1px; }
.marT10 { margin-top: 10px; }
.marT10i { margin-top: 10px !important; }
.marT20 { margin-top: 20px; }
.marT24 { margin-top: 24px; }
.marT25 { margin-top: 25px; }
.marT30 { margin-top: 30px; }
.marT50 { margin-top: 50px; }
.marR1 { margin-right: 1px; }
.marR2, .marginR2 { margin-right: 2px; }
.marR2i { margin-right: 2px !important; }
.marR3, .marginR3 { margin-right: 3px; }
.marginR3i { margin-right: 3px !important; }
.marR10 { margin-right: 10px; }
.marR20 { margin-right: 20px; }
.marL1 { margin-left: 1px; }
.marL1_5 { margin-left: 1.5px; }
.marL2 { margin-left: 2px; }
.marL3 { margin-left: 3px; }
.marL4 { margin-left: 4px; }
.marL4i { margin-left: 4px !important; }
.marginL7 { margin-left: 7px; }
.marginL10 { margin-left: 10px; }
.marginL15 { margin-left: 15px; }
.marginL20 { margin-left: 20px; }
.marginL25 { margin-left: 25px; }
.marginL30 { margin-left: 30px; }
@keyframes expandSubmenu {
    from { top: 0px; }
    to { top: 32px; }
}
@-webkit-keyframes blinkanim {
    0% { opacity: 0.5; }
    100% { opacity: 1; }
}
@-moz-keyframes blinkanim {
    0% { opacity: 0.5; }
    100% { opacity: 1; }
}
@-o-keyframes blinkanim {
    0% { opacity: 0.5; }
    100% { opacity: 1; }
}
@keyframes blinkanim {
    0% { opacity: 0.5; }
    100% { opacity: 1; }
}
@-webkit-keyframes slideDown {
    0% { opacity: 0; -webkit-transform: translateY(-100%); }
    100% { opacity: 1; -webkit-transform: translateY(0); }
}
@-moz-keyframes slideDown {
    0% { opacity: 0; -moz-transform: translateY(-100%); }
    100% { opacity: 1; -moz-transform: translateY(0); }
}
@-o-keyframes slideDown {
    0% { opacity: 0; -o-transform: translateY(-100%); }
    100% { opacity: 1; -o-transform: translateY(0); }
}
@keyframes slideDown {
    0% { opacity: 0; transform: translateY(-100%); }
    100% { opacity: 1; transform: translateY(0); }
}


@-webkit-keyframes slideUp {
    0% { opacity: 0; -webkit-transform: translateY(100%); }
    100% { opacity: 1; -webkit-transform: translateY(0); }
}
@-moz-keyframes slideUp {
    0% { opacity: 0; -moz-transform: translateY(100%); }
    100% { opacity: 1; -moz-transform: translateY(0); }
}
@-o-keyframes slideUp {
    0% { opacity: 0; -o-transform: translateY(100%); }
    100% { opacity: 1; -o-transform: translateY(0); }
}
@keyframes slideUp {
    0% { opacity: 0; transform: translateY(100%); }
    100% { opacity: 1; transform: translateY(0); }
}
.qblink {
    -webkit-animation: blinkanim 0.55s infinite;
    -moz-animation: blinkanim 0.55s infinite;
    -o-animation: blinkanim 0.55s infinite;
    animation: blinkanim 0.55s infinite;
}
.blink {
    -webkit-animation: blinkanim 4s infinite;
    -moz-animation: blinkanim 4s infinite;
    -o-animation: blinkanim 4s infinite;
    animation: blinkanim 4s infinite;
}
@-webkit-keyframes highlightdim {
    0% { box-shadow: 0 0 3px 3px rgb(255,255,100,0.7); }
    20% { box-shadow: 0 0 4px 2px rgb(255,255,100,0.7), 0 0 10px 0 rgba(255,200,100,0.3) inset; }
    70% { box-shadow: 0 0 10px 5px rgba(255,255,100,0.7), 0 0 15px 5px rgba(255,200,100,0.5) inset; }
    90% { box-shadow: 0 0 4px 2px rgb(255,255,100,0.7), 0 0 10px 0 rgba(255,200,100,0.3) inset; }
    100% { box-shadow: 0 0 3px 3px rgb(255,255,100,0.7); }
}
@-moz-keyframes highlightdim {
    0% { box-shadow: 0 0 3px 3px rgb(255,255,100,0.7); }
    20% { box-shadow: 0 0 4px 2px rgb(255,255,100,0.7), 0 0 10px 0 rgba(255,200,100,0.3) inset; }
    70% { box-shadow: 0 0 10px 5px rgba(255,255,100,0.7), 0 0 15px 5px rgba(255,200,100,0.5) inset; }
    90% { box-shadow: 0 0 4px 2px rgb(255,255,100,0.7), 0 0 10px 0 rgba(255,200,100,0.3) inset; }
    100% { box-shadow: 0 0 3px 3px rgb(255,255,100,0.7); }
}
@-o-keyframes highlightdim {
    0% { box-shadow: 0 0 3px 3px rgb(255,255,100,0.7); }
    20% { box-shadow: 0 0 4px 2px rgb(255,255,100,0.7), 0 0 10px 0 rgba(255,200,100,0.3) inset; }
    70% { box-shadow: 0 0 10px 5px rgba(255,255,100,0.7), 0 0 15px 5px rgba(255,200,100,0.5) inset; }
    90% { box-shadow: 0 0 4px 2px rgb(255,255,100,0.7), 0 0 10px 0 rgba(255,200,100,0.3) inset; }
    100% { box-shadow: 0 0 3px 3px rgb(255,255,100,0.7); }
}
@keyframes highlightdim {
    0% { box-shadow: 0 0 3px 3px rgb(255,255,100,0.7); }
    20% { box-shadow: 0 0 4px 2px rgb(255,255,100,0.7), 0 0 10px 0 rgba(255,200,100,0.3) inset; }
    70% { box-shadow: 0 0 10px 5px rgba(255,255,100,0.7), 0 0 15px 5px rgba(255,200,100,0.5) inset; }
    90% { box-shadow: 0 0 4px 2px rgb(255,255,100,0.7), 0 0 10px 0 rgba(255,200,100,0.3) inset; }
    100% { box-shadow: 0 0 3px 3px rgb(255,255,100,0.7); }
}
input[type="file"]:not(.bootstrap) {
    display: inline-block;
}
input[type="file"].highlight, input[type="submit"].highlight {
    font-weight: bold;
}
.highlight {
    -webkit-animation: highlightdim 5s infinite linear;
    -moz-animation: highlightdim 5s infinite linear;
    -o-animation: highlightdim 5s infinite linear;
    animation: highlightdim 5s infinite linear;
}
.inhghlght10 {
    outline: 10px solid rgba(200,120,0,0.3);
    outline-offset: -10px;
    /* box-shadow: inset 0px 0px 10px 10px #EAC66B; */
}
.badge {
    display: block;
    color: white;
    text-align: center;
    vertical-align: middle;
    font-size: 8px;
    font-weight: normal;
    line-height: 7px;
    border-radius: 50%;
    min-width: auto;
    box-shadow: inset 0px 0px 2px 2px rgb(255, 90, 40);
    background-color: initial;
    padding: initial;
}
span.highlight:not(.fit):not(.semifit) {
    height: 22px;
    display: inline-block;
    padding: 1px;
}
span.highlight.semifit, span.mark.semifit {
    display: inline-block;
    padding: 2px;
}
.navOverlayButton {
    visibility: hidden;
}
.invisible {
    visibility: hidden;
}
.hidden {
    display: none;
}
.camouflage {
    opacity: 0;
    filter: alpha(opacity=0);
}
.hidempty:empty {
    display: none;
}
.element {
    transition:transform .2s ease;
    width:30px;
    height:30px;
    border:1px solid red;
    transform: translate3d(0,0,0);
}
.element.is-animated {
    transform: translate3d(-100%,0,0);
}
.hgtSlider {
    overflow: hidden;
    transition: height 2s ease;
} /* hei20 hei0 */
.showOnlyLastChild>:not(:last-child) {
    display: none;
}
.masked {
    width: 0.1px;
    height: 0.1px;
    opacity: 0;
    overflow: hidden;
    position: absolute;
    z-index: -1;
    visibility: hidden;
}
.inputfile + label {
    font-size: .75em;
    font-weight: 700;
    background-color: rgba(100,100,100,0.1);
    display: inline-block;
    cursor: pointer;
    padding: 2px 2px 2px 4px;
    margin-top: -2px;
    vertical-align: middle;
}
.inputfile + label * {
    pointer-events: none;
}
.clicksOff {
    pointer-events: none;
    opacity: 0.5;
    filter: alpha(opacity=5);
}
.inputfile:focus + label {
    outline: 1px dotted #000;
    outline: -webkit-focus-ring-color auto 5px;
}
.inputfile:focus + label, .inputfile + label:hover, .salmonBG {
    background-color: lightsalmon;
}
.selectedOnFocus:focus {
    outline: none;
    color: white;
    background-color: #0074ff;
}
input[type="button"].boots:active, input[type="submit"].boots:active, input[type="reset"].boots:active {
    -webkit-appearance: push-button;
    background-color: rgba(0,0,0,0.01);
}
.bpad0 {
    border-spacing: 0px;
}
.bpad02 {
    border-spacing: 0px 2px;
}
.padding0, .pad0 {
    padding: 0px;
}
.pad0i {
    padding: 0px !important;
}
.pad1, .pad1r *, table.pad1c>tbody>tr>td, table.pad1c>tbody>tr>th {
    padding: 1px;
}
.pad1_6 {
    padding: 1.6px;
}
.pad1_8 {
    padding: 1.8px;
}
.pad2, .pad2r *, table.pad2c>tbody>tr>td, table.pad2c>tbody>tr>th, table.pad2c>thead>tr>td, table.pad2c>thead>tr>th, table.pad2c>tfoot>tr>td, table.pad2c>tfoot>tr>th {
    padding: 2px;
}
.pad3 {
    padding: 3px;
}
.pad4 {
    padding: 4px;
}
.padding5, .pad5 {
    padding: 5px;
}
.pad6 {
    padding: 6px;
}
.pad7 {
    padding: 7px;
}
.pad10 {
    padding: 10px;
}
.pad12 {
    padding: 12px;
}
.padt0 {
    padding-top: 0px;
}
.padtop1, .padt1 {
    padding-top: 1px;
}
.padtop2, .padt2 {
    padding-top: 2px;
}
.padtop3, .padt3 {
    padding-top: 3px;
}
.padtop4, .padt4 {
    padding-top: 4px;
}
.padtop5, .padt5 {
    padding-top: 5px;
}
.padtop6, .pad6 {
    padding-top: 6px;
}
.padtop7, .padt7 {
    padding-top: 7px;
}
.padt10 {
    padding-top: 10px;
}
.padt20 {
    padding-top: 20px;
}
.padb1 {
    padding-bottom: 1px;
}
.padbtm2, .padb2 {
    padding-bottom: 2px;
}
.padb3 {
    padding-bottom: 3px;
}
.padb4 {
    padding-bottom: 4px;
}
.paddingbottom, .padbtm5, .padb5 {
    padding-bottom: 5px;
}
.padb6 {
    padding-bottom: 6px;
}
.padb7 {
    padding-bottom: 7px;
}
.padb25 {
    padding-bottom: 25px;
}
.padl1 {
    padding-left: 1px;
}
.padl2, .padL2 {
    padding-left: 2px;
}
.padl3, tr.padl3>th, tr.padl3>td {
    padding-left: 3px;
}
.padl4 {
    padding-left: 4px;
}
.padl4i {
    padding-left: 4px !important;
}
.padl5, .padL5 {
    padding-left: 5px;
}
.padl6 {
    padding-left: 6px;
}
.padl7 {
    padding-left: 7px;
}
.padL8 {
    padding-left: 8px;
}
.padL8i {
    padding-left: 8px !important;
}
.padL10 {
    padding-left: 10px;
}
.padL16 {
    padding-left: 16px;
}
.padL20 {
    padding-left: 20px;
}
.padL50 {
    padding-left: 50px;
}
.padL173 {
    padding-left: 173px;
}
.padL200 {
    padding-left: 200px;
}
.padr1 {
    padding-right: 1px;
}
.padr2, .padrgt2 {
    padding-right: 2px;
}
.padr3 {
    padding-right: 3px;
}
.padr4 {
    padding-right: 4px;
}
.padr5 {
    padding-right: 5px;
}
.padr6 {
    padding-right: 6px;
}
.padr7 {
    padding-right: 7px;
}
.padr10 {
    padding-right: 10px !important;
}
.padr10i {
    padding-right: 10px !important;
}
.padR20 {
    padding-right: 20px;
}
.padR20i {
    padding-right: 20px !important;
}
.padh0 {
    padding-top: 0px;
    padding-bottom: 0px;
}
.padh1 {
    padding-top: 1px;
    padding-bottom: 1px;
}
.padh2 {
    padding-top: 2px;
    padding-bottom: 2px;
}
.padh3 {
    padding-top: 3px;
    padding-bottom: 3px;
}
.padh4 {
    padding-top: 4px;
    padding-bottom: 4px;
}
.padh5 {
    padding-top: 5px;
    padding-bottom: 5px;
}
.padh6 {
    padding-top: 6px;
    padding-bottom: 6px;
}
.padh7 {
    padding-top: 7px;
    padding-bottom: 7px;
}
.padhtt {
    padding-top: 20px;
    padding-bottom: 10px;
    padding-left: 5px;
    padding-right: 5px;
}
.padh1em {
    padding-top: 1em;
    padding-bottom: 1em;
}
.padv1 {
    padding-right: 1px;
    padding-left: 1px;
}
.padv2, #viaje_acumulado td, #viaje_acumulado th {
    padding-right: 2px;
    padding-left: 2px;
}
.padv3, tr.padv3>th, tr.padv3>td {
    padding-right: 3px;
    padding-left: 3px;
}
.padv4 {
    padding-right: 4px;
    padding-left: 4px;
}
.padv4i {
    padding-right: 4px !important;
    padding-left: 4px !important;
}
.padv5, .screen.help {
    padding-right: 5px;
    padding-left: 5px;
}
.padv6 {
    padding-right: 6px;
    padding-left: 6px;
}
.padv6_7 {
    padding-right: 6.7px;
    padding-left: 6.7px;
}
.padv7 {
    padding-right: 7px;
    padding-left: 7px;
}
.padv10 {
    padding-right: 10px;
    padding-left: 10px;
}
.padv02 {
    padding: 0px 2px;
}
.padv03 {
    padding: 0px 3px;
}
.padBtn0 {
    padding: 0px 2px 1px 0px;
}
.padBtn1 {
    padding: 1px 0 3px 3px;
}
.padPreTxt {
    padding: 1px 3px 2px;
}
.mar1 {
    margin: 1px;
}
.mar2 {
    margin: 2px;
}
.margin5 {
    margin: 5px;
}
.mar7 {
    margin: 7px;
}
.margin20 {
    margin: 20px;
}
.marginbottom0, .marbtm0 {
    margin-bottom: 0px;
}
.marbtm0i {
    margin-bottom: 0px !important;
}
.marbtm1 {
    margin-bottom: 1px;
}
.marginbottom2, .marbtm2 {
    margin-bottom: 2px;
}
.marbtm3 {
    margin-bottom: 3px;
}
.marginbottom, .marbtm5 {
    margin-bottom: 5px;
}
.margintop0, .martop0 {
    margin-top: 0px;
}
.margintop2, .martop2 {
    margin-top: 2px;
}
.martop3 {
    margin-top: 3px;
}
.martop3i {
    margin-top: 3px !important;
}
.margintop, .martop5 {
    margin-top: 5px;
}
.martop5i {
    margin-top: 5px !important;
}
.margintop10, .martop10 {
    margin-top: 10px;
}
.marL16 {
    margin-left: 16px;
}
.marL100 {
    margin-left: 100px;
}
.marL160 {
    margin-left: 160px;
}
.marNeg20 {
    margin-left: -20px;
    margin-top: -2px;
}
.size8 {
    width: 8px;
    height: 8px;
}
.size10 {
    width: 10px;
    height: 10px;
}
.size12 {
    width: 12px;
    height: 12px;
}
.size15 {
    width: 15px;
    height: 15px;
}
.size20 {
    width: 20px;
    height: 20px;
}
.size100 {
    width: 100px;
    height: 100px;
}
.size400 {
    width: 400px;
    height: 400px;
}
.size410 {
    width: 410px;
    height: 410px;
}
.size420 {
    width: 420px;
    height: 420px;
}
.size480 {
    width: 480px;
    height: 480px;
}
.posNE8 {
    margin-left: -8px;
    vertical-align: 5px;
}
.posNE8d {
    margin-left: -8px;
    vertical-align: -1px;
}
.posSE8 {
    margin-left: -8px;
    vertical-align: -5px;
}
.posNE10 {
    margin-left: -10px;
    vertical-align: 5px;
}
.posSE10 {
    margin-left: -10px;
    vertical-align: -5px;
}
.marSE2 {
    margin-left: -2px;
    margin-top: 14px;
}
.marSE3 {
    margin-left: -3px;
    margin-top: 14px;
}
.marSE4 {
    margin-left: -4px;
    margin-top: 14px;
}
.marSE5 {
    margin-left: -5px;
    margin-top: 14px;
}
.marSE6 {
    margin-left: -6px;
    margin-top: 14px;
}
.marSE7 {
    margin-left: -7px;
    margin-top: 14px;
}
.marSE8 {
    margin-left: -8px;
    margin-top: 14px;
}
.marSE9 {
    margin-left: -9px;
    margin-top: 14px;
}
.retFix {
    cursor: pointer;
    outline: 1px solid black;
    border-radius: 2px;
    background-color: rgba(255,255,255,0.7);
}
.retFix10:hover {
    outline: 2px solid brown;
    filter: brightness(0.8);
    background-color: rgba(255,255,0,0.9);
}
.retFix10:active {
    filter: brightness(1.2);
}
.delFix12.va0 {
    vertical-align: 0px;
}
.delFix12.va8 {
    vertical-align: 8px;
}
.delFix12:not(.va0):not(.va8) {
    vertical-align: -12px;
}
.delFix12 {
    margin-left: -12px;
    cursor: pointer;
    outline: 1px solid black;
    /* outline-offset: -1px; */
}
.delFix12:hover {
    outline: 2px solid brown;
    filter: brightness(0.8);
    border-radius: 2px;
}
.delFix12:active {
    filter: brightness(1.2);
    border-radius: 2px;
}
#scrollMenuTab {
    position: absolute;
    width: 24px;
    height: 24px;
    right: -24px;
    top: 126px;
}
.menuHandle {
    position: absolute;
    width: 20px;
    height: 48.66px;
    right: -20px;
    z-index: 2000px;
    /*opacity: 55%;*/
}
.handleTop {
    top: 170px;
}
.handleBottom {
    bottom: 120px;
}
.scrollSubMenuBtn {
    position: absolute;
    z-index: 1100;
    right: 0px;
    width: 18px;
    height: 12px;
    background-color: rgba("255, 255, 255, 0.5");
}
.scrollSubMenuBtn.isUP {
    bottom: <?= $enableHiddenMenu?44:14 ?>px;
}
.scrollSubMenuBtn.isDW {
    bottom: <?= $enableHiddenMenu?32:2 ?>px;
}
.scrollSubMenuBtn:hover:not(.disabled) {
    outline: 1px solid darkgreen;
    background-color: palegreen;
    /*filter: brightness(0.8);*/
    border-radius: 2px;
    cursor: pointer;
    filter: brightness(1.2);
}
.scrollSubMenuBtn:active {
    /*filter: brightness(1.2);*/
}
.noblockstart {
    margin-block-start: 0;
}
.nomarginblock {
    margin-block-start: 0;
    margin-block-end: 0;
}
.nomarblkend {
    margin-block-end: 0;
}
.marblkend10 {
    margin-block-end: 10px;
}
.marblk2 {
    margin-block-start: 2px;
    margin-block-end: 2px;
    background-color: transparent;
}
.marblk5 {
    margin-block-start: 5px;
    margin-block-end: 5px;
}
.marblk5_1 {
    margin-block-start: 5px;
    margin-block-end: 1px;
}
.marblk7 {
    margin-block-start: 7px;
    margin-block-end: 7px;
}
.mb20px {
    margin-block-start: 20px;
    margin-block-end: 20px;
}
.mbp {
    margin-block-start: 1em;
    margin-block-end: 1em;
    margin-inline-start: 0px;
    margin-inline-end: 0px;
}
.mbpi {
    margin-top: 12px !important;
    margin-bottom: 12px !important;
}
div.tooltip.simple {
    padding: 2px;
    font-size: 10px;
}
div.tooltip.simple>p {
    margin: 0 auto;
    font-weight: bold;
}
div.tooltip.simple>ul {
    margin: 0 auto;
    padding-inline-start: 15px;
}
div.tooltip.simple>ul>li>span {
    display: block;
    margin-left: -5px;
}
#testListBlock>ul {
    padding-inline-start: 35px;
}
ul.mbmenu {
    margin-block-start: 3px;
    margin-block-end: 3px;
    background-color: white;
    border: solid 1px gray;
    padding-inline-start: 0px;
    padding: 0px;
    list-style-type: none;
}
ul.mbmenu li {
    padding: 0px 2px;
}
ul.mbmenu li:hover {
    background-color: rgba(255, 255, 100, 0.3);
}
ul.logmenu {
    list-style-type: none;
    font-size: 10px;
}
ul.logmenu:not(.next) {
    padding-inline-start: 10px;
}
ul.logmenu.next {
    padding-inline-start: 0px;
}
ul.logmenu>li {
    margin: 2px;
    padding: 2px;
    border: solid 1px gray;
    width: calc(100% - 8px);
    border-radius: 3px;
    background-color: rgba(0, 0, 0, 0.1);
    cursor: pointer;
    text-align: center;
    font-weight: bold;
}
ul.logmenu>li.selected {
    background-color: rgba(255, 255, 0, 0.1);
    border-radius: 5px;
    border: solid 2px #008;
}
ul.logmenu>li:hover {
    background-color: rgba(255, 255, 255, 0.1);
}
ol.genTxtSteps {
    width: 310px;
    margin: auto;
    text-align: left;
}
ol.highSequence {
    list-style: none;
    counter-reset: high-sequence;
}
ol.highSequence li {
    counter-increment: high-sequence;
}
ol.highSequence li::before {
    content: counter(high-sequence) ". ";
    font-weight: bold;
}
ol.highSequence li.selected {
    font-weight: bold;
    background-color: rgba(255,127,0,0.1);
}
ol.highSequence li.selected::before {
    background-color: rgba(255,0,0,0.2);
    color: darkred;
}
.altafacturatable, .nomargin {
    margin: 0 auto;
}
.nomargini {
    margin: 0 auto !important;
}
.top, .optionalEditCheck img, .topvalign, tr.topvalign>td, tr.topvalign>th, tbody.topvalign>tr>td, tbody.topvalign>tr>th {
    vertical-align: top;
}
.txttop {
    vertical-align: text-top;
}
.vAlignCenter {
    vertical-align: middle;
}
.vATBtm {
    vertical-align: text-bottom;
}
.vATTop {
    vertical-align: text-top;
}
.vATTopi, .vATTopi>* {
    vertical-align: text-top !important;
}
.vaBase {
    vertical-align: baseline;
}
.vaBasei {
    vertical-align: baseline !important;
}
.vaTest {
    vertical-align: baseline;
}
.izquierdo {
    text-align: left;
}
.lefted, table.lefted td:not(.centered), table.lefted th:not(.centered), tbody.lefted>tr>td:not(.centered), thead.lefted>tr>th:not(.centered), tfoot.lefted>tr>td:not(.centered), tfoot.lefted>tr>th:not(.centered), tr.lefted>td:not(.centered), tr.lefted>th:not(.centered) {
    text-align: left;
    align: left;
}
.leftedi {
    text-align: left !important;
    align: left !important;
}
.justified {
    text-align: justify;
}
.justified2 {
    display: flex;
    justify-content: space-around;
}
tbody.centered td.righted, td.righted, .righted, .rightAligned {
    text-align: right;
}
.rightedi {
    text-align: right !important;
}
.nocenter {
    text-align: left;
    align: left;
}
.wordwrap {
    white-space: pre-wrap;      /* CSS3 */   
    white-space: -moz-pre-wrap !important; /* Mozilla */    
    white-space: -pre-wrap;     /* Opera <7 */   
    white-space: -o-pre-wrap;   /* Opera 7 */    
    word-wrap: normal; /*break-word;*/      /* IE */
    white-space: -webkit-pre-wrap; /* Chrome/Safari newer versions */
    word-break: normal; /*break-all;*/
    /* white-space: normal; */
}
.wordkeep {
    overflow-wrap: break-word;
    word-break: normal;
    /* hyphens: auto; */
}
th.title {
    border: 1px solid lightblue;
    background-color: rgba(0,0,200,0.3);
    padding: 2px 3px;
}
th.titlearea {
    border: 1px solid lightblue;
    background-color: rgba(100,0,200,0.3);
    padding: 2px 3px;
}
.bgblend {
    background-blend-mode: multiply;
}
.transparent, input[type="text"].transparent {
    background-color: transparent;
}
.bgbtnf {
    background-color: buttonface;
}
.bgbtn {
    background-color: #F7F5F6;
    background-image: -webkit-gradient(linear, left top, left bottom, from(#F7F5F6), to(#DDDDDD));
    background-image: -webkit-linear-gradient(top, #F7F5F6, #DDDDDD);
    background-image: -moz-linear-gradient(top, #F7F5F6, #DDDDDD);
    background-image: -ms-linear-gradient(top, #F7F5F6, #DDDDDD);
    background-image: -o-linear-gradient(top, #F7F5F6, #DDDDDD);
    background-image: linear-gradient(to bottom, #F7F5F6, #DDDDDD);filter:progid:DXImageTransform.Microsoft.gradient(GradientType=0,startColorstr=#F7F5F6, endColorstr=#DDDDDD);
}
.bgbtnIO:not(.pressed) {
    background-color: rgba(255, 255, 255, 0.1);
}
.bgbtnIO:active, .bgbtnIO:focus-within, .bgbtnIO.pressed {
    background-color: rgba(  0,   0,   0, 0.1);
}
.bgbtnIO:not(.pressed):hover {
    background-color: rgba(255, 255,   0, 0.3);
} 
.bgwhite0 {
    background-color: white;
}
.bgwhite1 {
    background-color: rgba(255, 255, 255, 0.1);
}
.bgwhite2 {
    background-color: rgba(255, 255, 255, 0.2);
}
.bgwhite3,.bgwhite {
    background-color: rgba(255, 255, 255, 0.3);
}
.bgwhite5 {
    background-color: rgba(255, 255, 255, 0.5);
}
.bgwhite7 {
    background-color: rgba(255, 255, 255, 0.7);
}
.bgyellow0 {
    /* background-color: rgb(255,255,230); */
    background-color: rgb(233,233,207);
}
.bgyellow {
    background-color: rgba(255, 255,   0, 0.1);
}
.bgyellow2 {
    background-color: rgba(255, 255,   0, 0.2);
}
.bgBeigeSolid {
    background-color: #ffa;
}
.bgbeige1 {
    background-color: rgba(255, 226, 134, 0.1);
}
.bgbeige2 {
    background-color: rgba(255, 226, 134, 0.2);
}
.bgbeige {
    background-color: rgba(255, 226, 134, 0.5);
}
.bgyellowvip {
    background-color: rgba(255, 255,   0, 0.1) !important;
}
.bggold {
    background-color: rgba(255, 215,   0, 0.2);
}
.bgbrown1 {
    background-color: rgba(129,  65,  18, 0.1);
}
.bgbrown, input[type="text"].bgbrown, select.bgbrown {
    background-color: rgba(129,  65,  18, 0.2);
}
.bgmagenta0 {
    /* background-color: rgb(255,230,255); */
    background-color: rgb(233,207,233);
}
.bgmagenta {
    background-color: rgba(255,   0, 255, 0.1);
}
.bgorangex {
    background-color: rgb(221, 216, 174 );
}
.bgorange0 {
    background-color: rgb(237, 221, 205);
}
.bgorangea {
    background-color: rgba(255, 175, 95, 0.3);
}
.bgorange {
    background-color: rgba(255, 175,  95, 0.2);
}
.bgred0 {
    /* background-color: rgb(255, 230, 230); */
    background-color: rgb(236, 210, 210);
}
.bgred {
    background-color: rgba(255,   0,   0, 0.1);
}
.bgred0i {
    background-color: rgba(255,   0,   0, 0.01) !important;
    color: darkred;
}
.bgred2 {
    background-color: rgba(255, 100, 100, 0.2);
}
.bgred2b {
    background-color: rgba(255, 100, 100, 0.2);
    font-weight: bold;
}
.bgredvip {
    background-color: rgba(255, 100, 100, 0.1) !important;
}
.bgredvip2 {
    background-color: rgba(255, 100, 100, 0.2) !important;
}
.bgredvip01 {
    background-color: rgba(255,   0,   0, 0.01) !important;
}
.bgred05 {
    background-color: rgba(255,   0,   0, 0.05);
}
.bgpink {
    background-color: pink;
}
.bgcyan {
    background-color: rgba(  0, 255, 255, 0.1);
}
.bggreen0 {
    /* background-color: rgba( 230, 255, 230); */
    background-color: rgba( 209, 235, 209);
}
.bggreen {
    background-color: rgba(  0, 255,   0, 0.1);
}
.bggreen2 {
    background-color: rgba(  0, 255,   0, 0.2);
}
.bggreenvip {
    background-color: rgba(  0, 255,   0, 0.1) !important;
}
.bggreenvip2 {
    background-color: rgba(  0, 255,   0, 0.2) !important;
}
.bggreenlt {
    background-color: rgba(220, 255, 220, 0.1);
}
.bggreenish {
    background-color: rgba( 10,  70,   0, 0.1);
}
.bgblue {
    background-color: rgba(  0,   0, 255, 0.1);
}
.bgblue0 {
    background-color: rgb( 200, 227, 255);
}
.bgdarkblue {
    background-color: rgba(  0,   0, 180, 0.2);
}
.bgdarkbluelt {
    background-color: rgba(  0,   0, 180, 0.05);
}
.bgdarkbluelt1 {
    background-color: rgba(  0,   0, 180, 0.1);
}
.bgblack, input[type="text"].bgblack {
    background-color: rgba(  0,   0,   0, 0.1);
}
.darkerHeader, thead.darker2>tr {
    background-color: rgba(  0,   0,   0, 0.1);
}
.bglightgray {
    background-color: rgba(150,150,150,0.2);
}
.bglightgray1 {
    background-color: rgba(150,150,150,0.1);
}
.bgBasicBlur {
    background-color: rgba(230,228,220,0.8);
}
.bgblackvip {
    background-color: rgba(  0,   0,   0, 0.1) !important;
}
.bgwrongtip {
    background-color: pink;
    color: darkred;
    font-weight: bold;
    padding: 0px 3px;
    border: 1px solid darkred;
    /* border-radius: 3px; => .round */
}
.invisTxt {
    color: transparent;
}
.blacked {
    color: black;
}
.grayed {
    color: gray;
}
.lightgrayed {
    color: lightgray;
}
.darkgrayed {
    color: darkgray;
}
.whited {
    color: white;
}
.redden {
    color: red;
}
.reddish {
    color: rgba(255,   0,   0, 0.5);
}
.lightredden {
    color: lightred;
}
.greener {
    color: green;
}
.rebecca {
    color: rebeccapurple;
}
.maroon {
    color: maroon;
}
.maroon_important {
    color: maroon !important;
}
.lightgreener {
    color: lightgreen;
}
.bodycolor {
    color: #008;
}
.cancelLabel, .darkRedLabel {
    color: darkred;
}
.unauthorized {
    color: darkred;
    background-color: rgba(230,230,230,0.6);
}
.cancelValue {
    color: red;
    font-weight: bold;
}
.importantLabel {
    color: rebeccapurple;
    font-weight: bold;
}
.importantValue {
    color: maroon;
    font-weight: bold;
}
.errorLabel:not(.bgpink) {
    background-color: rgba(255,0,0,0.01) !important;
}
.errorLabel {
    color: darkred;
    font-weight: bold;
    margin: 0 auto;
    text-align: center;
    align: center;
}
.lightBlurred {
    background-color: #efffda;
    color: #884;
}
.greenHighlight {
    background-color: #dfffdf;
    color: #080;
    font-weight: bold;
}
.yellowedbg {
    background-color: #efffda;
}
.bluedbg {
    background-color: rgba(218,239,255,0.1);
}
.bluedbg5 {
    background-color: rgba(218,239,255,0.5);
}
.greenbgi {
    background-color: #dfffdf !important;
}
.greyedbg {
    background-color: #dadada;
}
.reddenbg {
    background-color: #ffdada; /* #daffff; */
}
.reddenbgi {
    background-color: #ffdada !important; /* #daffff; */
}
.italic {
    font-style: oblique;
}
.smaller {
    font-size: smaller;
}
.larger {
    font-size: larger;
}
.siblingMessage {
    font-size: smaller;
    color: chocolate;
}
.highMsg {
    font-size: 110%;
    color: #308;
    text-shadow: 0 0 2px #FDF;
}
.counterInvoiceRow {
    min-font-size: 9px;
    font-size: calc(50% + 0.5vw);
}
.underDash {
    text-decoration: underline;
    text-decoration-style: dashed;
}
.requiredCfdi {
    color: #309 !important;
}
.requiredCfdiMissing {
    color: maroon !important;
/*    background-color: rgba(200,240,240,0.3) !important; */
}
.requiredCfdi:nth-child(even) {
/*    background-color: rgba(200,240,200,0.3) !important; */
}
.requiredCfdiMissing:nth-child(even) {
/*    background-color: rgba(190,190,190,0.3) !important; */
}
.optionalCfdi {
    color: #079 !important;
}
.optionalCfdi:nth-child(even) {
/*    background-color: rgba(220,200,220,0.3) !important; */
}
.optionalCfdiMissing {
    color: #aac !important;
}
.optionalCfdiMissing:nth-child(even) {

}
.negativeValue {
    color: #800;
}
.boldValue, .footValue {
    font-weight: bold;
}
.fontNrrwi {
    font-family: Arial !important;
    font-stretch: 50% !important;
}
.font8N {
    font-size: 8px;
    font-weight: normal;
    white-space: nowrap;
}
.fontSmall {
    font-size: 9px;
}
.fontSmalli {
    font-size: 9px !important;
}
.fontPageFormat {
    font-size: 9px;
    font-weight: normal;
}
.font10 {
    font-size: 10px;
}
.font10i {
    font-size: 10px !important;
}
.fontCondensed {
    font-size: 12px;
    font-weight: bold;
    font-family: Arial !important;
    font-stretch: 50% !important;
}
.fontMedium {
    font-size: 12px;
    font-weight: normal;
}
.fontMedFat {
    font-size: 12px;
    font-weight: bold;
}
.fontNormali {
    font-weight: normal !important;
}
.font14 {
    font-size: 14px;
}
.fontBig {
    font-size: 16px;
}
.fontLarge {
    font-size: 18px;
}
.fontRelevant {
    font-size: 20px;
    font-weight: bold;
}
.fontImportant {
    font-size: 24px;
    font-weight: bold;
}
.fontHuge {
    font-size: 36px;
}
table.collapse, table.bcollapse {
    border-collapse: collapse;
}
input[type="text"].clearable {
  background: rgba(255, 255, 255, 0.7) url(data:image/gif;base64,R0lGODlhBwAHAIAAAP///5KSkiH5BAAAAAAALAAAAAAHAAcAAAIMTICmsGrIXnLxuDMLADs=) no-repeat right 2px center;
  cursor: pointer;
}
table.separate0 {
    border-spacing: 0px;
    border-collapse: separate;
}
table.separate1 {
    border-spacing: 1px;
    border-collapse: separate;
}
table.layauto {
    table-layout: auto;
}
table.lytfxd {
    table-layout: fixed;
}
table.cellborder1>thead>tr>th, table.cellborder1>thead>tr>td, table.cellborder1>tbody>tr>th, table.cellborder1>tbody>tr>td {
    border: 1px solid gray;
}
div.tableWrapper {
    width: 722px;
    height: 273px;
    overflow-x: auto;
    overflow-y: hidden;
    border: 1px inset lightgray;
}
div.tableWrapper table, .wideClash {
    width: 100%;
    border-collapse: collapse;
}
div.tableWrapper thead {
    border-bottom: 1px solid lightgray;
}
div.tableWrapper thead tr {
    background-color: rgba(200, 255, 255, 0.3);
}
div.tableWrapper thead th.asc {
    background-image: url(<?= "/{$_pryNm}/imagenes/fondos/cellSortAsc.png" ?>);
    background-repeat: repeat;
}
div.tableWrapper thead th.desc {
    background-image: url(<?= "/{$_pryNm}/imagenes/fondos/cellSortDesc.png" ?>);
    background-repeat: repeat;
}
div.tableWrapper tbody tr:nth-child(even) {
    background-color: rgba(255, 255, 200, 0.3);
}
div.tableWrapper tbody tr:hover {
    background-color: rgba(255, 255, 255, 0.4);
}
div.tableWrapper td, div.tableWrapper th {
    padding: 3px;
}
div.tableWrapper input[type="text"] {
}
div.tableWrapper hr {
    margin-top: 3px;
    margin-bottom: 2px;
}
div.tableWrapper button {
    margin-bottom: 3px;
}
div.cattableWrapper {
    width: 750px;
}
div.cattableWrapper fieldset {
    width: 725px;
    border: 1px groove lightgray;
}
div.cattableWrapper span.pageCtrl {
    width: 32px;
    display: inline-block;
}
img.taricon {
    width: 18px;
    height: 18px;
    vertical-align: middle;
}
.vbottom {
    vertical-align: bottom;
}
img.refresher, img.operator, #reloadGRP, #reloadPRV {
    width: 16px;
    height: 16px;
    vertical-align: text-bottom;
    cursor: pointer;
}
img.file24 {
    width: 24px;
    height: 24px;
    cursor: pointer;
}
.pointer {
    cursor: pointer;
}
.zoomIn {
    cursor: zoom-in;
}
.zoomOut {
    cursor: zoom-out;
}
.erasePointer {
    cursor: url(<?= "/{$_pryNm}/imagenes/icons/deleteCursor20.png" ?>), auto;
}
.fatPointer1 {
    cursor: url(data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAACAAAAAgCAYAAABzenr0AAAJFklEQVR42rWXCXBU9R3Hv+/Ye7PZTbLZJCQBRIej2JHSkStgoS2jWJlBzhpNOKscBR2wIrSlVA4NIGoJMBVBoTOFloKlDGEIV0K4hyvDCBEQAiSQY7PZ7G52913//t4L4WiCoh3ezl5v3/v/Pr/f//s7lsN9h8fjcdpstmcFnq9rjkYrOY6L1NfXq3iMB3f/F7fbnZGamrqtS5cnfnL7dk1JdXV1SSwWKzObTRV1dfW3HjuA3W7J8KZmbFmw/KOcZ7pkYf++Azh69AiruFhxrPpWdVE8Ht9vtVrL/X5/6PEAWO2+5BT3P976YNWg/LEjkCQAtAU4d+4sjh09hrLDhwPnz58vbmxs/JLn+ZKmpqbq/xsgi8uxArxFYXI4yF9JTe7Ab576x2WDeg38OXqlJ8Lnst+9+Nq1azhz5gz27d+vHC4rO3b16tXdpJedDYHAuR8MkMn1d9Fbqsa0UEyo89p9sU/nLFrSt8+QYWiONqN3tg+JdjPYfeGKRCK4fOUKSkpKULRr16Uzp08fjkWjfwuGQvt+CEACA5/GGIvJQtBnTmlc9faihX2GvTwW9cEQBDL9TFYqRF4AQYIyAwLfgqIxhpqa26STY9i+bXvdkSOHT/gb/BtUWf13OBJWHgmgAzcggd58LQCNXlNKYPWs38/rO2JcPmRZQigag8tmRbe0JAOAsXs3kw5whwXNzc2klXPYtGlT8969e8tramoKnU7nVsqk2LcD8P0TwPg7AEGvmOQvnDb37X5jXpsMWZGhqSqisop0twNZngSoqgb2v4tQVHgi0Vk0jeHEiePYuHEjKy0tPUgAK0VRLK6rq2sXhLYgh7YABoAiBlN4d33hlNlv9s+dOBWKqhCAZnguaxo6p7iR7LC2C3EvKgRDQPrvBw8cxOefb2DFxcVrSTfvUda0qSVcFj/IqWmaj5aUCMDDu+oKJ8yanpP/xiyoigJVUw3PZDKqh7yrzwObWSQ47Vv3VhB4475QKIQPP1yJDRvW7wlHIpP89fU3HwDI5gY4VSMCIICmROa8vSpvxhvPTZoxh8Kpkbdyi2fklb4VdjKuQ+hCVDX2UABdK3QLRAKpq/dj+EsvSZe+rnjV39DwzwcjwD3r1GDxgWmyJISczHnrL+Mmjx8ydfa7xt4qinJnn2lReoRjCpIcNoJwG1mgsfYhdMP6cf36daz7bB02b95cVnWzaiyJ9YHixXUU+jpkTUzjGJMlPmTXnLc/eTlv9C9nzv0ThVE0hHj3Yt0zegaaJXRKSkDHFFfbrSBS8U5q7NixA+vXr8ep06fOUvWcEA6Fz7bRQCe+n0NiQhrPoMTRZNZcNStfGPXii7MXLIbFYjNSscU4Z0RA3wrdqD8SQ/f0ZGRQdrRCtKblhYsXsaZwNUpKS0B9Y08gEJhJnle0mwU+5NjNHEvXGKdS1nPMVftBztD+o+ctWYkElwuSAdDqewuGQBCBWNzYjt7ZqUhJsBmLkZcU6i04VFqKyuuVuF55Yx+l38hYPBp8mFa4NOTYBI5l0LoE0Mw4d+3Cp/t0z1+4Yg2SvamQJemesO6D0D9VB8OwWaz4aWYSvqKGtWXrVmRnZyM3N5ckxTBz5szKnTt3jg6Fmk4+FCAT/W2M4wiAYzIicd7TMLdz9/QZC1YUolOXpyDF4w+q+04F0GMS0zjUNoVxdNeXiNZWY9KE8ejxox53+0Z5eTny8vKOkxCH0jY0PQzASgBp5JcpzqIhwR2Y6s2yzV+wfJXQs1dvxOP3Clir71S0YLPZ0Uxw69cWIhgMYuL0tzCwayZIzEZ6tvaMpUuXqgUFBX+g7VnaLkAGBljo2nTeAIgFhcSmXzu8yuJ5i5c5+g8ZSgBRtJY9HUAvTHa7wzi17qMCNIQiGPn6m+ApY5502/AkpWdrpdRT8UJFBcaMGnW6qqpqcHtR0JuRid4zaHGzwqQgczT9zJoc+XjGO/PTho/JRTwWM7xuNe5wOI3FVxcsQmXlDUx6989wJ7ogU+t22S3o2SEFZkGgazUDgMov8vPzbx06dGgkZcTRtmnI9RNl8OlkwKYyNaxagp1FT+CzMfnju74+ey4USW7pghRWZ4KTIiJh9bLFOFi8G7OXrUbPnk/DxasUbh7BqIRMali+RLsBoJ/TS/HkyZP9RUVFE+jzf9oAZKGPoHGirgGHXo7jXKPZ6gut7dG7x+DFn/wVdvJYkWU4nQkI+OuxZsX72LNjGzI6PoGFa77AUx18oKZhiC4iqYhT9+zidcNtMxlFqeLSZbyW+0otCTGXWvTedkTYh+N4kSYiJNJXJcbCUUda83y7m02bMvMdbsSreSQsDV9f+Aprlr+P8lPHYXM4qFGq4rARY/DbOb+jAiRQyZYNATZGZUjkvcdJBYpqyOrlS7Br+9ZL9NPzNNJ9004EBujwSZRRyRQFTWJSBI7AwJRsodDudKb8atQ4WEnxO7f+HTW3bsLEO8oDtbG19kRhuMmqPf+LF4bjlYlTkOpLgyiajC4UpiJ15epV/OuL9ThZdgA02n9K8+Nv2s0C/SWL6+eiZptqpBn1lxgaeUeaND0hWciPxpo9+nmT2eJXouLuULXwsSoJ3zBTuJsnk3+PM8mDU7w+dOvxY3gJQqHuWV9Tg0sUsQa/HxzPH6utrc1raGi49FAAmgttpPM0vXvCCLiqxVmTYEqUBjvc4lAaMdRoI3ZJQUuxCTYmcLyTaobevn2udEyjSAyT5bi3pQfrT54ywHJTlpWiSCRcQKP95YdWQv0lFQNFE6+mUzW00Ql98tRVT6WZchCKlUqKxMEcMcHkIQN6nDX9VpUaaBwhkylBGWBN4PuYzBwNt6TDqHBDFkO7q6orD+A7jrt/TDK5vh4G0Xun6rCWCU8fArQw9cAAOUW+MS9NKVaqcrqvxjU0D9DEIMUYZJGusNF8SedFfy1OBr7L+AMAejoyTkwiI/r/BOq6TNEYHxHABW+wQ0ZD6MDrf2JYCjG2tD8j5i2jF/TZxCjSkEwQ/JUojX0vABjlcABHPckmMt6kUEJwjI9Xs7IHJg7Si4nucpP/DjImoLVXUwsg6AhjYqjqEY23AXjUI417jqd4m8BkC8czXtN4KgKQSb7yTRxh32et/wJPSoRd6oGs9QAAAABJRU5ErkJggg==), auto;
}
.fatPointer2 {
    cursor: url(data:image/png;base64,AAACAAEAICAAAAAAAACoEAAAFgAAACgAAAAgAAAAQAAAAAEAIAAAAAAAgBAAABMLAAATCwAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAPwEYAkABHAc7ABwOOgIdEDwAGws8ARsGOgAcAQAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAADsBGgE6AhkKPAEdDzoCGQk8ARoCAAAAAAAAAAAAAAAAAAAAADoBHAU6AhwVOwEcIDsBGyM7ARskOwEbIzsAGyA6ARwVPgAbCAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA3AB0DOwEcFToBGyM7ARslOgIcITwBGhY9AR0HAAAAAAAAAAA8AhsIOwIbHDsBGyU7ARslOgEbJDwBGyQ8ARwkPAAcIjsAGyE7ABobPgAfCAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAADgBHBE8ARsmOwEbJjsBGyU8ARwlPQEcJTsAHBk9ABsIPQIcCTwCGx46ABsmOwEcJTsBGyQ8ARwkOwAaIDUAFhgwABUcLwEVJTEAFSQ1ABYRAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA9ABYEOgIbHTsBGyY7ARwkPAIcJDsAGyEzABUZLwAVITIAFhs4ABcQPQAcITwBHCY7ARwkPAEcJDcAGR4qABIjJg0XXigaH5UpICStKR8jpiwWIIkfGhtBAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAADoBHAk7ARshOwEcJTwBHCQ5ARoiKwATJSUPGGYpHyOsKx8kqSsPG18yABUaOwAaGT0BHSU0ABYfJgQSNiYdIKAkJSXqHSAg/xwfIP8bICD/HSEg/yQkJOMgHx+aFBQUJAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAOQEdDjsBGyQ8ARwkPAAbIiwAEyQmGB6PKSsr7h8jIv8gIyP/Jyko7CsgJKQuBhc1MQAUEyUFEzsnISS+IiYl/zk7Of9nZWH/f3x2/3p5cv9jYl7/OTk5/yYmJ/8oKCjHEhISJQAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAADsAHQE7AB0TOwEbJjwBHCQ2ABYZJQsWVigpKus0NTT/jImA/5aSiP89Pjz/ISUl/ywnKtEkERlpJyAjtyUoKP9zcWz/2NPG//Dq3P/17uD/9O3g/+3l1P/MxLH/amVe/ykpKf8hISGaAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAANwAcATsCHRc7ARsmPAEbIjAAExkmGB6UJSgo/pyWjf/68t7/+PHf/87Iuf9WVFD/HyEi/yosK/snKCj/fXly//Pt3//27+H/8evd//Dq3v/r4c7/6d/I//Tr2f/u5tX/dHJr/yAhIdogICAiAAAAAAAAAAAAAAAAAAAAAAAAAAA3AB0DOwEbGzwBHCY8ABsgLAEUJh8YHL5JS0b/493L/+/o1f/q49H/9+/d/+PczP9raWP/GRsd/zs6N//c0bj/8+zd/+/p3f/v6d7/7OPT/+ngzP/59e///v7////////b2tj/NTU16RcXF0QAAAAAAAAAAAAAAAAAAAAAAAAAADkBIAQ7ARsdPAEcJjkAGRoqBxY7GRga2Hl3cP/x6tf/7OTT/+rj0f/p4dD/9e7c/+/n1f90cmv/LC0r/8K4nv/v5Mr/7ObY/+7o2//o38z/9fDo///////9/fz///////Ly8v9WVlbsDQ0NTQAAAAAAAAAAAAAAAAAAAAAAAAAAOwEbBTsBGx88ARwlNgAYFigLF1YdHh/nmJWL//Pr2f/r49L/6uPR/+vj0v/o4c//9Oza//Ts2f94dm//W1dQ/93Qs//r38P/4da+/+jgzP/8+/j///7+//38+///////8fHx/1BQUOYTExM5AAAAAAAAAAAAAAAAAAAAAAAAAAA7ABoHOwEbIDwBHSU0ABYWJg8ZbyIkJPStqZ7/8+vY/+rk0f/q49H/6uPR/+vj0v/o4c//8+vZ//Pr2P99e3T/cm5i/+LVtf/m2Lf/7+jb///////9/Pz//fz7///////Y2Nb/LCwszREREQ4AAAAAAAAAAAAAAAAAAAAAAAAAADoAHAg7ARsgPAEcJTMAFRgiEBiBKy0t/L+5rP/y6tj/6uPR/+rj0f/q49H/6+PS/+rj0f/p4tD/8+vZ/+zkz/+BfG3/cm1e/+PYwP////3//v7+//38+//+/fz//////4aGhvwQEBB5AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAOgAcCTsBGyM8ABsiMQAWGx8RGJM4Ojn/zce4//Lq2P/r49L/6+PS/+vj0v/r49L/6uPR/+rj0f/q49L/6+HK/9/TtP+PiXv/dXNy/9XU1f/////////+///////R0dH/Ly8vyBEREQ4AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA8ABgJOwEbIzsAGyAxABUfGxAWoERHQ//b1MT/8enX/+vj0v/r49L/6+PS/+rj0f/r49H/6uPS/+rj0f/e1Lf/5tvA//j39P/IyMr/dHRz/5KSkf/t7ez/7Ovr/1pZWeAODQ01AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAADwAFwo7AR0jPAAbIC8AFSEZDhSqU1RQ/+Layv/w6Nf/6uPR/+vj0f/q49H/6+PR/+rj0v/r5NX/5NzE/+DUuP/28uv////////////v7+//mJeX/1pZWv87Ozv/HhwdvAkHCA8AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAOQIgCzwBGiM8ABwfLgAVJRcOE7RiYlz/5+HP//Do1v/q49H/6uPR/+vj0v/q49H/6+TT/+nhz//h1rz/9O/l///////9/Pv//fz7///+/f//////19fW/21sbf8uLi7/GxsbuQoKCicAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA4Ax8LPAEaIzsAGx4uAhUoFg4TumtsZf/r5NP/7+fW/+rj0f/q49H/6uPR/+rj0v/q49H/5NnA//Hr4P///////fz7//38+//9/Pv//fz7//79+////////////7Gxsf9PT0//HB0czwwNDDAAAAAAAAAAAAAAAAAAAAAAAAAAADYBHQw9ARsjOwAbHi4DFSoVDxS9dXNs/+7m1f/v59b/6+PS/+vj0v/r5NP/6uTS/+bcxv/v59j///7///79/f/9/Pv//fz7//38+//9/Pv//fz7//38+////////////+Hh4f99fn3/JSYlwwoKChoAAAAAAAAAAAAAAAAAAAAAOwEeDDsBGyM7ABodLQQWLBUQFcB9fHP/8ejW/+/m1P/q49H/6uPR/+vk0//o38r/7OXU//38+//+/v7//fz7//38+//9/Pv//fz7//38+//9/Pv//fz7//37+v/9/Pv//////+zr6v85ODj5EhITVQAAAAAAAAAAAAAAAAAAAAA+Ah0MPAAbIzsAGR0sBBUuFxIXw4KBeP/x6df/7eXU/+rj0f/q49L/6ODN/+riz//7+fb///////38+//9/Pv//fz7//38+//9/Pv//fz7//38+//9/Pv////+///////y8vL/gYCA/x8fH7kUFBMcAAAAAAAAAAAAAAAAAAAAADwBHA08ARskOwAaHCwEFjAYFBjFiId9//Hp2P/t5tT/6+PS/+ni0P/n4Mv/9/Ts///////9/Pz//fz7//38+//9/Pv//fz7//38+//9/Pv//v79//////////7/v76+/15eXv4ZGRmWDg4OGQAAAAAAAAAAAAAAAAAAAAAAAAAAOgIbDTwBGyQ7ABocLAMVMRkVGcaOjYP/8urZ/+3m1P/q49L/597J//Lu4v///////fz8//38+//9/Pv//fz7//38+//+/fz////+///////+/f3/x8fH/2lpaf4rKyu+ERERTRUVFQgAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA7ARsNPAEbJDsAGhwrAxUxGhcax5STif/z69r/7ebV/+XdyP/t5tb///7+//39/P/9/Pv//fz7//38+////v3////////////19fX/xsbG/2lpafUlJSXHDg4OXBoaGggAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAADoBHA08ARslOwAaHSwDFTQcFxvInZuQ//Tt2v/n3sb/5t7J//z7+f///f3//fz7//79/P////////////7+/f/m5+b/rq6t/11cXOoiIiK0DxAPUwwLDAQAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAOgEcDjsBHCQ5ABoXJAMSLBwaHMeinpD/7ODH/+LXvf/59fD//////////v////////////T09P/R0dD/kpKR/0hISNoWFhefDQ0PQwAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA8AR0IPgEdDzkAGQMWAgoZGB0dwaObhv/m2Lf/9fHo/////////////////9/f3/+urq7/bGxr+zAwMMMPDw97ERESLwAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAoIChcaHB3BrqWO///87f//////6+vr/7u8vP+Dg4T/SEhI7hwcHKkMDAxZDg4OGgAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAABwoKGiEgH8OxrJ7/zs/O/42MjP9TU1P+LCssxhISEn0LCws7ExMTDQAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAQEBAaJiYmyEhJSfoxMTG7FxcXfA8PD0AUFRUZAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAABEREQwaGhpCHBwdLRMTEw4UFBQDAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA//gP/+DwB//AYAP/wAAD/4AAAf+AAAB/gAAAPwAAAD8AAAAfAAAAHwAAAB8AAAAfAAAAHwAAAD8AAAA/AAAAfwAAAH8AAAA/AAAAHwAAAA8AAAAPAAAADwAAAB8AAAA/AAAA/wAAA/8AAB//AAB//+AB///gB///4D///+D///8=), auto;
}
.unmarked:not(.mark) {
    padding: 2px;
}
.mark {
    border: 2px solid #96b9f2;
    border-radius: 3px;
}
.round {
    border-radius: 3px;
}
.groovy, tr.groovy>th:not(.nogroovy) {
    border-left: 1px solid #e0e0e0;
    border-right: 1px solid #959595;
}
thead.groov th:first-child:not(.nogroov) {
    border-left: 1px solid #999999;
}
thead.groov th:not(.nogroov) {
    border-right: 1px solid #999999;
}
.sprut th:not(.nosprut) {
    border-top: 1px solid #ffffe8;
    border-left: 1px solid #ffffe8;
    border-right: 1px solid #979797;
    border-bottom: 1px solid #979797;
}
.nosprut.lsprut {
    border-top: 1px solid #ffffe8;
    border-left: 1px solid #ffffe8;
    border-bottom: 1px solid #979797;
}
.nosprut.rsprut {
    border-top: 1px solid #ffffe8;
    border-right: 1px solid #979797;
    border-bottom: 1px solid #979797;
}
tfoot.total td, tfoot.total th {
    border-top: 1px solid #979797;
}
.multiselect-container>li>a>label {
  padding: 4px 20px 3px 20px;
}
.multiselect-custom-btn {
    height: 19px;
    padding-top: 1px;
    font-size: 12px;
}
.btn .caret {
    float: right;
    margin-top: 6px;
}
.btn .multiselect-selected-text {
    float: left;
    width: 95%;
    overflow: hidden;
    text-overflow: ellipsis;
    text-align: left;
}
.ellipsis {
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  display: inline-block;
  vertical-align: middle;
}
.ellipsis91 {
    width: 91%;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.ellipsisCel {
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    vertical-align: middle;
}
.flowH {
    white-space: nowrap;
    overflow: auto hidden;
    display: inline-block;
}
.tooltipCase:not(.nodecoration) {
    text-decoration: underline;
    text-decoration-style: dashed;
}
.tooltipCase:hover {
    cursor: help;
}
.tooltipCase>.tooltip {
    border: #a0a0a0 2px dotted;
    display: block;
    z-index: 1050;
    background-color: rgba(255, 255, 255, 0.9);
    position: absolute;
    text-decoration: none;
    opacity: 1;
    filter: alpha(opacity=10);
}
.itooltip {
    /* border-bottom: 1px dashed; */
    text-decoration: none;
    display: inline-block;
    height: 20px;
}
.itooltip:hover {
    cursor: help;
    position: relative;
}
.itooltip span.itip {
    display: none;
}
.itooltip:hover span.itip {
    border: #c0c0c0 1px dotted;
    display: block;
    z-index: 1000;
    background-color: rgba(255, 255, 255, 0.9);
    position: absolute;
    text-decoration: none;
    top: 18px;
}
.itooltip:hover span.itip.toLeft {
    left: 0px;
}
.itooltip:hover span.itip.toRight {
    right: 45px;
}
.itooltip:hover span.itip.toTop {
    right: 45px;
    bottom: 20px;
    top: unset;
}
span[data], div[data] {
    position: relative;
    /*
    text-decoration: underline;
    text-decoration-style: dashed;
    color: #008;
    cursor: help;
    */
}
span[data]::hover::after, span[data]::focus::after, div[data]::hover::after, div[data]::focus::after {
    content: attr(data);
    position: absolute;
    left: 0px;
    top: 22px;
    max-width: 200px;
    border: 1px solid #aaaaaa;
    border-radius: 3px;
    background-color: #ffffdd;
    padding: 2px;
    color: black;
    font-size: 12px;
    z-index: 1000;
}
.previewTip {
    border: #c0c0c0 1px dotted;
    z-index: 1000;
    background-color: rgba(220, 220, 255, 0.7);
    position: absolute;
    text-decoration: none;
    bottom: 0px;
    right: 2px;
}
.sticky {
    position: -webkit-sticky; /* Safari */
    position: sticky;
}
#admfactura_settings, .absLeft {
    z-index: 1000;
    position: absolute;
    left: 1px;
}
#payStamp {
    z-index: 1000;
    position: absolute;
    width: 245px;
    height: 155px;
    right: 30px;
    bottom: 60px;
    transform: rotate(-30deg);
}
.adm_fileIcon {
    z-index: 1000;
    position: absolute;
    right: 3px;
    top: 1px;
    width: 16px;
    height: 16px;
    border: 2px outset lightgray;
}
.adm_fileIcon:hover {
    background-color: gold;
    cursor: pointer;
}
.adm_fileIcon:active {
    width: 18px;
    height: 18px;
    border: 1px solid lightgray;
    background-color: white;
}
.adm_noFileIcon {
    z-index: 1000;
    position: absolute;
    right: 8px;
    top: 8px;
    width: 8px;
    height: 8px;
    background-image: url(<?= "/{$_pryNm}/imagenes/hideColumnBtn.png" ?>);
}
.adm_fixFileIcon {
    z-index: 1000;
    position: absolute;
    right: 4px;
    top: 4px;
    width: 16px;
    height: 16px;
    background-image: url(<?= "/{$_pryNm}/imagenes/icons/repair.png" ?>);
    background-size: 14.6px;
    background-position: center;
}
.adm_fixFileIcon:hover {
    cursor: grab;
}
.adm_fixFileIcon:active {
    cursor: grabbing;
}
.zNo {
    z-index: -1;
}
.zIdx0 {
    z-index: 0;
}
.zIdx1 {
    z-index: 1;
}
.zIdx2 {
    z-index: 2;
}
.zIdx3 {
    z-index: 3;
}
.zIdx4 {
    z-index: 4;
}
.zIdx10 {
    z-index: 10;
}
.zIdx100 {
    z-index: 100;
}
.zIdx200 {
    z-index: 200;
}
.zIdx1000 {
    z-index: 1000;
}
.zIdx2k {
    z-index: 2000;
}
.zIdx3k {
    z-index: 3000;
}
.chartButtons {
    padding-right: 20px;
    display: flex;
    justify-content: center;
    align-items: center;
}
.chartSwitch {
    /* font-weight: 600; */
    /* font-size: 14px; */
    display: flex;
    justify-content: center;
    align-items: center;
    position: relative;
    /* padding-right: 20px; */
    box-shadow: 0 1px 3px 0 rgba(0,0,0,.25);
    padding: 10px 40px 10px 20px;
    background: rgba(255, 255, 255, 1);
    cursor: pointer;
    border-radius: 30px;
    height: 40px;
}
.chartSwitch:after {
    content: "▿";
    font-size: 24px;
    font-family: "Feather";
    position: absolute;
    right: 14px;
}
.switchColor {
    width: 20px;
    min-width: 20px;
    height: 20px;
    margin-right: 10px;
    border-radius: 50px;
}
.switchColor.infected {
    background: rgb(255, 65, 108);
}
.switchColor.dead {
    background: rgb(134, 67, 230);
}
.switchColor.recovered {
    background: rgb(97, 206, 129);
}
.switchColor.sick {
    background: rgb(40, 110, 255);
}
.switchName {
    font-weight: 600;
    font-size: 14px;
    white-space: nowrap;
}
.switchDate {
    font-size: 14px;
    white-space: nowrap;
    padding: 3px;
}
.switchUser {
    font-size: 14px;
    white-space: nowrap;
    padding: 3px;
    margin: 3px;
}
.switchDetail {
    font-size: 14px;
    white-space: nowrap;
    padding: 3px;
    margin: 3px;
}
.switchHeader {
    background-color: rgba(0,0,0,0.1);
    font-weight: 600;
    margin: 2px;
    border: 1px solid lightgray;
}
select.reduced {
    -moz-appearance: none;
    -webkit-appearance: none;
    appearance: none;
    border: 0px;
    background-color: transparent;
    color: #008;
    text-align-last:center;
}
select.reduced::-ms-expand {
    display: none;
}
select.reduced>option {
    background-color: transparent;
}
span.comparaSAT {
    background-color: rgba(255, 127, 0, 0.1);
}
span.consultaSAT {
    background-color: rgba(0, 127, 255, 0.1);
}
span.eliminaSAT {
    background-color: rgba(255, 127, 127, 0.3);
}
span.consultaSAT, span.comparaSAT, span.eliminaSAT:not(.hidden) {
    border: 1px solid darkgray;
    display: inline-block;
}
span.consultaSAT>button, span.comparaSAT>button, span.eliminaSAT>button {
    margin: 5px;
}
.satKeys.invoice {
    font-size: 9px;
    line-height: 1.2;
    vertical-align: top;
}
.satKeys.request {
    background-color: rgba(255,255,0,0.2);
    vertical-align: top;
    line-height: 1.0;
    border-top: 2px solid transparent;
}
[data-icon]:before {
    font-family: icons;
    content: attr(data-icon);
    speak: none;
    text-align: center;
    display: inline-block;
    margin: 0 auto;
}
/*
.icon:before {
    font-family: icons;
    speak: none;
}
.email {
    content: ✉;
}
*/
table.doPaginate { -fs-table-paginate: paginate; }
thead.doHeadGroup { display: table-header-group; }
tfoot.doRowGroop { display: table-row-group; }
table.breakAvoidI tr, table.breakAvoidI td, table.breakAvoidI th { page-break-inside: avoid !important; }
.breakAuto, table.breakAuto tr, table.breakAuto td, table.breakAuto th, table.breakAuto>td>p, table.breakAuto>td>span, table.breakAuto>td>span>p, tr.breakAuto>td, tr.breakAuto>td>p, tr.breakAuto>td>span, tr.breakAuto>td>span>p { page-break-inside: auto !important; }
@media screen {
<?php
    if ($_esDesarrollo) {
        //echo "    html {\n        outline: 8px solid light-magenta;\n        outline-offset: -10px;\n    }\n";
    }
?>
    html:not(.blank) {
        height: 100%;
        font-family: Tahoma, Arial, sans-serif;
    }
    body:not(.blank):not(.w8margin) {
        height: 100%; /* calc(100% - 8px); */
        margin: 0px;
    }
    body.w8margin {
        height: calc(100% - 16px);
    }
    #cntRcpHeader, #cntRcpSide, #cntRcpFooter {
        display: none !important;
    }
    .noscreen, .noscreen * { 
        display: none !important;
    }
}
@media screen and (max-width: 504px) {
    #area_general>h1.mod1, #area_general>#area_top>h1.mod1 {
        font-size: 17px;
    }
}
@media screen and (max-width: 512px) {
    #area_general>h1, #area_general>#area_top>h1 {
        font-size: 18px;
    }
}
@media screen and (min-width: 504.1px) and (max-width: 527px) {
    #area_general>h1.mod1, #area_general>#area_top>h1.mod1 {
        font-size: 17px;
    }
}
@media screen and (max-width: 595px) {
    #area_general>h1, #area_general>#area_top>h1 {
        line-height: 1.4;
    }
    #area_general>h1.mod1, #area_general>#area_top>h1.mod1 {
        height: 65px;
    }
    #area_detalle.mod1 {
        height: calc(100% - 95px);
    }
    /* #principal>h1.area_header {
        line-height: 2;
    } */
}
@media screen and (min-width: 595.1px) and (max-width: 818px) {
    #area_general>h1.mod1, #area_general>#area_top>h1.mod1 {
        height: 60px;
    }
    #area_detalle.mod1 {
        height: calc(100% - 90px);
    }
}
@media screen and (min-width: 600.1px) {
    .mobile-menu-toggle,.mobile-x-toggle,
    .menu-overlay {
        display: none !important;
    }
}
@media screen and (max-width: 600px) {
    .column, .column2 {
        width: 100%;
    }
    .hideOnThinnest {
        display: none;
    }
    h1 {
        margin-top: 0;
    }
    .padhtt {
        padding-top: 0;
    }
    #encabezado { height: 80px; }
    #head_logo, #head_logo>a, #head_logo>a>img {
        height: 78px;
    }
    #head_logo>a, #head_logo>a>img {
        width: 78px;
    }
    #head_logo { position: absolute; top: 0; left: 0; text-align: left; }
    #head_main { width: 100%; height: 78px; margin-top: 0px; padding: 0 60px; }
    #head_main>h1 { margin-top: 10px; }
    #bloque_central:not(.noHeader):not(.noEncabezado) {
        height: calc(100% - 104px);
    }
    #pie_pagina { height: 24px; line-height: 22px; font-size: 12px; }
    #pie_pagina>.pie_element { padding: 0 2px; }
    * {
        font-family: "Roboto Condensed", sans-serif !important;
        font-optical-sizing: auto;
    }
<?php if ($hasUser) { ?>
    #lado_izquierdo {
        position: fixed;
        top: -1200px;
        left: 0;
        width: 100%;
        z-index: 2000;
        padding: 10px;
        border-width: 2px;
        border-color: #878787;
        border-style: solid;
        background: #e9e9e9;
        transition: top 0.3s ease-out;
    }
    .mobile-menu-toggle{
        position: fixed;
        right: 2%;
        top: 1%;
        z-index: 2000;
    }
<?php } else { ?>
    #lado_izquierdo {
        position: absolute;
        top: 80px;
        left: 40px;
        width: calc(100% - 80px);
        z-index: 100;
        opacity: 50%;
    }
    #lado_izquierdo:not(.shortBy3) {
        height: calc(100% - 104px);
    }
    #area_acceso {
        width: 232px;
        background-color: lightgray;
        padding-bottom: 8px;
        border-radius: 6px;
    }
    .mobile-menu-toggle, .mobile-x-toggle {
        display: none;
    }
    #principal:not(.noMenu) {
        position: absolute;
        top: 80px;
        left: 0;
        width: 100%;
        z-index: 1000;
    }
<?php } ?>
    #lado_izquierdo.menu-mobile-visible {
        top: 0;
        height: max-content;
    }
    #lado_izquierdo>form {
        box-shadow: unset;
    }
    .menuHandle, .logoSpace, .navIniImg {
        display: none;
    }
    .menu_izquierdo {
        height: calc(100vh - 60px);
        overflow-y: auto;
    }
    .mobile-x-toggle {
        position: relative;
        left: 46%;
    }
    .menu-overlay.visible {
        display: block;
    }
    .fontCondensed, .fontMedium, .fontMedFat {
        font-size: 11px;
    }
    .font14 {
        font-size: 12px;
    }
    body h4, .fontBig, table.contrarrecibo, table.firmasCtr {
        font-size: 14px;
    }
    body h3, .fontLarge, #area_detalle.noboots>fieldset>legend {
        font-size: 16px;
    }
    body h2, #contenedor>h2, #area_general>h1, #area_central>h1, #area_central2>h1, #area_central3>h1, #area_central4>h1, #area_central_gencr>h1, .fontRelevant, .chartSwitch:after {
        font-size: 18px;
    }
    body h1, #head_main>h1, .fontImportant {
        font-size: 20px;
    }
    .fontHuge, .creditDays {
        font-size: 26px;
    }
    #menu {
        width: calc(100% - 4px);
    }
    #top {
        width: calc(100% - 4px);
    }
    #top>li {
        margin-bottom: 4px;
    }
    #top ul.floating {
        top: 25px;
    }
    #top input[type="submit"], #top button {
        width: calc(100% - 4px);
        padding: 2px;
        margin: 2px;
        letter-spacing: -0.5px;
        transform: spaceX(0.8);
    }
    #top>li>input[type="submit"], #top>li>button {
        font-size: 11px;
    }
    #top ul>li>input[type="submit"], #top ul>li>button {
        font-size: 10px;
    }
    .menu_izquierdo a:not(.noApply), .menu_izquierdo input[type="submit"], .menu_izquierdo button {
        font-size: 11px;
    }
    #principal:not(.noMenu) {
        width: 100%;
    }
    #principal>h1.area_header {
        margin-top: 0;
    }
    #catalog_menu, #catalog_content {
        height: calc(100% - 40px);
    }
    #catalog_content>fieldset {
        width: 100%;
        height: 100%;
    }
    #catalog_fieldset>legend {
        margin-bottom: 0;
    }
    #catalog_content_section:not(.nofilter) {
        height: calc(100% - 77px);
    }
}
@media screen and (min-width: 600.1px) and (max-width: 620px) {
    #bloque_central:not(.noHeader):not(.noEncabezado) {
        height: calc(100% - 125px);
    }
    #pie_pagina { height: 27px; }
    #pie_pagina>.pie_element { padding: 3px; }
}
@media (max-width: 620px) {
  .fullbloc      { width: 574px; overflow: hidden; }
  .halfbox       { width: 281px; }
  .addML_EC_Peso { margin-left: 0px; }
  .addML_EP_Peso { margin-left: 0px; }
  .addML_SC_Peso { margin-left: 0px; }
  .addML_SP_Peso { margin-left: 0px; }
  .addML_EP_Prod { margin-left: 0px; }
  .addML_SP_Prod { margin-left: 0px; }
  .addML_SC_Prod { margin-left: 6px; }
}
@media (min-width: 620.1px) {
  .fullbloc      { width: 100%; overflow: hidden; }
}
@media (min-width: 620.1px) and (max-width: 650px) {
  .halfbox       { width: 48.5%; }
  .addML_EC_Peso { margin-left: 5px; }
  .addML_EP_Peso { margin-left: 5px; }
  .addML_SC_Peso { margin-left: 5px; }
  .addML_SP_Peso { margin-left: 5px; }
  .addML_EP_Prod { margin-left: 5px; }
  .addML_SP_Prod { margin-left: 5px; }
  .addML_SC_Prod { margin-left: 11px; }
}
@media (min-width: 650.1px) {
  .halfbox       { width: 49%; }
}
@media (min-width: 650.1px) and (max-width: 702px) {
  .addML_EP_Peso { margin-left: 0px; }
  .addML_EP_Prod { margin-left: 0px; }
  .addML_SP_Peso { margin-left: 0px; }
  .addML_SP_Prod { margin-left: 0px; }
  .addML_SC_Peso { margin-left: 0px; }
  .addML_SC_Prod { margin-left: 6px; }
}
@media (min-width: 650.1px) and (max-width: 765px)   { /* 77 */
  .addML_EC_Peso { margin-left: 1px; }
}
@media (min-width: 702.1px) and (max-width: 800px) {
  .addML_EP_Peso { margin-left: 1px; }
  .addML_EP_Prod { margin-left: 1px; }
  .addML_SP_Peso { margin-left: 0px; }
  .addML_SP_Prod { margin-left: 0px; }
  .addML_SC_Peso { margin-left: 0px; }
  .addML_SC_Prod { margin-left: 7px; }
}
@media (min-width: 765.1px) and (max-width: 916px)   { /* 151 */
  .addML_EC_Peso { margin-left: 2px; }
}
@media (min-width: 800.1px) {
    td.middleMediaAdjust1 {
        padding-left: 1%;
    }
    td.middleMediaAdjust2 {
        width: 45%;
    }
}
@media (min-width: 800.1px) and (max-width: 883px) {
  .addML_EP_Peso { margin-left: 2px; }
  .addML_EP_Prod { margin-left: 2px; }
  .addML_SP_Peso { margin-left: 1px; }
  .addML_SP_Prod { margin-left: 1px; }
  .addML_SC_Peso { margin-left: 1px; }
  .addML_SC_Prod { margin-left: 7px; }
}
@media (min-width: 883.1px) and (max-width: 933px) {
  .addML_EP_Peso { margin-left: 3px; }
  .addML_EP_Prod { margin-left: 3px; }
  .addML_SP_Peso { margin-left: 2px; }
  .addML_SP_Prod { margin-left: 2px; }
  .addML_SC_Peso { margin-left: 2px; }
  .addML_SC_Prod { margin-left: 8px; }
}
@media (min-width: 916.1px) and (max-width: 992px)   { /* 76 */
  .addML_EC_Peso { margin-left: 3px; }
}
@media (min-width: 933.1px) and (max-width: 1024px) {
  .addML_EP_Peso { margin-left: 4px; }
  .addML_EP_Prod { margin-left: 4px; }
  .addML_SP_Peso { margin-left: 3px; }
  .addML_SP_Prod { margin-left: 3px; }
  .addML_SC_Peso { margin-left: 3px; }
  .addML_SC_Prod { margin-left: 9px; }
}
@media (min-width: 992.1px) and (max-width: 1024px)  { /* 32 */
  .addML_EC_Peso { margin-left: 4px; }
}
@media (min-width: 1024.1px) and (max-width: 1031px) { /* 7 */
  .addML_EC_Peso { margin-left: 2px; }
}
@media (min-width: 1024.1px) and (max-width: 1125px) {
  .addML_EP_Peso { margin-left: 2px; }
  .addML_EP_Prod { margin-left: 2px; }
  .addML_SP_Peso { margin-left: 2px; }
  .addML_SP_Prod { margin-left: 2px; }
  .addML_SC_Peso { margin-left: 2px; }
  .addML_SC_Prod { margin-left: 8px; }
}
@media (min-width: 1031.1px) and (max-width: 1223px) { /* 192 */
  .addML_EC_Peso { margin-left: 3px; }
}
@media (min-width: 1125.1px) and (max-width: 1202px) {
  .addML_EP_Peso { margin-left: 3px; }
  .addML_EP_Prod { margin-left: 3px; }
  .addML_SP_Peso { margin-left: 3px; }
  .addML_SP_Prod { margin-left: 3px; }
  .addML_SC_Peso { margin-left: 3px; }
  .addML_SC_Prod { margin-left: 9px; }
}
@media (min-width: 1202.1px) and (max-width: 1356px) {
  .addML_EP_Peso { margin-left: 4px; }
  .addML_EP_Prod { margin-left: 4px; }
  .addML_SP_Peso { margin-left: 4px; }
  .addML_SP_Prod { margin-left: 4px; }
  .addML_SC_Peso { margin-left: 4px; }
  .addML_SC_Prod { margin-left: 10px; }
}
@media (min-width: 1223.1px) and (max-width: 1300px) { /* 77 */
  .addML_EC_Peso { margin-left: 4px; }
}
@media (min-width: 1300.1px) and (max-width: 1415px) { /* 115 */
  .addML_EC_Peso { margin-left: 5px; }
}
@media (min-width: 1356.1px) and (max-width: 1432px) {
  .addML_EP_Peso { margin-left: 5px; }
  .addML_EP_Prod { margin-left: 5px; }
  .addML_SP_Peso { margin-left: 5px; }
  .addML_SP_Prod { margin-left: 5px; }
  .addML_SC_Peso { margin-left: 5px; }
  .addML_SC_Prod { margin-left: 11px; }
}
@media (min-width: 1415.1px) and (max-width: 1530px) { /* 115 */
  .addML_EC_Peso { margin-left: 6px; }
}
@media (min-width: 1432.1px) and (max-width: 1547px) {
  .addML_EP_Peso { margin-left: 6px; }
  .addML_EP_Prod { margin-left: 6px; }
  .addML_SP_Peso { margin-left: 6px; }
  .addML_SP_Prod { margin-left: 6px; }
  .addML_SC_Peso { margin-left: 6px; }
  .addML_SC_Prod { margin-left: 12px; }
}
@media (min-width: 1530.1px) {
  .addML_EC_Peso { margin-left: 7px; }
}
@media (min-width: 1547.1px) {
  .addML_EP_Peso { margin-left: 7px; }
  .addML_EP_Prod { margin-left: 7px; }
  .addML_SP_Peso { margin-left: 7px; }
  .addML_SP_Prod { margin-left: 7px; }
  .addML_SC_Peso { margin-left: 7px; }
  .addML_SC_Prod { margin-left: 13px; }
}
@media print {
    *:not(.nooverfix) {
        overflow: visible !important;
    }
    html {
        height: auto;
        font-family: Tahoma, Arial, sans-serif !important;
    }
    body {
        height: auto;
        overflow: visible;
    }
    select {
        -moz-appearance: none;
        -webkit-appearance: none;
        appearance: none;
        padding: 2px 3px 3px 3px;
        box-shadow: 1px 1px 1px 0px gray;  
    }
    select::-ms-expand {
        display: none;
        padding: 2px 3px 3px 3px;
        box-shadow: 1px 1px 1px 0px gray;  
    }
    a[href]:after {
        content: none !important;
    }
    #bloque_central {
        height: 100%;
    }
    #principal {
        width: 100% !important;
    }
    .menuHandle {
        display: none;
    }
    #area_general>h1, #area_general>div, #area_general>table {
        text-align: center;
        white-space: nowrap;
    }
    #cntRcpHeader {
        font-size: 9px;
        display: block !important;
        position: fixed;
        top: 0;
        right: 0;
        text-align: right;
        background-color: rgba(255,255,255,0.7);
    }
    #cntRcpSide {
        font-size: 9px;
        display: block !important;
        position: fixed;
        top: 250;
        right: 0;
        text-align: right;
    }
    #cntRcpFooter {
        font-size: 9px;
        display: block !important;
        position: fixed;
        bottom: 0;
        right: 0;
        text-align: right;
    }
    #area_detalle {
        display: block;
    }
    .printWrapped {
        white-space: pre-wrap !important;
    }
    .noprint, .noprint *, .extraNoPrint { 
        display: none !important;
    }
    .invisiblePrint {
        visibility: hidden !important;
    }
    .semiPrint4 {
        opacity: 0.4 !important;
    }
    .noprintBorder {
        border: 0;
        outline: none;
    }
    .printBorder {
        border: 1px solid gray;
    }
    .printVBorder {
        border-top: 1px solid gray;
        border-bottom: 1px solid gray;
    }
    .printLBorder {
        border-left: 1px solid gray;
    }
    .printRBorder {
        border-right: 1px solid gray;
    }
    .printOutline {
        outline: 1px solid #ddd;
        outline-offset: -1px;
    }
    .noprintFlow {
        overflow: hidden;
    }
    .doprintBlock {
        display: block !important;
        visibility: visible !important;
    }
    .doprintInline {
        display: inline-block !important;
        visibility: visible !important;
    }
    .printStatic {
        position: static !important;
    }
    .breakAvoidI { page-break-inside: avoid !important; }
    table.doprintTable {
        display: table !important;
        visibility: visible !important;
    }
    tr.doPrint {
        display: table-row !important;
        visibility: visible !important;
    }
    thead.moveDownOnPrint th { margin-top: 20px; }
    .asTBody { display: table-row-group; }
    .asTHead { display: table-header-group; }
    .asTHead th { margin-top: -20px; height: 30px; display: table-cell; }
    table tbody tr td:not(.notbefore):before,
    table tbody tr td:not(.notafter):after {
        content : "" ;
        height : 2px ;
        display : block ;
    }
    .resultarea table:not(.contrafacturas) tr.adjustOnPrint th {
        padding: 3px;
    }

    .onprintBR0 {
        border-right-width: 0px;
    }
    .onprintTopMargin5 {
        margin-top: 5px;
    }
    .onprintTopMargin10 {
        margin-top: 10px;
    }
}
/* Bootstrap fixes */
.dropdown-menu>li>div.likeLink:hover {
    color: #262626;
    background-color: #f5f5f5;
}
.dropdown-menu>li>div.likeLink {
    display: block;
    padding: 3px 20px;
    clear: both;
    font-weight: 400;
    line-height: 1.42857143;
    color: #333;
    white-space: nowrap;
}
@media (prefers-color-scheme: darkX) {
    body:not(.blank), #encabezado, .pie_element, .basicBG, .menu_izquierdo ul>li>ul.floating>li button:hover, #autoCloseWindow, #dialogbox, .btnTab {
        background-image: url(<?= "/{$_pryNm}/{$bkgdImgName}" ?>);
    }
    .lightBG {
        background-image: radial-gradient(rgba(0,0,0,0.8), rgba(0,0,00,0.3)),
                        url(<?= "/{$_pryNm}/{$bkgdImgName}" ?>);
    }
    .menu_izquierdo ul>li>ul.floating>li input[type="submit"], .menu_izquierdo ul>li>ul.floating>li button {
        background-image: url(<?= "/{$_pryNm}/imagenes/fondos/oscuro/corrugatedbox7wm.jpg" ?>);
    }
    .menu_izquierdo ul>li>ul.floating>li a:hover, .menu_izquierdo ul>li>ul.floating>li input[type="submit"]:hover, .menu_izquierdo ul>li>ul.floating>li button:hover {
        background-image: url(<?= "/{$_pryNm}/imagenes/fondos/oscuro/corrugatedboxhwm.jpg" ?>);
    }
    .logo, #head_logo>a {
        background-color: rgba(255, 255, 255, 0.7);
    }
    .invoiceDoc, h1 select.likeh1 option, th>select.liketh, h1 select.likebh1, h1 select.likebh1 option, button.likeTH, table.contrafacturas {
        background-color: rgba(255, 255, 255, 0.5);
    }
    #area_acceso fieldset, #area_usuario3 fieldset, table.contrafacturas>thead>tr, #area_detalle.noboots>fieldset>legend, table.fixedHeader:not(.noblk) thead tr, table.fixedHeader tfoot tr:first-child {
        background-color: rgba(255,255,255,0.25);
    }
    #area_acceso fieldset, #area_usuario3 fieldset {
        border: groove lightgray 2px;
    }
    #result_table th {
        border: 1px solid rgba(155,155,155,0.1);
        background-color: rgba(155,155,155,0.1);
    }
    #catalog_menu input[type="button"]:hover, .menu_izquierdo a:hover, .menu_izquierdo input[type="submit"]:hover, .menu_izquierdo button:hover {
        background-color: rgba(200, 100, 200, 0.5);
        color: rgb(100,200,200) !important;
        /* -webkit-text-stroke: 1px black; */
        /* text-shadow: -1px 0 black, 0 1px black, 1px 0 black, 0 -1px black; */
    }
    ul.alternate>li:nth-child(even) {
        background-color: rgba(155, 155, 255, 0.3);
    }
    .menu_izquierdo ul>li .navSelected {
        background-color: rgba(155, 155, 75, 0.7);
        color: white !important;
        /* -webkit-text-stroke: 1px black; */
        /* text-shadow: -1px 0 black, 0 1px black, 1px 0 black, 0 -1px black; */
    }
    .menu_izquierdo ul>li>ul>li a:hover, .menu_izquierdo ul>li>ul>li input[type="submit"]:hover, .menu_izquierdo ul>li>ul>li button:hover {
        background-color: rgba(100, 100, 200, 0.7);
        color: rgb(255,155,255) !important;
        /* -webkit-text-stroke: 1px black; */
        /* text-shadow: -1px 0 black, 0 1px black, 1px 0 black, 0 -1px black; */
    }
    .menu_izquierdo ul>li>ul>li .navSelected {
        background-color: rgba(100, 200, 200, 0.5) !important;
    }
    div.admon>input[type="checkbox"]:checked {
        background-color: rgba(0, 55, 55, 0.1);
    }
    #area_usuario2>p {
        background-color: rgba(25, 25, 25, 0.7);
    }
    table.fixedHeader tbody tr:nth-child(even) {
        background-color:  rgba(5, 5, 55, 0.2);
    }
    table.fixedHeader:not(.noblk) tbody {
        background-color: rgba(0, 0, 0, 0.1);
    }
    #result_table td {
        border: 1px solid rgba(155,155,155,0.1);
        background-color: rgba(0,0,0,0.2);
    }
    #admin_block>form, #admin_block>div.blk, #admfactura_screen {
        background-color: rgba(0, 0, 0, 0.3);
    }
    table.fixedHeader tbody tr:nth-child(odd) {
        background-color:  rgba(0, 0, 0, 0.8);
    }
    #firstPageCover {
        background-color: black;
    }
    input[type="submit" i] {
        background-color: #C6E1DC;
    }
    select.concept_dropdown_list::-ms-expand {
        box-shadow: 1px 1px 1px 0px lightgray;  
    }
    select.concept_dropdown_list option:hover, .invertHoverBG:hover {
        box-shadow: 0 0 10px 100px #e16f00 inset;
        color: black;
    }
    select.inverse {
        color: #210;
        background-color: #884;
    }
    select.inverse>option:hover {
        background-color: blue;
    }
    select.inverse>option:not(:checked) {
        color: #210;
        background-color: #a82;
    }
    select.inverse>option:checked {
        background-color: #000;
        color: #ff7;
        /* -webkit-text-stroke: 1px black; */
        /* text-shadow: -1px 0 black, 0 1px black, 1px 0 black, 0 -1px black; */
    }
    select.inverse>option:checked:after {
        background; #999;
        color: #000;
    }
    select:not(.inverse)>option:checked {
        color: #210;
        background-color: #a82;
    }
    body:not(.blank), #pie_clock, #dialog_resultarea>table>tbody>tr, input[type="submit"], button, .menu_izquierdo a:not(.noApply), .menu_izquierdo input[type="submit"], .menu_izquierdo button, #area_acceso legend, #area_usuario3 legend, th>select.liketh, h1 select.likebh1, h1 select.likebh1 option, #area_detalle.noboots>fieldset>legend {
        color: #d8d8ff !important;
        /* -webkit-text-stroke: 1px black; */
        /* text-shadow: -1px 0 black, 0 1px black, 1px 0 black, 0 -1px black; */
    }
    input[type="submit" i], #dialog_resultarea>table:not(.nohover)>tbody:not(.nohover)>tr:not(.nohover):hover {
        color: #008 !important;
    }
    .menu_izquierdo input[type="submit" i], .bggreen0, .bgorange0, #pickFilter {
        color: #d8d8ff !important;
        /* -webkit-text-stroke: 1px black; */
        /* text-shadow: -1px 0 black, 0 1px black, 1px 0 black, 0 -1px black; */
        background-color: #1E2339;
    }
    .txtstrk {
        /* -webkit-text-stroke: 1px black; */
        text-shadow: -1px 0 black, 0 1px black, 1px 0 black, 0 -1px black;
    }
    .menu_izquierdo ul>li>ul>li input[type="submit"], .menu_izquierdo ul>li>ul>li button {
        box-shadow: -3px 0px 0px 3px #23280f, 3px 0px 0px 3px #23280f;
    }
    input, img, button {
        outline-width: 1px !important;
        outline-style: solid !important;
        outline-color: #88c !important; 
        /* outline-offset: -2px !important; */
    }
    a {
        color: #9fdfff;
        /* -webkit-text-stroke: 1px black; */
        /* text-shadow: -1px 0 black, 0 1px black, 1px 0 black, 0 -1px black; */
    }
    a:hover {
        color: #cfafff;
    }
}
