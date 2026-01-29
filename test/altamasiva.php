<?php
header('charset=UTF-8');
//ini_set('display_errors', 1);
//ini_set('display_startup_errors', 1);
//error_reporting(E_ALL);
require_once dirname(__DIR__)."/bootstrap.php";

$monthTag=$_REQUEST["monthTag"]??"";
$today=date("m-d-Y");
$currentMonthTag=date("Y/m");
if (!isset($monthTag[0])) $monthTag=$currentMonthTag;
$currentYear=+substr($monthTag, 0, 4);
$currentMonth=+substr($monthTag, 5);
require_once "configuracion/inicializacion.php";
if (!hasUser()||!validaPerfil("Administrador")) {
    if (hasUser()) {
        setcookie("menu_accion", "", time() - 3600);
        setcookie("menu_accion", "", time() - 3600, "/invoice");
    }
    header("Location: /".$_project_name."/");
    die("Redirecting to /".$_project_name."/");
}
$yearList=[];
$firstYear=2020;
for($i=$firstYear; $i<=$currentYear; $i++) $yearList[]=$i;
$monthList=["01"=>"enero","02"=>"febrero","03"=>"marzo","04"=>"abril","05"=>"mayo","06"=>"junio","07"=>"julio","08"=>"agosto","09"=>"septiembre","10"=>"octubre","11"=>"noviembre","12"=>"diciembre"];

require_once "clases/Facturas.php";
$invObj=new Facturas();
$invObj->rows_per_page=0;
// ToDo: Generar lista de archivos en espera de ser validados (ftp->list), solo los q cumplan con la fecha indicada
$num=-1;
$err=null;
if (isset($_POST["process"])) {
    require_once "clases/Facturas.php";
    $invObj=new Facturas();
    $invObj->rows_per_page=0;
    try {
        $num=$invObj->altaMasiva($monthTag);
    } catch (Exception $ex) {
        $err=$ex;
    }
}
$review=$invObj->resumenDeAltaMasiva();
require_once "templates/generalScript.php";
?>
<html>
<head>
    <meta charset="utf-8">
    <base href="<?= $_SERVER['HTTP_ORIGIN'] . $_SERVER['WEB_MD_PATH'] ?>" target="_blank">
<?php echoGeneralScript(); ?>
    <link href="css/general.php" rel="stylesheet" type="text/css"/>
    <script type="text/javascript">
        function submitMonthTag() {
            const monthTag=ebyid("year").value+"/"+ebyid("month").value;
            console.log("INI calcMonthTag "+monthTag);
            ebyid("monthTag").value=monthTag;
            if (canSubmit()) {
                const yrEl=ebyid("year");
                const yOpLs=yrEl.options;
                const yOptn=yOpLs.length;
                const yrVal=yrEl.value;
                for(let i=yOptn-1; i>=0; i--) if (yOpLs[i].value!==yrVal) yrEl.remove(i);

                const mnEl=ebyid("month");
                const mOpLs=mnEl.options;
                const mOptn=mOpLs.length;
                const mnVal=mnEl.value;
                for(let i=mOptn-1; i>=0; i--) if (mOpLs[i].value!==mnVal) mnEl.remove(i);

                clfix("topblock","relative");
                clfix("mask","hidden");
                ebyid("forma").submit();
            }
        }
        function checkSubmit() {
            if (canSubmit()) {
                clfix("topblock","relative");
                clfix("mask","hidden");
                return true;
            }
            return false;
        }
        function canSubmit() {
            const prEl=ebyid("processBtn");
            if (prEl.busy) {
                console.log("BUSY...");
                // después de 5 intentos cancelar el submit (si encuentro una forma de hacerlo) y quitar busy. O hacer refresh de la página...
                return false;
            }
            prEl.busy=true;
            return true;
        }
    </script>
</head>
<body>
    <div id="topblock" class="outoff1a mar7 pad7 scrollauto" style="width: calc(100% - 28px); height: 20px;background-color: rgba(255,255,255,0.5);">
        <form id="forma" class="inblock" method="POST" target="_self" style="margin-block-end: 0;width: calc(100% - 155px);" onsubmit="return checkSubmit();">
        AÑO:<select id="year" onchange="submitMonthTag();"><?php foreach ($yearList as $yr) {
            echo "<option value=\"$yr\"".($yr==$currentYear?" selected":"").">$yr</option>";
        } ?></select>
        MES:<select id="month" onchange="submitMonthTag();"><?php foreach ($monthList as $m=>$mes) {
            echo "<option value=\"$m\"".($m==$currentMonth?" selected":"").">$mes</option>";
        } ?></select>
        <input type="hidden" id="monthTag" name="monthTag" value="<?=$monthTag?>">
        <input type="submit" name="process" id="processBtn" value="Procesar">
        </form>
        <div id="mask" class="inblock abs_nw hidden semitransparent mar1" style="width: calc(100% - 157px); height: 32px; padding-left: 68px;"><img src="imagenes/icons/rollwait2.gif" height="32"></div>
        <div id="review" class="inblock hidden" style="width: calc(100% - 155px);"><?= isset($review)?($review["title"]??count($review["data"]??[])." bloques encontrados"):"Sin Resumen" ?></div>
        <div id="btnArea" class="inblock righted" style="width: 150px;"><input type="button" id="printBtn" value="IMPRIMIR" class="hidden" onclick="printContainer('fullview');"> <input id="switchBtn" type="button" value="RESUMEN" onclick="clfix(['fullview','forma','review','printBtn'],'hidden');toggleValue(this, ['RESUMEN','PROCESO']);"></div>
    </div>
    <div id="fullview" class="hidden outoff1a mar7 pad7 scrollauto" style="width: calc(100% - 28px); background-color: rgba(255,255,255,0.5); height: calc(100% - 69px);">
<?php
    if (isset($review["data"][0])) {
        echo "<UL>";
        foreach ($review["data"] as $reviewRow) {
            echo "<LI>{$reviewRow}</LI>";
        }
        echo "</UL>";
    }
?>
    </div>
<?php 
try {
    $lista=$invObj->listaDeAltaMasiva($monthTag);
    usort($lista,"sortList");
    require_once "clases/Proceso.php";
    $prcObj=new Proceso();
    $prcObj->rows_per_page=0;
    $prcObj->clearOrder();
    $prcObj->addOrder("date(fecha)","desc");
    $prcObj->addOrder("status");
    $prcObj->addOrder("left(detalle,7)");
    $prcObj->addOrder("identif");
    $prcData=$prcObj->getData("modulo='AltaMasiva' and detalle like '".substr($monthTag, 0, 4).substr($monthTag, 5)."%'");
    if (isset($lista[0])) {
        $heightValue=isset($prcData[0])?"50% - 45px":"100% - 109px";
        echo "<div class=\"outoff1a pad7 scrollauto\" style=\"margin: 0px 7px; width: calc(100% - 28px); background-color: rgba(255,255,255,0.5); height: calc({$heightValue});\">";
        echo "<table><thead><tr><th style=\"padding: 2px; text-align: left; font-size: 12px;\">#</th>".
                               "<th style=\"padding: 2px; text-align: left; font-size: 12px;\">FECHA</th>".
                               "<th style=\"padding: 2px; text-align: left; font-size: 12px;\">UBICACION</th>".
                               "<th style=\"padding: 2px; text-align: left; font-size: 12px;\">ARCHIVO</th>".
                               "<th style=\"padding: 2px; text-align: left; font-size: 12px;\">TAMAÑO</th>".
                               "</tr></thead><tbody>";
        foreach ($lista as $idx => $row) {
            echo "<tr".($row[0]===$today?" class=\"bggreen\"":"")."><td style=\"padding: 2px; text-align: left; font-size: 12px; white-space: nowrap;\">".($idx+1)."</td>".
                     "<td style=\"padding: 2px; text-align: left; font-size: 12px; white-space: nowrap;\">".$row[0]." ".strtolower($row[1])."</td>".
                     "<td style=\"padding: 2px; text-align: left; font-size: 12px; white-space: nowrap;\">".$row[4]."</td>".
                     "<td style=\"padding: 2px; text-align: left; font-size: 12px; white-space: nowrap;\">".$row[3]."</td>".
                     "<td style=\"padding: 2px; text-align: left; font-size: 12px; white-space: nowrap;\">".sizeFix($row[2])."</td>".
                     "</tr>";
        }
        echo "</tbody></table></div>";
    } else echo "<p style=\"outline: 1px solid #aaa; outline-offset: -1px; margin: 0px 7px; padding: 7px; width: calc(100% -28px); background-color: rgba(255,100,100,0.1);\">NO HAY NUEVAS FACTURAS PARA DAR DE ALTA EN $monthTag</p>";
} catch (Exception $ex) {
    echo "<p style=\"outline: 1px solid #a33; outline-offset: -1px; margin: 0px 7px; padding: 7px; width: calc(100% -28px); background-color: rgba(255,100,100,0.1);\">".$ex->getMessage()."</p>";
}
if (isset($prcData[0])) {
    $heightValue=isset($lista[0])?"50% - 45px":"100% - 109px";
    echo "<div style=\"outline: 1px solid #aaa; outline-offset: -1px; margin: 7px; padding: 7px; width: calc(100% - 28px); overflow: auto; background-color: rgba(255,255,255,0.5); height: calc($heightValue);\">";
    echo "<table><thead><tr><th style=\"padding: 2px; text-align: left; font-size: 12px;\">ID</th>".
                           "<th style=\"padding: 2px; text-align: left; font-size: 12px;\">FECHA</th>".
                           "<th style=\"padding: 2px; text-align: left; font-size: 12px;\">STATUS</th>".
                           "<th style=\"padding: 2px; text-align: left; font-size: 12px;\">IDENTIF</th>".
                           "<th style=\"padding: 2px; text-align: left; font-size: 12px;\">DETALLE</th>".
                           "</tr></thead><tbody>";
    foreach ($prcData as $idx => $row) {
        echo "<tr><td style=\"padding: 2px; text-align: left; font-size: 12px; white-space: nowrap;\">$row[id]</td>".
                 "<td style=\"padding: 2px; text-align: left; font-size: 12px; white-space: nowrap;\">$row[fecha]</td>".
                 "<td style=\"padding: 2px; text-align: left; font-size: 12px; white-space: nowrap;\">$row[status]</td>".
                 "<td style=\"padding: 2px; text-align: left; font-size: 12px; white-space: nowrap;\">$row[identif]</td>".
                 "<td style=\"padding: 2px; text-align: left; font-size: 12px; white-space: nowrap;\">$row[detalle]</td></tr>";
    }
    echo "</tbody></table></div>";
} else echo "<p style=\"outline: 1px solid #aaa; outline-offset: -1px; margin: 7px; padding: 7px; width: calc(100% -28px); background-color: rgba(255,100,100,0.1);\">NO SE ENCONTRÓ STATUS DE FACTURAS PROCESADAS</p>";
if (isset($err)) {
    echo "<p style=\"outline: 1px solid #a33; outline-offset: -1px; margin: 0px 7px; padding: 7px; width: calc(100% -28px); background-color: rgba(255,100,100,0.1);\">Error al procesar. ".$err->getMessage()."</p>";
}
?>
</body>
</html>
<?php
function sortList($rowA, $rowB) { // 0=date,1=hour,2=size,3=file,4=path
    if (isset($rowA[0]) && !isset($rowB[0])) return 1;
    if (isset($rowB[0]) && !isset($rowA[0])) return -1;
    return 0;
}
function byteconvert($bytes) {
    $symbol = array('B', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB');
    $exp = floor( log($bytes) / log(1024) );
    return sprintf( '%.2f ' . $symbol[ $exp ], ($bytes / pow(1024, floor($exp))) );
}
function chmodnum($chmod) {
    $trans = array('-' => '0', 'r' => '4', 'w' => '2', 'x' => '1');
    $chmod = substr(strtr($chmod, $trans), 1);
    $array = str_split($chmod, 3);
    return array_sum(str_split($array[0])) . array_sum(str_split($array[1])) . array_sum(str_split($array[2]));
}
