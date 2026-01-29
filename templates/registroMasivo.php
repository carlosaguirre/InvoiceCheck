<?php
clog2ini("templates.registroMasivo");
clog1seq(1);
?>
            <h1 class="txtstrk">Consulta Masiva de Proveedores</h1>
            <div id="providers_section">
                <table id="dataHeaders" class="fixedHeader">
                    <thead><tr><th><div class="rowCode" title="CÓDIGO DE PROVEEDOR">COD.</div></th><th><div class="rowRazSoc">RAZON SOCIAL</div></th><th><div class="rowRFC">RFC</div></th><th><div class="rowCuenta">CUENTA</div></th><th><div class="rowOpinion">OPINION</div></th><th><div class="rowOpPags" title="Páginas">Pg</div></th><th><div class="rowStatus">ESTADO</div></th><th class="wid15px scr"></th></tr></thead>
                    <tbody>
<?php
$statusList=["activo"=>"ACTIVO","actualizar"=>"ACTUALIZAR","bloqueado"=>"BLOQUEADO","inactivo"=>"INACTIVO"];
if (!$bloqueaProv) {
    unset($statusList["bloqueado"]);
    unset($statusList["inactivo"]);
}
$statusFunc=function($provId, $provStatusVal) { global $statusList, $modificaProv; if (isset($statusList[$provStatusVal])) { if ($modificaProv) return "<select id=\"status_$provId\" class=\"bgbtn bodycolor\" onchange=\"cambiaEstado('$provId',this.value);\" baseValue=\"$provStatusVal\">".getHtmlOptions($statusList, $provStatusVal)."</select>"; return $statusList[$provStatusVal]; } return strtoupper($provStatusVal); };
$accountFunc=function($provId, $provRfc, $btnText, $bnkName, $bnkRfc, $accNum, $accFile, $verVal, $numpag) { global $modificaProv, $validaBanco; if ($modificaProv||$validaBanco) return "<input type=\"button\" id=\"cuenta_$provId\" value=\"$btnText\" bank=\"$bnkName\" bankrfc=\"$bnkRfc\" account=\"$accNum\" filename=\"$accFile\" verify=\"$verVal\" onclick=\"revisaCuenta(event,$provId,'$provRfc')\">"; return $btnText; };
$opinionFunc=function($provId, $provRfc, $btnText, $opiFile, $opiVal, $opiGen, $numpag) {
    global $modificaProv, $validaOpinion, $esAdmin;
    $retVal=[
        "button"=>(($modificaProv||$validaOpinion)?"<input type=\"button\" id=\"opinion_$provId\" value=\"$btnText\" filename=\"$opiFile\" verify=\"$opiVal\" revision=\"$opiGen\" onclick=\"revisaOpinion(event,$provId,'$provRfc')\">":$btnText),
        "numPags"=>(($validaOpinion&&$numpag)?"{$numpag}":""), 
        "extraPg"=>(($validaOpinion&&$esAdmin)?" ondblclick=\"let thisspan=this;".
            // "console.log('requesting numpg',thisspan);".
            "postService(".
                "'consultas/Proveedores.php',".
                "{command:'setnumpg',id:'$provId'},".
                "function(text,params,state,status){".
                    "if(state<4&&status<=200)return;".
                    "if(state>4||status>200){".
                        // "console.log('state'+state+',status'+status);".
                        "return;".
                    "}".
                    "if(text.length>0){".
                        // "console.log(text);".
                        "try{".
                            "let jobj=JSON.parse(text);".
                            "if(jobj.result==='success'&&jobj.numpagOpinion){".
                                "thisspan.innerHTML=jobj.numpagOpinion;".
                                "cladd(thisspan,'bggreen2');".
                                "setTimeout(c=>{clrem(c,'bggreen2');},3000,thisspan);".
                            "}".
                        "}catch(ex){".
                            "console.log(ex);".
                            "cladd(thisspan,'bgred2');".
                            "setTimeout(c=>{clrem(c,'bgred2');},3000,thisspan);".
                        "}".
                    "}else{".
                        "console.log('empty');".
                        "cladd(thisspan,'bgblack');".
                        "setTimeout(c=>{clrem(c,'bgblack');},3000,thisspan);".
                    "}".
                "},".
                "function(text,pars,evt){".
                    "console.log('ERROR '+text+' '+pars.xmlHttpPost.readyState+'-'+pars.xmlHttpPost.status);".
                "}".
            ");\"":"")
    ];
    return $retVal;
};
for($iter=0;isset($prvData[$iter]);$iter++) {
    $prvId=$prvData[$iter]["id"]; $prvCode=$prvData[$iter]["codigo"]; $prvName=$prvData[$iter]["razonSocial"];
    $prvReg=$prvData[$iter]["rfc"]; $prvBnk=$prvData[$iter]["banco"]; $prvBRf=$prvData[$iter]["rfcbanco"];
    $prvAcc=$prvData[$iter]["cuenta"]; $prvAFN=$prvData[$iter]["edocta"]; $prvVer=$prvData[$iter]["verificado"]; $nPgEdoCta=$prvData[$iter]["numpagEdoCta"];
    $prvOFN=$prvData[$iter]["opinion"]; $prvFul=$prvData[$iter]["cumplido"]; $nPgOpinion=$prvData[$iter]["numpagOpinion"];
    if(!empty($prvData[$iter]["generaopinion"])) {
        $tmpDate=DateTime::createFromFormat('Y-m-d',$prvData[$iter]["generaopinion"]);
        $prvGen=$tmpDate->format('d/m/Y');
    } else $prvGen="";
    $prvStt=$prvData[$iter]["status"];
    switch($prvVer) {
        case "-1": $cuentaInfo=isset($prvAFN[0])?"RECHAZADO":"FALTANTE"; break;
        case "0": $cuentaInfo="PENDIENTE"; break;
        case "1": $cuentaInfo="ACEPTADO"; break;
        case "": $cuentaInfo="INDEFINIDO"; break;
        default: $cuentaInfo="OTRO"; break;
    }
    switch($prvFul) {
        case "-2": $opinionInfo="RECHAZADO"; break;
        case "-1": $opinionInfo=isset($prvOFN[0])?"VENCIDO":"FALTANTE"; break;
        case "0": $opinionInfo="PENDIENTE"; break;
        case "1": $opinionInfo="ACEPTADO"; break;
        case "": $opinionInfo="INDEFINIDO"; break;
        default: $opinionInfo="OTRO"; break;
    }
    $opinionResult=$opinionFunc($prvId,$prvReg,$opinionInfo,$prvOFN,$prvFul,$prvGen,$nPgOpinion);
?>
                        <tr><td><div class="rowCode btnLt bRad2 pointer nowrap" id="code_<?= $prvId ?>"><?= $prvCode ?></div></td><td><div class="rowRazSoc"><?= $prvName ?></div></td><td><div class="rowRFC"><?= $prvReg ?></div></td><td><div class="rowCuenta"><?= $accountFunc($prvId,$prvReg,$cuentaInfo,$prvBnk,$prvBRf,$prvAcc,$prvAFN,$prvVer,$nPgEdoCta) ?></div></td><td><div class="rowOpinion"><?= $opinionResult["button"] ?></div></td><td><div class="rowOpPags"<?= $opinionResult["extraPg"] ?>><?= $opinionResult["numPags"] ?></div></td><td><div class="rowStatus"><?= $statusFunc($prvId, $prvStt) ?></div></td></tr>
<?php
}
$totReg=+$prvObj->numrows;
$needPagedRegInfo=$iter!==$totReg;
if ($needPagedRegInfo) {
    $regInfo="$iter/$totReg Registros";
    $prvPg=($prvObj->pageno-1);
    $btn_1=" class=\"btnFX pro16\"";
    $btn_0=" class=\"btnHid off16\"";
    $go2pg="";
    if ($prvPg>1) $pageInfo="<span{$btn_1} onclick=\"goto('pageSwitch',1);\">1</span>&nbsp;<span{$btn_1} onclick=\"goto('pageSwitch',$prvPg);\">$prvPg</span>";
    else if ($prvPg==1) $pageInfo="<span{$btn_0}></span>&nbsp;<span{$btn_1} onclick=\"goto('pageSwitch',1);\">1</span>";
    else $pageInfo="<span{$btn_0}></span>&nbsp;<span{$btn_0}></span>";
    $pageInfo.=" <span class=\"pageTxt\">$prvObj->pageno<input type=\"hidden\" id=\"pageSwitch\" value=\"$prvObj->pageno\"></span> ";
    $nxtPg=($prvObj->pageno+1);
    if ($nxtPg<$prvObj->lastpage) $pageInfo.="<span{$btn_1} onclick=\"goto('pageSwitch',$nxtPg);\">$nxtPg</span>&nbsp;<span{$btn_1} onclick=\"goto('pageSwitch',$prvObj->lastpage);\">$prvObj->lastpage</span>";
    else if ($nxtPg==$prvObj->lastpage) $pageInfo.="<span{$btn_1} onclick=\"goto('pageSwitch',$nxtPg);\">$nxtPg</span>&nbsp;<span{$btn_0}></span>";
    else $pageInfo.="<span{$btn_0}></span>&nbsp;<span{$btn_0}></span>";
} else {
    $regInfo="$iter Registro".($iter==1?"":"s");
    $pageInfo="";
}
$needPageResize=($prvObj->numrows>100);
if ($needPageResize) {
    $resiz=+($_POST["regPerPage"]??100);
    clog3("REGPERPAGE = $resiz");
    $is100=$resiz==100; $is250=$resiz==250; $is500=$resiz==500; $is1000=$resiz==1000;
    $resizeInfo="<select id=\"regPerPage\" onchange=\"goto(this.id,this.value);\"><option value=\"100\"".($is100?" selected":"").">100</option><option value=\"250\"".($is250?" selected":"").">250</option><option value=\"500\"".($is500?" selected":"").">500</option><option value=\"1000\"".($is1000?" selected":"").">1000</option></select>";
} else $resizeInfo="";
?>
                    </tbody>
                    <tfoot>
                        <tr class="vAlignCenter"><th class="footLeft"><?= $regInfo ?></th><th class="footCenter" colspan="3"><?= $pageInfo ?></th><th class="footRight"><?= $resizeInfo ?></th><th class="wid15px scr"></th></tr>
                        <tr class="vAlignCenter"><th class="scr vAlignCenter" colspan="5"><form method="post" name="forma_reg_prv" id="forma_reg_prv" target="_self" enctype="multipart/form-data" class="footForm" onsubmit="return validateForm(event);">
                            <input type="hidden" name="prov_return" id="prov_return" value="1">
                            <input type="hidden" name="prov_code" id="prov_code" value="<?= $_POST["prov_code"]??"" ?>">
                            <input type="hidden" name="prov_id" id="prov_id" value="<?= $_POST["prov_id"]??"" ?>">
                            <input type="hidden" name="prov_field" id="prov_field" value="<?= $_POST["prov_field"]??"" ?>">
                            <input type="hidden" name="prov_rfc" id="prov_rfc" value="<?= $_POST["prov_rfc"]??"" ?>">
                            <input type="hidden" name="user_email" id="user_email" value="<?= $_POST["user_email"]??"" ?>">
                            <input type="hidden" name="user_id" id="user_id" value="<?= $_POST["user_id"]??"" ?>">
                            <input type="hidden" name="prov_credit" id="prov_credit" value="<?= $_POST["prov_credit"]??"" ?>">
                            <input type="hidden" name="prov_paym" id="prov_paym" value="<?= $_POST["prov_paym"]??"" ?>">
                            <input type="hidden" name="prov_bank" id="prov_bank" value="<?= $_POST["prov_bank"]??"" ?>">
                            <input type="hidden" name="prov_bankrfc" id="prov_bankrfc" value="<?= $_POST["prov_bankrfc"]??"" ?>">
                            <input type="hidden" name="prov_account" id="prov_account" value="<?= $_POST["prov_account"]??"" ?>">
                            <input type="hidden" name="prov_zone" id="prov_zone" value="<?= $_POST["prov_zone"]??"" ?>">
                            <input type="hidden" name="prov_status" id="prov_status" value="<?= $_POST["prov_status"]??"" ?>">
                            <input type="hidden" name="acc_verified" id="acc_verified" value="<?= $_POST["acc_verified"]??"" ?>">
                            <input type="hidden" name="opinion_fulfilled" id="opinion_fulfilled" value="<?= $_POST["opinion_fulfilled"]??"" ?>">
                            <input type="hidden" name="opinion_expired" id="opinion_expired" value="<?= $_POST["opinion_expired"]??"" ?>">
                            <button type="submit" name="menu_accion" id="backButton" value="Registro" class="vAlignCenter">Regresar</button>
                            <input type="hidden" name="menu_accion" id="menu_accion" value="Registro"?>
                            <img src="data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7" onload="const v=fillValue;const b=ebyid('backButton');b.onclick=(event)=>{/*console.log('RETURN CLICK! '+b.code);*/if(b.code)delete b.code;else v('prov_return','1');};fee(lbycn('rowCode'),(el)=>{if(el.id&&el.id.slice(0,5)==='code_'){el.onclick=(event)=>{const t=event.target;const f=ebyid('forma_reg_prv');fee(f.getElementsByTagName('input'),(it)=>{it.value='';});v('prov_code',t.textContent);b.code=t.id.slice(5);v('prov_id',b.code);v('menu_accion','Registro');b.name='prov_browse';b.click();cladd(b,'hidden');}}});this.parentNode.removeChild(this);">
                        </form></th><th class="wid15px scr"></th></tr>
                    </tfoot>
                </table>
            </div>
<?php
clog1seq(-1);
clog2end("templates.registroMasivo");
