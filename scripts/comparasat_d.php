<?php
require_once dirname(__DIR__)."/bootstrap.php";
header("Content-type: application/javascript; charset: UTF-8");
clog2ini("scripts.comparasat_d");
clog1seq(1);
?>
window.onresize = fixHeight;
//var needReload=false;
function fixHeight() {
  let pp=ebyid('principal');
  let at=ebyid('areaTemplate');
  let hx=pp.offsetHeight-19;
  at.style.height=hx+'px';
  let tw=ebyid('tables_wrapper');
  hx-=223;
  tw.style.height=hx+'px';
}
function loadCSV(name) {
  console.log("INI function loadCSV");
  let tgtid="loadCSV";
  if (name==="comparaavance") tgtid+="Avance";
  else if (name==="comparasat") tgtid+="SAT";
  else return;
  let tgt=ebyid(tgtid);
  let file=tgt.files[0];
  let url="consultas/comparaSAT.php";
  let parameters={accion:"alta_c",name:name,file:file};
  overlayMessage(ecrea({eName:"IMG",src:"imagenes/icons/flying.gif"}),"PROCESANDO...");
  console.log("PROCESANDO ...");
  let successFunc=function(resultText, params, state, status) {
    if (state===4) {
      if (status==200) {
        try {
          let jobj=JSON.parse(resultText);
          console.log("RESULT:\n"+resultText);
          if (jobj.result && jobj.result==="error" && jobj.message) overlayMessage("<p>"+jobj.message+"</p>","ERROR");
          else if (jobj.result) {
            if (jobj.inserted || jobj.inserted===0) {
                let loops=jobj.loops-1; // se quita encabezado, incluye lineas en blanco o con errores
                let noqry=jobj.numqry; // num registros incluidos en query
                let arows=jobj.inserted; // num registros afectados en DB
                let dupes=+jobj.duplicates; // num registros con error: duplicados
                let noneuuid=+jobj.noneuuid; // num registros con error: uuid vacio
                let ignrd=noqry-arows-dupes-noneuuid; // registros ignorados por BD sin contar duplicados ni uuid vacios
                let invld=loops-noqry; // registros invalidos (no agregados al query)
                let errors=jobj.errors; // error message
                let wrngs=jobj.warnings; // warning message, includes errors also.
                let info=jobj.info;
                let table=jobj.table;
                let fields=jobj.fields;
                let isOne=(arows==1);
                let message = "<p>Se agreg"+(isOne?"ó":"aron")+" "+arows+" registro"+(isOne?"":"s")+" a la base de datos.</p>";
                if (invld>0) {
                  isOne=(invld==1);
                  message+="<p>Se ignor"+(isOne?"ó":"aron")+" "+invld+" registro"+(isOne?"":"s")+" por tener datos incompletos.</p>";
                }
                if (dupes>0) {
                  isOne=(dupes==1);
                  message+="<p>Se ignor"+(isOne?"ó":"aron")+" "+dupes+" registro"+(isOne?"":"s")+" que fueron ingresados anteriormente.</p>";
                }
                if (noneuuid>0) {
                  isOne=(noneuuid==1);
                  message+="<p>Se rechaz"+(isOne?"ó":"aron")+" "+noneuuid+" registros sin UUID.</p>";
                }
                if (ignrd>0) {
                  isOne=(ignrd==1);
                  message+="<p>Se rechaz"+(isOne?"ó":"aron")+" "+ignrd+" registro"+(isOne?"":"s")+" con otros errores.</p>";
                }
                if (errors) {
                  let errcount=0;
                  for(var uid in errors) {
                    if (errcount==0) message+="<p>Se generaron los siguientes errores: </p><table><tbody>";
                    message+="<tr><th>"+uid+"</th><td>"+errors[uid]+"</td></tr>";
                    errcount++;
                  }
                  if (errcount>0) message+="</tbody></table>";
                }
                overlayMessage(message,"RESULTADO");
                let mylog=ebyid("mylog");
                if (wrngs) {
                  let wo={eName:"OL",eChilds:[]};
                  for(var uid in wrngs) {
                    wo.eChilds.push({eName:"LI",eText:uid+": "+wrngs[uid]});
                  }
                  mylog.appendChild(ecrea({eName:"B",eText:"WARNINGS:"}));
                  mylog.appendChild(ecrea(wo));
                }
                if (info) {
                  let io={eName:"OL",eChilds:[]};
                  for(var uid in info) {
                    io.eChilds.push({eName:"LI",eText:uid+": "+info[uid]});
                  }
                  mylog.appendChild(ecrea({eName:"B",eText:"INFO:"}));
                  mylog.appendChild(ecrea(io));
                }
            } else if (jobj.errors) {
              console.log("ERRORS: ",jobj.errors);
              let errcount=0;
              let message="";
              for(var uid in jobj.errors) {
                if (errcount==0) message+="<p>Se generaron los siguientes errores: </p><table><tbody>";
                message+="<tr><th>"+uid+"</th><td>"+jobj.errors[uid]+"</td></tr>";
                errcount++;
              }
              if (errcount>0) message+="</tbody></table>";
              overlayMessage(message,"ERRORES");
              if (errcount==0) console.log(resultText);
            } else if (jobj.warnings) {
              console.log("WARNINGS: ",jobj.warnings);
              let errcount=0;
              let message="";
              for(var uid in jobj.warnings) {
                if (errcount==0) message+="<p>Se generaron las siguientes alertas: </p><table><tbody>";
                message+="<tr><th>"+uid+"</th><td>"+jobj.warnings[uid]+"</td></tr>";
                errcount++;
              }
              if (errcount>0) message+="</tbody></table>";
              overlayMessage(message,"ERRORES");
              if (errcount==0) console.log(resultText);
            } else overlayMessage("<p>No se agregaron datos al servidor: "+resultText+"</p>","ERROR");
          } else overlayMessage("<p>Ocurrió un error por resultado no esperado: "+resultText+"</p>","ERROR");
          if (jobj.duration) console.log("Tiempo de ejecucion: "+jobj.duration+" segundos");
        } catch(err) {
          console.log("Exception: ", err);
          console.log("JSON: "+resultText);
          overlayMessage("<p>Ocurrió un error en respuesta "+err.name+": "+err.message+"</p>","ERROR");
          let text="loadCSV. ";
          if (err instanceof SyntaxError)
            text+=err.name+":"+err.message+" ("+err.fileName+"#"+err.lineNumber+"#"+err.columnNumber+")";
          else if (err.name || err.message) {
            if (err.name) text+=err.name;
            if (err.message) {
              if (err.name) text+=" ";
              text+=err.message;
            }
          } else text+=err.toString();
          text+="\nResultText = "+resultText;
          postService("consultas/Errores.php",{accion:"savelog",nombre:"comparasat",texto:text});
        }
      } else overlayMessage("<p>Ocurrió un error de comunicación "+status+": "+resultText+"</p>","ERROR");
    }
  };
  let errorFunc=function(reasonText, parameters, evt) {
    overlayMessage("<p>REASON "+parameters.xmlHttpPost.readyState+"/"+parameters.xmlHttpPost.status+":</p><pre>"+reasonText+"</pre>","ERROR");
  };
  postService(url,parameters,successFunc,errorFunc);
  tgt.value="";
}
function tablaVisible() {
  let tablas=lbycn("bdResult");
  for(let i=0; i<tablas.length; i++) if(!tablas[i].classList.contains("hidden")) return tablas[i];
  return false;
}
var cacheDatos={};
function resetTablas() {
  console.log("INI function resetTablas");
  let tablas=lbycn("bdResult");
  for(let i=0; i<tablas.length; i++) {
    let thd=tablas[i].firstElementChild;
    while (thd.nextElementSibling) {
      console.log("KILL: ",thd.nextElementSibling);
      ekil(thd.nextElementSibling);
    }
    cladd(tablas[i],"hidden");
  }
  ekfil(ebyid("pagina"));
  ekfil(ebyid("ultimapag"));
  cladd(ebyid("firstNav"),"disabled");
  cladd(ebyid("prevNav"),"disabled");
  cladd(ebyid("nextNav"),"disabled");
  cladd(ebyid("lastNav"),"disabled");
  cladd(ebyid("tables_footer"),"hidden");
  cladd(ebyid("eliminaSAT"),"hidden");
}
function muestraDatos(tabla,recargar) {
  if (!tabla) tabla=tablaVisible();
  if (!tabla) return;
  console.log("INI function muestraDatos "+tabla.getAttribute("name")+", Recargar='"+recargar+"'");
  clrem(tabla,'hidden');
  let tHead=tabla.firstElementChild;
  let tBody=tHead.nextElementSibling;
  let fn=ebyid("firstNav");
  let pn=ebyid("prevNav");
  let nn=ebyid("nextNav");
  let ln=ebyid("lastNav");
  if (!tBody||recargar) {
    console.log("reload");
    //if (needReload) needReload=false;
    let fechaInicio=ebyid("fechaInicio").value;
    let dIni=fechaInicio.substr(0,2);
    let mIni=fechaInicio.substr(3,2);
    let aIni=fechaInicio.substr(6);
    let fechaFin=ebyid("fechaFin").value;
    let dFin=fechaFin.substr(0,2);
    let mFin=fechaFin.substr(3,2);
    let aFin=fechaFin.substr(6);
    let empresa=ebyid("empresa").value;
    let tipo=ebyid("tipo").value;
    let parameters={tableid:tabla.id,tablename:tabla.getAttribute("name"),accion:"consulta_d",fieldnames:"uuid,fecha,tipoComprobante,rfcEmisor,rfcReceptor",finicio:aIni+"-"+mIni+"-"+dIni+" 00:00:00",ffin:aFin+"-"+mFin+"-"+dFin+" 23:59:59",empresa:empresa,tipo:tipo};
    if (!tBody) {
      tBody=ecrea({eName:"TBODY"});
      tabla.appendChild(tBody);
      tabla.pagina=1;
      tabla.numreg=10;
    } else {
      let sortArr=["sortBy1","sortBy2"];
      sortArr.forEach(function(val) {
        let sortElem=lbycn(val,tHead,0);
        if (sortElem) {
          if (parameters.sortBy) parameters.sortBy+=",";
          else parameters.sortBy="";
          parameters.sortBy+=sortElem.getAttribute("name");
          if (sortElem.hasAttribute("sortDesc")) parameters.sortBy+=" desc";
        }
      });
      let regElem=ebyid("registrosPorPagina");
      tabla.numreg=+regElem.value;
      let pagElem=ebyid("pagina");
      tabla.pagina=+pagElem.innerHTML;
      let ultElem=ebyid("ultimapag");
      tabla.ultimapag=+ultElem.innerHTML;
    }
    parameters.registros=tabla.numreg;
    parameters.pagina=tabla.pagina;
    let url="consultas/comparaSAT.php";
    let paramText=JSON.stringify(parameters);
    cladd(fn,"disabled");
    cladd(pn,"disabled");
    cladd(nn,"disabled");
    cladd(ln,"disabled");
    if (cacheDatos[paramText]) despliegaDatosEncontrados(cacheDatos[paramText],parameters,5,200);
    else {
      postService(url,parameters,despliegaDatosEncontrados,despliegaError);
    }
  } else {
    console.log("tosort");
    let sortArr=["sortBy1","sortBy2"];
    sortArr.forEach(function(val) {
      let elem=lbycn(val,contenedor,0);
      if (elem) {
        if(elem.classList.contains("sortBy1")) clrem(elem,"sortBy1");
        if(elem.classList.contains("sortBy2")) clrem(elem,"sortBy2");
        if(elem.hasAttribute("sortDesc")) elem.removeAttribute("sortDesc");
      }
    });
    let pag=tabla.pagina;
    let ult=tabla.ultimapag;
    let pagElem=ebyid("pagina");
    ekfil(pagElem);
    pagElem.appendChild(ecrea({eText:pag.toString()}));
    let ultElem=ebyid("ultimapag");
    ekfil(ultElem);
    ultElem.appendChild(ecrea({eText:ult.toString()}));
    let regElem=ebyid("registrosPorPagina");
    regElem.value=tabla.numreg.toString();
    if (pag>2) clrem(fn,"disabled"); else cladd(fn,"disabled");
    if (pag>1) clrem(pn,"disabled"); else cladd(pn,"disabled");
    if (ult>1&&pag<ult) clrem(nn,"disabled"); else cladd(nn,"disabled");
    if (ult>2&&(pag+1)<ult) clrem(ln,"disabled"); else cladd(ln,"disabled");
  }
}
function despliegaDatosEncontrados(resultText,parameters,state,status) {
  if (state>=4) {
    console.log("INI function despliegaDatosEncontrados.\nParams: ",parameters,"\nState: "+state+", Status: "+status+"\nResult: ",resultText,"\n"); // 
    if (state==4) {
      let paramText=JSON.stringify(parameters);
      cacheDatos[paramText]=resultText;
    }
    if (resultText.length>0) {
      let jobj=JSON.parse(resultText);
      if (jobj && jobj.result && jobj.result==="error") overlayMessage("<p>"+jobj.message+"</p>","ERROR");
      else if (jobj) {
        //overlayMessage("<pre>"+jobj.result+"</pre>","RESULTADO");
        let tabla=ebyid(parameters.tableid);
        let tHead=tabla.firstElementChild;
        let thRow=tHead.firstElementChild;
        let colNames=[];
        [].forEach.call(thRow.children,function(cell) {
          colNames.push(cell.getAttribute("name"));
        });
        let tBody=tHead.nextElementSibling;
        ekfil(tBody);
        if (jobj.data&&jobj.data.length>0) {
          jobj.data.forEach(function(dRow) {
            let tRowObj={eName:"TR",eChilds:[]};
            colNames.forEach(function(cn){
              let value="-";
              if (dRow[cn]) value=dRow[cn];
              tRowObj.eChilds.push({eName:"TD",eText:value});
            });
            tBody.appendChild(ecrea(tRowObj));
          });
        }

        let numCols=colNames.length;
        let pagOptObjs=[];
        for(let i=10;i<=50;i+=10) {
          let ix=i.toString();
          let oo={eName:"OPTION",value:""+i,eText:""+i};
          if (parameters.registros==i) oo.selected=true;
          pagOptObjs.push(oo);//parameters.registros
        }

        clrem(ebyid("tables_footer"),"hidden");
        let newpag=+parameters.pagina;
        let newult=+jobj.totpag;
        if (newpag>newult) newpag=newult;
        console.log("PAG="+newpag);
        let pagElem=ebyid("pagina");
        let ultElem=ebyid("ultimapag");
        let curpag=+pagElem.innerText;
        let curult=+ultElem.innerText;
        if (ultElem && (curult!==newult || ultElem.innerText.length==0)) {
          ekfil(ultElem);
          ultElem.appendChild(ecrea({eText:newult.toString()}));
          console.log("setting last page value");
        }
        if (pagElem && (curpag!==newpag || pagElem.innerText.length==0)) {
          ekfil(pagElem);
          pagElem.appendChild(ecrea({eText:newpag.toString()}));
        }
        tabla.pagina=newpag;
        tabla.ultimapag=newult;
        let fn=ebyid("firstNav");
        let pn=ebyid("prevNav");
        let nn=ebyid("nextNav");
        let ln=ebyid("lastNav");
        if (newpag>2) clrem(fn,"disabled");
        if (newpag>1) clrem(pn,"disabled");
        if (newult>1&&newpag<newult) clrem(nn,"disabled");
        if (newult>2&&(newpag+1)<newult) clrem(ln,"disabled");
        let regElem=ebyid("registrosPorPagina");
        if (regElem) regElem.value=parameters.registros;
        tabla.numreg=parameters.registros;
        tabla.totreg=+jobj.totReg;
        if (tabla.totreg>0) {
          clrem(ebyid("eliminaSAT"),"hidden");
        }
      } else console.log("No JSON RESULT. RESULTTEXT:\n",resultText);//overlyMessage("<pre>"+resultText+"</pre>","SIN RESULTADO");
    }
  }
}
function despliegaError(errorText, parameters, evt) {
  console.log("INI function despliegaError", errorText, parameters.xmlHttpPost.readyState, parameters.xmlHttpPost.status);
  overlayMessage("<pre>"+errorText+"</pre>","ERROR");
}
function page(e) {
  let tg=e.target;
  if (tg.classList.contains("disabled")) return false;
  console.log("INI function page ",tg);
  let fn=ebyid("firstNav");
  let pn=ebyid("prevNav");
  let nn=ebyid("nextNav");
  let ln=ebyid("lastNav");
  let pge=ebyid("pagina");
  let lge=ebyid("ultimapag");
  let pg=+pge.innerText;
  let lg=+lge.innerText;
  let newpg=false;
  if (tg===fn) {
    if (pg<=1) return;
    newpg=1;
  } else if (tg===pn) {
    if (pg<=1) return;
    newpg=pg-1;
  } else if (tg===nn) {
    if (pg>=lg) return;
    newpg=pg+1;
  } else if (tg===ln) {
    if (pg>=lg) return;
    newpg=lg;
  }
  if (newpg) {
    ekfil(pge);
    pge.appendChild(ecrea({eText:newpg.toString()}));
    muestraDatos(false,true);
  }
}
function gotoFirstPage(tabla) {
  let pagElem=ebyid("pagina");
  ekfil(pagElem);
  pagElem.appendChild(ecrea({eText:"1"}));
}
function ordenaPor(elem) {
  let tabla=epar(elem,3);
  console.log("INI function ordenaPor "+tabla.getAttribute("name")+"."+elem.getAttribute("name"));
  let sortElem1=lbycn("sortBy1",tabla,0);
  let sortElem2=lbycn("sortBy2",tabla,0);
  if (elem===sortElem1) {
    if (elem.hasAttribute("sortDesc")) {
      elem.removeAttribute("sortDesc");
      clrem(elem,"sortBy1");
      if (sortElem2) {
        clrem(sortElem2,"sortBy2");
        cladd(sortElem2,"sortBy1");
      }
    } else elem.setAttribute("sortDesc","1");
  } else {
    if (sortElem2) {
      clrem(sortElem2,"sortBy2");
      if (elem!==sortElem2 && elem.hasAttribute("sortDesc")) elem.removeAttribute("sortDesc");
    }
    if (sortElem1) {
      clrem(sortElem1,"sortBy1");
      cladd(sortElem1,"sortBy2");
    }
    cladd(elem,"sortBy1");
  }
  muestraDatos(tabla,true);
}
function comparaDatos() {
  console.log("INI function comparaDatos");
  let url="consultas/comparaSAT.php";
  let fechaInicio=ebyid("fechaInicio").value;
  let dIni=fechaInicio.substr(0,2);
  let mIni=fechaInicio.substr(3,2);
  let aIni=fechaInicio.substr(6);
  let fechaFin=ebyid("fechaFin").value;
  let dFin=fechaFin.substr(0,2);
  let mFin=fechaFin.substr(3,2);
  let aFin=fechaFin.substr(6);
  let empresa=ebyid("empresa").value;
  let tipo=ebyid("tipo").value;
  let parameters={accion:"comparaAmbas_d",finicio:aIni+"-"+mIni+"-"+dIni+" 00:00:00",ffin:aFin+"-"+mFin+"-"+dFin+" 23:59:59",empresa:empresa,tipo:tipo};
  overlayMessage("<p>OBTENIENDO DATOS...</p>","... PROCESANDO ...");
  let successFunc=function(resultText, params, state, status) {
    if (state===4) {
      if (status==200) {
        try {
          let jobj=JSON.parse(resultText);
          if (jobj.result && jobj.result==="error") overlayMessage("<p>"+jobj.message+"</p>","ERROR");
          else if (jobj.result) {
            let msgarr=[ecrea({eName:"H2",className:"centered marginH5",eText:"Comparativo Avance-SAT, del "+fechaInicio+" al "+fechaFin}),ecrea({eName:"TABLE",className:"maxfit centered screen",eChilds:[{eName:"THEAD",eChilds:[{eName:"TR",eChilds:[{eName:"TH",eText:"Sólo Avance"},{eName:"TH",eText:"Sólo SAT"},{eName:"TH",eText:"Iguales"},{eName:"TH",eText:"Total Avance"},{eName:"TH",eText:"Total SAT"}]}]},{eName:"TBODY",eChilds:[{eName:"TR",eChilds:[{eName:"TD",className:"maxfit",eText:jobj.numSoloAvance.toString()},{eName:"TD",eText:jobj.numSoloSat.toString()},{eName:"TD",eText:(+jobj.numAmbos).toString()},{eName:"TD",eText:jobj.totavn.toString()},{eName:"TD",eText:jobj.totsat.toString()}]}]}]})];
            overlayMessage(msgarr,"Resultado entre "+fechaInicio+" y "+fechaFin);
            let csvContent="Avance,SAT,UUID,FECHA,TIPO,RFCEMISOR,RFCRECEPTOR\n";
            let dra=ebyid("dialog_resultarea");
            if ((+jobj.numSoloAvance)>0) {
                let onlyAvnBody={eName:"TBODY",eChilds:[]};
                for(let i=0; i<jobj.soloAvance.length; i++) {
                    let row=jobj.soloAvance[i];
                    onlyAvnBody.eChilds.push({eName:"TR",className:"csvRow",eChilds:[{eName:"TD",className:"hidden",eText:"SI"},{eName:"TD",className:"hidden",eText:"NO"},{eName:"TD",className:"maxfit",eText:row.uuid},{eName:"TD",eText:row.fecha},{eName:"TD",eText:row.tipo},{eName:"TD",eText:row.rfcEmisor},{eName:"TD",eText:row.rfcReceptor}]});
                    csvContent+="SI,NO,"+row.uuid+","+row.fecha+","+row.tipo+","+row.rfcEmisor+","+row.rfcReceptor+"\n";
                }
                appendMessageElement(dra, [ecrea({eName:"H2",className:"centered marginHSp",eText:"Sólo Avance: "+jobj.numSoloAvance+"/"+jobj.soloAvance.length}), ecrea({eName:"TABLE", className:"maxfit centered screen",eChilds:[{eName:"THEAD",eChilds:[{eName:"TR",className:"csvRow",eChilds:[{eName:"TH",className:"hidden",eText:"Avance"},{eName:"TH",className:"hidden",eText:"SAT"},{eName:"TH",eText:"UUID"}, {eName:"TH",eText:"FECHA"}, {eName:"TH",eText:"TIPO"}, {eName:"TH",eText:"RFCEMISOR"}, {eName:"TH",eText:"RFCRECEPTOR"}]}]},onlyAvnBody]})]);
            }
            if ((+jobj.numSoloSat)>0) {
                let onlySATBody={eName:"TBODY",eChilds:[]};
                for(let i=0; i<jobj.soloSat.length; i++) {
                    let row=jobj.soloSat[i];
                    onlySATBody.eChilds.push({eName:"TR",className:"csvRow",eChilds:[{eName:"TD",className:"hidden",eText:"NO"},{eName:"TD",className:"hidden",eText:"SI"},{eName:"TD",className:"maxfit",eText:row.uuid},{eName:"TD",eText:row.fecha},{eName:"TD",eText:row.tipo},{eName:"TD",eText:row.rfcEmisor},{eName:"TD",eText:row.rfcReceptor}]});
                    csvContent+="NO,SI,"+row.uuid+","+row.fecha+","+row.tipo+","+row.rfcEmisor+","+row.rfcReceptor+"\n";
                }
                appendMessageElement(dra, [ecrea({eName:"H2",className:"centered marginHSp",eText:"Sólo SAT: "+jobj.numSoloSat+"/"+jobj.soloSat.length}), ecrea({eName:"TABLE",className:"maxfit centered screen",eChilds:[{eName:"THEAD",eChilds:[{eName:"TR",eChilds:[{eName:"TH",className:"hidden",eText:"Avance"},{eName:"TH",className:"hidden",eText:"SAT"},{eName:"TH",eText:"UUID"}, {eName:"TH",eText:"FECHA"}, {eName:"TH",eText:"TIPO"}, {eName:"TH",eText:"RFCEMISOR"}, {eName:"TH",eText:"RFCRECEPTOR"}]}]},onlySATBody]})]);
            }
            appendMessageElement(dra,ecrea({eName:"BR"}));
            let filename="comparasat";
            let curDt=new Date();
            let yr=curDt.getFullYear();
            let mo=curDt.getMonth()+1;
            let dy=curDt.getDate();
            let hr=curDt.getHours();
            let mi=curDt.getMinutes();
            let se=curDt.getSeconds();
            filename+=yr+(mo<10?"0"+mo:mo)+(dy<10?"0"+dy:dy)+(hr<10?"0"+hr:hr)+(mi<10?"0"+mi:mi)+(se<10?"0"+se:se)+".csv";
            let csvData='data:application/csv;charset=utf-8,'+encodeURIComponent(csvContent);
            let closeBtn=ebyid("closeButton");
            closeBtn.parentNode.insertBefore(ecrea({eName:"A",eText:"Descargar",className:"buttonLike",href:csvData,download:filename,target:"_blank"}),closeBtn);
            closeBtn.parentNode.insertBefore(ecrea({eName:"button",className:"marginV2",eText:"Imprimir",onclick:printOverlay}),closeBtn);
            console.log("JOBJ",jobj);
          } else overlayMessage("<p>Ocurrió un error por resultado inesperado: "+resultText+"</p>","ERROR");
        } catch(err) {
          console.log("Exception: ", err);
          console.log("JSON: "+resultText);
          overlayMessage("<p>Ocurrió un error en respuesta "+err.name+": "+err.message+"</p>","ERROR");
          let text="comparaDatos. ";
          if (err instanceof SyntaxError)
            text+=err.name+":"+err.message+" ("+err.fileName+"#"+err.lineNumber+"#"+err.columnNumber+")";
          else if (err.name || err.message) {
            if (err.name) text+=err.name;
            if (err.message) {
              if (err.name) text+=" ";
              text+=err.message;
            }
          } else text+=err.toString();
          text+="\nResultText = "+resultText;
          postService("consultas/Errores.php",{accion:"savelog",nombre:"comparasat",texto:text});
        }
      } else overlayMessage("<p>Ocurrió un error de comunicación "+status+": "+resultText+"</p>","ERROR");
    }
  };
  let errorFunc=function(reasonText, parameters, evt) {
    overlayMessage("<p>REASON "+parameters.xmlHttpPost.readyState+"/"+parameters.xmlHttpPost.status+":</p><pre>"+reasonText+"</pre>","ERROR");
  };
  postService(url,parameters,successFunc,errorFunc);
}
function preEliminaDatos() {
  let tabla=tablaVisible();
  console.log("INI function preEliminaDatos "+tabla.totreg);
  if (tabla.totreg && tabla.totreg>0) {
    let title="Confirmar Borrado";
    let msg="<p>Confirme que desea eliminar "+tabla.totreg+" registro"+(tabla.totreg==1?"":"s")+"?</p>";
    overlayConfirmation(msg, title, eliminaDatos);
  }
}
function eliminaDatos() {
  console.log("INI function eliminaDatos");
  let tabla=tablaVisible();
  //console.log("TABLA: id="+tabla.id+", name="+tabla.getAttribute("name"));
  let url="consultas/comparaSAT.php";
  let fechaInicio=ebyid("fechaInicio").value;
  let dIni=fechaInicio.substr(0,2);
  let mIni=fechaInicio.substr(3,2);
  let aIni=fechaInicio.substr(6);
  let fechaFin=ebyid("fechaFin").value;
  let dFin=fechaFin.substr(0,2);
  let mFin=fechaFin.substr(3,2);
  let aFin=fechaFin.substr(6);
  let empresa=ebyid("empresa").value;
  let tipo=ebyid("tipo").value;
  let parameters={accion:"elimina_d",finicio:aIni+"-"+mIni+"-"+dIni+" 00:00:00",ffin:aFin+"-"+mFin+"-"+dFin+" 23:59:59",empresa:empresa,tipo:tipo,tabla:tabla.getAttribute("name")};
  let successFunc=function(resultText, params, state, status) {
    if (state===4) {
      if (status==200) {
        try {
          let jobj=JSON.parse(resultText);
          if (jobj.result && jobj.result==="error") {
            let msg=ecrea({eName:"P",eText:jobj.message});
            overlayMessage(msg,"ERROR");
            console.log("Result: ",resultText);
          } else {
            if (jobj.result==="exito") resetTablas();
            if (jobj.message) {
              let msg=ecrea({eName:"P",eText:jobj.message});
              let ttl="Respuesta";
              if (jobj.title) ttl=jobj.title;
              overlayMessage(msg,ttl);
            } else {
              overlayMessage(ecrea({eName:"P",eText:"Resultado vacío"}),"Error");
              console.log("Result Text: ",resultText);
            }
          }
        } catch(err) {
          console.log("Exception: ", err);
          console.log("JSON: "+resultText);
          console.log("Parameters: ", parameters);
          overlayMessage("<p>Ocurrió un error en respuesta "+err.name+": "+err.message+"</p>","ERROR");
          let text="Elimina Datos. ";
          if (err instanceof SyntaxError)
            text+=err.name+":"+err.message+" ("+err.fileName+"#"+err.lineNumber+"#"+err.columnNumber+")";
          else if (err.name || err.message) {
            if (err.name) text+=err.name;
            if (err.message) {
              if (err.name) text+=" ";
              text+=err.message;
            }
          } else text+=err.toString();
          text+="\nResultText = "+resultText;
          postService("consultas/Errores.php",{accion:"savelog",nombre:"comparasat",texto:text});
        }
      } else overlayMessage("<p>Ocurrió un error de comunicación "+status+": "+resultText+"</p>","ERROR");
      resetTablas();
    }
  };
  let errorFunc=function(reasonText, parameters, evt) {
    overlayMessage("<p>REASON "+parameters.xmlHttpPost.readyState+"/"+parameters.xmlHttpPost.status+":</p><pre>"+reasonText+"</pre>","ERROR");
  };
  postService(url,parameters,successFunc,errorFunc);
}
function dateIniSet() {
    console.log("function dateIniSet");
    let iniDateElem = document.getElementById("fechaInicio");
    let day = strptime(date_format, iniDateElem.value);
    setFullMonth(prev_month(day));
    let wdgt=ebyid('calendar_widget');
    if (wdgt) wdgt.style.display = 'none';
}
function dateEndSet() {
    console.log("function dateEndSet");
    let iniDateElem = document.getElementById("fechaInicio");
    let day = strptime(date_format, iniDateElem.value);
    setFullMonth(next_month(day));
    let wdgt=ebyid('calendar_widget');
    if (wdgt) wdgt.style.display = 'none';
}
function setFullMonth(date) {
    let firstDay = first_of_month(date);
    let lastDay = day_before(first_of_month(next_month(date)));
    let iniDateElem = document.getElementById("fechaInicio");
    let endDateElem = document.getElementById("fechaFin");
    iniDateElem.value = strftime(date_format, firstDay);
    endDateElem.value = strftime(date_format, lastDay);

    adjustCalMonImgs();
}
function adjustCalMonImgs(tgtWdgt) {
    let iniDateElem = document.getElementById("fechaInicio");
    let endDateElem = document.getElementById("fechaFin");

    let iniday = strptime(date_format, iniDateElem.value);
    let endday = strptime(date_format, endDateElem.value);
    let inimon = iniday.getMonth()+1;
    let endmon = endday.getMonth()+1;

    let curday = iniday;
    let curmon = inimon;
    if (tgtWdgt===iniDateElem) {
        if (iniday>endday) {
            endDateElem.value = iniDateElem.value;
        }
    } else if (tgtWdgt===endDateElem) {
        curday = endday;
        curmon = endmon;
        if (iniday>endday) {
            iniDateElem.value = endDateElem.value;
        }
    }
}
function soloHoy() {
  let hoyStr = strftime(date_format,new Date());
  ebyid("fechaInicio").value=hoyStr;
  ebyid("fechaFin").value=hoyStr;
  adjustCalMonImgs();
  let wdgt=ebyid('calendar_widget');
  if (wdgt) wdgt.style.display = 'none';
}
function muestraFechaIni(tgtWdgt, dti, dtf) {
  console.log("INI function muestraFechaIni tgtWdgt:",tgtWdgt,", dti:",dti,", dtf:",dtf);
  let doReset=false;
  if (dti) {
    let fini=ebyid("fechaInicio");
    let nwvl=strftime(date_format,dti);
    if (fini.value!==nwvl) {
      fini.value=nwvl;
      doReset=true;
    }
  }
  if (dtf) {
    let ffin=ebyid("fechaFin");
    let nwvl=strftime(date_format,dtf);
    if (ffin.value!==nwvl) {
      ffin.value=nwvl;
      doReset=true;
    }
  }
  if (tgtWdgt.isUpdated) {
    delete tgtWdgt.isUpdated;
    doReset=true;
  }
  if (doReset) resetTablas();
  adjustCalMonImgs(tgtWdgt);
}
function muestraFechaFin(tgtWdgt, dti, dtf) {
  console.log("INI function muestraFechaFin tgtWdgt:",tgtWdgt,", dti:",dti,", dtf:",dtf);
  muestraFechaIni(tgtWdgt, dti, dtf);
  //if (dti) ebyid("fechaInicio").value = strftime(date_format,dti);
  //if (dtf) ebyid("fechaFin").value = strftime(date_format,dtf);
  //adjustCalMonImgs(tgtWdgt);
}
<?php
clog1seq(-1);
clog2end("scripts.comparasat_d");
