<?php
require_once dirname(__DIR__)."/bootstrap.php";

header("Content-type: application/javascript; charset: UTF-8");
if(!hasUser()) {
    die("Empty File");
}
$esAdmin = validaPerfil("Administrador");
$esSistemas = validaPerfil("Sistemas")||$esAdmin;
$esDesarrollo = $esAdmin && (getUser()->nombre==="admin");
$esComExtMonitor = validaPerfil("ComercioExterior Monitor");
$esComExtControl = validaPerfil("ComercioExterior Control");
$esComExtAdmin = validaPerfil("ComercioExterior Admin");
$bloqueaProv = validaPerfil("BloquearPrv")||$esAdmin||$esSistemas;
clog2ini("scripts.comext");
clog1seq(1);
$optDefaultValue=$_SESSION['optDefaultValue'];$esOptCodigo=($optDefaultValue==="codigo");$esOptRFC=($optDefaultValue==="rfc");$esOptRazon=($optDefaultValue==="razon");
$optAllList=["codigo"=>"Todos","rfc"=>"Todos","razon"=>"Todas"];$optAllValue=$optAllList[$optDefaultValue];
$gpoIdOpt = $_SESSION ['gpoIdOpt'];
clog2("GPOIDOPT: ".json_encode($gpoIdOpt));
$gpoOptList=getEOBJStrOptions($gpoIdOpt, (count($gpoIdOpt)==1 ? key($gpoIdOpt) : ""));
if(isset($gpoOptList [0])) $gpoOptList=", $gpoOptList"; else $gpoOptList="";
$gpoOptList=" [ {eName: \"OPTION\", value: \"\", codigo: \"{$optAllList["codigo"]}\", rfc: \"{$optAllList["rfc"]}\", razon: \"{$optAllList["razon"]}\", eText: \"$optAllValue\"}$gpoOptList ]";
$extIdOpt = $_SESSION ['extIdOpt'];
clog2("EXTIDOPT: ".json_encode($extIdOpt));
$extOptList=getEOBJStrOptions($extIdOpt, (count($extIdOpt)==1 ? key($extIdOpt) : ""));
if(isset($extOptList [0])) $extOptList=", $extOptList"; else $extOptList="";
$extOptList=" [ { eName: \"OPTION\", value: \"\", codigo: \"{$optAllList["codigo"]}\", rfc: \"{$optAllList["rfc"]}\", razon: \"{$optAllList["razon"]}\", eText: \"{$optAllValue}\"}$extOptList]";
$agtIdOpt = $_SESSION ['agtIdOpt'];
clog2("AGTIDOPT: ".json_encode($agtIdOpt));
$agtOptList=getEOBJStrOptions($agtIdOpt, (count($agtIdOpt)==1 ? key($agtIdOpt) : ""));
if(isset($agtOptList [0])) $agtOptList=", $agtOptList"; else $agtOptList="";
$agtOptList=" [ { eName: \"OPTION\", value: \"\", codigo: \"{$optAllList["codigo"]}\", rfc: \"{$optAllList["rfc"]}\", razon: \"{$optAllList["razon"]}\", eText: \"$optAllValue\"}$agtOptList]";
clog2("-- --");

$dia = date('j');
$mes = date('n');
$anio = date('Y');
$maxdia = date('t');
$fmtDay0 = "01/".str_pad($mes, 2, "0", STR_PAD_LEFT)."/".$anio;
$fmtDay = str_pad($dia, 2, "0", STR_PAD_LEFT)."/".str_pad($mes, 2, "0", STR_PAD_LEFT)."/".$anio;
require_once "clases/ComExtExpediente.php";
?>
let comextTimeout=0;
let currentComExtOpt="prv";
console.log("COMEXT SCRIPT READY!!!");
console.log("PERFILES: "+JSON.stringify(<?= json_encode(getUser()->perfiles); ?>));

const tiposDeOperacion=[<?="\"".implode("\",\"",ComExtExpediente::TIPOS_OPERACION)."\""?>];
const ktdo = [ "<?=implode("\", \"",array_keys(ComExtExpediente::TIPOS_OPERACION))?>" ];
const vds={ "":"Elige...", <?=implode(', ',array_map(function($v, $k){return $k.':"'.$v.'"';},ComExtExpediente::STATUSES,array_keys(ComExtExpediente::STATUSES)))?> };
const vks = [ "", "<?=implode("\", \"",array_keys(ComExtExpediente::STATUSES))?>"];
//console.log("TIPOS DE OPERACION: ",tiposDeOperacion);
//console.log("PROPERTY NAMES: ",ktdo);
const comextViewConstants = { prv: "foreign", agt: "customs", nwo: "nvoexpd", rdo: "srchexp", ope: "vwexpdn", auo: "audoper", rpo: "repoper" };
const cpyAts = (oriObj,desObj,pfx)=>{if(!desObj)desObj={};if(oriObj){for(let att in oriObj){let kda=pfx?pfx+att.charAt(0).toUpperCase()+att.slice(1):att;desObj[kda]=oriObj[att];}}return desObj;};
const objFnc = (name,atts,childs)=>{if(!childs&&atts&&(Array.isArray(atts)||(typeof atts!=="object"))){childs=atts;atts=false;}const rt={eName:name, ...atts};if(childs){const tCh=typeof childs;if(tCh==="array"||(tCh==="object"&&Array.isArray(childs)))rt.eChilds=childs;else rt.eText=childs;}return rt;};
const tabFnc = (hrws,brws,frws,tats)=>{const tch=[];if(hrws)tch.push(objFnc("THEAD",hrws.ats,Array.isArray(hrws)?hrws:false));if(brws)tch.push(objFnc("TBODY",brws.ats,Array.isArray(brws)?brws:false));if(frws)tch.push(objFnc("TFOOT",frws.ats,Array.isArray(frws)?frws:false));return objFnc("TABLE",tats,tch);};
const inpFnc=(idv,typ,fnc,cls,att)=>{return objFnc("INPUT",{id:idv,type:typ,...att,...(fnc?{oninput:fnc}:{}),...(cls?{className:cls}:{})});};
const txaFnc=(idv,val,ats)=>{return objFnc("TEXTAREA",{id:idv,...(val?{value:val}:{}),...ats});};
const aucoFnc=(el,ls)=>objFnc("DIV",{ className:"autocomplete" },[el]);
const btnFnc=(idv,val,cls)=>objFnc("INPUT",{type:"button",id:idv,value:val,className:cls?cls:"hidden",onclick:comextAction});
const radFnc=(bas,idT,nam,bval)=>objFnc("INPUT",{type:"radio",id:bas+nam[0]+idT,name:bas+nam,value:idT,className:"wid8px marT0i marL4i marR2i vAlignCenter "+nam.toLowerCase(),checked:!!bval});
const rdTFnc=(bas,idT,bval)=>radFnc(bas,idT,"Tipolista",bval);
const optLst=(map,kys)=>{ const opts=[]; if(!kys)kys=Object.getOwnPropertyNames(map); kys.forEach(k=>{ if(map[k]) opts.push(objFnc("OPTION",{value:k},map[k])); }); return opts; };
const selFnc=(idv,cls,opts,ats)=>objFnc("SELECT", {id:idv, ...(cls?{className:cls}:{}), onchange:btnCheck, ...ats}, optLst(opts));
const thFnc=(atts,childs)=>{return objFnc("TH",atts,childs);};
const tdFnc=(atts,childs)=>{return objFnc("TD",atts,childs);};
const trFnc=(atts,childs)=>{return objFnc("TR",atts,childs);};
const rw2Fnc=(rowats,cap,capats,cnt,cntats)=>{const cells=[];if(cap!==false&&cap!==null)cells.push(thFnc(capats,cap));cells.push(tdFnc(cntats,cnt));return trFnc(rowats,cells);};
const rwnFnc=(rowats,caps,capats,cnts,cntats)=>{const cells=[];for(let i=0; i < cnts.length; i++){if(caps&&caps[i]!==false&&caps[i]!==null)cells.push(thFnc(capats?capats[i]:false,caps[i]));cells.push(tdFnc(cntats?cntats[i]:false,cnts[i]));} return trFnc(rowats,cells);};
const filterCell=(childs,rowId,ats)=>tdFnc({onclick:pickRow, ondblclick:pickRow, rowId:rowId,...ats},childs);
const codeRwF=bas=>rw2Fnc(false,"Código",false,[inpFnc(bas+"Id","hidden"),aucoFnc(inpFnc(bas+"Code","text",comextAction,"autoFocus",{maxLength:9,autofocus:true}))]);
const nameRwF=(bas,idT,nam,fnc,atti)=>rw2Fnc(false,nam,false,[inpFnc(bas+idT,"text",fnc,false,atti)]);
const dateRwF=bas=>rw2Fnc(false,"Fecha Creación",false,[inpFnc(bas+"Date","text",false,"calendarV padv02 noprintBorder clearable",{onchange:btnCheck,readOnly:true,value:"<?= $fmtDay ?>"})],{className:"lefted"});
const btnRwF=(bas,csp,cls)=>rw2Fnc(false,false,false,[...(cls&&cls.delete?[btnFnc(bas+"DeleteButton","Eliminar",cls.delete)]:[]),...(cls&&cls.browse?[btnFnc(bas+"BrowseButton","Buscar",cls.browse)]:[]),...(cls&&cls.save?[btnFnc(bas+"SaveButton","Guardar",cls.save)]:[])],{colSpan:csp?csp:"2",className:"centered"});
const radRwF=(bas,cntats)=>rw2Fnc(false,"",false,[rdTFnc(bas,"codigo",<?=b2s($esOptCodigo)?>),{eText:"Código"}/*,rdTFnc(bas,"rfc",<?=b2s($esOptRFC)?>),{eText:"RFC"}*/,rdTFnc(bas,"razon",<?=b2s($esOptRazon)?>),{eText:"Razón Social"}],{className:"fontSmall noApply nohover lefted",...cntats});
const entRwF=(bas,cap,key,nam,opts,cntats)=>rw2Fnc(false,cap,{className:"capcell"},[objFnc("SELECT",{id:bas+key+"Id",className:"comext_fixedSelect",onchange:btnCheck,name:nam,ltype:"<?= $optDefaultValue ?>"},opts)],{id:bas+key+"Cell",className:"lefted nohover",...cntats});

const operOpts={<?=ComExtExpediente::TIPO_OPERACION_IMPORTACION?>:"Importación",<?=ComExtExpediente::TIPO_OPERACION_IMPORTACION_ACTIVOS?>:"Importación con Activos",<?=ComExtExpediente::TIPO_OPERACION_EXPORTACION?>:"Exportación"};
let gpoDtL=<?= $gpoOptList ?>;
let gpoRwF=(bas,cntats)=>entRwF(bas,"Empresa","Gpo","grupo",gpoDtL,cntats);
let extDtL=<?= $extOptList ?>;
let extRwF=(bas,cntats)=>entRwF(bas,"Proveedor","Ext","extranjero",extDtL,cntats);
let agtDtL=<?= $agtOptList ?>;
let agtRwF=(bas,cntats)=>entRwF(bas,"Agente","Agt","agente",agtDtL,cntats);
var comextTimeoutCount=0;
var xmlTypes=["text/xml","application/xml","text/plain"];
var pdfTypes=["application/pdf"];
function comextOpt2(val) {
    console.log("INI function comextOpt2 "+val);
    const cef=ebyid("comext_frame");
    if (!cef) {
        comextTimeout=setTimeout(initMenu, 100);
        return;
    }

    console.log("CEF:",cef);
    cladd("comext_title","hidden");
    cladd("comext_content","hidden");
    clrem(cef,"hidden");
    fee(lbycn("comextMenuBtn"), el=> { return clrem (el, "selected");});
    /*
    }*/
    const usrProfile=<?= json_encode(getUser()->perfiles); ?>;
    cef.onload = function() {
        cef.contentWindow.postMessage({message:usrProfile,value:usrProfile.length}, '*'); // Envía los datos al iframe
    };
    switch(val) {
        case "prv": 
            cladd("prvBtn", "selected");
            cef.src="/comercioexterior/";
            break;
        case "nwo":
            cladd("nwoBtn", "selected");
            cef.src="/comercioexterior/operaciones.php";
            break;
        case "rdo":
            cladd("rdoBtn", "selected");
            cef.src="/invoice/templates/comextrdo.php";
            break;
        default:
            cef.src="about:blank"; // simplest solution
            if (false) {
                cef.srcdoc=""; // HTML5 feature
                cef.src = 'data:text/html;charset=utf-8,' + encodeURIComponent('<html></html>'); // data uri for more detailed and specific blank page
                cef.contentWindow.document.open();
                cef.contentWindow.document.close(); // clear content if same origin
            }
    }
//    cef.contentWindow.postMessage(<?= json_encode(getUser()->perfiles); ?>, "*");
}
function initMenu() {
    ebyid('comext_menu_close').addEventListener('mouseenter', clickMenuOpen);
    ebyid('comext_menu').addEventListener('mouseleave', clickMenuClose);
    lbycn("comextMenuBtn")[0].click();
}
function clickMenuOpen(ev) {
    console.log("INI clickMenuOpen");
    cladd('comext_menu', 'menu-visible');
    cladd(['comext_menu_down','comext_menu_up'], 'menu-open');
}
function clickMenuClose(ev) {
    console.log("INI clickMenuClose");
    clrem('comext_menu', 'menu-visible');
    clrem(['comext_menu_down','comext_menu_up'], 'menu-open');
}
function comextOpt(val,data,editable) {
    console.log("INI function comextOpt '"+val+"'");
    if (val!=="ope") {
        const tgtId=val+"Btn"; // menu de comercio exterior en templates/comext.php
        const tgt=ebyid(tgtId);
        if (!tgt) { // opeBtn no existe ni va a existir, solo q se haga un hidden...!
            console.log("COMEXTOPT "+val+" NOT READY YET. Not found "+tgtId);
            clearTimeout(comextTimeout);
            if (comextTimeoutCount<10) {
                comextTimeoutCount++;
                comextTimeout=setTimeout(comextOpt, 100, val);
            }
            return;
        }
        fee(lbycn("comextMenuBtn"), el=> { return clrem (el, "selected");});
        cladd(tgtId, "selected");
    }
    cladd("comext_frame","hidden");
    clrem("comext_title","hidden");
    clrem("comext_content","hidden");
    let ttl = "";
    let cnt = [];
    cnt [0] = { eName: "DIV" };
    cvV=comextViewConstants[val];
    let focusElemId=false;
    cxT=ebyid("comext_title");
    if (cxT) {
        cxT.className="";
        cladd(cxT,cvV);
    }
    switch(val) {
        case "prv": ttl = "ABC PROVEEDOR EXTRANJERO";
            let typOpts={}, sttOpts = {};
            if (!data||editable) {
                typOpts = {"1":"Comercial","2":"Flete","4":"Aduanal","8":"Logistico"};
                sttOpts = {actualizar:"ACTUALIZAR",activo:"ACTIVO"<?= ($bloqueaProv?",bloqueado:\"BLOQUEADO\",inactivo:\"INACTIVO\"":"").($esSistemas?",eliminado:\"ELIMINADO\"":"") ?>};
            } else {
                switch(data["tipo.id"]) {
                    case 1: typOpts={"1":"Comercial"}; break;
                    case 2: typOpts={"2":"Flete"}; break;
                    case 4: typOpts={"4":"Aduanal"}; break;
                    case 8: typOpts={"8":"Logistica"}; break;
                    default: typOpts={"":"No definido"};
                }
                const stt=data["status"];
                sttOpts = {stt:stt.toUpperCase()};
            }
            const abpe = tabFnc(false, [
                rwnFnc(false,["Código","Tax Id"], false,[[inpFnc(cvV+"Id","hidden",false,false,(data?{value:data["id"]}:false)),aucoFnc(inpFnc(cvV+"Code","text",comextAction,"autoFocus req2save",{maxLength:9,size:3,autofocus:true,value:(data?data["codigo"]:'')<?=$esDesarrollo?",title:(data?data['id']:''),onkeyup:(data?copyValOrTtlUp:false)":""?>,readOnly:(data?(!!editable):false)}))],[inpFnc(cvV+"Taxid","text",comextAction,"req2save",{maxLength: "20",size:9,value:(data?data["taxId"]:''),readOnly:(data?(!!editable):false)})]]),
                rwnFnc(false,["Nombre"],false,[[inpFnc(cvV+"Name","text",btnCheck,"req2save",{maxLength:"100",size:30,value:(data?data["descripcion"]:'')})]],[{colSpan:"3",className:"lefted"}]),
                rwnFnc(false,["Banco"],false,[[inpFnc(cvV+"Bank","text",btnCheck,false,{maxLength:"100",value:(data?data["banco"]:'')})]],[{colSpan:"3",className:"lefted"}]),
                rwnFnc(false,["Cuenta"],false,[[inpFnc(cvV+"Account","text",btnCheck,false,{maxLength:"100",value:(data?data["cuenta"]:'')})]],[{colSpan:"3",className:"lefted"}]),
                rwnFnc(false,["Tipo","Status"], false,[[selFnc(cvV+"Type",false,typOpts,{value:(data?data["tipo"]:'')})],[selFnc(cvV+"Status",false,sttOpts,{value:(data?data["status"]:''),onchange:comextAction})]]),
                rwnFnc(false,["Dirección"],false,[[inpFnc(cvV+"Address","text",btnCheck,false,{maxLength:"100",value:(data?data["calle"]:'')})]],[{colSpan:"3",className:"lefted"}]),
                rwnFnc(false,["Ciudad","Estado"], false,[[aucoFnc(inpFnc(cvV+"City","text",comextAction,false,{maxLength:12,size:6,value:(data?data["ciudad"]:''),readOnly:(data?(!!editable):false)}))],[inpFnc(cvV+"State","text",comextAction,false,{maxLength: 12,size:6,value:(data?data["estado"]:''),readOnly:(data?(!!editable):false)})]]),
                rwnFnc(false,["País","ZIPCode"], false,[[aucoFnc(inpFnc(cvV+"Country","text",comextAction,"req2save",{maxLength:12,size:6,value:(data?data["pais"]:''),readOnly:(data?(!!editable):false)}))],[inpFnc(cvV+"ZipCode","text",comextAction,false,{maxLength: 12,size:6,value:(data?data["codigoPostal"]:''),readOnly:(data?(!!editable):false)})]]),
                rwnFnc(false,["Teléfono","Correo"], false,[[aucoFnc(inpFnc(cvV+"Phone","text",comextAction,false,{maxLength:12,size:6,value:(data?data["telefono"]:''),readOnly:(data?(!!editable):false)}))],[inpFnc(cvV+"Email","text",comextAction,false,{maxLength: 12,size:6,value:(data?data["correo"]:''),readOnly:(data?(!!editable):false)})]]),
                rwnFnc(false,["Comentarios"],[{className:"top"}],[[txaFnc(cvV+"Desc",(data?data["comentarios"]:false),{maxLength:"490",className:"all_space"})]],[{colSpan:"3",className:"lefted"}]),
             // btnRwF(cvV)
                btnRwF(cvV,"4",{delete:"hidden",browse:"hidden",save:"hidden"})
            ]);
            //console.log("CODE TABLE: ",abpe);
            focusElemId=cvV+"Code";
            cnt[0].eChilds = [ abpe ]; break;
        case "nwo": ttl = "GENERAR NUEVO REGISTRO";
            const gne = tabFnc(false, [
                dateRwF(cvV),
                rw2Fnc(false,"Operación",false,[inpFnc(cvV+"Id","hidden"),selFnc(cvV+"Opid","autoFocus",operOpts)],{className:"lefted"}),
                rw2Fnc(false,"Orden",false,[inpFnc(cvV+"OrdId","text",btnCheck,"ordNo",{maxLength:40}),objFnc("LABEL",{htmlFor:cvV+"OrdFile",className:"asBtn forPDF"},"Anexar PDF"),inpFnc(cvV+"OrdFile","file",btnCheck,"ordFile masked",{accept:".pdf",onchange:storeFile})], {className:"lefted"}),
                radRwF(cvV),
                gpoRwF(cvV),
                extRwF(cvV),
                agtRwF(cvV),
                rw2Fnc(false,"Importe",false,[inpFnc(cvV+"Total","number",btnCheck,false,{placeholder:"0.00",min:"0",step:"0.01",pattern:"^\d+(?:\.\d{1,2})?$"}),objFnc("INPUT",{id:cvV+"Curr",type:"button",className:"wid40px",value:"MXN",onclick:ev=>{currencySwitch(ev);console.log("V:"+ev.target.value+" vs DV:"+ev.target.defaultValue);}})],{className:"lefted"}),
                rw2Fnc(false,"Descripción",false,[txaFnc(cvV+"Desc",false,{"maxLength":"490",className:"all_space"})],{className:"lefted"}),
                btnRwF(cvV,false,{delete:"hidden",browse:"hidden",save:"hidden"})
            ]);
            cnt [0].eChilds = [ gne ];
            break;
        case "rdo": ttl = "CONSULTAR REGISTROS";
            const flom={ord:"Orden",fol:"Folio",/*ped:"Pedimento",*/emp:"Empresa",pro:"Proveedor",aga:"Agente Aduanal",sta:"Status",/*rdf:"Rango de fecha",*/fda:"Fecha de alta",tdo:"Tipo de Operación"};
            //const diAdd="data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7";
            const diAdd="imagenes/icons/add.png";
            const diSame="imagenes/icons/same.png";
            const diLwr="imagenes/icons/lowerthan.png";
            const diGtr="imagenes/icons/greaterthan.png";
            const rrd = radRwF(cvV);
            rrd.id=cvV+"FilterOptions";
            rrd.className="invisible";
            rrd.eChilds[0].className="stretch";
            const cex = tabFnc([/*HD1*/ rrd, trFnc([ tdFnc({ className:"stretch" }, [ objFnc("SELECT", { id:cvV+"FilterList", onchange:searchOpBy }, optLst(flom)) ]), tdFnc({ id:cvV+"FilterFields" }, [ objFnc("IMG", { id:cvV+"LoadImg", onload:searchOpBy, className:"loadImg invisible", src:diAdd, onclick:filterCheck }), objFnc("IMG", { id:cvV+"LoadImgL", className:"loadImg hidden", src:diLwr, onclick:filterCheck }),objFnc("IMG", { id:cvV+"LoadImgS", className:"loadImg invisible", src:diSame, onclick:filterCheck }),objFnc("IMG", { id:cvV+"LoadImgG", className:"loadImg hidden", src:diGtr, onclick:filterCheck }) ]) ]), trFnc([ tdFnc({ colSpan:"2" }, [ objFnc("DIV", { id:cvV+"FilterSummary", className:"scrollauto" }) ]) ]) /*HD1*/], [/*BD1*/ trFnc([ tdFnc({ colSpan:"2" }, [ objFnc("DIV", { id:cvV+"OpResults", className:"hidden" }, [ tabFnc([/*HD2*/ trFnc({ id:cvV+"OpHead"/*, className:"hidden"*/ }, [ thFnc("FECHA"), thFnc("EMPRESA"), thFnc("PROVEEDOR"), thFnc("OPERACION"), thFnc("FOLIO"), thFnc("ORDEN"), /*thFnc("PEDIMENTO"), */thFnc("AGENTE"), thFnc("IMPORTE"), /*thFnc("DESCRIPCION"), */thFnc("STATUS") ]) /*HD2*/], /*BD2*/{ ats: { id:cvV+"OpList" } }/*BD2*/) ]), objFnc("DIV", { id:cvV+"OpReview", className:"hidden" }, [ objFnc("DIV", { id:cvV+"OpCount" }), objFnc("DIV", [ inpFnc(cvV+"OpOpen", "button", false, "hidden", { value:"Abrir", onclick:openSelected }) ]), objFnc("DIV", { id:cvV+"OpSum" }) ]) ]) ]) /*BD1*/], /*FT*/false, {/*ATS*/ className:"maxxed" /*ATS*/});
            cnt [0].eChilds = [ cex ];
            break;
        case "ope": ttl="VISTA REGISTROS";
            const archivosOrden=[];
            console.log(val+") "+ttl+". data=",data)
            if (data["orden.href"]) {
                archivosOrden.push(objFnc("BR"));
                if (typeof data["orden.href"] === 'string')
                    data["orden.href"] = [data["orden.href"]];
                data["orden.href"].forEach(lnk=>{
                    archivosOrden.push(objFnc("A",{href:data["documentos.docRoot"]+lnk,target:"orden"},[objFnc("IMG",{src:data["documentos.imgRoot"]+data["orden.src"],width:"20"},[])]));
                });
            }
            const dfAts={accept:".pdf",multiple:true,dbFiles:data["documentos"],docRoot:data["documentos.docRoot"],imgRoot:data["documentos.imgRoot"],onchange:storeFile};
            data["documentos"].forEach(d=>{
                if (!d.key) console.log("NO KEY FOR: "+d.titulo+" '"+d["name.short"]+"'|'"+d["type.ext"]+"'");
                else if(data[d.key+".src"]) dfAts[d.key+".src"]=data[d.key+".src"];
                else console.log("NOT FOUND KEY("+d.key+").SRC");
            });
            const vex = tabFnc(false, cpyAts({ats:{className:"noApply lefted"}},[
                rwnFnc(false,["Folio","Creación"],false,[[inpFnc(cvV+"Id","hidden",false,false,{value:data["id"]}),inpFnc(cvV+"Folio","text",false,"cent foliox noprintBorder",{value:data["folio.desc"]<?=$esDesarrollo?",title:data['id'],onkeyup:copyValOrTtlUp":""?>,readOnly:true})],[inpFnc(cvV+"Date","text",false,"calendarV padv02 noprintBorder",{value:data["fechaAlta.calendarValue"],readOnly:true})]]),
                rwnFnc(false,["Orden","Operación"],false,[[inpFnc(cvV+"OrdId","text",btnCheck,"cent ordNox",{value:data["orden"],maxLength:40}),...archivosOrden,objFnc("LABEL",{htmlFor:cvV+"OrdFile",className:"asBtn forPDF"},"Cambiar PDF"),inpFnc(cvV+"OrdFile","file",btnCheck,"ordFile masked",{accept:".pdf",onchange:storeFile})],[selFnc(cvV+"Opid","operacion autoFocus",operOpts),objFnc("BR"),inpFnc(cvV+"DocFile","file",false ,"masked",dfAts),objFnc("INPUT",{id:cvV+"AppendDocs",type:"button",value:"Anexar Docs",onclick:openDocsDialog})]]),
                radRwF(cvV,{colSpan:"3"}),
                gpoRwF(cvV,{colSpan:"3",value:data["grupoId"]}),
                extRwF(cvV,{colSpan:"3",value:data["proveedorId"]}),
                agtRwF(cvV,{colSpan:"3",value:data["agenteId"]}),
                rwnFnc(false,["Importe","Saldo"],false,[[inpFnc(cvV+"Total","number",btnCheck,"importe",{placeholder:"0.00",min:"0",step:"0.01",pattern:"^\d+(?:\.\d{1,2})?$",value:data["importe.numero"]}),objFnc("INPUT",{id:cvV+"Curr",type:"button",className:"wid40px",value:data["moneda.new"],onclick:ev=>{currencySwitch(ev);console.log("V:"+ev.target.value+" vs DV:"+ev.target.defaultValue);}})],[objFnc("SPAN",{id:cvV+"Diferencia",eText:data["importe.difvisible"]??data["importe.diferencia"]??data["importe.visible"]})]],[{className:"lefted"},{}]),
                rwnFnc(false,["Descripción"],false,[[txaFnc(cvV+"Desc",data["descripcion"],{"maxLength":"490",className:"all_space"})]],[{colSpan:"3",className:"lefted"}]),
                btnRwF(cvV,"4",{delete:"ready",save:"ready"}),
                rw2Fnc(false,false,false,[objFnc("DIV", { id:cvV+"DocList" })],{colSpan:"4",className:"centered"})
            ]),false,{className:cvV});
            cnt[0].eChilds = [ vex, objFnc("IMG",{src:"data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7", onload:evt=>{
                ebyid(cvV+'GpoId').value=data["grupoId"];
                ebyid(cvV+'ExtId').value=data["proveedorId"];
                ebyid(cvV+'AgtId').value=data["agenteId"];
                console.log('LAST IMAGE LOADED',evt);}}), {eComment:"DATA = "+JSON.stringify(data)} ]; // <img src="data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7" onload="anyFunc();ekil(this);">
            console.log("INIT SHOW SAVED OP: "+ttl);
            break;
        case "auo": ttl="AUDITAR OPERACIONES";
            const aud = inpFnc(cvV+"Test","button",false,false,{value:"Prueba",onclick:evt=>{
                overlayMessage(objFnc("DIV",{className:"padhtt "+cvV,ondblclick:appendErrorMessageProperty,errorDetail:{eName:"P",className:"marL4 boldValue darkRedLabel",eText:"PRUEBA DE TEXTO ADICIONAL"}},"PRUEBA DE TEXTO INICIAL"),"PRUEBA");
            }});
            cnt[0].className = "centered";
            cnt[0].eChilds = [ aud ];
            break;
        case "rpo": ttl="REPORTE DE OPERACIONES";
            break;
        default: console.log("OPCION DESCONOCIDA");
            return;
    }
    sessionService("comextChoice", val);
    const tto=ebyid("comext_title");
    if (tto) {
        ekfil(tto);
        tto.appendChild(ecrea(objFnc("DIV",ttl)));
    } else console.log("COMEXT TITLE NOT FOUND");
    currentComextOpt=val;
    const cto=ebyid("comext_content");
    if (cto) {
        ekfil(cto);
        cnt.forEach((e, i)=> { if (e.eName) {
            cto.appendChild(ecrea(e));
            // habilitar opciones para Empresa/Proveedor para mostrar codigo, RFC o Razón Social
            const tls=lbycn("tipolista");
            fee(tls, t=>{ t.onclick=()=>{ pickType(t);};});
            // habilitar funcionalidad de calendario en campos de fecha
            const cvVDate=ebyid(cvV+"Date");
            if (cvVDate) cvVDate.onclick=()=>{ show_calendar_widget( cvVDate, ()=>{ btnCheck( { target: cvVDate});});};
            // habilitar funcionalidad particular por sección
            switch(val) {
                case "nwo":
                    const ccs=lbycn("capcell");
                    fee(ccs, c=> {c.ondblclick=()=> {
                        let cec=ebyid("comextChoice");
                        const f=document.forms.forma_menu;
                        if(!cec) {
                            cec=ecrea( {eName: "INPUT", type: "hidden", name: "comextChoice", id: "comextChoice", value: "nwo"});
                            f.appendChild(cec);
                        } else cec.value="nwo";
                        f.appendChild(ecrea( {eName: "INPUT", type: "hidden", name: "menu_accion", value: "Comercio Exterior"} ));
                        f.submit();
                    };});
                    break;
            }
        }});
        const af=lbycn("autoFocus");
        if (af.length>0) af[0].focus();
    } else console.log("COMEXT CONTENT NOT FOUND");
}
function copyValOrTtlUp(evt) {
    if (!evt) {
        console.log("NO EVENT...");
        return;
    }
    const tgt=evt.target ? evt.target : false;
    if (!tgt) {
        console.log("NO TARGET...");
        return;
    }
    if (evt.type==="keyup") {
        let mod="";
        if (evt.shiftKey) mod+="|SHFT";
        if (evt.ctrlKey) mod+="|CTRL";
        if (evt.altKey) mod+="|ALT";
        if (evt.metaKey) mod+="|META";
        const doTitle=(evt.ctrlKey || evt.metaKey || evt.altKey);
        if (doTitle) {
            const rt1=copyTextToClipboard(tgt.title);
            if (rt1===true) console.log("Title copiado: '"+tgt.title+"'"+mod);
            else if (rt1===false) console.log("No se pudo copiar Title: '"+tgt.title+"'"+mod);
            else rt1=console.log("Error al copiar title: '"+rt1+"'"+mod);
        } else {
            const rt2=copyFieldToClipboard(tgt);
            if (rt2===true) console.log("Value copiado: '"+tgt.value+"'"+mod);
            else if (rt2===false) console.log("No se pudo copiar value: '"+tgt.value+"'"+mod);
            else rt2=console.log("Error al copiar value: '"+rt2+"'"+mod);
        }
    } else console.log("Invalid event "+evt.type+mod);
}
function btnCheck(evt) {
    if (!evt) {
        console.log("NO EVENT... check trace");
        return false;
    }
    const tgt=evt.target ? evt.target : false;
    if (!tgt) {
        console.log("NO TARGET... check trace",evt);
        return false;
    }
    const tgtId=tgt.id;
    const tgtPrefix=tgtId.substr(0, 7);
    const tgtName=tgtId.substr(7);
    const tgtType=tgt.type;
    const tgtLtype=tgt.ltype;
    const tgtOptions=tgt.options;
    const tgtSelectedIndex=tgtOptions?tgt.selectedIndex:false;
    const tgtSelectedOption=(tgtSelectedIndex===false)?false:tgtOptions[tgtSelectedIndex];
    tgt.title = (tgtSelectedOption && tgtSelectedOption.value.length>0) ? (tgtLtype==="razon" ? tgtSelectedOption.codigo + ((tgtSelectedOption.rfc.substr(0,4)==="XEXX") ? "" : " " + tgtSelectedOption.rfc) : tgtSelectedOption.razon) : "";
    console.log("INI function btnCheck " + tgt.tagName + " " + tgtId+(tgtLtype ? " (" + tgtLtype + ")" : "") + ": " + tgt.value + (tgtSelectedOption ? " (" + tgtSelectedOption.codigo + " " + tgtSelectedOption.rfc + " " + tgtSelectedOption.razon + ")" : ""));
    clrem(tgtPrefix+"CheckButton", "hidden");
    const elemId=(tgtName==="Id"?tgt:ebyid(tgtPrefix+"Id"));
    if (elemId) {
        if (elemId.value.length>0) {
            clrem(tgtPrefix+"DeleteButton", "hidden");
            delBtn=ebyid(tgtPrefix+"DeleteButton");
            delBtn.value=elemId.isDeleted?"Limpiar":"Eliminar";
        } else cladd(tgtPrefix+"DeleteButton", "hidden");
    } else {
        console.log("ERR btnCheck: Doesnt exist "+tgtPrefix+"Id");
    }
    let ppt=tgt.parentNode;
    while(ppt.parentNode && ppt.tagName!=="TBODY") ppt=ppt.parentNode;
    let isMissing=false;
    const requiredElems=ppt.querySelectorAll(".req2save"); // input[type=text], input[type=number], select"
    requiredElems.forEach((e)=> { if (_isMissing(e)) { console.log("Missing elem to save",e); isMissing=true;} });
    if (isMissing && evt.action && evt.action==="Guardar") isMissing=false;
    clset(tgtPrefix+"SaveButton", "hidden", isMissing);
}
function storeFile(evt) {
    const tgt=evt.target;
    const tgtId=tgt.id;
    const cvV=tgtId.substr(0,7);
    const tgtName=tgtId.substr(7);
    const tgtFiles=tgt.files;
    const numFiles = tgtFiles?tgtFiles.length:0;
    if (numFiles<=0) {
        console.log("INI function storeFile for "+tgtName+": NOTHING SELECTED");
        return;
    }
    const tgtFileArray=numFiles>0?Array.from(tgtFiles):[];
    const lbl = document.querySelector('label[for="'+tgtId+'"]');
    console.log("INI function storeFile for "+tgtName+": "+(numFiles>1?numFiles+" archivos":tgtFiles[0].name),tgtFiles);
    switch(tgtName) {
        case "DocFile":
            const selT=ebyid(cvV+"docTypeList");
            if (!selT) console.log("No se encontró 'docTypeList'");
            else if (selT.value==="factura") {
                const tipo=selT.options[selT.selectedIndex].text;
                if (numFiles!=2) {
                    alert("Debe elegir un xml y un pdf con el mismo nombre");
                    tgt.value=null;
                    return;
                }
                let pdfName=false, xmlName=false;
                tgtFileArray.forEach((f,i)=>{
                    if (pdfTypes.includes(f.type)) pdfName=f.name.slice(0,-4);
                    else if (xmlTypes.includes(f.type)) xmlName=f.name.slice(0,-4);
                });
                if (!xmlName || !pdfName) {
                    alert("Debe elegir xml y pdf de una misma factura");
                    tgt.value=null;
                    return;
                } else if (xmlName!==pdfName) {
                    alert("Asegúrese que ambos archivos tengan el mismo nombre antes de la extensión");
                    tgt.value=null;
                    return;
                }
                if (!tgt.allFiles) tgt.allFiles=[];
                if (!tgt.allTypes) tgt.allTypes=[];
                const rfb=ebyid(cvV+"RecentFileBody"); if (!rfb) console.log("Not Found RecentFileBody");
                else {
                    console.log("Found RecentFileBody");
                    tgtFileArray.forEach((f,fi)=>{
                        let fnm=f.name;
                        console.log("Processing file: '"+fnm+"'");
                        let fidx=fnm.lastIndexOf("/");
                        if (fidx>0) fnm=fnm.slice(fidx+1);
                        let ftt=fnm;
                        if (ftt.length>100) ftt=ftt.slice(-99);
                        if (ebyid("vwdocrw"+ftt)) {
                            console.log("Ya existe un documento con ese nombre. Tiene que borrarlo primero.");
                            return;
                        }
                        tgt.allFiles.push(f);
                        tgt.allTypes.push(tipo);
                        // toDo: Si está en RecentFileBody se borra
                        // toDo: Si está en SavedFileBody se copia a cvV+DelFile y se borra
                        //       Sería mejor si se pide confirmación primero en ambos casos
                        fidx=fnm.lastIndexOf(".");
                        if (fidx>0) fnm=fnm.slice(0,fidx);
                        if (fnm.length>20) fnm=fnm.slice(0,9)+"..."+fnm.slice(-8);
                        let fty=f.type;
                        if (pdfTypes.includes(fty)) fty=tipo+" PDF";
                        else if (xmlTypes.includes(fty)) fty=tipo+" XML";
                        let fsz=human_filesize(f.size);
                        rfb.appendChild(ecrea(trFnc({id:"vwdocrw"+ftt},[tdFnc({title:ftt},fnm),tdFnc(fty),tdFnc(fsz),tdFnc([objFnc("IMG", {src:"imagenes/icons/crossRed.png", className:"file24 pointer", onclick:deleteRecentFileRow})])])));
                        console.log("Appended Row: "+fnm+" | "+fty+" | "+fsz);
                    });
                    if (rfb.rows.length>0) clrem(rfb.parentNode,"hidden");
                }
            } else if (selT.value.length==0)console.log("No document selected");
            else console.log("Selected other document: "+selT.value);
            break;
        default: console.log("This is not DocFile: '"+tgtName+"'");
    }
    let fname=tgt.accept===".pdf"?"Anexar PDF":(tgt.accept===".xml"?"Anexar XML":"Anexar Archivos");
    let ftitle=false;
    if (numFiles>0 && tgtFiles[0].name && tgtFiles[0].name.length>0) {
        if (numFiles>1) {
            fname=numFiles+" archivos";
            ftitle="";
            tgtFileArray.forEach((f, i)=>{if(i>0)ftitle+="\n";ftitle+=f.name;});
        } else {
            fname=tgtFiles[0].name;
            ftitle=fname;
        }
        if (fname.length>20) fname=fname.slice(0,9)+"..."+fname.slice(-8);
    } else console.log("Failed to change label");
    lbl.textContent = fname;
    if (ftitle) lbl.title=ftitle;
    else lbl.title=null;
    console.log("fname='"+fname+"'");
    console.log("ftitle='"+ftitle+"'");
}
function deleteRecentFileRow(ev) {
    const img=ev.target;
    console.log("INI FUNCTION deleteRecentFileRow",img);
    const cel=img.parentNode;
    const row=cel.parentNode;
    const bod=row.parentNode;
    const tbl=bod.parentNode;
    const cvV=bod.id.substr(0,7);
    const df=ebyid(cvV+"DocFile");
    console.log("DELETING RECENT FILE at row(index="+row.rowIndex+")="+row.textContent,"allFile=",df.allFiles[row.rowIndex],"allType=",df.allTypes[row.rowIndex]);
    delete df.allFiles[row.rowIndex];
    delete df.allTypes[row.rowIndex];
    // delete row;
    tbl.deleteRow(row.rowIndex);
    if(bod.rows.length==0)
        cladd(bod.parentNode,"hidden");
}
function openDocsDialog(evt) {
    const tgt=evt.target;
    console.log("INI openDocsDialog "+tgt.id);
    const cvV=tgt.id.substr(0,7);
    overlayClose();
    const selDTL=selFnc(cvV+"docTypeList","operacion",{"":"-Indicar tipo-",factura:"Factura"});
    selDTL.onchange=(evt)=>{
        const tgt=evt.target;
        const dfLbl=document.querySelector('label[for="'+cvV+'DocFile"]');
        const df=ebyid(cvV+"DocFile");
        console.log("Selected '"+tgt.value+"'",dfLbl);
        switch(tgt.value) {
            case "factura":
                clrem(dfLbl,"hidden");
                df.accept=".xml,.pdf";
                dfLbl.textContent="Anexar XML y PDF";
                break;
            case "":
                cladd(dfLbl,"hidden");
                dfLbl.textContent="Elegir Tipo";
                break;
            default:
                clrem(dfLbl,"hidden");
                df.accept=".pdf";
                dfLbl.textContent="Anexar PDF";
        }
    };
    const ifl = ebyid(cvV+"DocFile");
    const recentFileList=[];
    const dbSavedFileList=[];
    if (!ifl) console.log("NOT FOUND '"+cvV+"DocFile'");
    else {
        const rCells=[];
        rCells.ats={id:cvV+"RecentFileBody"};
        let nwTabAts={className:"noApply"};
        if (ifl.allFiles && ifl.allFiles.length>0) {
            console.log("MOSTRANDO NUEVOS: ",ifl.allTypes,ifl.allFiles);
            ifl.allFiles.forEach((f,fi)=>{
                let fnm=f.name;
                let fidx=fnm.lastIndexOf("/");
                if (fidx>0) fnm=fnm.slice(fidx+1);
                let ftt=fnm;
                if (ftt.length>100) ftt=ftt.slice(-99);
                fidx=fnm.lastIndexOf(".");
                if (fidx>0) fnm=fnm.slice(0,fidx);
                if (fnm.length>20) fnm=fnm.slice(0,9)+"..."+fnm.slice(-8);
                let fty=f.type;
                if (pdfTypes.includes(fty)) fty="PDF";
                else if (xmlTypes.includes(fty)) fty="XML";
                if (ifl.allTypes && ifl.allTypes.length>fi) fty=ifl.allTypes[fi]+" "+fty;
                let fsz=human_filesize(f.size);
                rCells.push(trFnc({id:"vwdocrw"+ftt},[tdFnc({title:ftt},fnm),tdFnc(fty),tdFnc(fsz),tdFnc([objFnc("IMG", {src:"imagenes/icons/crossRed.png", className:"file24 pointer", onclick:deleteRecentFileRow})])]));
            });
        } else {
            console.log("FOUND EMPTY NEW '"+cvV+"DocFile'");
            nwTabAts.className+=" hidden";
        }
        recentFileList.push(tabFnc([trFnc(false,[thFnc("Nombre"),thFnc("Tipo"),thFnc("Tamaño")])],rCells,false,nwTabAts));

        const dCells=[];
        dCells.ats={id:cvV+"SavedFileBody"};
        let dbTabAts={className:"noApply"};
        if (ifl.dbFiles && ifl.dbFiles.length>0) {
            console.log("MOSTRANDO VIEJOS: ",ifl.dbFiles,{docRoot:ifl.docRoot,imgRoot:ifl.imgRoot});
            if (ifl.delFiles) console.log("EXCEPTO BORRADOS: ",ifl.delFiles);
            ifl.dbFiles.forEach((f,fi)=>{
                if (ifl.delFiles && ifl.delFiles.includes(f.id)) return;
                let fnm=f.name;
                if (f.nombreOriginal) ftt=f.nombreOriginal;
                else {
                    let fidx=fnm.lastIndexOf("/");
                    if (fidx>0) ftt=fnm.slice(fidx+1);
                    if (ftt.length>100) ftt=ftt.slice(-99);
                }
                if (f["name.short"]) fnm=f["name.short"];
                else {
                    let fidx=fnm.lastIndexOf("/");
                    if (fidx>0) fnm=fnm.slice(fidx+1);
                    fidx=fnm.lastIndexOf(".");
                    if (fidx>0) fnm=fnm.slice(0,fidx);
                    if (fnm.length>20) fnm=fnm.slice(0,9)+"..."+fnm.slice(-8);
                }
                if (f.referencia&&ifl.docRoot&&f.titulo&&ifl.imgRoot&&f.key&&ifl[f.key+".src"]) {
                    fnm=[{eText:fnm+" "},objFnc("A", {href:ifl.docRoot+f.referencia, target: "archivo", title: f.titulo},[objFnc("IMG", {src:ifl.imgRoot+ifl[f.key+".src"], className:"file24"})])];
                } else {
                    console.log(f.referencia?"Referencia="+f.referencia:"No hay referencia");
                    console.log(ifl.docRoot?"DocRoot="+ifl.docRoot:"No hay docRoot");
                    console.log(f.titulo?"Titulo="+f.titulo:"No hay titulo");
                    console.log(f.key?"Key="+f.key:"No hay key ni key.src");
                    if (f.key) console.log(ifl[f.key+".src"]?"Key.src="+ifl[f.key+".src"]:"No hay key.src");
                }
                let fty="";
                if (f["type.ext"]) fty=f["type.ext"].toUpperCase();
                else if (f["type.finfo"]) fty=f["type.finfo"];
                else fty=f.type;
                if (pdfTypes.includes(fty)) fty="PDF";
                else if (xmlTypes.includes(fty)) fty="XML";

                let esOrden=false;
                if (f.docType) {
                    fty=f.docType+" "+fty;
                    esOrden=(f.docType==="Orden de Compra"); // f.idCatalogo==1
                } else if (f.titulo) fty=f.titulo+" "+fty;

                let fsz=f.size;
                if (f["size.fix"]) fsz=f["size.fix"];
                else fsz=human_filesize(fsz);
                const rowAction=esOrden?[]:[objFnc("IMG", {src:"imagenes/icons/crossRed.png", className:"file24 pointer",docId:f.id, onclick:evt=>{
                    const tgt=evt.target;
                    console.log("Deleting old file",tgt);
                    const cel=tgt.parentNode;
                    const row=cel.parentNode;
                    console.log("Row to delete",row);
                    const bod=row.parentNode;
                    const tbl=bod.parentNode;
                    const cvV=bod.id.substr(0,7);
                    console.log("Action Type="+cvV);
                    const df=ebyid(cvV+"DocFile");
                    if (df) {
                        if (!df.delFiles)
                            df.delFiles=[];
                        df.delFiles.push(tgt.docId);
                        console.log("Added to deleting list",df.delFiles);
                        // delete row;
                        tbl.deleteRow(row.rowIndex);
                    } else console.log("Not found DocFile to delete");
                }})];
                dCells.push(trFnc({id:"vwdocrw"+ftt},[tdFnc({title:ftt},fnm),tdFnc(fty),tdFnc(fsz),tdFnc(rowAction)]));
            });
        } else {
            console.log("FOUND EMPTY OLD '"+cvV+"DocFile'");
            dbTabAts.className+=" hidden";
        }
        dbSavedFileList.push(tabFnc([trFnc(false,[thFnc("Nombre"),thFnc("Tipo"),thFnc("Tamaño")])],dCells,false,dbTabAts));
    }
    overlayMessage(tabFnc(false,[
        rw2Fnc(false,"TIPO",false,[
            selDTL,
            objFnc("LABEL",{htmlFor:cvV+"DocFile",className:"asBtn forPDF marL2 hidden"},"Anexar Archivo")
        ]),
        rw2Fnc(false,"NUEVOS",false,recentFileList),
        rw2Fnc(false,"GUARDADOS",false,dbSavedFileList)
    ],false,{className:"noApply centered pad2cnw"}), "Listado de Documentos");

}
function doClean(elem) {
    let oldValues="";
    switch(elem.tagName) {
        case "SELECT": if(elem.selectedIndex==0) { break; } oldValues+=", selectedIndex="+elem.selectedIndex; elem.selectedIndex=0;
        case "INPUT": if (elem.type && elem.type.toUpperCase()==="BUTTON") break;
        case "TEXTAREA": if (!elem.value && !elem.defaultValue && elem.tagName!=="SELECT") { break; } oldValues+=", value='"+elem.value+"', defaultValue='"+elem.defaultValue+"'"; elem.value=""; elem.defaultValue="";
            console.log("INI doClean "+elem.tagName+" id="+elem.id+oldValues);
    }
}
function _isMissing(elem) {
    if (elem && elem.id) {
        switch(elem.tagName) {
            case "INPUT": const t=elem.type.toUpperCase();
                if (t==="TEXT") { /*console.log("EMPTY INPUT "+elem.id);*/
                    if (elem.value.length==0) return true;
                    if (elem.id.substr(7)==="Id" && elem.value.length<5) return true;
                }
                if (t==="NUMBER" && !elem.value) { /*console.log("ZERO INPUT "+elem.id);*/ return true; }
                if (t==="FILE" && !elem.files.length==0) { /*console.log("NO FILES "+elem.id);*/ return true; }
                break;
            case "SELECT": if (elem.selectedIndex==0 && elem.value.length==0) { /*console.log("EMPTY SELECT "+elem.id);*/ return true; }
        }
    }
    return false;
}
function isValueSaveable(elem) {
    const hasId=(elem&&elem.id);
    const sfx=hasId?elem.id.substr(7):"";
    const retVal = 
        hasId && 
        (   elem.tagName!=="INPUT" || 
            (   elem.type!=="file" &&
                (   elem.type!=="button" ||
                    sfx==="Curr"))) && 
        (   sfx==="Id" ||
            (   sfx==="Curr" &&
                elem.oldValue &&
                elem.oldValue!==elem.value) ||
            (   (   !elem.defaultValue ||
                    elem.defaultValue.length==0) &&
                !clhas(elem,"tipolista") &&
                elem.value.length>0) ||
            (   elem.defaultValue &&
                elem.defaultValue.length>0 &&
                elem.value!==elem.defaultValue));
    if (retVal) console.log("ELEM isValueSaveable id='"+elem.id+"'");
    else {
        if (!elem) console.log("ELEM ERROR: UNDEFINED or FALSEY, invalid check isValueSaveable");
        else if (!elem.id) console.log("ELEM ERROR: NO ID to validate isValueSaveable");
        else if (elem.tagName==="INPUT" && (elem.type==="file" || (elem.type==="button" && sfx!=="Curr"))) {
            console.log("ELEM AVOIDED TO SAVE: id="+elem.id+": NO isValueSaveable INPUT['"+elem.type+"']");
        } else {
            const elemId=elem.id;
            const elemTagType=elem.tagName+(elem.tagName==="INPUT"?"['"+elem.type+"']":"") + (clhas(elem,"tipolista") ? "|tipolista" : "");
            if (sfx==="Curr") console.log("Currency elem have not changed ("+elem.value+")");
            else {                
                const elemDfV=('defaultValue' in elem)?(elem.defaultValue?(elem.defaultValue===true?"true":(typeof elem.defaultValue === 'number'?elem.defaultValue:"'"+elem.defaultValue+"'")):(elem.defaultValue===0?"0":(elem.defaultValue===false?"false":(elem.defaultValue===null?"null":"empty")))):"undefined";
                const elemVal=('value' in elem)?(elem.value?(elem.value===true?"true":(typeof elem.value === 'number'?elem.value:"'"+elem.value+"'")):(elem.value===0?"0":(elem.value===false?"false":(elem.value===null?"null":"empty")))):"undefined";

                console.log("ELEM ERROR: NOT isValueSaveable id='"+elemId+"', tagType="+elemTagType+", defaultValue="+elemDfV+", value="+elemVal);
            }
        }
    }
    return retVal;
}
function isFileSaveable(elem) {
    return elem && elem.id && elem.id.substr(7)!=="DocFile" && elem.tagName==="INPUT" && elem.type==="file" && elem.files.length>0;
}
function comextAction(evt) {
    const tgt=evt ? evt.target : false;
    if (!tgt || tgt.blocked) return;
    tgt.blocked=true;
    setTimeout(t=>{if(t.blocked)t.blocked=false;},3000,tgt);
    const tgtId=tgt.id;
    const tgtPrefix=tgtId.substr(0, 7);
    const tgtPfxLen=7;
    const hasCode=["foreign", "customs"].includes(tgtPrefix);
    const tgtName=tgtId.substr(7);
    let tgtValue=tgt.value;
    let tgtValLen=tgtValue.length;
    const tgtType=tgt.type;
    const tgtTag=tgt.tagName;
    const tgtAction=(tgtType=="button") ? tgtValue : "Consulta";
    console.log("INI function comextAction. Event="+evt.type+", Prefix="+tgtPrefix+", Name="+tgtName+", Value="+tgtValue+", Type="+tgtType+" Action: "+tgtAction);
    let ppt=tgt.parentNode;
    while(ppt.parentNode && ppt.tagName!=="TBODY") ppt=ppt.parentNode;
    const saveElems=ppt.tagName==="TBODY"?ppt.querySelectorAll("input,select,textarea") : [];
    const elemId=(tgtName==="Id"?tgt:ebyid(tgtPrefix+"Id"));
    const hasElemIdValue = (elemId && elemId.value.length>0);
    elemId.isDeleted=false;
    if (tgtName==="CheckButton") {
        console.log("CHECK OK");
        if(tgt.blocked)tgt.blocked=false;
        return;
    }
    if (tgtAction==="Limpiar") {
        saveElems.forEach((e, i)=>doClean(e));
        cladd( [ tgtPrefix+"SaveButton", tgtPrefix+"DeleteButton", tgtPrefix+"BrowseButton" ], "hidden");
        if(tgt.blocked)tgt.blocked=false;
        return;
    } else if (tgtAction==="Eliminar") {
        if (!hasElemIdValue) {
            saveElems.forEach((e, i)=>doClean(e));
            cladd( [ tgtPrefix+"SaveButton", tgtPrefix+"DeleteButton", tgtPrefix+"BrowseButton" ], "hidden");
            if(tgt.blocked)tgt.blocked=false;
            return;
        }
        if (!tgt.confirmed) {
            if(tgt.blocked)tgt.blocked=false;
            overlayValidation("Está seguro que desea eliminar los datos del "+(hasCode?"proveedor":"registro")+"?", "CONFIRMAR", ()=>{
                tgt.confirmed=true;comextAction({target: tgt});return true;
            });
            const closeBtn = ebyid("closeButton");
            if (closeBtn) closeBtn.callOnClose=()=>{
                setTimeout((t,s)=>{if(t.confirmed){t.confirmed=false;s.forEach(e=>e.value=e.defaultValue);}},500,tgt,saveElems);
            };
            return;
        } else tgt.confirmed=false;
    } else if (tgtAction==="Consulta") {
        clset(tgtPrefix+"BrowseButton", "hidden", hasElemIdValue||tgtValLen>=5);
        if (hasCode) {
            if (tgtName==="Code") {
                tgtValue=tgtValue.toUpperCase();
                 // toDo: validar con REGEXP: LETRA+GUION+DIGITOx3+cualquier caracter adicional opcional
                if (tgtValLen>1 && tgtValue.charAt(1)!=="-") tgtValue=tgtValue.charAt(0)+"-"+tgtValue.substr(1);
                if (tgt.value!==tgtValue) tgt.value=tgtValue;
                if (tgtValLen<5) {
                    console.log("INCOMPLETE.");
                    if (hasElemIdValue) {
                        saveElems.forEach((e, i)=>{if (e!==tgt) doClean(e);});
                        cladd(tgtPrefix+"DeleteButton", "hidden");
                    }
                    clset(tgtPrefix+"BrowseButton", "hidden", tgtValLen==0);
                    cladd(tgtPrefix+"SaveButton", "hidden");
                    if(tgt.blocked)tgt.blocked=false;
                    return;
                }
            } else if (ebyid(tgtPrefix+"Code").value.length>0) {
                if (_isMissing(tgt)) cladd(tgtPrefix+"SaveButton", "hidden");
                if(tgt.blocked) tgt.blocked=false;
                return;
            }
        }
    } else if (tgtAction==="Guardar") {
        if (hasCode && hasElemIdValue) {
            const codeElem=ebyid(tgtPrefix+"Code");
            if (codeElem.value!==codeElem.defaultValue && !tgt.confirmed) {
                if(tgt.blocked)tgt.blocked=false;
                overlayValidation("Está seguro que desea cambiar el código de proveedor? Será reemplazado siempre y cuando no esté asignado a otro proveedor", "CONFIRMAR", ()=>{
                    tgt.confirmed=true;comextAction({target: tgt});return true;
                });
                const closeBtn = ebyid("closeButton");
                if (closeBtn) closeBtn.callOnClose=()=>{
                    setTimeout((t,s)=>{if(t.confirmed){t.confirmed=false;s.forEach(e=>e.value=e.defaultValue);}},500,tgt,saveElems);
                };
                return;
            }
        }
        tgt.confirmed=false;
    }
    const url = "consultas/ComExt.php";
    const parameters = {action: tgtAction, type: tgtPrefix};
    saveElems.forEach((e, i)=>{
        console.log("CHECK SAVE ELEM "+(i+1)+": "+e.id+"='"+e.value+"'",e);
        if (isValueSaveable(e)) {
            parameters[e.id]=e.value;
        } else if (isFileSaveable(e)) {
            if (e.files.length==1) parameters[e.id]=e.files[0];
            else if (e.files.length>1) parameters[e.id]=e.files;
        }
    });
    const df=ebyid(tgtPrefix+"DocFile");
    if (df) {
        const dataTransferObj = {};
        const selT=ebyid(tgtPrefix+"docTypeList");
        if (selT && selT.options && selT.options.length>0) {
            console.log("Checking docType options:");
            for (let o=0; o < selT.options.length; o++) {
                if (selT.options[o].value.length==0) {
                    continue;
                }
                dataTransferObj[selT.options[o].text]=new DataTransfer();
                console.log("Setting new DataTransfer for opt["+o+"] = "+selT.options[o].text);
            }
        } else console.log("Empty DocTypeList");
        if (df.allFiles) {
            df.allFiles.forEach((f,fi)=>{
                if(df.allTypes && df.allTypes.length>fi && dataTransferObj[df.allTypes[fi]]) {
                    console.log("Adding new file '"+f.name+"' to DataTransfer '"+df.allTypes[fi]+"' from file index "+fi);
                    dataTransferObj[df.allTypes[fi]].items.add(f);
                } else console.log("Missing AllTypes, or index "+fi+", or type '"+df.allTypes[fi]+"'",df.allTypes);
            });
            for (const typeKey in dataTransferObj) {
                console.log("Ready to add new file parameter from key '"+typeKey+"'");
                if (dataTransferObj.hasOwnProperty(typeKey)) {
                    console.log("Found dataTransfer property '"+typeKey+"'");
                    switch(typeKey) {
                        case "Factura": parameters[tgtPrefix+"InvFiles"]=dataTransferObj[typeKey].files;
                            console.log("Added parameter '"+tgtPrefix+"InvFiles' with "+(dataTransferObj[typeKey].files.length)+" files of type '"+typeKey+"'");
                            break;
                        default: console.log("Not added parameter for type '"+typeKey+"'");
                    }
                } else console.log("Not found dataTransfer property '"+typeKey+"'");
            }
        } else console.log("No new files added");
        if (df.delFiles && df.delFiles.length>0) {
            parameters[tgtPrefix+"DelDocIds"]=df.delFiles;
        } else console.log("No deleted files added");
        // toDo: Reflejar estos cambios en consultas
    } else console.log("No DocFile to save");
    console.log("URL:",url,"PARAMETERS:",parameters);
    readyService(url, parameters, (j, x, n)=> {
        if (j) console.log("Result "+JSON.stringify(j, jsonCircularReplacer()));
        if (x) console.log("EXTRA: "+JSON.stringify(x, jsonCircularReplacer()));
        if(tgt.blocked)tgt.blocked=false;
        overlayClose();
        if (!j.type && j.params.type) {
            j.type=j.params.type;
            console.log("TYPE ADJUST INTO J: "+j.type);
        }
        if (j.result && j.message) {
            switch(j.result) {
                case "success":
                    let doShow=false;
                    if (j.optdata) {
                        console.log("HAS OPTDATA "+j.type);
                        switch(j.type) {
                            case "group":
                                gpoDtL=j.optdata;
                                gpoRwF=(bas,cntats)=>entRwF(bas,"Empresa","Gpo","grupo",gpoDtL,cntats);
                                break;
                            case "foreign":
                                extDtL=j.optdata;
                                extRwF=(bas,cntats)=>entRwF(bas,"Proveedor","Ext","extranjero",extDtL,cntats);
                                break;
                            case "customs": 
                                agtDtL=j.optdata;
                                agtRwF=(bas,cntats)=>entRwF(bas,"Agente","Agt","agente",agtDtL,cntats);
                                break;
                        }
                    }
                    if (tgtAction==="Consulta") {
                        const pln=j.type.length;
                        let pfx=false;
                        let elem=false;
                        for (let pKy in j) {
                            pfx=pKy.slice(0,pln);
                            if (pfx===j.type) {
                                elem=ebyid(pKy);
                                if (elem) {
                                    elem.value=j[pKy];
                                    elem.defaultValue=elem.value;
                                }
                        }    }
                    } else if (tgtAction==="Guardar") {
                        console.log("SAVING "+(j.insertId?"NEW REGISTRY":(j.data.id?"OLD REGISTRY":"UNDETERMINED REGISTRY"))+" OF "+j.type+(j.insertId||j.data.id?": "+(j.insertId||j.data.id):""))
                        if (j.type==="nvoexpd") j.type="vwexpdn";
                        if (j.type==="vwexpdn") {
                            overlayClose();
                            comextOpt("ope",j.data);
                        }
                        overlayMessage(j.message, "Guardado exitoso");
                    } else if (tgtAction==="Eliminar") {
                        if (j.type==="foreign"||j.type==="customs") {
                            saveElems.forEach((e, i)=>doClean(e));
                        }
                        const idEl=ebyid(j.type+"Id");
                        if (idEl) {
                            idEl.value="";
                            cladd(j.type+"DeleteButton","hidden");
                        }
                        overlayMessage(j.message, "Borrado exitoso ");
                    } else {
                        overlayMessage(j.message, (tgtAction==="Eliminar" ? "Borr" : "Guard")+"ado exitoso");
                    }
                    console.log("DONE SAVING AND DOING BTNCHECK TO "+j.type+"Id");
                    btnCheck( { target: ebyid(j.type+"Id"), action: tgtAction } );
                    break;
                case "confirm":
                    if (tgtAction==="Consulta") {
                        overlayValidation(j.message, "CONFIRMAR", ()=> {
                            clrem(tgt,"defaultOnReject");
                            const saveBtn=ebyid("accept_overlay");
                            if (saveBtn.jobj) {
                                const pln=j.type.length;
                                for(let pKy in saveBtn.jobj) {
                                    if (pKy.slice(0, pln)===j.type) {
                                        let elem=ebyid(pKy);
                                        if (elem) {
                                            elem.value=saveBtn.jobj[pKy];
                                            elem.defaultValue=elem.value;
                                        }
                            }   }   }
                            btnCheck( { target: ebyid(j.type+"Id") } );
                        });
                        if (j.defaultOnReject) cladd(tgt,"defaultOnReject");
                        setTimeout(prepareConfirmation, 30, j, 30);
                    }
                    break;
                case "error":
                    if (j.type==="vwexpdn" && tgtAction==="Guardar" && j.validFileKeys) {
                        const df=ebyid(j.type+"DocFile");
                        if (df.allFiles)
                            df.allFiles.forEach((f,fi)=>{
                                const fname=f.name.slice(0,-4);
                                if (!j.validFileKeys.includes(fname)) {
                                    console.log("Removing invalid files '"+fname+"' from recent list");
                                    delete df.allFiles[fi];
                                    delete df.allTypes[fi];
                                }
                            });
                    }
                    overlayMessage(j.message, "ERROR", false, true);
                    btnCheck({target: ebyid(j.type+"Id") } );
                    break;
                case "warning":
                    const pln=j.type.length;
                    const elId=ebyid( j.type+"Id");
                    if (j.isDeleted) elId.isDeleted=true;
                    for (let pKy in j) {
                        if (pKy.slice(0, pln)===j.type) {
                            let elem=ebyid(pKy);
                            if (elem) {
                                elem.value=j[pKy];
                                elem.defaultValue=elem.value;
                            }
                    }    }
                    btnCheck( {target: elId} );
                    if (j.dataWarning && !j.ignoreWarning) overlayMessage(j.dataWarning, "ERROR", false, true);
                    break;
                case "empty": 
                    console.log("EMPTY "+j.type+": "+j.message);
                    btnCheck( { target: ebyid(j.type+"Id") } );
                    if (tgtAction==="Consulta") {
                        if (tgtName==="Code" && !hasElemIdValue) {
                            const txEl=ebyid("foreignTaxid");
                            if (txEl && txEl.value.length>0) {
                                console.log("KEEP VALUES");
                                break;
                            }
                            const rfEl=ebyid("customsRfc");
                            if (rfEl && rfEl.value.length>0) {
                                console.log("KEEP VALUES");
                                break;
                            }
                        }
                        const pln=j.type.length;
                        let pfx=false, sfx=false, elem=false, numFixes=0, numEmpty=0;
                        for (let pKy in j) {
                            pfx=pKy.slice(0,pln);
                            sfx=pKy.slice(pln);
                            if (pfx===j.type) {
                                elem=ebyid(pKy);
                                if (elem) {
                                    if (j[pKy].length==0) {
                                        numEmpty++;
                                        if(!elem.value) continue;
                                    } else if (elem.value===j[pKy]) continue;
                                    numFixes++;
                                    console.log("Key:"+pKy+", value='"+elem.value+"'=>'"+j[pKy]+"', defaultValue='"+(elem.defaultValue?elem.defaultValue:"")+"'");
                                    elem.value=j[pKy];
                                } else
                                    console.log("Key:"+pKy+": no change");
                        }   }
                        if (numEmpty>0) {
                            console.log("HIDE BUTTONS: "+j.type+"DeleteButton,"+j.type+"SaveButton");
                            cladd(j.type+"DeleteButton","hidden");
                            cladd(j.type+"SaveButton","hidden");
                        } else if (numFixes>0) {
                            console.log("SHOW BUTTONS: "+j.type+"DeleteButton,"+j.type+"SaveButton");
                            clrem(j.type+"DeleteButton","hidden");
                            clrem(j.type+"SaveButton","hidden");
                        } else console.log("DO NOTHING:"+j.type);
                    }
                    break;
                default: console.log(j.result.toUpperCase()+": "+j.message); btnCheck( { target: ebyid(j.type+"Id") } );
        }   }
    }, (e, t, x)=> {
        if(tgt.blocked)tgt.blocked=false;
        console.log("ReadyService error function: ");
        showOnError(e, t, x);
        btnCheck( { target: ebyid(x.type+"Id") } );
    });
}
function delFieldBox(evt) {
    let tgt=evt.target;
    let par=tgt.parentNode;
    const idBase=par.id.slice(0,7);
    setTimeout(filterBrowse,10,ebyid(idBase+"FilterSummary"));
    ekil(par);
}
function filterCheckDown(evt) {
    const tgt=evt.target;
    if (isEnterKey(evt)&&tgt.selectedIndex>0) {
        filterCheck(evt);
        evt.preventDefault();
    }
}
function filterCheck(evt) {
    let tgt=evt.target;
    const isImage=(tgt.tagName==="IMG");
    let isSelect=(tgt.tagName==="SELECT");
    let isInput=(tgt.tagName==="INPUT");
    let imgId="";
    if (isImage) {
        imgId=tgt.id.substr(7);
        if (imgId.substr(0,7)==="LoadImg") imgId=imgId.substr(7);
        while(clhas(tgt,"loadImg")) tgt=tgt.previousElementSibling;
        isSelect=(tgt.tagName==="SELECT");
        isInput=(tgt.tagName==="INPUT");
    }
    const tgtId=tgt.id;
    const tgtFld=tgt.fld;
    const tgtName=tgt.name.toUpperCase();
    let tgtVal=tgt.value;
    const idBase=tgtId.slice(0,7);
    const tgtIdName=tgtId.slice(7);
    console.log("INI filterCheck",{isImage:(isImage?imgId:"FALSE"), eventType:evt.type,eventTarget:evt.target,fieldElement:tgt,tgtId:tgtId,tgtFld:tgtFld,tgtName:tgtName,tgtVal:tgtVal,idBase:idBase,tgtIdName:tgtIdName});
    if (isImage || isSelect || (isInput && tgtVal.length>0 && evt.type==="keydown" && isEnterKey(evt))) {
        // toDo: check in FilterSummary if filterTextBlock with respective id exists, else add it in
        const fsCnt=ebyid(idBase+"FilterSummary");
        const elId=idBase+"FltSel"+tgtIdName+imgId;
        const el=fsCnt.querySelector("#"+elId);
        if (el) ekil(el);
        if (imgId.length==1) {
            switch(imgId) {
                case "S": ekil([idBase+"FltSel"+tgtIdName+"L", idBase+"FltSel"+tgtIdName+"G"]); break;
                case "L":
                case "G": ekil(idBase+"FltSel"+tgtIdName+"S"); break;
            }
        }
        if (isSelect) {
            if (tgt.selectedIndex==0) {
                if (fsCnt.children.length==0)
                    ekfil([idBase+"OpList",idBase+"OpCount",idBase+"OpSum"]);
                else setTimeout(filterBrowse,10,fsCnt);
                return;
            }
            tgtText=tgt.options[tgt.selectedIndex].text;
        } else tgtText=tgtVal;
        if (tgtIdName==="Date") tgtVal=tgtVal.slice(6)+"-"+tgtVal.slice(3,5)+"-"+tgtVal.slice(0,2);
        fsCnt.appendChild( ecrea( objFnc( "SPAN", { id:elId, className:"fldBox", fld:tgtFld, val:tgtVal }, [ {eText:tgtName+(imgId==="L"?"<":(imgId==="G"?">":"="))+tgtText}, objFnc( "IMG", { className:"closeFld", src:"imagenes/icons/crossRed.png", onclick:delFieldBox })])));
        setTimeout(filterBrowse,10,fsCnt);
    } else {
        if (!isImage) console.log("NOT IMAGE EVENT: ", { type:evt.type, tagName:tgt.tagName, tgtVal:tgtVal, isEnterKey:isEnterKey(evt)?"true":"false" });
        clset(idBase+"LoadImg","invisible",tgtVal.length==0);
    }
}
function filterBrowse(fsCnt) {
    console.log("INI filterBrowse "+fsCnt.id+" : "+fsCnt.textContent);
    const fieldList={};
    const idBase=fsCnt.id.slice(0,7);
    if (fsCnt.children.length>0) {
        fee(fsCnt.children,i=>{
            fieldList[i.fld]=i.val.split(",").map(function (value) { return value.trim(); });
        });
        console.log("FieldList: ", fieldList);
        readyService("consultas/ComExt.php",{action:"Lista",list:fieldList},(j, x, n)=>{
            if (j.result==="success") {
                console.log("RESULT: "+JSON.stringify(j, jsonCircularReplacer()));
                ekfil([idBase+"OpList",idBase+"OpCount",idBase+"OpSum"]);
                if (j.data && j.data.length>0) {
                    const dNum=j.data.length; const d_pl_s=(dNum==1)?"":"s";
                    clrem([idBase+"OpResults",idBase+"OpReview"],"hidden");
                    const ol=ebyid(idBase+"OpList");
                    j.data.forEach(row=>{
                        console.log(row);
                        const rowAts={id:cvV+"Op"+row.id, className:"filterRow", data:row};
                        ol.appendChild(ecrea(trFnc(rowAts,[
                            filterCell(row["fechaAlta.calendarValue"],"fechaAlta.calendarValue"),
                            filterCell(row["grupo.alias"],"grupo.alias"),
                            filterCell(row["proveedor.razonSocial"],"proveedor.razonSocial"),
                            filterCell(row["operacion.nombre"],"operacion.nombre"),
                            filterCell(row.folio,"folio"),
                            filterCell(row.orden,"orden"),
                            /*filterCell(row.pedimento,"pedimento"),*/
                            filterCell(row["agente.codigo"],"agente.codigo"),
                            filterCell(row["importe.visible"],"importe.visible",{className:"righted"}),
                            /*filterCell(row.descripcion,"descripcion"),*/
                            filterCell(row["status.desc"],"status.desc")])));});
                    const oc=ebyid(idBase+"OpCount");
                    oc.textContent=(dNum)+" registro"+d_pl_s+" encontrado"+d_pl_s;
                    if (j.totalVisible) {
                        const os=ebyid(idBase+"OpSum");
                        os.textContent="Total: "+j.totalVisible;
                    }
                } else {
                    cladd([idBase+"OpResults",idBase+"OpReview"],"hidden");
                }
            } else {
                overlayMessage(j.message,j.result.toUpperCase());
                console.log("Ready: ",j);
            }
        }, (e, t, x)=>{
            console.log("Error: ",e);
            showOnError(e, t, x);
        });
    } else {
        console.log("NO BROWSE WITH ZERO FILTERS");
        ekfil([idBase+"OpList",idBase+"OpCount",idBase+"OpSum"]);
        cladd([idBase+"OpResults",idBase+"OpReview"],"hidden");
    }
}
function pickRow(evt) {
    let tgt=evt.target;
    while (tgt && tgt.tagName!=="TR") tgt=tgt.parentNode;
    if (!tgt || tgt.tagName!=="TR") return;
    const idBase=tgt.id.substr(0,7);
    if (evt.type==="dblclick") {
        if(clhas(tgt,"opening")) return;
        cladd(tgt,"opening");
        openExp(tgt);
        fee(lbycn("filterRow"),r=>clrem(r,"selected"));
        cladd(idBase+"OpOpen","hidden");
        return;
    }
    if (clhas(tgt,"selected")) {
        fee(lbycn("filterRow"),r=>clrem(r,"selected"));
        cladd(idBase+"OpOpen","hidden");
        return;
    }
    let txt=tgt.className+": ";
    fee(tgt.children,(c,i)=>{if(i>0)txt+=", ";txt+=c.textContent;});
    console.log("INI pickRow "+evt.constructor.name+", "+evt.type+": "+txt);
    fee(lbycn("filterRow"),r=>clrem(r,"selected"));
    if (tgt && tgt.tagName==="TR") {
        cladd(tgt,"selected");
        clrem(idBase+"OpOpen","hidden");
    }
}
function openSelected(evt) {
    let tgt=evt.target;
    const idBase=tgt.id.substr(0,7);
    const selRow=lbycn("selected",tgt.parentNode.parentNode.parentNode.parentNode);
                //  button|td        |tr        |tfoot     |table
    if (selRow.length>0) openExp(selRow[0]);
}
function openExp(evt) {
    let tgt=false;
    clearSelection();
    if (evt.target) tgt=evt.target; else tgt=evt;
    if (tgt && tgt.tagName) while(tgt.tagName!=="TR" && tgt.parentNode) tgt=tgt.parentNode;
    if (!tgt || tgt.tagName!=="TR"||!tgt.data) {
        if (!tgt) console.log("ERROR: Row not identified",evt);
        else if (tgt.tagName!=="TR") console.log("ERROR: Failed to locate row",tgt);
        else console.log("ERROR: No data found",tgt);
        return;
    }
    comextOpt("ope",tgt.data);
    // toDo: save filter results, when browse is opened again should show last browse options 
}
function fixCheck(evt) {
    const tgt=evt.target?evt.target:evt;
    console.log("INI fixCheck "+tgt.id+": '"+tgt.value+"' <= '"+tgt.defaultValue+"'");
}
function searchOpBy(evt) {
    let tgt=evt.target;
    const idBase=tgt.id.substr(0, 7);
    const isImage=(tgt.tagName=="IMG");
    if (isImage) {
        tgt=ebyid(idBase+"FilterList");
    }
    const tval=tgt.value;
    const tidx=tgt.selectedIndex;
    const tva2=tgt.options[tgt.selectedIndex].value;
    const ttxt=tgt.options[tgt.selectedIndex].text;
    console.log("INI searchOpBy "+tva2+": '"+ttxt+"'");
    const fltFlds=ebyid(idBase+"FilterFields");
    ekfil(fltFlds,[idBase+"LoadImg", idBase+"LoadImgG", idBase+"LoadImgL", idBase+"LoadImgS"]);
    const fltOpts=ebyid(cvV+"FilterOptions");
    let ltv=document.querySelector('input[name='+idBase+'Tipolista]:checked').value;
    switch(tva2) {
        case "ord": // Orden : Numero de orden
            cladd(fltOpts,"invisible");
            fltFlds.insertBefore(ecrea( inpFnc(idBase+"Orden", "text", filterCheck, "folio filterValue", {autofocus: true, name:"Orden", fld: "orden", onkeydown:filterCheck})), fltFlds.firstChild);
            break;
        case "fol": // Folio : input abierto (podría hacerse inteligente autocompletable)
            cladd(fltOpts,"invisible");
            fltFlds.insertBefore(ecrea( inpFnc(idBase+"Folio", "text", filterCheck, "folio filterValue", {autofocus: true, name:"Folio", fld: "folio", onkeydown:filterCheck})), fltFlds.firstChild);
            break;
        /*case "ped": // Pedimento : input abierto (podría hacerse inteligente autocompletable)
            cladd(fltOpts, "invisible");
            fltFlds.insertBefore(ecrea( inpFnc(idBase+"Pedimento", "text", filterCheck, "folioV filterValue", {autofocus: true, name:"Pedimento", fld: "pedimento"})), fltFlds.firstChild);
            break;*/
        case "emp": // Empresa : select de empresas del corporativo
            clrem(fltOpts,"invisible");
            fltFlds.insertBefore(ecrea(objFnc("SELECT",{id:idBase+"Grupo",className:"comext_fixedSelect filterValue",name:"Empresa",ltype:"<?= $optDefaultValue ?>",onchange:filterCheck,onkeydown:filterCheckDown, fld: "grupoId"},gpoDtL)),fltFlds.firstChild);
            {
                const fx=ebyid(idBase+"Grupo");
                if (ltv && ltv!=="<?= $optDefaultValue ?>") fixSelectorBy(fx, ltv);
                fx.options[0].text="Selecciona...";
                setTimeout((x)=>{x.focus();},10,fx);
            }
            break;
        case "pro": // Proveedor : select de proveedores externos
            clrem(fltOpts,"invisible");
            fltFlds.insertBefore(ecrea(objFnc("SELECT",{id:idBase+"Extranjero",className:"comext_fixedSelect filterValue",name:"Proveedor",ltype:"<?= $optDefaultValue ?>",onchange:filterCheck,onkeydown:filterCheckDown,fld: "proveedorId"},extDtL)),fltFlds.firstChild);
            {
                const fx=ebyid(idBase+"Extranjero");
                if (ltv && ltv!=="<?= $optDefaultValue ?>") fixSelectorBy(fx, ltv);
                fx.options[0].text="Selecciona...";
                setTimeout((x)=>{x.focus();},10,fx);
            }
            break;
        case "aga": // Agente Aduanal : select de proveedores agentes
            clrem(fltOpts,"invisible");
            fltFlds.insertBefore(ecrea(objFnc("SELECT",{id:idBase+"Agente",className:"comext_fixedSelect filterValue",name:"Agente",ltype:"<?= $optDefaultValue ?>",onchange:filterCheck,onkeydown:filterCheckDown,fld: "agenteId"},agtDtL)),fltFlds.firstChild);
            {
                const fx=ebyid(idBase+"Agente");
                if (ltv && ltv!=="<?= $optDefaultValue ?>") fixSelectorBy(fx, ltv);
                fx.options[0].text="Selecciona...";
                setTimeout((x)=>{x.focus();},10,fx);
            }
            break;
        case "sta": // Status : select de status (En proceso, en proceso con anticipos, transferida (importada/exportada), pagada, cerrada, auditada)
            cladd(fltOpts,"invisible");
            fltFlds.insertBefore(ecrea(objFnc("SELECT",{id:idBase+"Status",name:"Status",className:"comext_status filterValue",onchange:filterCheck,onkeydown:filterCheckDown, fld:"status"},optLst(vds,vks))),fltFlds.firstChild);
            {
                const fx=ebyid(idBase+"Status");
                setTimeout((x)=>{x.focus();},10,fx);
            }
            break;
        /*case "rdf": // Rango de fecha : input de fecha readonly con dropdown de calendario
            cladd(fltOpts,"invisible");
            fltFlds.insertBefore(ecrea(inpFnc(idBase+"Date", "text", false, "calendarV padv02 noprintBorder clearable", {name:"Fecha", fld:"date(fechaAlta)", onchange:filterCheck, onclick:(evt)=>{ show_calendar_widget(evt.target); }, readOnly:true, value:"<?= $fmtDay ?>"})),fltFlds.firstChild);
            break;*/
        case "fda": // Fecha de alta : input de fecha readonly con dropdown de calendario y 3 botones para elegir menor, igual o mayor (buscará registros menores, iguales o mayores a la fecha indicada. Hay que permitir que se agreguen hasta dos registros para establecer un rango | o a la mejor permitir más y que todos se cumplan para permitir establecer varios rangos... | se deben mostrar los 3 botones y ocultar el general)
            cladd(fltOpts,"invisible");
            cladd()
            fltFlds.insertBefore(ecrea(inpFnc(idBase+"Date", "text", false, "calendarV padv02 noprintBorder clearable", {name:"Fecha", fld:"date(fechaAlta)", onchange:filterCheck, onclick:(evt)=>{ show_calendar_widget(evt.target); }, readOnly:true, value:"<?= $fmtDay ?>"})),fltFlds.firstChild);
            break;
        case "tdo": // Tipo de Operación : select de operacion (importacion, importacion de activos, exportacion)
            cladd(fltOpts,"invisible");
            fltFlds.insertBefore(ecrea(objFnc("SELECT",{id:idBase+"TipoOperacion",className:"comext_status filterValue",name:"Operacion",onchange:filterCheck,onkeydown:filterCheckDown, fld: "tipoOperacion"},optLst(tiposDeOperacion,ktdo))),fltFlds.firstChild);
            {
                const fx=ebyid(idBase+"TipoOperacion");
                setTimeout((x)=>{x.focus();},10,fx);
            }
            break;
        default:
            cladd(fltOpts,"invisible");
    }
    if (tva2==="rdf") clrem(idBase+"LoadImg",["invisible","hidden"]);
    else if (!isImage)
        cladd(idBase+"LoadImg",(tva2==="fda")?"hidden":"invisible");
    if (tva2==="fda") clrem([idBase+"LoadImgG", idBase+"LoadImgL", idBase+"LoadImgS"],["invisible","hidden"]);
    else if (!isImage) {
        cladd(idBase+"LoadImgS","invisible");
        cladd([idBase+"LoadImgG", idBase+"LoadImgL"],"hidden");
    }
    focusOnAutoFocus();
}
function prepareConfirmation(jo,t) {
    //console.log("INI prepareConfirmation "+t);
    const target=jo.target;
    const saveBtn=ebyid("accept_overlay");
    if (saveBtn) {
        saveBtn.jobj=jo;
        const ovy=ebyid("overlay");
        ovy.callOnClose= ()=>{
            focusOnAutoFocus();
            fee(lbycn("defaultOnReject"),e=>{e.value=e.defaultValue;clrem(e,"defaultOnReject");});
        };
    } else if (t<1000) setTimeout(prepareConfirmation, 10, jo, t+10);
}
function fixSelectorBy(sel, type) {
    if (!sel) return;
    const atttype=sel.ltype;
    if (!atttype) return;
    if (atttype===type) return;
    const val=sel.value;
    const o=sel.options[sel.selectedIndex];
    const tmp= [...sel.options];
    tmp.forEach(item=>item.text=item[type]);
    tmp.sort((a, b) => {
        if(a.value.length==0 || (b.value.length>0 && a.text <= b.text)) return -1;
        return 1;
    });
    ekfil(sel);
    sel.append(...tmp);
    if (sel.ltype==="razon"||type==="razon") sel.title=((o&&o.value.length>0) ? (type==="razon" ? o.codigo + " " + ((o.rfc.substr(0,4)==="XEXX") ? "" : " " + o.rfc) : o.razon) : "");
    sel.ltype=type;
    sel.value=val;
}
function pickType(tchk) {
    if (!tchk || !tchk.value) { /*console.log("INI FUNCTION PICKTYPE NULL");*/ return; }
    const idBase=tchk.id.substr(0, 7);
    fee(lbycn("comext_fixedSelect"),s=>fixSelectorBy(s, tchk.value));
}
<?php
if (isset($_SESSION ["comextChoice"][0])) echo "comextTimeout=setTimeout(comextOpt2, 100, \"$_SESSION [comextChoice]\");"; // console.log('SESSION with comextChoice');
else {
    echo "comextTimeout=setTimeout(initMenu, 100);"; //  comextOpt2('prv');
}
clog1seq(-1);
clog2end("scripts.comext");
