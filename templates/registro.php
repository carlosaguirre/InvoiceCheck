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
$statusElement="<input type=\"text\" name=\"prov_status\" id=\"prov_status\" readonly value=\"$provStatusVal\" class=\"uppercase\" size=\"12\">";
$puedeModificar=$modificaProv&&!$soloLectura;

if ($consultaTiposProveedor) {
    $flg=[/*0*/$esServicio,/*1*/$tipoComercial,/*2*/$tipoAduanal,/*3*/$tipoTraslado,/*4*/$tipoLogistica,/*5*/$conCodgEnDesc,/*6*/$consultaObjImp,/*7*/$reqObjImp,/*8*/$modificaObjImp,/*9*/$consultaPayTaxChk,/*10*/$reqPayTaxChk,/*11*/$modificaPayTaxChk,/*12*/$consultaDefCvPrdSrv,/*13*/$reqDefCvPrdSrv,/*14*/$modificaDefCvPrdSrv,/*15*/$puedeModificar];
    $fife = function($i, $valret, $errret) { if (in_array($i, [0,1,2,3,4,5,7,10,13])) return $valret; return $errret; };
    $fsrc = function($i) use ($flg, $fife) { return $fife($i, $flg[$i]?"chkd24":"deleteIcon12", "assistance"); };
    $fval = function ($i) use ($flg, $fife) { return $fife($i, $flg[15]?($i<6?($flg[$i]?" checked":"")." value=\"1\"":($flg[$i]?($flg[$i+1]?" checked":" value=\"1\""):($flg[$i+1]?"":" value=\"0\"")).($flg[$i+1]?"":" value=\"0\"")):($i==0?" checked=\"".($flg[i]?"true":"false")."\"":"")." value=\"".($flg[i]?"1":"0")."\"", " value=\"-1\""); };
    $ftxt = function($i, $ky) use ($fife) { $arr=[
    "iim"=>[0=>"esSrvImg", 1=>"esTComImg", 2=>"esTAdnImg", 3=>"esTTraImg", 4=>"esTLogImg", 5=>"conCEDImg", 7=>"objImpImg", 10=>"pyTxChkImg", 13=>"dfCvPSImg"],
    "iin"=>[0=>"esServicio", 1=>"esTComer", 2=>"esTAduan", 3=>"esTTrasl", 4=>"esTLogis", 5=>"conCodgEnDesc", 7=>"reqObjImp", 10=>"reqPayTaxChk", 13=>"reqDefCvPrdSrv"],
    "isp"=>[0=>"esSrvCap", 1=>"esTCCap", 2=>"esTACap", 3=>"esTTCap", 4=>"esTLCap", 5=>"conCEDCap", 7=>"objImpCap", 10=>"pyTxChkCap", 13=>"dfCvPSCap"],
    "ist"=>[0=>"Es servicio", 1=>"Tipo comercial", 2=>"Tipo aduanal", 3=>"Tipo traslado", 4=>"Tipo logístico", 5=>"Con código en descripción", 7=>"Requiere Objeto de Impuesto", 10=>"Valida Impuestos en Complementos", 13=>"Restringe Clave Sin Definir"]]; return $fife($i, $arr[$ky][$i], ""); };
    $fblk = function ($i, $bt) use ($flg, $fsrc, $fval, $ftxt) {
        $sfx=$i==0?"Srv":($i==1?"Tco":($i==2?"Tad":($i==3?"Ttr":($i==4?"Tlo":($i==5?"Ced":($i==7?"Oim":($i==10?"Imc":($i==13?"Csd":"Xxx"))))))));
        switch($bt) {
            case "img": return ($flg[15]&&($i<6||$flg[$i+1]))?"":"<img src=\"imagenes/icons/".$fsrc($i).".png\" id=\"".$ftxt($i,"iim")."\" width=\"12\" height=\"12\" class=\"top prvChkImg prvChk$sfx\">";
            case "inp": return "<input type=\"".($flg[15]&&($i<6||$flg[$i+1])?"checkbox":"hidden")."\" name=\"".$ftxt($i,"iin")."\" id=\"".$ftxt($i,"iin")."\"".$fval($i)." class=\"top prvChkInp prvChk$sfx\">";
            case "spn": return "<span id=\"".$ftxt($i,"isp")."\" class=\"top prvChkSpn prvChk$sfx\">".$ftxt($i,"ist")."</span>";
        }
    };
    $typeList=$fblk(0,"img").$fblk(0,"inp").$fblk(0,"spn")."<br>".$fblk(1,"img").$fblk(1,"inp").$fblk(1,"spn")."<br>".$fblk(2,"img").$fblk(2,"inp").$fblk(2,"spn")."<br>".$fblk(3,"img").$fblk(3,"inp").$fblk(3,"spn")."<br>".$fblk(4,"img").$fblk(4,"inp").$fblk(4,"spn");
    $ignoreList="".$fblk(5,"img").$fblk(5,"inp").$fblk(5,"spn").($flg[6]?"<br>".$fblk(7,"img").$fblk(7,"inp").$fblk(7,"spn"):"").($flg[9]?"<br>".$fblk(10,"img").$fblk(10,"inp").$fblk(10,"spn"):"").($flg[12]?"<br>".$fblk(13,"img").$fblk(13,"inp").$fblk(13,"spn"):"");
} else {
    $flg=[/*0*/$conCodgEnDesc,/*1*/$esServicio,/*2*/$consultaObjImp,/*3*/$reqObjImp,/*4*/$modificaObjImp,/*5*/$consultaPayTaxChk,/*6*/$reqPayTaxChk,/*7*/$modificaPayTaxChk,/*8*/$consultaDefCvPrdSrv,/*9*/$reqDefCvPrdSrv,/*10*/$modificaDefCvPrdSrv,/*11*/$puedeModificar];
    $fife = function($i, $valret, $errret) { if (in_array($i, [0,1,3,6,9])) return $valret; return $errret; }; 
    $fsrc = function($i) use ($flg, $fife) { return $fife($i, $flg[$i]?"chkd24":"deleteIcon12", "assistance"); };
    $fval = function($i) use ($flg, $fife) { return $fife($i, $flg[11]?($i<2?($flg[$i]?" checked":"")." value=\"1\"":($flg[$i]?($flg[$i+1]?" checked":" value=\"1\""):($flg[$i+1]?"":" value=\"0\"")).($flg[$i+1]?"":" value=\"0\"")):($i==0?" checked=\"".($flg[$i]?"true":"false")."\"":"")." value=\"".($flg[$i]?"1":"0")."\"", " value=\"-1\""); };
    $ftxt = function($i, $ky) use ($fife) { $arr=[
    "iim"=>[0=>"conCEDImg", 1=>"esSrvImg", 3=>"objImpImg", 6=>"pyTxChkImg", 9=>"dfCvPSImg"],
    "iin"=>[0=>"conCodgEnDesc", 1=>"esServicio", 3=>"reqObjImp", 6=>"reqPayTaxChk", 9=>"reqDefCvPrdSrv"],
    "isp"=>[0=>"conCEDCap", 1=>"esSrvCap", 3=>"objImpCap", 6=>"pyTxChkCap", 9=>"dfCvPSCap"],
    "ist"=>[0=>"Tiene código en descripción", 1=>"Es servicio", 3=>"Requiere Objeto de Impuesto (02)", 6=>"Valida Impuestos en Complementos de Pago", 9=>"Restringe Clave de Producto/Servicio Sin Definir"]]; return $fife($i, $arr[$ky][$i], ""); };
    $fblk = function($i, $bt) use ($flg, $fsrc, $fval, $ftxt) {
        switch($bt) {
            case "img": return "<img src=\"imagenes/icons/".$fsrc($i).".png\" id=\"".$ftxt($i,"iim")."\" width=\"12\" height=\"12\" class=\"top".($flg[11]&&($i<2||$flg[$i+1])?" hidden":"")."\">";
            case "inp": return "<input type=\"".($flg[11]&&($i<2||$flg[$i+1])?"checkbox":"hidden")."\" name=\"".$ftxt($i,"iin")."\" id=\"".$ftxt($i,"iin")."\"".$fval($i)." class=\"top\">";
            case "spn": return "<span id=\"".$ftxt($i,"isp")."\" class=\"top\">".$ftxt($i,"ist")."</span>";
        }
        return "";
    };
    $flagList="<div id=\"flagBlock\" class=\"hidden\">".$fblk(0,"img").$fblk(0,"inp").$fblk(0,"spn")."<br>".$fblk(1,"img").$fblk(1,"inp").$fblk(1,"spn").($flg[2]?"<br>".$fblk(3,"img").$fblk(3,"inp").$fblk(3,"spn"):"").($flg[5]?"<br>".$fblk(6,"img").$fblk(6,"inp").$fblk(6,"spn"):"").($flg[8]?"<br>".$fblk(9,"img").$fblk(9,"inp").$fblk(9,"spn"):"")."</div>";
}

if (!empty($_SESSION["$_SERVER[SERVER_NAME]_invoice_check_provider_cache"])) {
    $refreshCode = " <img id='refreshImage' class='hidden noprint' src='imagenes/icons/descarga6.png' width='16' height='16' onclick='refreshCode()'>";
}
$switchCode="";
if($esAdmin&&!$soloLectura) {
    $switchCode="<img src=\"data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7\" onload=\"this.parentNode.appendChild(ecrea({eName:'IMG',id:'itchdot',src:'data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7',className:'vATBtm".(empty($idProveedor)?"":" bglightgray1")."',ondblclick:itchlet,width:'18',height:'18'}));ekil(this);\">";
}
?>
<?php  /* div id="area_base" class="centered" */ ?>
            <h1 class="txtstrk">Datos de Proveedor</h1>
            <div id="providers_section" class="basicHeight">
<?php
$puedeGuardar0=$modificaProv||$validaBanco||$validaOpinion||$bloqueaProv;
$puedeGuardar=$puedeGuardar0&&!$soloLectura;
$puedeInteractuar=($puedeGuardar0||$consultaMasiva)&&!$soloLectura;
echo "<!-- ".($puedeGuardar?"":"NO ")."PUEDE GUARDAR -->";
echo "<!-- ".($puedeInteractuar?"":"NO ")."PUEDE INTERACTUAR -->";
if ($puedeInteractuar) { ?>
            <form method="post" name="forma_reg_prv" id="forma_reg_prv" target="_self" enctype="multipart/form-data">
<?php
    if ($bloqueaProv||($modificaProv&&$provStatusVal!=="bloqueado"&&$provStatusVal!=="inactivo"&&$provStatusVal!=="eliminado")) {
        $optionArray=[""=>"TODOS","activo"=>"ACTIVO","actualizar"=>"ACTUALIZAR"];
        //,"bloqueado"=>"BLOQUEADO","inactivo"=>"INACTIVO"];
        //if (!empty($idProveedor)) unset($optionArray[""]);
        if ($bloqueaProv) {
            $optionArray["bloqueado"]="BLOQUEADO";
            $optionArray["inactivo"]="INACTIVO";
        }
        $statusElement="<select name=\"prov_status\" id=\"prov_status\">".getHtmlOptions($optionArray, $provStatusVal)."</select>";
    }
} ?>
                <input type="hidden" name="menu_accion" value="Registro">
                <div class="centered"><div class="inblock brdr1d screenBG"><table class="margin5 pad2c">
<?php 
if ($esDesarrollo) {
    echo "<tr><td class='lefted'>ID: </td><td class='lefted'><input type='text' name='prov_id' id='prov_id'{$provIdVal}></td></tr>\n";
    $provIdElem="";
} else $provIdElem="<input type='hidden' name='prov_id' id='prov_id'{$provIdVal}>";
?>
                <tr><td class="lefted">C&oacute;digo:<?= $refreshCode ?> </td><td class="lefted"><input type="text" name="prov_code" id="prov_code" size="35"<?= $provCodVal ?>><?= $provIdElem ?><?= $switchCode ?></td></tr>
                <tr><td class="lefted">Raz&oacute;n Social: </td><td class="lefted"><input type="text" name="prov_field" id="prov_field" size="35"<?= $provRzSocVal.$editAttrib ?>></td></tr>
                <tr><td class="lefted">RFC: </td><td class="lefted"><input type="text" name="prov_rfc" id="prov_rfc" size="12"<?= $provRfcVal.$editAttrib ?>></td></tr>
                <tr class="<?= $beginHidden ?>"><td class="lefted">Correo electr&oacute;nico: </td><td class="lefted"><input type="text" name="user_email" id="user_email" size="35"<?= $userEmailVal.$editAttrib ?>><input type="hidden" name="user_id" id="user_id"></td></tr>
                <tr class="<?= $beginHidden ?>"><td class="lefted">Días de crédito: </td><td class="lefted"><input type="number" name="prov_credit" id="prov_credit" min="0" max="1462" <?= $provCreditVal.$editAttrib ?>><?php /* &nbsp;<label title="Al guardar con check se recalcula la fecha de vencimiento de las facturas aceptadas pero no pagadas"><input type="checkbox" name="recalc_duedate" id="recalc_duedate" value="1"<?=$chkCrdAttrib?>>Recalcula vencimiento en facturas.</label> */ ?></td></tr>
                <tr class="<?= $beginHidden ?>"><td class="lefted">Forma de Pago: </td><td class="lefted"><select name="prov_paym" id="prov_paym"><?= $provPaymVal ?></select></td></tr>
                <tr><td class="lefted">Banco: </td><td class="lefted"><input type="text" name="prov_bank" id="prov_bank" size="35"<?= $provBankVal.$editAttrib ?>></td></tr>
                <tr><td class="lefted">RFC del Banco: </td><td class="lefted"><input type="text" name="prov_bankrfc" id="prov_bankrfc" size="12"<?= $provBankRfcVal.$editAttrib ?>></td></tr>
                <tr><td class="lefted">CLABE: </td><td class="lefted"><input type="text" name="prov_account" id="prov_account" size="35" maxlength="30"<?= $provAccountVal.$editAttrib ?>></td></tr>
                <tr id="refRow" class="<?= $beginHidden ?>"><td class="lefted<?= $puedeModificar?"":" hidden" ?>" colspan="2">Se requiere Ref. para pago: <input type="checkbox" id="doRef1" onclick="checkRef(event)"<?=$provHasRef1?>>Numérica <input type="checkbox" id="doRef2" onclick="checkRef(event)"<?=$provHasRef2?>>Alfanumérica</td></tr>
                <tr id="refRow1"<?= $provShowRef1 ?>><td class="lefted">Ref. numérica:</td><td<?= $puedeModificar?"":" class=\"lefted\"" ?>><input type="text" name="referencia[0]" id="referencia1"<?= $provRef1Val.$editAttrib ?>></td></tr>
                <tr id="refRow2"<?= $provShowRef2 ?>><td class="lefted">Ref. alfanumérica:</td><td<?= $puedeModificar?"":" class=\"lefted\"" ?>><input type="text" name="referencia[1]" id="referencia2"<?= $provRef2Val.$editAttrib ?>></td></tr>
                <tr><td class="lefted" title="Seleccione un archivo en formato PDF que contenga la carátula de un estado de cuenta reciente con CLABE visible">Car&aacute;tula Edo.Cta.: <?= $puedeModificar?"<img src=\"imagenes/icons/assistance.png\" class=\"vAlignCenter btnFX noprint\" id=\"sampleBtn\" width=\"20\" height=\"20\" title=\"Ejemplo de Edo.Cta.\">":"" ?></td><td class="lefted"><input type="file" name="prov_receipt" id="prov_receipt" textname="El estado de cuenta" accept=".pdf" size="35"<?= isset($provReceiptElem[0])?" class=\"hidden\"":"" ?>><?= $provReceiptElem ?></td></tr>
                <tr><td class="lefted top" title="Seleccione un archivo en formato PDF que contenga la Opinión del Cumplimiento de Obligaciones Fiscales Vigente generado por el SAT">Opini&oacute;n Cumplim.: <?= $puedeModificar?"<a href=\"https://www.sat.gob.mx/consultas/20777/consulta-tu-opinion-de-cumplimiento-de-obligaciones-fiscales\" target=\"SAT\" class=\"noborder noprint\"><img src=\"imagenes/icons/sat.gif\" class=\"vAlignCenter btnFX\" id=\"satBtn\" width=\"20\" height=\"20\" title=\"Enlace al SAT para Generar Documento de Opinión de Cumplimiento\"></a>":""?></td><td class="lefted"><input type="file" name="prov_opinion" id="prov_opinion" textname="La opinión de cumplimiento" accept=".pdf" size="35"<?= isset($provOpinionElem[0])?" class=\"hidden\"":"" ?>><?= $provOpinionElem ?></td></tr>
                <tr title="Necesaria sólo para sucursales con el mismo RFC"><td class="lefted">Zona: </td><td class="lefted"><input type="text" name="prov_zone" id="prov_zone" size="35"<?= $provZoneVal.$editAttrib ?>></td></tr>
                <tr title="Regula el acceso del proveedor"><td class="lefted top">Status: </td><td class="lefted"><?= $statusElement.($consultaTiposProveedor?"":$flagList) ?> </td></tr>
<?php
if ($consultaTiposProveedor) { ?>
                <tr id="flagBlock" class="hidden"><td colspan="2" class="centered vAlignCenter"><TABLE class="centered topvalign pad2c"><THEAD><TR><TH class="padR20i">CARACTERISTICAS</TH><TH>PERMISOS</TH></TR></THEAD><TBODY class="lefted topvalign"><TR><TD class="padL8i"><?= $typeList ?></TD><TD class="padL8i"><?= $ignoreList ?></TD></TR></TBODY></TABLE></td></tr>
<?php
} ?>
<?php
if ($esSistemas) { ?>
                <tr title="Breve información adicional"><td class="lefted">Comentarios: </td><td class="lefted"><?php if (!$soloLectura) { ?><textarea name="prov_text" id="prov_text" rows="4" cols="36"<?= $editAttrib ?>><?php } else { ?><P><?php } ?><?= $provComments ?><?php if (!$soloLectura) { ?></textarea><?php } else { ?></P><?php } ?></td></tr>
<?php
} ?>
                </table></div></div>
<?php
if ($puedeInteractuar) { ?>
                <div class="sticky toBottom basicBG centered pad2">
<?php
    if ($consultaMasiva) { ?>
                <input type="<?= $browseType ?>" name="prov_browse" id="prov_browse" value="Buscar">
<?php
    }
    if ($puedeGuardar) { ?>
                <input type="submit" name="prov_submit" id="prov_submit"<?= $canSaveClass ?> value="Guardar">
<?php
    }
    if ($puedeModificar) { ?>
                <input type="reset" name="prov_reset" id="prov_reset"<?= $canSaveClass ?> value="Reset"></td>
<?php
    } ?>
                </div>
<?php
} 
$browseScript=($consultaMasiva&&!$soloLectura?"const w=ebyid('prov_browse');w.onclick=function(){f.submitValue='browse';};".($consultaTiposProveedor?"clrem(ebyid('flagBlock'),'hidden');":""):"");
if (!isset($browseScript[0])&&!empty($idProveedor)) $browseScript="clrem(ebyid('flagBlock'),'hidden');";
$providerScript=(empty($idProveedor)&&!isset($codigoProveedor[4])&&!isset($_POST["prov_submit"])?"":"let ss=ebyid('prov_status');if(ss.children&&ss.children.length>0&&ss.children[0].tagName&&ss.children[0].tagName==='OPTION')ss.children[0].classList.add('hidden');");
$modifyScript="";
if ($esDesarrollo)
    $modifyScript.="let d=ebyid('prov_id');d.onchange=function(){prvIdChng(d);};";
if ($puedeGuardar)
    $modifyScript.="let s=ebyid('prov_submit');const tt=ebyid('test');const roi=ebyid('reqObjImp');s.onclick=function(){f.submitValue='save';if(roi)tt.value='ROI'+(roi.checked?'.chkd':'')+'='+roi.value;};f.onsubmit=submitting;f.onreset=function(){clearProv();c.value='';c.focus();return false;};";
if ($puedeModificar)
    $modifyScript.="let r=ebyid('prov_credit');r.oninput=function(){maxNEV(r,1462);};let z=ebyid('prov_field');z.oninput=function(){fixedUpper(z);};let u=ebyid('prov_rfc');u.oninput=function(){fixedUpper(u);};let x=function(m){if(m){m.value='0';if(m.tagName==='INPUT'){while(m.nextSibling)m.parentNode.removeChild(m.nextSibling);m.parentNode.appendChild(ecrea({eText:'PENDIENTE'}));}}};let v=ebyid('acc_verified');let b=ebyid('prov_bank');b.onchange=function(){x(v);};let k=ebyid('prov_bankrfc');k.onchange=function(){x(v);};let a=ebyid('prov_account');a.onchange=function(){x(v);};let e=ebyid('prov_receipt');e.onchange=function(){checkFile(e);x(v);};let l=ebyid('opinion_fulfilled');let o=ebyid('prov_opinion');o.onchange=function(){checkFile(o);x(l);};let i=ebyid('sampleBtn');i.onclick=function(){viewImageSample('Ejemplo de Car&aacute;tula');};";
if ($puedeInteractuar) { ?>
              <input type="hidden" id="test" name="test" value="">
            </form>  <!-- FIN BLOQUE USUARIOS -->
<?php
} ?>
            <img src="data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7" onload="let f=ebyid('forma_reg_prv');<?= $browseScript.$providerScript ?>
            let c=ebyid('prov_code');c.oninput=function(){prvCodFix(c);};<?= $modifyScript ?>this.parentNode.removeChild(this);">
            </div>
          <?php  /* /div */ ?>
<?php
clog1seq(-1);
clog2end("templates.registro");
