<?php
clog2ini("bloques.login");
clog1seq(1);
?>
<?php
if(!$hasUser) {
?>
          <div id="area_acceso" class="centered">
            <form target="_self" method="post">
<?php
/*
    if(isset($rediurl)&&!empty($rediurl))
        echo "              <input type=\"hidden\" name=\"rdt\" value=\"".rawurlencode($rediurl)."\">\n";
*/
?>
<?php
    if (isValidBrowser()) { ?>
              <fieldset class="centered">
                <legend>Introduce tu clave de acceso</legend>
                <table>
                  <tr>
                    <td>Usuario:</td>
                    <td><input type="text" name="username" id="username" value="<?= $submitted_username; ?>" class="smalltext"/></td>
                  </tr>
                  <tr>
                    <td>Contrase&ntilde;a:</td>
                    <td><input type="password" name="password" value="" class="smalltext"/></td>
                  </tr>
                </table>
             </fieldset>
             <fieldset class="centered">
               <input type="submit" value="Iniciar Sesi&oacute;n" />
             </fieldset>
<?php
        global $query, $usrObj;
        if (!isset($usrObj)) { require_once "clases/Usuarios.php"; $usrObj = new Usuarios(); }
        echo "<!-- LAST QUERY: ".($query??"-")." -->\n";
        echo "<!-- ERRNO: ERRORMSG - ".DBi::$errno.": ".DBi::$error." -->\n";

        global $_config;
        if (!isset($_config)) {
            echo "<!-- NO CONFIG in PDO -->\n";
        } else {
            $db = $_config["db"]??null;
            if (!($db["enable"]??false)) {
                echo "<!-- DB DISABLED in PDO -->\n";
            } else {
                $dbKey = "conpro"; // "invoice"; //
                $dbHost=$db["host"][$dbKey]??"";
                if (!isset($dbHost[0])) {
                    echo "<!-- NO DB HOST in PDO -->\n";
                } else {
                    $dbBase=$db["base"][$dbKey]??"";
                    $dbDrv=$db["driver"][$dbKey]??"";
                    $dbIns=$db["instance"][$dbKey]??"";
                    $dbUsr=$db["user"][$dbKey]??"";
                    if (isset($dbIns[0])) $dbIns="/$dbIns";
                    $dbDsn="$dbDrv:Server=$dbHost{$dbIns};Database=$dbBase";
                    echo "<!-- DSN='$dbDsn', User='$dbUsr' -->\n";
                }
            }
        }

        //require_once "clases/DBPDO.php";
        //$invoiceConnected=DBPDO::isConnected("invoice");
        //$conproConnected=DBPDO::isConnected("conpro");
        //echo "<!-- INVOICE ".($invoiceConnected?"":"DES")."CONECTADO -->\n";
        //echo "<!-- CONPRO ".($conproConnected?"":"DES")."CONECTADO -->\n";
        //if ($conproConnected) {
            //DBPDO::validaAceptacion("ABCD9912319Z8", "2020-02-02", 1000);
        //    $pdoResult=DBPDO::validaAceptacion("AEAG7406218X9", "2025-11-19", 5973.77);
        //    echo "<!-- Valida Aceptacion (AEAG7406218X9, 2025-11-19, 5973.77) = ".($pdoResult?"TRUE":"FALSE")." -->\n";
        //}
    } else { ?>
             <div class="centered"><br><br><img src="imagenes/navegadorRequerido.png" width="400" height="295" class="centered"></div>
<?php
    } ?>
           </form>
         </div>  <!-- FIN BLOQUE LOGIN -->
<?php
} else {
    $imageWidth = "150";
    //$imageHeight = ["APSA"=>"50.2", "GLAMA"=>"53.59", "JYL"=>"107.64", "CASABLANCA"=>"49.25", "DESA"=>"22.13", "ENVASES"=>"36.77", "LAISA"=>"45.33", "MELO"=>"86.67"];

    if ($user->cambiaClave && !$user->isSystem) {
?>
          <div id="area_usuario3" class="centered">
            <form target="_self" method="post">
              <fieldset class="centered">
                <legend>Introduce tu nueva contraseña.</legend>
                <table>
                  <tr>
                    <td>Usuario:</td>
                    <td><?= $username ?></td>
                  </tr>
                  <tr>
                    <td>Contrase&ntilde;a:</td>
                    <td><input type="password" name="password" value="" class="smalltext"/></td>
                  </tr>
                  <tr>
                    <td>Confirmaci&oacute;n:</td>
                    <td><input type="password" name="password2" value="" class="smalltext"/></td>
                  </tr>
                </table>
              </fieldset>
              <fieldset class="centered">
                <input type="submit" value="Registrar" />
              </fieldset>
            </form>
          </div>

<?php
    } else  if ($habilitado) {
        $claveMensaje="MENSAJE_INICIAL";
        $claveMensajeCompras=$claveMensaje."_COMPRAS";

        $claveMensajeSesion=(($_esComprasB||$_esSistemasX)&&isset($_SESSION[$claveMensajeCompras][0]))?$claveMensajeCompras:(isset($_SESSION[$claveMensaje][0])?$claveMensaje:"");
        $testClaseMensaje=$_SESSION["CLASEMENSAJE"]??"";
        if ($_esProveedor) {
          $testClaseMensaje=" class=\\\"maroon highlight\\\"";
        }
        if (isset($claveMensajeSesion[0])) {
            $mensajeSesion=$_SESSION[$claveMensajeSesion];
?>
<script>
var base_onLoad=window.onload;
window.onload = function (event) {
  overlayMessage("<div id=\"mensaje_inicial\"<?= $testClaseMensaje ?>><b><?= $mensajeSesion ?></b></div>");
  ebyid('overlay').callOnClose=function() { base_onLoad && base_onLoad(); };
}
</script>
<?php
            $resultMessage = "Mensaje Inicial";
            unset($_SESSION[$claveMensaje]);
            unset($_SESSION[$claveMensajeCompras]);
        }
        echo "<!-- ".($_esComprasB?"SI":"NO")." ES COMPRAS -->\n";
?>
<h1 class="txtstrk sticky top">Bienvenid@<br><?= $user->persona ?></h1>
          <div id="area_usuario2" class="centered relative">
<?php   if ($_esProveedor || $_esComprasB) { $art1=$_esProveedor?"tus":"los"; ?>
<p class="boldValue fontLarge">En este sitio podr&aacute;s dar de alta <?=$art1?> <b class='highMsg underDash pointer' title='Comprobante Fiscal Digital por Internet'>C.F.D.I.</b> (facturas, notas de crédito, pagos, etc) y llevar un seguimiento de todo el proceso administrativo.</p>
<br>
<p class="importantValue fontLarge">Tambi&eacute;n podr&aacute;s hacer Consultas de todos <?=$art1?> movimientos y verificar el status de <?=$art1?> comprobantes,
as&iacute; como visualizar y descargar <?=$art1?> Contra Recibos.
</p><br>
<?php   } ?>
<?php   if ($_esSistemasX) { ?>
<p><U class="boldValue fontLarge pointer hoverable" onclick="window.open('/tuts/','sistemas');">Aquí puedes consultar la Guía de Sistemas.</U></p><br>
<?php   } ?>
<?php   if ($_esComprasB || $_esSistemasX) { ?>
<p><A class="boldValue fontLarge alink" target="manual" href="info://Manual Compras" onclick="switchAHref(this,'manual/manualCompras.pdf');">Aquí puedes descargar el "Manual de Compras y Avance" del portal.</A></p><br>
<?php   } ?>
<?php   if ($_esProveedor || $_esComprasB || $_esSistemasX) { ?>
<p><A class="boldValue fontLarge alink" target="manual" onclick="setAHref(this,'manual/manualProveedor.pdf');">Aquí puedes descargar el "Manual de Proveedor" del portal.</A></p><br>
<?php       if (isset($noCCPTable[0])) {
?>
<div class="noccpwarn" autofocus tabindex="-1" onfocus="this.blur();"><B>Recuerde ingresar los complementos de pago faltantes para evitar que se detengan sus pagos:</B><?=$noCCPTable?></div><br>
<?php               
            } else if ($_esDesarrollo) {
                clog2ini("DEV_NOCCP");
                //global $invObj,$query;
                //if (!isset($invObj)) {
                //    require_once "clases/Facturas.php";
                //    $invObj=new Facturas();
                //}
                //$invObj->rows_per_page=0;
                //$dvCCPData=$invObj->getData("f.tipoComprobante=\"i\" and f.metodoDePago=\"PPD\" and f.idReciboPago is null and f.statusn between 32 and 127 and (f.fechaPago is null or f.fechaPago>\"2018-09-01 00:00:00\") and p.status!='inactivo'",0,"COUNT(DISTINCT f.codigoProveedor,f.version,f.ciclo) AS totRows","f inner join proveedores p on f.codigoProveedor=p.codigo");
                //if (isset($dvCCPData[0])) {
                //    $totRows=$dvCCPData[0]["totRows"];
                    echo "<div id=\"noccparea\" class=\"noccplist\"><B>Lista de proveedores con dos o más facturas sin Complemento de Pago<br>Han sido avisados que no se pagarán sus facturas hasta ingresarlos:</B><table class=\"lytfxd width100 pad2c screenBG centered\"><thead><tr class=\"bbtmdblu\"><th class=\"wid35px\">#</th><th class=\"wid45px ellipsisCel invisTxt\">CODIGO</th><th>RAZON SOCIAL</th><th class=\"wid119px invisTxt\">RFC</th><th class=\"wid33px\">VER</th><th class=\"wid35px\">AÑO</th><th class=\"wid50px\">noCCP</th></tr></thead><tbody id=\"noccpbody\"></tbody><tfoot><tr><th colspan=\"7\" class=\"centered\" id=\"noccpfoot\">Obteniendo registros...<img src=\"data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7\" onload='readyService(\"selectores/generico.php\", {module:\"NOCCP\",totRows:-1, rdyFnc:showOnReady, errFnc:showOnError}, showOnReady, showOnError);'></th></tr></tfoot></table><i id=\"msg\"></i><br></div>";
                //} else echo "<div class=\"noccplist\">ISDEV</div>\n";
                clog2end("DEV_NOCCP");
                //echo "<!-- QUERY: $query -->\n";
            } else if (!$_esProveedor) {
                if ($_esPruebas) {
                    echo "<div id=\"noccplist\" class=\"noccplist op80\"><div class=\"inblock rot5anim size480 pad12\"><img class=\"rot10anim size400\" src=\"imagenes/papel.webp\" onload=\"loadNoCCP();\"></div></div><script>function loadNoCCP() { console.log(\"INI FUNCTION loadNoCCP!!\"); readyService(\"selectores/generico.php\", {module:\"NOCCP\",totRows:-1, rdyFnc:showOnReady, errFnc:showOnError}, showOnReady, showOnError); }</script>";
                } else {
                    $groupWhere="";
                    if (!$_esSistemasX) {
                        global $ugObj;
                        if (!isset($ugObj)) {
                            require_once "clases/Usuarios_Grupo.php";
                            $ugObj=new Usuarios_Grupo();
                        }
                        $ugObj->rows_per_page=0;
                        $rfcEmpresas=$ugObj->getGroupRFC($user, ["Compras","Compras Basico","Alta Facturas"], "vista");
                        if (empty($rfcEmpresas)) {
                            if (!$_esSistemas) $groupWhere = " and f.rfcGrupo='NORFC'";
                            else echo "<!-- ES SISTEMAS -->\n";
                        } else {
                            $groupWhere = " and f.rfcGrupo in ('".implode("','",$rfcEmpresas)."')";
                        }
                        echo "<!-- GROUP WHERE = '$groupWhere' -->\n";
                    } else echo "<!-- SISTEMASX -->\n";
                    clog2ini("NOCCPDATABLOCK");
                    global $invObj,$query;
                    if (!isset($invObj)) {
                        require_once "clases/Facturas.php";
                        $invObj=new Facturas();
                    }
                    $invObj->rows_per_page=1000;
                    $invObj->clearOrder();
                    $invObj->addOrder("f.version","desc");
                    $invObj->addOrder("f.ciclo","desc");
                    $invObj->addOrder("f.codigoProveedor");
                    $invObj->addOrder("right(f.ubicacion,3)","desc");
//                    $invObj->addOrder("f.folio","desc");
                    $invObj->addOrder("f.ubicacion");
                    //$invObj->addOrder("d.numParcialidad","desc");
                    $noCCPData=$invObj->getData(
                        // WHERE_str
                        "f.tipoComprobante=\"i\" and f.metodoDePago=\"PPD\" and (f.idReciboPago is null or f.statusReciboPago is null or f.statusReciboPago<1 or f.saldoReciboPago>0) and f.statusn between 32 and 127 and (f.fechaPago is null or f.fechaPago>\"2018-09-01 00:00:00\") and p.status!='inactivo'".$groupWhere, 
                        // NUM_ROWS_PRESET
                        0, 
                        // FIELDNAMES
                        "g.alias, f.codigoProveedor, p.razonSocial, p.rfc, f.version, f.ciclo, f.statusReciboPago, f.saldoReciboPago>0 part, right(f.ubicacion, 3) mes, count(1) n",
                        // EXTRA_FROM
                        "f inner join proveedores p on f.codigoProveedor=p.codigo inner join grupo g on f.rfcGrupo=g.rfc",
                        // GROUP_STR
                        "f.version, f.ciclo, g.alias, f.codigoProveedor, right(f.ubicacion, 3), f.statusReciboPago, f.saldoReciboPago>0,f.ubicacion");
                    clog2end("NOCCPDATABLOCK");
                    echo "<!-- QRY-BLK: $query -->";
                    echo "<!-- TOTNUM: ".$invObj->numrows." -->";
                    if (isset($noCCPData[0])) {
                        echo "<div class=\"noccplist\"><B>Lista de proveedores con dos o más facturas sin Complemento de Pago<br>Han sido avisados que no se pagarán sus facturas hasta ingresarlos:</B><table class=\"lytfxd width100 pad2c screenBG centered\" onclick=\"addPipes(this);copyTextToClipboard(this.textContent);delPipes(this);console.log('COPIADO');seleccionaElemento(this);\"><thead><tr class=\"bbtmdblu\"><th class=\"wid35px\">#</th><th class=\"wid45px ellipsisCel invisTxt\">CODIGO</th><th> P R O V E E D O R </th><th class=\"wid119px invisTxt\">RFC</th><th class=\"wid33px\">VER</th><th class=\"wid35px\">MES</th><th class=\"wid50px\">noCCP</th></tr></thead><tbody>";
                        $num=0;
                        foreach ($noCCPData as $idx => $crow) {
                          if ($crow["n"]==="1") continue;
                          $num++;//=$idx+1;
                          $mes=substr($crow["mes"], 0, 2)."/".substr($crow["ciclo"], -2);
                          echo "<tr class=\"bbtm1d\"><td class=\"wid35px\">$num</td><td class=\"wid45px\">$crow[codigoProveedor]</td><td class=\"ellipsisCel\">$crow[razonSocial]</td><td class=\"wid119px\">$crow[rfc]</td><td class=\"wid33px\">$crow[version]</td><td class=\"wid35px\">$mes</td><td class=\"wid50px\">$crow[n]</td></tr>";
                        }
                        echo "</tbody></table><br></div>";
                    }
                }
            }
        } ?>
              
              <div id="area_usuario2_desc">
                <div>
<!-- APSA -->     <a class="noApply" href="http://www.apsa.com.mx/" width="<?= $imageWidth ?>" style="display:inline-block;"><img src="imagenes/logos/apsa.png" name="APSA" width="<?= $imageWidth ?>" title="ACABADOS DE PAPELES SATINADOS Y ABSORBENTES, S.A. DE C.V."></a>
<!-- CASA - - >     <img src="imagenes/logos/casablanca.png" width="<?= $imageWidth ?>" title="LAMINADOS CASABLANCA, S.A. DE C.V." -->
<!--COREPACK-->   <img src="imagenes/logos/corepack.png" width="<?= $imageWidth ?>" title="COREPACK, S.A. DE C.V.">
<!-- SKARTON -->  <img src="imagenes/logos/skarton.png" width="<?= $imageWidth ?>" title="SKARTON, S.A. DE C.V.">
<!-- MARLOT -->   <img src="imagenes/logos/marlot.png" width="<?= $imageWidth ?>" title="TRANSPORTES MARLOT, S.A. DE C.V.">
                </div>
                <div>
<!-- GLAMA -->    <a class="noApply" href="http://productosglama.net/" width="<?= $imageWidth ?>" style="display:inline-block;"><img src="imagenes/logos/glama.png" width="<?= $imageWidth ?>" title="PRODUCTOS GLAMA, S.A. DE C.V."></a>
<!-- MELO -->     <img src="imagenes/logos/melo.png" width="<?= $imageWidth ?>" title="DISTRIBUCIONES INDUSTRIALES MELO, S.A. DE C.V.">
<!-- RGA -->      <img src="imagenes/logos/rga.png" width="<?= $imageWidth ?>" title="RGA ARQUITECTOS, S.A. DE C.V.">
<!-- ENVASES - - >  <img src="imagenes/logos/envases.png" width="<?= $imageWidth ?>" title="ENVASES EFICIENTES, S.A. DE C.V." -->
<!-- morysan -->  <img src="imagenes/logos/morysan.png" width="<?= $imageWidth ?>" title="MORYSAN COMERCIAL, S.A. DE C.V.">
<!-- foamymex -->  <img src="imagenes/logos/foamymex.png" width="<?= $imageWidth ?>" title="FOAMYMEX, S.A. DE C.V.">
                </div>
                <div>
<!-- LAISA -->    <img src="imagenes/logos/laisa.png" width="<?= $imageWidth ?>" title="LAMINAS ACANALADAS INFINITA, S.A. DE C.V.">
<!-- JYL -->      <img src="imagenes/logos/jyl.png" width="<?= $imageWidth ?>" title="PAPELES Y MAQUILAS NACIONALES JYL, S.A. DE C.V.">
<!-- BIDASOA -->  <img src="imagenes/logos/bidasoa.png" width="<?= $imageWidth ?>" title="MANUFACTURERA DE PAPEL BIDASOA, S.A. DE C.V.">
                </div>
              </div>
          </div>  <!-- FIN BLOQUE USUARIO -->
<?php
    }
}
clog1seq(-1);
clog2end("bloques.login");
