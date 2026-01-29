<?php
clog2ini("templates.registro");
clog1seq(1);
//    clog2("CLEAR BROWSER LOG.".getBrowser("clear"));
//    $useragent = getBrowser("ua");
$browser = getBrowser();
//    $version = getBrowser("ver");
$esChrome = $browser=="Chrome";
$esFirefox = $browser=="Firefox";
$esMSIE = $browser=="IE";
//    clog2("UserAgent: $useragent");
//    clog2("Browser: $browser. Version: $version");
//    clog2("Browser Log: ".getBrowser("debug"));
$refreshCode = "";
$statusElement="<input type=\"text\" name=\"frgn_status\" id=\"frgn_status\" readonly value=\"$provStatusVal\" class=\"uppercase\" size=\"12\">";
$flagList="<div id=\"flagBlock\" class=\"hidden\"><img src=\"imagenes/icons/".($conCodgEnDesc?"chkd24":"deleteIcon12").".png\" id=\"conCEDImg\" width=\"12\" height=\"12\" class=\"top\"><input type=\"hidden\" name=\"conCodgEnDesc\" id=\"conCodgEnDesc\"".($conCodgEnDesc?" checked=\"true\" value=\"1\"":" checked=\"false\" value=\"0\"")." class=\"top\" onclick=\"flagCheck(event);\"><span id=\"conCEDCap\" class=\"top\">".($conCodgEnDesc?"T":"No t")."iene código en descripción</span><br><img src=\"imagenes/icons/".($esServicio?"chkd24":"deleteIcon12").".png\" id=\"esSrvImg\" width=\"12\" height=\"12\" class=\"top\"><input type=\"hidden\" name=\"esServicio\" id=\"esServicio\"".($esServicio?" value=\"1\"":" value=\"0\"")." class=\"top\" onclick=\"flagCheck(event);\"><span id=\"esSrvCap\" class=\"top\">".($esServicio?"E":"No e")."s servicio</span><br><img src=\"imagenes/icons/".($reqObjImp?"chkd24":"deleteIcon12").".png\" id=\"objImpImg\" width=\"12\" height=\"12\" class=\"top\"><input type=\"hidden\" name=\"reqObjImp\" id=\"reqObjImp\"".($reqObjImp?" value=\"1\"":" value=\"0\"")." class=\"top\" onclick=\"flagCheck(event);\"><span id=\"objImpCap\" class=\"top\">".($reqObjImp?"R":"No r")."equiere Objeto de Impuesto (02)</span><br><img src=\"imagenes/icons/".($reqPayTaxChk?"chkd24":"deleteIcon12").".png\" id=\"pyTxChkImg\" width=\"12\" height=\"12\" class=\"top\"><input type=\"hidden\" name=\"reqPayTaxChk\" id=\"reqPayTaxChk\"".($reqPayTaxChk?" value=\"1\"":" value=\"0\"")." class=\"top\" onclick=\"flagCheck(event);\"><span id=\"pyTxChkCap\" class=\"top\">".($reqPayTaxChk?"R":"No r")."equiere Validar Impuestos en Complementos de Pago</span></div>";
if (!empty($_SESSION["$_SERVER[SERVER_NAME]_invoice_check_provider_cache"])) {
    $refreshCode = " <img id='refreshImage' class='hidden noprint' src='imagenes/icons/descarga6.png' width='16' height='16' onclick='refreshCode()'>";
}
$switchCode="";
if($esAdmin&&!$soloLectura) {
    $switchCode="<img src=\"data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7\" onload=\"this.parentNode.appendChild(ecrea({eName:'IMG',id:'itchdot',src:'data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7',className:'vATBtm".(empty($idProveedor)?"":" bglightgray1")."',ondblclick:itchlet,width:'18',height:'18'}));ekil(this);\">";
}
?>
          <?php /* div id="area_base" class="centered" */ ?>
            <h1 class="txtstrk">Datos de Proveedor</h1>
            <div id="providers_section">
<?php 
if (($bloqueaProv||$modificaProv||$validaBanco||$validaOpinion||$consultaMasiva)&&!$soloLectura) { ?>
            <form method="post" name="forma_reg_prv" id="forma_reg_prv" target="_self" enctype="multipart/form-data">
<?php
    if ($bloqueaProv||($modificaProv&&$provStatusVal!=="bloqueado")) {
        $optionArray=[""=>"TODOS","activo"=>"ACTIVO","actualizar"=>"ACTUALIZAR","bloqueado"=>"BLOQUEADO","inactivo"=>"INACTIVO"];
        //if (!empty($idProveedor)) unset($optionArray[""]);
        if (!$bloqueaProv) unset($optionArray["bloqueado"]);
        $statusElement="<select name=\"frgn_status\" id=\"frgn_status\">".getHtmlOptions($optionArray, $provStatusVal)."</select>";
    }
    if ($modificaProv) {
        $flagList="<div id=\"flagBlock\" class=\"hidden\"><img src=\"imagenes/icons/".($conCodgEnDesc?"chkd24":"deleteIcon12").".png\" id=\"conCEDImg\" width=\"12\" height=\"12\" class=\"top hidden\"><input type=\"checkbox\" name=\"conCodgEnDesc\" id=\"conCodgEnDesc\"".($conCodgEnDesc?" checked":"")." value=\"1\" class=\"top\" onclick=\"flagCheck(event);\"><span id=\"conCEDCap\" class=\"top\">".($conCodgEnDesc?"T":"No t")."iene código en descripción</span><br><img src=\"imagenes/icons/".($esServicio?"chkd24":"deleteIcon12").".png\" id=\"esSrvImg\" width=\"12\" height=\"12\" class=\"top hidden\"><input type=\"checkbox\" name=\"esServicio\" id=\"esServicio\"".($esServicio?" checked":"")." value=\"1\" class=\"top\" onclick=\"flagCheck(event);\"><span id=\"esSrvCap\" class=\"top\">".($esServicio?"E":"No e")."s servicio</span><br><img src=\"imagenes/icons/".($reqObjImp?"chkd24":"deleteIcon2").".png\" id=\"objImpImg\" width=\"12\" height=\"12\" class=\"top hidden\"><input type=\"checkbox\" name=\"reqObjImp\" id=\"reqObjImp\"".($reqObjImp?" checked":"")." value=\"1\" class=\"top\" onclick=\"flagCheck(event);\"><span id=\"objImpCap\" class=\"top\">".($reqObjImp?"R":"No r")."equiere Objeto de Impuesto (02)</span><br><img src=\"imagenes/icons/".($reqPayTaxChk?"chkd24":"deleteIcon2").".png\" id=\"pyTxChkImg\" width=\"12\" height=\"12\" class=\"top hidden\"><input type=\"checkbox\" name=\"reqPayTaxChk\" id=\"reqPayTaxChk\"".($reqPayTaxChk?" checked":"")." value=\"1\" class=\"top\" onclick=\"flagCheck(event);\"><span id=\"pyTxChkCap\" class=\"top\">".($reqPayTaxChk?"R":"No r")."equiere Validar Impuestos en Complementos de Pago</span></div>";
    }
} ?>
                <input type="hidden" name="menu_accion" value="Registro">
                <table class="centered">
<?php 
if ($esDesarrollo) {
    echo "<tr><td class='lefted'>ID: </td><td class='lefted'><input type='text' name='frgn_id' id='frgn_id'{$provIdVal}></td></tr>\n";
    $provIdElem="";
} else $provIdElem="<input type='hidden' name='frgn_id' id='frgn_id'{$provIdVal}>";
?>
                <tr><td class="lefted">C&oacute;digo:<?= $refreshCode ?> </td><td class="lefted"><input type="text" name="frgn_code" id="frgn_code" size="35"<?= $provCodVal ?>><?= $provIdElem ?><?= $switchCode ?></td></tr>
                <tr><td class="lefted">Raz&oacute;n Social: </td><td class="lefted"><input type="text" name="frgn_field" id="frgn_field" size="35"<?= $provRzSocVal.$editAttrib ?>></td></tr>
                <tr><td class="lefted">RFC: </td><td class="lefted"><input type="text" name="frgn_rfc" id="frgn_rfc" size="12"<?= $provRfcVal.$editAttrib ?>></td></tr>
                <tr class="<?= $beginHidden ?>"><td class="lefted">Correo electr&oacute;nico: </td><td class="lefted"><input type="text" name="user_email" id="user_email" size="35"<?= $userEmailVal.$editAttrib ?>><input type="hidden" name="user_id" id="user_id"></td></tr>
                <tr class="<?= $beginHidden ?>"><td class="lefted">Días de crédito: </td><td class="lefted"><input type="number" name="frgn_credit" id="frgn_credit" min="0" max="1462" <?= $provCreditVal.$editAttrib ?>><?php /* &nbsp;<label title="Al guardar con check se recalcula la fecha de vencimiento de las facturas aceptadas pero no pagadas"><input type="checkbox" name="recalc_duedate" id="recalc_duedate" value="1"<?=$chkCrdAttrib?>>Recalcula vencimiento en facturas.</label> */ ?></td></tr>
                <tr class="<?= $beginHidden ?>"><td class="lefted">Forma de Pago: </td><td class="lefted"><select name="prov_paym" id="prov_paym"><?= $provPaymVal ?></select></td></tr>
                <tr><td class="lefted">Banco: </td><td class="lefted"><input type="text" name="prov_bank" id="prov_bank" size="35"<?= $provBankVal.$editAttrib ?>></td></tr>
                <tr><td class="lefted">RFC del Banco: </td><td class="lefted"><input type="text" name="prov_bankrfc" id="prov_bankrfc" size="12"<?= $provBankRfcVal.$editAttrib ?>></td></tr>
                <tr><td class="lefted">CLABE: </td><td class="lefted"><input type="text" name="prov_account" id="prov_account" size="35" maxlength="30"<?= $provAccountVal.$editAttrib ?>></td></tr>
                <tr id="refRow" class="<?= $beginHidden ?>"><td class="lefted" colspan="2">Se requiere Ref. para pago: <input type="checkbox" id="doRef1" onclick="checkRef(event)"<?=$provHasRef1?>>Numérica <input type="checkbox" id="doRef2" onclick="checkRef(event)"<?=$provHasRef2?>>Alfanumérica</td></tr>
                <tr id="refRow1"<?= $provShowRef1 ?>><td class="lefted">Ref. numérica:</td><td><input type="text" name="referencia[0]" id="referencia1"<?= $provRef1Val ?>></td></tr>
                <tr id="refRow2"<?= $provShowRef2 ?>><td class="lefted">Ref. alfanumérica:</td><td><input type="text" name="referencia[1]" id="referencia2"<?= $provRef2Val ?>></td></tr>
                <tr><td class="lefted" title="Seleccione un archivo en formato PDF que contenga la carátula de un estado de cuenta reciente con CLABE visible">Car&aacute;tula Edo.Cta.: <?= $modificaProv?"<img src=\"imagenes/icons/assistance.png\" class=\"vAlignCenter btnFX noprint\" id=\"sampleBtn\" width=\"20\" height=\"20\" title=\"Ejemplo de Edo.Cta.\">":"" ?></td><td class="lefted"><input type="file" name="prov_receipt" id="prov_receipt" textname="El estado de cuenta" accept=".pdf" size="35"<?= isset($provReceiptElem[0])?" class=\"hidden\"":"" ?>><?= $provReceiptElem ?></td></tr>
                <tr><td class="lefted top" title="Seleccione un archivo en formato PDF que contenga la Opinión del Cumplimiento de Obligaciones Fiscales Vigente generado por el SAT">Opini&oacute;n Cumplim.: <?= $modificaProv?"<a href=\"https://www.sat.gob.mx/consultas/20777/consulta-tu-opinion-de-cumplimiento-de-obligaciones-fiscales\" target=\"SAT\" class=\"noborder noprint\"><img src=\"imagenes/icons/sat.gif\" class=\"vAlignCenter btnFX\" id=\"satBtn\" width=\"20\" height=\"20\" title=\"Enlace al SAT para Generar Documento de Opinión de Cumplimiento\"></a>":""?></td><td class="lefted"><input type="file" name="prov_opinion" id="prov_opinion" textname="La opinión de cumplimiento" accept=".pdf" size="35"<?= isset($provOpinionElem[0])?" class=\"hidden\"":"" ?>><?= $provOpinionElem ?></td></tr>
                <tr title="Necesaria sólo para sucursales con el mismo RFC"><td class="lefted">Zona: </td><td class="lefted"><input type="text" name="prov_zone" id="prov_zone" size="35"<?= $provZoneVal.$editAttrib ?>></td></tr>
                <tr title="Regula el acceso del proveedor"><td class="lefted top">Status: </td><td class="lefted"><?= $statusElement.$flagList ?> </td></tr>
<?php if ($esAdmin) { ?>
                <tr title="Breve información adicional"><td class="lefted">Comentarios: </td><td class="lefted"><?php if (!$soloLectura) { ?><textarea name="prov_text" id="prov_text" rows="4" cols="36"<?= $editAttrib ?>><?php } else { ?><P><?php } ?><?= $provComments ?><?php if (!$soloLectura) { ?></textarea><?php } else { ?></P><?php } ?></td></tr>
<?php } ?>
<?php if (($modificaProv||$validaBanco||$validaOpinion||$consultaMasiva)&&!$soloLectura) { ?>
                <tr><td colspan="2">
<?php     if ($consultaMasiva) { ?>
                <input type="<?= $browseType ?>" name="prov_browse" id="prov_browse" value="Buscar">
<?php     } ?>
<?php     if ($modificaProv||$validaBanco||$validaOpinion) { ?>
                <input type="submit" name="prov_submit" id="prov_submit"<?= $canSaveClass ?> value="Guardar">
                <input type="reset" name="prov_reset" id="prov_reset"<?= $canSaveClass ?> value="Reset"></td>
<?php     } ?>
                </tr>
<?php } ?>
                </table>
<?php 
$browseScript=($consultaMasiva&&!$soloLectura?"const w=ebyid('prov_browse');w.onclick=function(){f.submitValue='browse';};clrem(ebyid('flagBlock'),'hidden');":"");
if (!isset($browseScript[0])&&!empty($idProveedor)) $browseScript="clrem(ebyid('flagBlock'),'hidden');";
$providerScript=(empty($idProveedor)&&!isset($codigoProveedor[4])&&!isset($_POST["prov_submit"])?"":"let ss=ebyid('prov_status');if(ss.children&&ss.children.length>0&&ss.children[0].tagName&&ss.children[0].tagName==='OPTION')ss.children[0].classList.add('hidden');");
$modifyScript="";
if ($esDesarrollo)
    $modifyScript.="let d=ebyid('prov_id');d.onchange=function(){prvIdChng(d);};";
if (($modificaProv||$validaBanco||$validaOpinion)&&!$soloLectura)
    $modifyScript.="let s=ebyid('prov_submit');const tt=ebyid('test');const roi=ebyid('reqObjImp');s.onclick=function(){f.submitValue='save';tt.value='ROI'+(roi.checked?'.chkd':'')+'='+roi.value;};f.onsubmit=submitting;f.onreset=function(){clearProv();c.value='';c.focus();return false;};";
if ($modificaProv&&!$soloLectura)
    $modifyScript.="let r=ebyid('prov_credit');r.oninput=function(){maxNEV(r,1462);};let z=ebyid('prov_field');z.oninput=function(){fixedUpper(z);};let u=ebyid('prov_rfc');u.oninput=function(){fixedUpper(u);};let x=function(m){if(m){m.value='0';if(m.tagName==='INPUT'){while(m.nextSibling)m.parentNode.removeChild(m.nextSibling);m.parentNode.appendChild(ecrea({eText:'PENDIENTE'}));}}};let v=ebyid('acc_verified');let b=ebyid('prov_bank');b.onchange=function(){x(v);};let k=ebyid('prov_bankrfc');k.onchange=function(){x(v);};let a=ebyid('prov_account');a.onchange=function(){x(v);};let e=ebyid('prov_receipt');e.onchange=function(){checkFile(e);x(v);};let l=ebyid('opinion_fulfilled');let o=ebyid('prov_opinion');o.onchange=function(){checkFile(o);x(l);};let i=ebyid('sampleBtn');i.onclick=function(){viewImageSample('Ejemplo de Car&aacute;tula');};";
if (($bloqueaProv||$modificaProv||$validaBanco||$validaOpinion||$consultaMasiva)&&!$soloLectura) { ?>
              <input type="hidden" id="test" name="test" value="">
            </form>  <!-- FIN BLOQUE USUARIOS -->
<?php
} ?>
            <img src="data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7" onload="let f=ebyid('forma_reg_prv');<?= $browseScript.$providerScript ?>
            let c=ebyid('prov_code');c.oninput=function(){prvCodFix(c);};<?= $modifyScript ?>this.parentNode.removeChild(this);">
            </div>
          <?php /* /div */ ?>
<?php
clog1seq(-1);
clog2end("templates.registro");
