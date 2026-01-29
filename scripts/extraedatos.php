<?php
require_once dirname(__DIR__)."/bootstrap.php";
/*
sessionInit();
if(!hasUser()) {
    die("Empty File");
}
*/
header("Content-type: application/javascript; charset: UTF-8");
clog2ini("scripts.extraedatos");
clog1seq(1);
?>
//console.log("EXTRAEDATOS SCRIPT READY!!!");
doShowLogs=true;
var cols=["folio","uuid","fecha","subtotal","descuento","total","totalimpuestostrasladados","moneda","conceptos"]; // ,"emisor","receptor"
// var cs=["conceptos"]; // ,"emisor","receptor"
var ccc=["claveprodserv","claveunidad","unidad","descripcion","cantidad","valorunitario","descuento","importe"];
function add() {
    const fl=ebyid("filelist");
    //console.log("INI add: ",fl.files);
    if (fl.files.length>0)
        postService("consultas/Archivos.php",{action:"extractData",file:fl.files},addResult);
}
function addResult(responseText, parameters, state, status) {
    if (state!=4||status!=200) return;
    if (responseText.length>0) {
        const da=ebyid("data_area");
        try {
            const jobj=JSON.parse(responseText);
            if (jobj.result!=="success") {
                //da.appendChild(ecrea({eName:"P",eText:jobj.message}));
                //console.log("JSON="+responseText);
            } else if (jobj.data) {
                const tbch=[];
                let dtHdr=ebyid("dataHeader");
                let numCols=0;
                if (!dtHdr) {
                    const htrch=[];//[{eName:"TH",eText:"#",onclick:doSort}];
                    for(let i=0; i< cols.length; i++) {
                        htx=cols[i];
                        if (htx==="conceptos") { // ([].includes(htx))
                            ccc.forEach(function(v){htrch.push({eName:"TH",eText:v.toUpperCase(),onclick:doSort});});
                        } else htrch.push({eName:"TH",eText:htx.toUpperCase(),onclick:doSort});
                    }
                    numCols=htrch.length;
                    tbch.push({eName:"THEAD",id:"dataHeader",eChilds:[{eName:"TR",eChilds:htrch}]});
                } else numCols=dtHdr.firstElementChild.children;
                const btr=[];
                let dtBdy=ebyid("dataBody");
                //console.log("PROCESS DATA:");
                dLoop:
                for(let d=0;d< jobj.data.length; d++) {
                    const dto=jobj.data[d];
                    //console.log((d+1)+") "+JSON.stringify(dto));
                    if ("error" in dto) {
                        //btr.push({eName:"TR",eChilds:[{eName:"TD",colSpan:""+(numCols-1),eText:JSON.stringify(dto.error)}]}); // {eName:"TD",eText:""+(d+1)},
                        continue;
                    }
                    const dtrch=[];//[{eName:"TD",eText:""+(d+1)}];
                    for(let k=0;k< cols.length; k++) {
                        let kn=cols[k];
                        if (kn in dto) {
                            let kv=dto[kn];
                            if (kn==="conceptos") {
                                for (let cc=0;cc< kv.length;cc++) {
                                    let dtrcc=dtrch.slice();
                                    ccc.forEach(function(v){let cvl="";if(kv[cc]["@"+v])cvl=kv[cc]["@"+v];dtrcc.push({eName:"TD",eText:cvl});});
                                    btr.push({eName:"TR",id:dto.uuid+"-"+(cc+1),eChilds:dtrcc});
                                }
                                continue dLoop;
                            } else dtrch.push({eName:"TD",eText:kv});
                        } else if (kn==="conceptos") {
                            ccc.forEach(function(v){dtrch.push({eName:"TD",eText:""});});
                            /*
                            let msg="";
                            if ("tipo_comprobante" in dto) {
                                const tc=dto["tipo_comprobante"];
                                if (tc=="I") msg="La factura no tiene conceptos";
                                else msg="No es una factura ("+tc+")";
                            } else msg="Tipo de comprobante no especificado";
                            dtrch.push({eName:"TD",colSpan:""+ccc.length,eText:msg});
                            */
                        } else {
                            dtrch.push({eName:"TD",eText:""});
                        }
                    }
                    btr.push({eName:"TR",id:dto.uuid,eChilds:dtrch});
                }
                if (dtBdy) {
                    btr.forEach(function(r){if(ebyid(r.id)){/*console.log("REPEATED "+r.id);*/}else dtBdy.appendChild(ecrea(r));});
                } else {
                    tbch.push({eName:"TBODY",id:"dataBody",eChilds:btr});
                    da.appendChild(ecrea({eName:"DIV",className:"all_space lessOneLine scrollauto",eChilds:[{eName:"TABLE",id:"dataTable",className:"fontSmall pad2cnw",border:"1",eChilds:tbch}]}));
                    da.appendChild(ecrea({eName:"DIV",className:"all_space oneLine centered fontSmall",eChilds:[{eText:"Sep.: "},{eName:"INPUT",type:"text",id:"separator",className:"wid12px marR10",maxLength:"1",value:","},{eName:"IMG",className:"asBtn hei22i",src:"imagenes/excelicon32.png",onclick:exportToCSV},{eName:"SPAN",className:"inblock wid55px lefted padL10",eChilds:[{eName:"INPUT",type:"button",id:"clearData",value:"RESET",onclick:clearData}]}]}));
                }
                const x=ebyid("clearData");
                if (!x && da.children.length>0) {
                    //const fa=ebyid("filter_area");
                    //fa.appendChild(ecrea());
                }
            } //else {
                //da.appendChild(ecrea({eName:"P",eText:"No se encontraron datos ("+jobj.result+")"}));
                //console.log("JSON="+responseText);
            //}
            if (jobj.log) console.log("LOG: ",jobj.log);
            if (jobj.cfdilog) console.log("CFDI: ",jobj.cfdilog);
        } catch (e) {
            let msg=e.message;
            if(e.fileName) msg+=" @"+e.fileName;
            if(e.lineNumber) msg+=" #"+e.lineNumber;
            //da.appendChild(ecrea({eName:"P",eText:msg}));
            console.log("EXCEPTION=",e);
            console.log("JSON="+responseText);
        }
    } else console.log("Empty Response");
}
function clearData(evt) {
    ekfil("data_area");
    //ekil("clearData");
    ebyid("filelist").value=null;
}
function exportToCSV(evt) {
    const arr=htmlTableToArray("dataTable", true, ebyid("separator").value);
    //console.log(arr);
    /* for(let i=0; i < arr.length; i++) {
        let row=arr[i];
        console.log("ROW "+(i+1));
        if (row.length>43) {
            let prefix=" [ ", suffix="...";
            for (let m=0,n=43;m < row.length;m=n,n+=43) {
                if (n>=row.length) {
                    n=row.length;
                    suffix=" ]";
                }
                console.log(prefix+row.slice(m,n)+suffix);
                prefix="   ";
            }
        } else console.log(row);
    } */
    if (arrayToCSVFile(arr,false,true,true)===false) //{
        console.log("DATOS NO RECONOCIDOS");
    //}
    else console.log("ARCHIVO GENERADO");
}
function doSort(evt) {
    const tgt=evt.target;
    //console.log("INI doSort "+tgt.textContent);
    let sortDesc=false;
    fee(lbycn("sortDesc"),e=>clrem(e,"sortDesc"));
    if(clhas(tgt,"sortAsc")) {
        sortDesc=true;
        cladd(tgt,"sortDesc");
    }
    fee(lbycn("sortAsc"),e=>clrem(e,"sortAsc"));
    if(!sortDesc) cladd(tgt,"sortAsc");
    console.log("INI doSort "+tgt.textContent+(sortDesc?" desc":" asc"));
    sortHtmlTable(tgt.closest("TABLE"),tgt.cellIndex+1,sortDesc);
}
<?php
clog1seq(-1);
clog2end("scripts.extraedatos");
