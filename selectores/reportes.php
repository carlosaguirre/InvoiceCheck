<?php
require_once dirname(__DIR__)."/bootstrap.php";
//    setMetaLogLevel('meta_logl_eall');
//    error_reporting(E_ALL);
//    ini_set('display_errors', 'On');
echo "<!-- SELECTOR REPORTES -->";
if (!hasUser()) {
    echo "<img src=\"data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7\" onload=\"location.reload(true);\">";
    die();
}

require_once "clases/DBi.php";
//    setMetaLogLevel('meta_logl_checks');

$export2Excel = isset($_REQUEST["command"]) && $_REQUEST["command"]==="Exportar";
$showComments = !$export2Excel && true; // cambiar a false si se desea deshabilitar por completo los comentarios
$consultaRepo = consultaValida("Reportes")||validaPerfil("Administrador")||validaPerfil("Sistemas");
if (!$consultaRepo) {
    require_once "configuracion/finalizacion.php";
    setcookie("menu_accion", "", time() - 3600);
    setcookie("menu_accion", "", time() - 3600, "/invoice");
    echo "<img src=\"data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7\" onload=\"location.reload(true);\">";
    die();
}
$esCompras = validaPerfil("Compras");
$esProveedor = validaPerfil("Proveedor");
if ($esCompras) {
    if (!isset($ugObj)) {
        require_once "clases/Usuarios_grupo.php";
        $ugObj=new Usuarios_Grupo();
    }
    $ugObj->rows_per_page=0;
    $rfcGpoList=$ugObj->getGroupRFC(getUser(), "Compras", "vista");
    if (!empty($rfcGpoList)) {
        if (isset($rfcGpoList[1]))
            $gpoWhere="and f.rfcGrupo in ('".implode("','", $rfcGpoList)."') ";
        else
            $gpoWhere="and f.rfcGrupo='".$rfcGpoList[0]."' ";
    }
} else if ($esProveedor) {
    $prvWhere = "and f.codigoProveedor='".getUser()->nombre."' ";
}

if ($export2Excel) {
    header('Content-type: application/excel');
    if (isset($_REQUEST["filename"])) {
        $filenameRaw = $_REQUEST["filename"];
        $title = str_replace("|"," ",$filenameRaw);
        $header = str_replace("|","<BR>",$filenameRaw);
        $title2 = ucwords(str_replace("_"," ",$title));
        $filename = str_replace(" ","",$title2).".xls";
    } else $filename = "reporte.xls";
    header('Content-Disposition: attachment; filename='.$filename);
    
    echo "<html xmlns:x=\"urn:schemas-microsoft-com:office:excel\">\n";
    echo "<head>\n";
    echo "    <!--[if gte mso 9]>\n";
    echo "    <xml>\n";
    echo "        <x:ExcelWorkbook>\n";
    echo "            <x:ExcelWorksheets>\n";
    echo "                <x:ExcelWorksheet>\n";
    echo "                    <x:Name>Sheet 1</x:Name>\n";
    echo "                    <x:WorksheetOptions>\n";
    echo "                        <x:Print>\n";
    echo "                            <x:ValidPrinterinfo/>\n";
    echo "                        </x:Print>\n";
    echo "                    </x:WorksheetOptions>\n";
    echo "                </x:ExcelWorksheet>\n";
    echo "            </x:ExcelWorksheets>\n";
    echo "        </x:ExcelWorkbook>\n";
    echo "    </xml>\n";
    echo "    <![endif]-->\n";
    echo "  <title>$title</title>\n";
    echo "</head>\n";
    echo "<body>\n";
    echo "<h1 style=\"text-align:center;\">$header</h1>\n";
}
if ($showComments) {
    if (isset($_REQUEST)) {
        clog2("POST:\n".http_build_query($_REQUEST));
    } else {
        clog2("NO POST");
    }
}

// empresa : (resumen, desglose, rfc-de-empresa)
if (isset($_REQUEST["empresa"])) $empresa=$_REQUEST["empresa"]; else $empresa="resumen";

// fecha : dd/mm/aaaa => aaaa-mm-dd
if (isset($_REQUEST["fechaIni"])) $fechaIni=date('Y-m-d',strtotime(str_replace("/","-",$_REQUEST["fechaIni"]))); else $fechaIni="";
if (isset($_REQUEST["fechaFin"])) $fechaFin=date('Y-m-d',strtotime(str_replace("/","-",$_REQUEST["fechaFin"]))); else $fechaFin="";

// importe : total, subtotal
if (isset($_REQUEST["importe"])) $tipoImporte=$_REQUEST["importe"]; else $tipoImporte="total";

// moneda : todas, pesos, dolares
if (isset($_REQUEST["moneda"])) $tipoMoneda=$_REQUEST["moneda"]; else $tipoMoneda="todas";

$esResumen = ($empresa==="resumen");               // Empresa
$esDesglose = ($empresa==="desglose");             // Empresa | Proveedor
$todasLasEmpresas = ($esResumen || $esDesglose);
$conFecha = ($fechaIni!==$fechaFin) && false;
$todasLasMonedas = ($tipoMoneda==="todas");
$esPesos = ($tipoMoneda==="pesos");
$esDolares = ($tipoMoneda==="dolares");
$esTotal = ($tipoImporte==="total");
$esSubtotal = ($tipoImporte==="subtotal");

$query = "SELECT f.codigoProveedor, p.razonSocial proveedor, f.folio, right(f.uuid,10) uuid";
if (!$esResumen) {
    $query.=", CASE WHEN cr.aliasGrupo IS NOT NULL THEN concat(cr.aliasGrupo,'-',cr.folio) ELSE ' SIN CR' END crecibo";
    if ($conFecha) $query .= ", f.fechafactura"; //, f.fechacaptura";
}
if ($todasLasEmpresas) $query .= ", g.alias aliasEmpresa, g.razonSocial empresa";
if ($todasLasMonedas) $query .= ", if (f.tipoCambio=1 or f.tipoCambio=0, 1, 0) esPesos";
if ($esTotal) $query .= ", f.total";
else if ($esSubtotal) $query .= ", f.subtotal";
else $query .= ", f.total, f.subtotal";

$query .= " from invoice.facturas f";
if ($todasLasEmpresas) $query .= " inner join invoice.grupo g on f.rfcGrupo=g.rfc";
$query .= " inner join invoice.proveedores p on f.codigoProveedor=p.codigo";
if(!$esResumen) $query.= " left join invoice.contrafacturas cf on f.id=cf.idFactura left join invoice.contrarrecibos cr on cf.idContrarrecibo=cr.id";
$query .= " where f.status not in ('Temporal','Pendiente','Rechazado') ";
if (!$todasLasEmpresas) $query .= "and f.rfcGrupo='$empresa' ";
if (isset($gpoWhere)) $query .= $gpoWhere;
if (isset($prvWhere)) $query .= $prvWhere;
if ($esPesos) $query .= "and (f.tipoCambio=1 or f.tipoCambio=0) ";
else if ($esDolares) $query .= "and f.tipoCambio<>1 and f.tipoCambio<>0 ";
$query .= "and date(f.fechaFactura) between '$fechaIni 00:00:00' and '$fechaFin 23:59:59'";
if (isset($_REQUEST["tipoComprobante"])) $query.=" and f.tipoComprobante='$_REQUEST[tipoComprobante]'";
else $query.=" and f.tipoComprobante='i'";
$query .= " order by";
if ($todasLasEmpresas) $query .= " g.alias,";
$query .= " f.codigoProveedor";
if (!$esResumen) $query .= ", crecibo";
$query .= ", f.folio";

ini_set('memory_limit','512MB');
$result = DBi::query($query) or trigger_error("SQL", E_USER_ERROR);

if ($showComments) clog2($query);
echo "<TABLE class=\"breakAvoidI\" style=\"margin: 0 auto;font-size: 12px;\">";
echo /* put/remove asterisk --> */"<TBODY>"; /*/"<THEAD class=\"moveDownOnPrint\">"; /* */
echo "<TR class=\"darkerBG repHeader\">";
if (!$esResumen) {
    if ($conFecha) echo "<TH style=\"padding:3px;border-bottom:2px solid #ddd;vertical-align:top;\">Fecha</TH>";
    echo "<TH style=\"padding:3px;border-bottom:2px solid #ddd;vertical-align:top;\">Recibo</TH>";
}
if ($todasLasEmpresas) echo "<TH style=\"padding:3px;border-bottom:2px solid #ddd;vertical-align:top;\">Alias</TH><TH style=\"padding:3px;border-bottom:2px solid #ddd;vertical-align:top;text-align:center;max-width:200px;overflow:hidden;white-space:nowrap;\">Empresa</TH>";
if (!$esResumen) {
    if (!$todasLasEmpresas) echo "<TH style=\"padding:3px;border-bottom:2px solid #ddd;vertical-align:top;text-align:center;\">C&oacute;digo</TH>";
    echo "<TH style=\"padding:3px;border-bottom:2px solid #ddd;vertical-align:top;text-align:center;max-width:200px;overflow:hidden;white-space:nowrap;\">Proveedor</TH><TH style=\"padding:3px;border-bottom:2px solid #ddd;vertical-align:top;text-align:center;\">Folio</TH>";
}
if ($todasLasMonedas) echo "<TH style=\"text-align:right;padding:3px 20px 3px 3px;border-bottom:2px solid #ddd;vertical-align:top;\">MXN</TH><TH style=\"text-align:right;padding:3px 20px 3px 3px;border-bottom:2px solid #ddd;vertical-align:top;\">DLL</TH>";
else if ($esPesos) echo "<TH style=\"text-align:right;padding:3px 20px 3px 3px;border-bottom:2px solid #ddd;vertical-align:top;\">MXN</TH>";
else if ($esDolares) echo "<TH style=\"text-align:right;padding:3px 20px 3px 3px;border-bottom:2px solid #ddd;vertical-align:top;\">DLL</TH>";
echo "</TR>"; 
// <-- put remove asterisk in between */echo "</THEAD><TBODY>"; /* */

$sumMxn=0;
$sumDll=0;
$partMxn=0;
$partDll=0;
$currEmp="";
$currAli="";
if ($showComments) {
    if ($esResumen) clog2("esResumen");
    if ($esDesglose) clog2("esDesglose");
    if ($esPesos) clog2("esPesos");
    if ($esDolares) clog2("esDolares");
    if ($todasLasMonedas) clog2("es Pesos y Dolares");
    if ($esTotal) clog2("esTotal");
    if ($esSubtotal) clog2("esSubtotal");
}

setlocale(LC_MONETARY, 'es_MX');
$ultimoAlias=null;
$ultimaEmpresa=null;
$ultimoProveedor=null;
$ultimaRazonSocial=null;
$sumaPesosPorProveedor=0;
$sumaDLLPorProveedor=0;
$sumaPesosPorEmpresa=0;
$sumaDLLPorEmpresa=0;
while ($row = $result->fetch_assoc()) {
    //if ($showComments) clog2("row : ".json_encode($row));
    $tipoPesos = $esPesos || (isset($row["esPesos"]) && $row["esPesos"]==1);
    $importe = ($esSubtotal?$row["subtotal"]:$row["total"]);
    if ($esResumen) {
        if ($currAli!==$row["aliasEmpresa"]) {
            if (!empty($currAli)) {
                echo "<TR class=\"repResumen repEmpresa\">";
                echo "<TD style=\"width:1%;white-space:nowrap;padding:3px;border-bottom:1px solid #ddd;vertical-align:top;\">$currAli</TD><TD class=\"nooverfix\" style=\"padding:3px;border-bottom:1px solid #ddd;vertical-align:top;text-align:left;max-width:200px;\"><DIV class=\"nooverfix\" style=\"text-align:left;overflow:hidden;white-space:nowrap;text-overflow:ellipsis;\">$currEmp</DIV></TD>";
                $currMxn = number_format(+$partMxn,2);
                $currDll = number_format(+$partDll,2);
                if ($todasLasMonedas) echo "<TD style=\"padding:3px;border-bottom:1px solid #ddd;vertical-align:top;text-align:right;white-space:nowrap;\">$ $currMxn</TD><TD style=\"padding:3px;border-bottom:1px solid #ddd;vertical-align:top;text-align:right;white-space:nowrap;\">$ $currDll</TD>";
                else if ($esPesos) echo "<TD style=\"padding:3px;border-bottom:1px solid #ddd;vertical-align:top;text-align:right;white-space:nowrap;\">$ $currMxn</TD>";
                else if ($esDolares) echo "<TD style=\"padding:3px;border-bottom:1px solid #ddd;vertical-align:top;text-align:right;white-space:nowrap;\">$ $currDll</TD>";
                echo "</TR>";
            }
            $partMxn=0;
            $partDll=0;
            $currAli=$row["aliasEmpresa"];
            $currEmp=$row["empresa"];
        }
        if ($tipoPesos) $partMxn += +$importe;
        else $partDll += +$importe;
        if ($showComments) clog2("empresa=$row[aliasEmpresa], proveedor=$row[codigoProveedor], importe=$importe".($tipoPesos?"MXN":"DLL")); // empr=$currEmp, 
    } else {
        if(isset($ultimoProveedor) && (($esDesglose&&$ultimoAlias!==$row["aliasEmpresa"]) || $ultimoProveedor!==$row["codigoProveedor"])) {
            echo "<TR class=\"repSumaPorProveedor\">";
            if ($conFecha) echo "<TH style=\"border-top:3px solid #ddc;border-bottom:3px solid #ccd;\">&nbsp;</TH>";
            echo "<TH style=\"padding:3px;border-top:3px solid #ddc;border-bottom:3px solid #ccd;\">&nbsp;</TH>";
            if ($esDesglose) {
                echo "<TH style=\"width:1%;white-space:nowrap;padding:3px;border-top:3px solid #ddc;border-bottom:3px solid #ccd;vertical-align:top;\">$ultimoAlias</TH><TH class=\"nooverfix\" style=\"padding:3px;border-top:3px solid #ddc;border-bottom:3px solid #ccd;vertical-align:top;text-align:left;max-width:200px;\"><DIV class=\"nooverfix\" style=\"text-align:left;overflow:hidden;white-space:nowrap;text-overflow:ellipsis;\">$ultimaEmpresa</DIV></TH>";
                echo "<TH title=\"$ultimaRazonSocial\" style=\"text-align:center;padding:3px;border-top:3px solid #ddc;border-bottom:3px solid #ccd;vertical-align:top;\">$ultimoProveedor</TH>";
            } else echo "<TH style=\"text-align:center;padding:3px;border-top:3px solid #ddc;border-bottom:3px solid #ccd;vertical-align:top;\">$ultimoProveedor</TH><TH class=\"nooverfix\" style=\"padding:3px;border-top:3px solid #ddc;border-bottom:3px solid #ccd;vertical-align:top;text-align:left;max-width:200px;\"><DIV class=\"nooverfix\" style=\"text-align:left;overflow:hidden;white-space:nowrap;text-overflow:ellipsis;\">$ultimaRazonSocial</DIV></TH>";
            echo "<TH style=\"border-top:3px solid #ddc;border-bottom:3px solid #ccd;\">&nbsp;</TH>";
            if ($todasLasMonedas) echo "<TH style=\"padding:3px;border-top:3px solid #ddc;border-bottom:3px solid #ccd;vertical-align:top;text-align:right;white-space:nowrap;\">$ ".number_format($sumaPesosPorProveedor,2)."</TH><TH style=\"padding:3px;border-top:3px solid #ddc;border-bottom:3px solid #ccd;vertical-align:top;text-align:right;white-space:nowrap;\">$ ".number_format($sumaDLLPorProveedor,2)."</TH>";
            else if ($esPesos) echo "<TH style=\"padding:3px;border-top:3px solid #ddc;border-bottom:3px solid #ccd;vertical-align:top;text-align:right;white-space:nowrap;\">$ ".number_format($sumaPesosPorProveedor,2)."</TH>";
            else if ($esDolares) echo "<TH style=\"padding:3px;border-top:3px solid #ddc;border-bottom:3px solid #ccd;vertical-align:top;text-align:right;white-space:nowrap;\">$ ".number_format($sumaDLLPorProveedor,2)."</TH>";
            echo "</TR>";
            //if($esDesglose) { $ultimoAlias=$row["aliasEmpresa"]; $ultimaEmpresa=$row["empresa"]; }
            $ultimoProveedor=$row["codigoProveedor"]; $ultimaRazonSocial=$row["proveedor"];
            $sumaPesosPorProveedor=0;
            $sumaDLLPorProveedor=0;
        } else if (!isset($ultimoProveedor)) {
            $ultimoProveedor=$row["codigoProveedor"]; $ultimaRazonSocial=$row["proveedor"];
        }
        if ($esDesglose && isset($ultimaEmpresa) && $ultimoAlias!==$row["aliasEmpresa"]) {
            echo "<TR class=\"repDesglose repSumaPorEmpresa\">";
            if ($conFecha) echo "<TH style=\"border-top:3px solid #ddc;border-bottom:3px solid #ccd;\">&nbsp;</TH>";
            echo "<TH style=\"padding:3px;border-top:3px solid #ddc;border-bottom:3px solid #ccd;\">&nbsp;</TH>";
            echo "<TH style=\"width:1%;white-space:nowrap;padding:3px;border-top:3px solid #ddc;border-bottom:3px solid #ccd;vertical-align:top;\">$ultimoAlias</TH><TH class=\"nooverfix\" style=\"padding:3px;border-top:3px solid #ddc;border-bottom:3px solid #ccd;vertical-align:top;text-align:left;max-width:200px;\"><DIV class=\"nooverfix\" style=\"text-align:left;overflow:hidden;white-space:nowrap;text-overflow:ellipsis;\">$ultimaEmpresa</DIV></TH>";
                echo "<TH title=\"$ultimaRazonSocial\" style=\"text-align:center;padding:3px;border-top:3px solid #ddc;border-bottom:3px solid #ccd;vertical-align:top;\">&nbsp;</TH>";
            echo "<TH style=\"border-top:3px solid #ddc;border-bottom:3px solid #ccd;\">&nbsp;</TH>";
            if ($todasLasMonedas) echo "<TH style=\"padding:3px;border-top:3px solid #ddc;border-bottom:3px solid #ccd;vertical-align:top;text-align:right;white-space:nowrap;\">$ ".number_format($sumaPesosPorEmpresa,2)."</TH><TH style=\"padding:3px;border-top:3px solid #ddc;border-bottom:3px solid #ccd;vertical-align:top;text-align:right;white-space:nowrap;\">$ ".number_format($sumaDLLPorEmpresa,2)."</TH>";
            else if ($esPesos) echo "<TH style=\"padding:3px;border-top:3px solid #ddc;border-bottom:3px solid #ccd;vertical-align:top;text-align:right;white-space:nowrap;\">$ ".number_format($sumaPesosPorEmpresa,2)."</TH>";
            else if ($esDolares) echo "<TH style=\"padding:3px;border-top:3px solid #ddc;border-bottom:3px solid #ccd;vertical-align:top;text-align:right;white-space:nowrap;\">$ ".number_format($sumaDLLPorEmpresa,2)."</TH>";
            echo "</TR>";
            $sumaPesosPorEmpresa=0;
            $sumaDLLPorEmpresa=0;
            $ultimoAlias=$row["aliasEmpresa"]; $ultimaEmpresa=$row["empresa"];
        } else if ($esDesglose && !isset($ultimaEmpresa)) {
            $ultimoAlias=$row["aliasEmpresa"]; $ultimaEmpresa=$row["empresa"];
        }
        echo "<TR class=\"repLinea\">";
        if ($conFecha) echo "<TD style=\"width:1%;white-space:nowrap;padding:3px;border-bottom:1px solid #ddd;vertical-align:top;\">$row[fechafactura]</TD>";
        $sinCR=($row["crecibo"]===" SIN CR");
        echo "<TD style=\"width:1%;white-space:nowrap;padding:3px;border-bottom:1px solid #ddd;vertical-align:top;".($sinCR?"color:darkred;font-weight: bold;":"")."\">$row[crecibo]</TD>";
        if ($esDesglose) {
            echo "<TD style=\"width:1%;white-space:nowrap;padding:3px;border-bottom:1px solid #ddd;vertical-align:top;\">$row[aliasEmpresa]</TD><TD class=\"nooverfix\" style=\"padding:3px;border-bottom:1px solid #ddd;vertical-align:top;text-align:left;max-width:200px;\"><DIV class=\"nooverfix\" style=\"text-align:left;overflow:hidden;white-space:nowrap;text-overflow:ellipsis;\">$row[empresa]</DIV></TD>";
            echo "<TD title=\"$row[proveedor]\" style=\"text-align:center;padding:3px;border-bottom:1px solid #ddd;vertical-align:top;\">$row[codigoProveedor]</TD>";
        } else echo "<TD style=\"text-align:center;padding:3px;border-bottom:1px solid #ddd;vertical-align:top;\">$row[codigoProveedor]</TD><TD class=\"nooverfix\" style=\"padding:3px;border-bottom:1px solid #ddd;vertical-align:top;text-align:left;max-width:200px;\"><DIV class=\"nooverfix\" style=\"text-align:left;overflow:hidden;white-space:nowrap;text-overflow:ellipsis;\">$row[proveedor]</DIV></TD>";
        $folio = $row["folio"];
        if (empty($folio) && isset($row["uuid"])) $folio = $row["uuid"];
        echo "<TD style=\"width:1%;white-space:nowrap;padding:3px;border-bottom:1px solid #ddd;vertical-align:top;text-align:center;\">$folio</TD>";
        $currMxn = number_format($tipoPesos?+$importe:0,2);
        $currDll = number_format($tipoPesos?0:+$importe,2);
        if ($todasLasMonedas) echo "<TD style=\"padding:3px;border-bottom:1px solid #ddd;vertical-align:top;text-align:right;white-space:nowrap;\">$ $currMxn</TD><TD style=\"padding:3px;border-bottom:1px solid #ddd;vertical-align:top;text-align:right;white-space:nowrap;\">$ $currDll</TD>";
        else if ($esPesos) echo "<TD style=\"padding:3px;border-bottom:1px solid #ddd;vertical-align:top;text-align:right;white-space:nowrap;\">$ $currMxn</TD>";
        else if ($esDolares) echo "<TD style=\"padding:3px;border-bottom:1px solid #ddd;vertical-align:top;text-align:right;white-space:nowrap;\">$ $currDll</TD>";
        echo "</TR>";
        if ($tipoPesos) {
            $sumaPesosPorProveedor += +$importe;
            $sumaPesosPorEmpresa += +$importe;
        } else {
            $sumaDLLPorProveedor += +$importe;
            $sumaDLLPorEmpresa += +$importe;
        }
    }
    if ($tipoPesos) $sumMxn += +$importe;
    else $sumDll += +$importe;
}
if (!$esResumen && isset($ultimoProveedor)) {
    echo "<TR class=\"repUltimoProveedor\">";
    if ($conFecha) echo "<TH style=\"border-top:3px solid #ddc;border-bottom:3px solid #ccd;\">&nbsp;</TH>";
    echo "<TH style=\"border-top:3px solid #ddc;border-bottom:3px solid #ccd;\">&nbsp;</TH>";
    if ($esDesglose) {
        echo "<TH style=\"width:1%;white-space:nowrap;padding:3px;border-top:3px solid #ddc;border-bottom:3px solid #ccd;vertical-align:top;\">$ultimoAlias</TH><TH class=\"nooverfix\" style=\"padding:3px;border-top:3px solid #ddc;border-bottom:3px solid #ccd;vertical-align:top;text-align:left;max-width:200px;\"><DIV class=\"nooverfix\" style=\"text-align:left;overflow:hidden;white-space:nowrap;text-overflow:ellipsis;\">$ultimaEmpresa</DIV></TH>";
        echo "<TH title=\"$ultimaRazonSocial\" style=\"text-align:center;padding:3px;border-top:3px solid #ddc;border-bottom:3px solid #ccd;vertical-align:top;\">$ultimoProveedor</TH>";
    } else echo "<TH style=\"text-align:center;padding:3px;border-top:3px solid #ddc;border-bottom:3px solid #ccd;vertical-align:top;\">$ultimoProveedor</TH><TH class=\"nooverfix\" style=\"padding:3px;border-top:3px solid #ddc;border-bottom:3px solid #ccd;vertical-align:top;text-align:left;max-width:200px;\"><DIV class=\"nooverfix\" style=\"text-align:left;overflow:hidden;white-space:nowrap;text-overflow:ellipsis;\">$ultimaRazonSocial</DIV></TH>";
    echo "<TH style=\"border-top:3px solid #ddc;border-bottom:3px solid #ccd;\">&nbsp;</TH>";
    if ($todasLasMonedas) echo "<TH style=\"padding:3px;border-top:3px solid #ddc;border-bottom:3px solid #ccd;vertical-align:top;text-align:right;white-space:nowrap;\">$ ".number_format($sumaPesosPorProveedor,2)."</TH><TH style=\"padding:3px;border-top:3px solid #ddc;border-bottom:3px solid #ccd;vertical-align:top;text-align:right;white-space:nowrap;\">$ ".number_format($sumaDLLPorProveedor,2)."</TH>";
    else if ($esPesos) echo "<TH style=\"padding:3px;border-top:3px solid #ddc;border-bottom:3px solid #ccd;vertical-align:top;text-align:right;white-space:nowrap;\">$ ".number_format($sumaPesosPorProveedor,2)."</TH>";
    else if ($esDolares) echo "<TH style=\"padding:3px;border-top:3px solid #ddc;border-bottom:3px solid #ccd;vertical-align:top;text-align:right;white-space:nowrap;\">$ ".number_format($sumaDLLPorProveedor,2)."</TH>";
    echo "</TR>";
}
if ($esDesglose && isset($ultimaEmpresa)) {
    echo "<TR class=\"repDesglose repUltimaEmpresa\">";
    if ($conFecha) echo "<TH style=\"border-top:3px solid #ddc;border-bottom:3px solid #ccd;\">&nbsp;</TH>";
    echo "<TH style=\"padding:3px;border-top:3px solid #ddc;border-bottom:3px solid #ccd;\">&nbsp;</TH>";
    echo "<TH style=\"width:1%;white-space:nowrap;padding:3px;border-top:3px solid #ddc;border-bottom:3px solid #ccd;vertical-align:top;\">$ultimoAlias</TH><TH class=\"nooverfix\" style=\"padding:3px;border-top:3px solid #ddc;border-bottom:3px solid #ccd;vertical-align:top;text-align:left;max-width:200px;\"><DIV class=\"nooverfix\" style=\"text-align:left;overflow:hidden;white-space:nowrap;text-overflow:ellipsis;\">$ultimaEmpresa</DIV></TH>";
    echo "<TH title=\"$ultimaRazonSocial\" style=\"text-align:center;padding:3px;border-top:3px solid #ddc;border-bottom:3px solid #ccd;vertical-align:top;\">&nbsp;</TH>";
    echo "<TH style=\"border-top:3px solid #ddc;border-bottom:3px solid #ccd;\">&nbsp;</TH>";
    if ($todasLasMonedas) echo "<TH style=\"padding:3px;border-top:3px solid #ddc;border-bottom:3px solid #ccd;vertical-align:top;text-align:right;white-space:nowrap;\">$ ".number_format($sumaPesosPorEmpresa,2)."</TH><TH style=\"padding:3px;border-top:3px solid #ddc;border-bottom:3px solid #ccd;vertical-align:top;text-align:right;white-space:nowrap;\">$ ".number_format($sumaDLLPorEmpresa,2)."</TH>";
    else if ($esPesos) echo "<TH style=\"padding:3px;border-top:3px solid #ddc;border-bottom:3px solid #ccd;vertical-align:top;text-align:right;white-space:nowrap;\">$ ".number_format($sumaPesosPorEmpresa,2)."</TH>";
    else if ($esDolares) echo "<TH style=\"padding:3px;border-top:3px solid #ddc;border-bottom:3px solid #ccd;vertical-align:top;text-align:right;white-space:nowrap;\">$ ".number_format($sumaDLLPorEmpresa,2)."</TH>";
    echo "</TR>";
}
if ($esResumen && !empty($currAli)) {
    echo "<TR class=\"repResumen repUltimaEmpresa\">";
    echo "<TD style=\"width:1%;white-space:nowrap;padding:3px;border-bottom:1px solid #ddd;vertical-align:top;\">$currAli</TD><TD class=\"nooverfix\" style=\"padding:3px;border-bottom:1px solid #ddd;vertical-align:top;text-align:left;max-width:200px;\"><DIV class=\"nooverfix\" style=\"text-align:left;overflow:hidden;white-space:nowrap;text-overflow:ellipsis;\">$currEmp</DIV></TD>";
    $currMxn = number_format(+$partMxn,2);
    $currDll = number_format(+$partDll,2);
    if ($todasLasMonedas) echo "<TD style=\"padding:3px;border-bottom:1px solid #ddd;vertical-align:top;text-align:right;white-space:nowrap;\">$ $currMxn</TD><TD style=\"padding:3px;border-bottom:1px solid #ddd;vertical-align:top;text-align:right;white-space:nowrap;\">$ $currDll</TD>";
    else if ($esPesos) echo "<TD style=\"padding:3px;border-bottom:1px solid #ddd;vertical-align:top;text-align:right;white-space:nowrap;\">$ $currMxn</TD>";
    else if ($esDolares) echo "<TD style=\"padding:3px;border-bottom:1px solid #ddd;vertical-align:top;text-align:right;white-space:nowrap;\">$ $currDll</TD>";
    echo "</TR>";
}
//echo "</TBODY><TFOOT>";
if ($esResumen) {
    echo "<TR class=\"repResumen repTotal\">";
    echo "<TH colspan=\"2\" class=\"noApply\" style=\"text-align:right;padding:3px;vertical-align:top;\">TOTAL: </TH>";
    $currMxn = number_format(+$sumMxn,2);
    $currDll = number_format(+$sumDll,2);
    if ($todasLasMonedas) echo "<TH class=\"noApply\" style=\"white-space:nowrap;text-align:right;padding:3px;vertical-align:top;\">$ $currMxn</TH><TH class=\"noApply\" style=\"white-space:nowrap;text-align:right;padding:3px;vertical-align:top;\">$ $currDll</TH>";
    else if ($esPesos) echo "<TH class=\"noApply\" style=\"white-space:nowrap;text-align:right;padding:3px;vertical-align:top;\">$ $currMxn</TH>";
    else if ($esDolares) echo "<TH class=\"noApply\" style=\"white-space:nowrap;text-align:right;padding:3px;vertical-align:top;\">$ $currDll</TH>";
    echo "</TR>";
} else {
    echo "<TR class=\"repTotal\">";
    $colspan = 4;
    if ($todasLasEmpresas) $colspan++;
    if ($conFecha) $colspan++;
    echo "<TH class=\"noApply\" colspan=\"$colspan\" style=\"text-align: right;padding:3px;vertical-align:top;\">Total: </TH>";
    $currMxn = number_format(+$sumMxn,2);
    $currDll = number_format(+$sumDll,2);
    if ($todasLasMonedas) echo "<TH class=\"noApply\" style=\"white-space:nowrap;text-align:right;padding:3px;vertical-align:top;\">$ $currMxn</TH><TH class=\"noApply\" style=\"white-space:nowrap;text-align:right;padding:3px;vertical-align:top;\">$ $currDll</TH>";
    else if ($esPesos) echo "<TH class=\"noApply\" style=\"white-space:nowrap;text-align:right;padding:3px;vertical-align:top;\">$ $currMxn</TH>";
    else if ($esDolares) echo "<TH class=\"noApply\" style=\"white-space:nowrap;text-align:right;padding:3px;vertical-align:top;\">$ $currDll</TH>";
    echo "</TR>";
}

if ($result) $result->close();
echo "</TBODY>"; //"</TFOOT>";
echo "</TABLE>";
if ($export2Excel) {
    echo "</body></html>";
}
require_once "configuracion/finalizacion.php";
