<?php
require_once dirname(__DIR__)."/bootstrap.php";
header("Content-type: application/javascript; charset: UTF-8");
clog2ini("scripts.ecreaShortcuts");
clog1seq(1);
?>
const cpyAts = (oriObj,desObj,pfx)=>{if(!desObj)desObj={};if(oriObj){for(let att in oriObj){let kda=pfx?pfx+att.charAt(0).toUpperCase()+att.slice(1):att;desObj[kda]=oriObj[att];}}return desObj;};
const objFnc = (name,atts,childs)=>{if(!childs&&atts&&(Array.isArray(atts)||(typeof atts!=="object"))){childs=atts;atts=false;}const rt=cpyAts(atts,{eName:name});if(childs){const tCh=typeof childs;if(tCh==="array"||(tCh==="object"&&Array.isArray(childs)))rt.eChilds=childs;else rt.eText=childs;}return rt;};
const tabFnc = (hrws,brws,frws,tats)=>{const tch=[];if(hrws)tch.push(objFnc("THEAD",hrws.ats,Array.isArray(hrws)?hrws:false));if(brws)tch.push(objFnc("TBODY",brws.ats,Array.isArray(brws)?brws:false));if(frws)tch.push(objFnc("TFOOT",frws.ats,Array.isArray(frws)?frws:false));return objFnc("TABLE",tats,tch);};
<?php /* const inpFnc=(idv,typ,fnc,cls,att)=>{const ats={id:idv,type:typ};if(fnc)ats.oninput=fnc;if(cls)ats.className=cls;return objFnc("INPUT",cpyAts(att,ats));}; */ ?>
const inpFnc=(idv,typ,fnc,cls,att)=>{if(!att)att={};att.id=idv;att.type=typ;if(fnc)att.oninput=fnc;if(cls)att.className=cls;return objFnc("INPUT",att);};
<?php /* const btnFnc=(idv,val,clk,cls,ats)=>{if(!ats)ats={};ats.type="button";ats.id=idv;ats.value=val;if(cls)ats.className=cls;objFnc("INPUT",ats);}; */ ?>
const btnFnc=(idv,val,clk,cls,ats)=>{if(!ats)ats={};ats.value=val;if(clk)ats.onclick=clk;inpFnc(idv,"button",false,cls,ats);};
<?php /* const radFnc=(bas,idT,nam,bval)=>objFnc("INPUT",{type:"radio",id:bas+nam[0]+idT,name:bas+nam,value:idT,className:"wid8px marT0i marL4i marR2i vAlignCenter "+nam.toLowerCase(),checked:!!bval}); */ ?>
const radFnc=(bas,idT,nam,bval)=>inpFnc(bas+nam[0]+idT,"radio",false,"wid8px marT0i marL4i marR2i vAlignCenter "+nam.toLowerCase(),{name:bas+nam,value:idT,checked:!!bval});
const rdTFnc=(bas,idT,bval)=>radFnc(bas,idT,"Tipolista",bval);
const optLst=(map,kys)=>{ const opts=[]; if(!kys)kys=Object.getOwnPropertyNames(map); kys.forEach(k=>{ if(map[k]) opts.push(objFnc("OPTION",{value:k},map[k])); }); return opts; };
const selFnc=(idv,cls,opts,ats)=>objFnc("SELECT",cpyAts({id:idv,className:cls,onchange:btnCheck},ats),optLst(opts));
//const imgFnc=()
const thFnc=(atts,childs)=>{return objFnc("TH",atts,childs);};
const tdFnc=(atts,childs)=>{return objFnc("TD",atts,childs);};
const trFnc=(atts,childs)=>{return objFnc("TR",atts,childs);};
const rw2Fnc=(rowats,cap,capats,cnt,cntats)=>{const cells=[];if(cap!==false&&cap!==null)cells.push(thFnc(capats,cap));cells.push(tdFnc(cntats,cnt));return trFnc(rowats,cells);};

const filterCell=(childs,rowId,ats)=>tdFnc(cpyAts({onclick:pickRow, ondblclick:pickRow, rowId:rowId},ats),childs);

<?php /*
const codeRwF=bas=>rw2Fnc(false,"Código",false,[inpFnc(bas+"Id","hidden"),inpFnc(bas+"Code","text",comextAction,"autoFocus",{maxLength:9,autofocus:true})]);
const nameRwF=(bas,idT,nam,fnc,atti)=>rw2Fnc(false,nam,false,[inpFnc(bas+idT,"text",fnc,false,atti)]);
const dateRwF=bas=>rw2Fnc(false,"Fecha Actual",false,[inpFnc(bas+"Date","text",false,"calendarV padv02 noprintBorder clearable",{onchange:btnCheck,readOnly:true,value:"< ?= $fmtDay ? >"})],{className:"lefted"});
const btnRwF=bas=>rw2Fnc(false,false,false,[btnFnc(bas+"DeleteButton","Eliminar"),btnFnc(bas+"SaveButton","Guardar")],{colSpan:"2"}); // ,btnFnc(bas+"CheckButton","X")
const entRwF=(bas,cap,key,nam,opts)=>rw2Fnc(false,cap,{className:"capcell"},[objFnc("SELECT",{id:bas+key+"Id",className:"comext_fixedSelect",onchange:btnCheck,name:nam,ltype:"< ?= $optDefaultValue ? >"},opts)],{id:bas+key+"Cell",className:"lefted nohover"});
*/ ?>
<?php /*
const radRwF=bas=>rw2Fnc(false,"",false,[rdTFnc(bas,"codigo",< ?=b2s($esOptCodigo)? >),{eText:"Código"},rdTFnc(bas,"rfc",< ?=b2s($esOptRFC)? >),{eText:"RFC"},rdTFnc(bas,"razon",< ?=b2s($esOptRazon)? >),{eText:"Razón Social"}],{className:"fontSmall noApply nohover lefted"});
*/ ?>
<?php
clog1seq(-1);
clog2end("scripts.ecreaShortcuts");

/*
HTML TOOLS
----------

FUNCION cpyAts(oriObj,desObj,pfx) : Copia propiedades de un objeto a otro
 + oriObj es el objeto origen, donde estan las propiedades a copiar. Si se omite se regresa desObj
 + desObj es el objeto destino, donde se van a agregar nuevas propiedades. Si se omite se crea un objeto nuevo
 + pfx es un prefijo a añadir al nombre de las propiedades copiadas. La primer letra del nombre original se cambia a mayúscula.

FUNCION objFnc(name,atts,childs) : Crea un objeto con las características de un componente html que aun requiere sea interpretado e instanciado a un elemento html con la funcion ecrea(obj)
 * Funcion sobrecargada (name, childs)
 + name es el tagName del elemento deseado
 + atts es un objeto cuyas propiedades representan los atributos del elemento a crear
 + childs son subelementos contenidos dentro del elemento a crear. Si no es arreglo se interpreta como texto.

FUNCION tabFnc(hrws,brws,frws,tats) : Crea un elemento TABLE
 + hrws contiene datos de encabezado THEAD, si es un arreglo contiene los subelementos a incluir. Si tiene propiedad ats se extrae como lista de propiedades del contenedor.
 + brws contiene datos de cuerpo TBODY, si es un arreglo contiene los subelementos a incluir. Si tiene propiedad ats se extrae como lista de propiedades del contenedor.
 + frws contiene datos de pie TFOOT, si es un arreglo contiene los subelementos a incluir. Si tiene propiedad ats se extrae como lista de propiedades del contenedor.
 + tats es un objeto cuyas propiedades se agregan al elemento TABLE

FUNCION inpFnc(idv,typ,fnc,cls,att) : Crea un elemento INPUT
 + idv es el valor de la propiedad requerida id
 + typ es el valor de la propiedad requerida type
 + fnc es una función anónima opcional que se liga al trigger oninput
 + cls es el valor de la propiedad opcional className
 + att es un objeto cuyas propiedades se agregan al elemento INPUT

FUNCION btnFnc(idv,val,clk,cls,ats) : Crea un elemento INPUT tipo BUTTON
 + idv se asigna al atributo id
 + val se asigna al atributo value
 + clk se asigna opcionalmente al atributo de evento onclick
 + cls se asigna al atributo class
 + ats es un objeto cuyas propiedades se agregan al elemento input

FUNCION radFnc(bas,idT,nam,bval) : Crea un elemento input de tipo RADIO ajustado para elegir valor de empresa del corporativo o proveedor
 + bas es un prefijo para englobar componentes de un mismo recuadro funcional
 + idT es el nombre central del id que identifica el propósito del elemento y además se asigna al atributo value
 + nam es una característica que identifica a un grupo de elementos radio. Los 3 elementos bas, idT y nam se conjuntan para formar el valor del atributo id. Los elementos bas y nam se juntan para formar el valor del atributo name. Tambien se agrega como clase del elemento
  + bval es el estado del elemento, si está seleccionado o no

FUNCION rdTFnc(bas,idT,bval) : Llama la funcion radFnc especificando el parametro nam="Tipolista". Los demás parámetros se pasan tal cual de los argumentos de esta función (ver radFnc)

FUNCION optLst(map,kys) : Crea una lista de elementos OPTION
 + map es un objeto cuyos nombres de propiedades se asignan a los atributos VALUE y los valores a los atributos al texto.
 + kys es un arreglo de nombres de claves, si corresponden a una propiedad en map se crea el elemento OPTION. Si no se especifica un argumento kys, se usan todas las propiedades de map. La lista se crea con los nombres en kys que correspondan a propiedades en map con sus respectivos valores.

FUNCION selFnc(idv,cls,opts,ats) : Crea un elemento SELECT
 + idv es el id del elemento
 + cls es la cadena de clases del elemento
 + opts es el arreglo de objetos OPTION
 + ats es un objeto cuyas propiedades se agregan al elemento

FUNCION thFnc(atts,childs) : Crea un elemento contenedor de celda TH
 + atts son las propiedades del elemento
 + childs son los subcomponentes del elemento

FUNCION tdFnc(atts,childs) : Crea un elemento contenedor de celda TD
 + atts son las propiedades del elemento
 + childs son los subcomponentes del elemento

FUNCION trFnc(atts,childs) : Crea un elemento contenedor de fila TR
 + atts son las propiedades del elemento
 + childs son los subcomponentes del elemento

FUNCION rw2Fnc(rowats,cap,capats,cnt,cntats) : Crea una fila de tabla de tipo forma (con un nombre a la izquierda y un elemento editable a la derecha)
 + rowats son las propiedades de la fila TR
 + cap es el texto o los elementos que representan el nombre identificador (caption) incluidos en una celda TH
 + capats son las propiedades de la celda TH
 + cnt es el texto o subcomponentes de la celda TD, se espera que al menos uno de estos sea editable.
 + cntats son las propiedades de la celda TD

 FUNCION filterCell(childs,rowId,ats) : Crea una celda TD que espera exista una funcion pickRow la cual se va a activar cuando se dispare un evento onclick o ondblclick
  + childs son los subcomponentes del elemento
  + rowId es un parámetro identificador asignado a una propiedad rowId en el elemento
  + ats son propiedades adicionales al elemento
*/