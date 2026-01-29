<?php
if(!hasUser()) {
    //setcookie("menu_accion", "", time() - 3600);
    //setcookie("menu_accion", "", time() - 3600, "/invoice");
    $_COOKIE["menu_accion"]="";
    //header("Location: /".$_project_name."/");
    //die("Redirecting to /".$_project_name."/");
    echo "<script>delCookie('menu_accion');sendRefresh();</script>";
    return;
}
$esAdmin = validaPerfil("Administrador");
$esSistemas = validaPerfil("Sistemas")||$esAdmin;
if (!$esSistemas) {
    //setcookie("menu_accion", "", time() - 3600);
    //setcookie("menu_accion", "", time() - 3600, "/invoice");
    $_COOKIE["menu_accion"]="";
    //header("Location: /".$_project_name."/");
    //die("Redirecting to /".$_project_name."/");
    //die();
    echo "<script>delCookie('menu_accion');sendRefresh(()=>{});</script>";
    return;
}
clog2ini("templates.crdiario");
clog1seq(1);
$dia = date('j');
$mes = date('n');
$anio = date('Y');
$fmtDay = str_pad($dia,2,"0",STR_PAD_LEFT)."/".str_pad($mes,2,"0",STR_PAD_LEFT)."/".$anio;
require_once "clases/PDFCR.php";
$pcrObj=new PDFCR(null,null,true);
$fileList=glob($pcrObj->getFilePath()."*.pdf");
$lastWeekTime=strtotime("-2 weeks");
foreach ($fileList as $idx => $filePath) {
    $fileTime=getCorrectMTime($filePath);
    $fileName=basename($filePath);
    if ($fileTime<$lastWeekTime) {
        doclog("Eliminar Archivo","crdiario",["nombre"=>$fileName, "creacion"=>$fileTime, "hace2Semanas"=>$lastWeekTime]);
        unlink($filePath);
        unset($fileList[$idx]);
    } else if (substr($fileName, 0, 6)==="prueba") {
        //doclog("VALIDTIME","crdiario",["fileName"=>$fileName, "fileTime"=>$fileTime, "lastWeekTime"=>$lastWeekTime]);
    } else {
        //doclog("OTHERFILE","crdiario",["fileName"=>$fileName, "fileTime"=>$fileTime, "lastWeekTime"=>$lastWeekTime]);
        unset($fileList[$idx]);
    }
}
array_values($fileList);
usort($fileList, function($a,$b) {
    //$na=basename($a,".pdf");
    //$nb=basename($b,".pdf");
    //$ida=substr($na, 0, -7);
    //$dta=substr($na, -6);
    //$idb=substr($nb, 0, -7);
    //$dtb=substr($nb, -6);
    $dtx=strcmp(substr($a,-10,-4), substr($b,-10,-4));
    if ($dtx==0) return strcmp(substr(basename($a), 0, -7),substr(basename($b), 0, -7));
    return $dtx;
});
//natsort($fileList);
$pathLen=strlen($pcrObj->getFilePath());
global $usrObj;
if (!isset($usrObj)) { require_once "clases/Usuarios.php"; $usrObj=new Usuarios(); }
?>
<div id="area_general" class="central">
    <div id="area_top" class="padhtt zIdx1"><h1 class="txtstrk nomargin mod1">GENERAR ACUMULADO DIARIO PARA PAGO</h1></div>
    <div id="area_detalle" class="all_space mod1">
        <div id="testCreationBlock" class="relative">
            <div class="inblock marginV2"><input type="text" id="fecha" class="calendar" value="<?=$fmtDay?>" onclick="javascript:show_calendar_widget(this);"></div><div class="top round buttonLike no_selection marginV2" onclick="showPDFCR()">PDFCR</div><div id="pdfcrResult" class="top preline monoi lightBG inblock outoff10 padPreTxt allWidBut200 marginV2 hei22 noFlow relative"><img id="pdfcrLed" class="hidden abs_nw marginV2 marginH3" src="imagenes/ledyellow.gif"><div id="pdfcrVwHd" class="abs_ne btnFX btn20 marR1 marT1 btnImgDown" onclick="showFullResult();">&nbsp;</div></div>
        </div>
        <div id="testListBlock" class="scrollauto lessOneLine marT1 inblock wid250"><UL><?php 
    $lastDate=null;
    foreach (array_reverse($fileList) as $idx => $fileabs) {
        $filesize=sizeFix(filesize($fileabs));
        $filename=substr($fileabs, $pathLen);
        $capname=substr($filename, 6, -4); // quitar primeros 6 ('prueba') y 4 últimos ('.pdf')
        $preText=substr($capname, 0, -6);
        $usrData=$usrObj->getData("id=$preText",0,"nombre,persona");
        if (isset($usrData[0]["nombre"][0])) {
            $preText=$usrData[0]["nombre"];
            $ttlText=" title='".$usrData[0]["persona"]."'";
        } else $ttlText="";
        $posText=substr($capname,-6);
        $dateText=substr($posText, 0, 2)."/".substr($posText, 2, 2)."/".substr($posText, 4);
        $btntext=$preText; // "$dateText $preText"; // $preText."-".$posText; //substr($capname,-6);

        if (isset($lastDate)) { if ($lastDate===$dateText) echo "<BR>\n                "; else echo "</LI>\n            <LI>$dateText<br>"; 
        } else echo "            <LI>$dateText<br>"; 
        $lastDate=$dateText;
    ?><FORM method="POST" action="consultas/docs.php" class="inblock vAlignCenter" target="doc" idx="<?=$idx?>" onsubmit="window.open('','doc');console.log('...SUBMITING...');return true;"><input type="hidden" name="type" value="application/pdf"><input type="hidden" name="name" value="<?=$filename?>"><input type="hidden" name="path" value="diarios/<?=$filename?>"><button type="submit" class="alink noborder nobg wid100px noFlow lefted"<?=$ttlText?>><?=$btntext?></button></FORM> <span class="smaller vAlignCenter inblock wid42 righted"><?=$filesize?></span> <img src="imagenes/icons/deleteIcon12.png" class="btnOp round btn20 pad2 vAlignCenter" onclick="delFile(event);"> <button type="button" class="btnfit20 btnview1 round vAlignCenter" value="<?=$capname?>" onclick="mailFile(event);">&#x2709;</button><?php
    }
    if (isset($fileList[0])) echo "</LI>\n";
    $dt = date("ymd");
    $filename="crdiario";
    $logAbsFile=getBasePath()."LOGS/$dt/$filename.log";
         ?></UL></div><div id="testLogBlock" class="scrollauto lessOneLine marT1 inblock widX250 pre"><H3 class="centered"><img src="imagenes/icons/prevPageE20.png" class="top pointer imgnav" onclick="changePage(-1);"><span id="logPick" class="top pointer" onclick="viewLogList();" defaultValue="<?=$filename?>" value="<?=$filename?>"><?=strtoupper($filename)?></span><span id="datePick" class="top" value="<?=$dt?>"><?=$dt?></span><img src="imagenes/icons/nextPageE20.png" class="top pointer invisible imgnav" onclick="changePage(1);"></H3><?=file_exists($logAbsFile)?file_get_contents($logAbsFile):""?></div>
    </div>
</div>
<script>
    function viewLogList() {
        console.log("INI viewLogList");
        const lpe=ebyid("logPick");
        const bcr=lpe.getBoundingClientRect();
        const lst=ecrea({eName:"span","id":"logList",tabIndex:"-1",className:"hidden abs basicBG pad3 br1_0",eChilds:[{eName:"UL",eChilds:[{eName:"LI",val:"crdiario",eText:"CRDIARIO",onclick:changeName},{eName:"LI",val:"docs",eText:"DOCS",onclick:changeName},{eName:"LI",val:"eventos",eText:"EVENTOS",onclick:changeName}]}]});
        document.body.appendChild(lst);
        lst.style.left=bcr.left+"px";
        lst.style.top=(bcr.top+bcr.height)+"px";
        lst.firstElementChild.style.listStyleType="none";
        lst.firstElementChild.style.paddingInlineStart="0px";
        lst.firstElementChild.style.marginBottom="0px";
        clrem(lst,"hidden");
        lst.focus();
        //lst.onblur=evt=>{console.log("blur");ekil("logList");console.log("removed loglist")};
        console.log(lst.firstElementChild);
        fee(lst.firstElementChild.children,el=>console.log(el));
        /*lst.onclick=evt=>{
            console.log("click",evt.target);
            const lpk=ebyid("logPick");
            if (lpk.getAttribute("value")==="crdiario") {
                lpk.setAttribute("value","docs");
                lpk.textContent="DOCS";
                console.log("to docs");
            } else {
                lpk.setAttribute("value","crdiario");
                lpk.textContent="crdiario";
                console.log("to crdiario");
            }
            changePage(0,lpk.getAttribute("value"));
            console.log("done");
            if (evt && evt.target && evt.target.id==="logList") evt.target.onblur=null;
            ekil("logList");
            console.log("removed loglist");
        };*/
    }
    function changeName(evt) {
        console.log("INI changeName ",evt.target.val);
        const lgPk=ebyid("logPick");
        lgPk.setAttribute("value",evt.target.getAttribute("val"));
        //changePage(0,lgPk.getAttribute("value"));
    }
    function changePage(n,fname) {
        const errFnc=function(messageError,responseText) {
            if (messageError && messageError.length>0) {
                //if (messageError.length>200) messageError=messageError.substring(0,200)+"...";
                console.log("MESSAGE: "+messageError);
            }
            if (responseText && responseText.length>0) {
                //if (responseText.length>200) responseText=responseText.substring(0,200)+"...";
                console.log("RESPONSE: "+responseText);
            }
        };
        const rdyFnc=(jobj)=>{
            console.log("rdy: "+JSON.stringify(jobj,jsonCircularReplacer()));
            if (jobj.result==="success") {
                const tbl=ebyid("testLogBlock");
                const h3=tbl.firstElementChild;
                ekfil(tbl,[h3]);
                if (jobj.log)
                    tbl.appendChild(ecrea({eText:jobj.log}));
                const imgnav=lbycn("imgnav");
                const im1=imgnav[0];
                const im2=imgnav[1];
                //ekfil(ih,[im1,im2]);
                clset(im1,"invisible",!jobj.dt);
                clset(im2,"invisible",!jobj.dt||(jobj.dt===jobj.params.maxdt));
                if (jobj.dt) fillContent("datePick", jobj.dt);
                if (jobj.name) {
                    const lgpk=ebyid("logPick");
                    lgpk.setAttribute("defaultValue")=jobj.name;
                    fillContent(lgpk, jobj.name.toUpperCase());
                }
                //if (jobj.message) console.log(jobj.message);
            } else errFnc(jobj.message);
        };
        const dtPk=ebyid("datePick");
        const lgPk=ebyid("logPick");
        postService("consultas/CRDiario.php",
            {action:"changelog","dt":dtPk.textContent,"maxdt":dtPk.getAttribute("value"),shift:n,fname:lgPk.getAttribute("value"),oname:lgPk.getAttribute("defaultValue")},
            getPostRetFunc(rdyFnc,errFnc),
            getPostErrFunc(errFnc)
        );
    }
    function showPDFCR() {
        const s=ebyid("pdfcrResult");
        // toDo: if (s.process) return; s.process=true;
        // o si pdfcrLed es visible
        ekfil(s,[ebyid("pdfcrVwHd"),ebyid("pdfcrLed")]);
        clrem(s,"minHei25");
        doWait(true);
        postService(
            "consultas/CRDiario.php",
            {action:"pdfcr",date:fixDate(ebyid("fecha").value)},
            function(text,params,state,status) {
                if (state==4&&status==200) {
                    if (text.length==0) {
                        console.log("RESPUESTA VACIA");
                        s.appendChild(ecrea({eName:"H3",className:"marginH2",eText:"PETICION SIN RESPUESTA"}));
                        cladd(s,"minHei25");
                        doWait(false); // s.process=false
                        return;
                    }
                    try {
                        const jobj=JSON.parse(text);
                        if (jobj.result) switch(jobj.result) {
                            case "refresh": location.reload(true); return;
                            case "error": 
                                if (!jobj.message) jobj.message="PROCESO CONCLUIDO CON ERRORES";
                                break;
                            case "success":
                                if (!jobj.message) jobj.message="PROCESO CONCLUIDO EXITOSAMENTE";
                                break;
                            default:
                                console.log("RESULTADO DESCONOCIDO: '"+text+"'");
                                s.appendChild(ecrea({eName:"H3",className:"marginH2",eText:"RESPUESTA CORRUPTA"}));
                                s.appendChild(ecrea({eText:text}));
                                cladd(s,"minHei25");
                                showFullResult(true);
                                doWait(false); // s.process=false
                                return;
                        } else {
                            console.log("SIN RESULTADO: "+text);
                            s.appendChild(ecrea({eName:"H3",className:"marginH2",eText:"PETICION SIN TIPO DE RESPUESTA"}));
                            s.appendChild(ecrea({eText:text}));
                            cladd(s,"minHei25");
                            showFullResult(true);
                            doWait(false); // s.process=false
                            return;
                        }
                        if (jobj.message && jobj.message.length>0) {
                            s.appendChild(ecrea({eName:"H3",className:"marginH2",eText:jobj.message}));
                            cladd(s,"minHei25");
                        }
                        if (jobj.errors&&jobj.errors.length>0) {
                            //s.appendChild(ecrea({eName:"P",eText:"ERRORES"}));
                            const errList={eName:"UL",eChilds:[]};
                            jobj.errors.forEach(function(msg,i) {
                                errList.eChilds.push({eComment:"ERROR "+i});
                                let re=/'(archivos\\.*\.pdf)'/;
                                let match=re.exec(msg);
                                const liObj={eName:"LI"};
                                if (match) {
                                    if (!liObj.eChilds) liObj.eChilds=[];
                                    //liObj.eChilds.push({eComment:"MATCH=("+match.index+","+match[0].length+")"});
                                    if(match.index>0) liObj.eChilds.push({eText:msg.slice(0,match.index)});
                                    liObj.eChilds.push({eName:"A",href:match[1],target:"doc",eText:match[1]});
                                    let idx=match.index+match[0].length;
                                    if (idx<msg.length) liObj.eChilds.push({eText:msg.slice(idx)});
                                } else liObj.eText=msg;
                                errList.eChilds.push(liObj);
                            });
                            // toDo: Extraer todos los nombres de archivos con error y agregar un botón para descargarlos y un botón para reemplazarlos (para cuando genere los reparados)
                            s.appendChild(ecrea(errList));
                            if (jobj.result==="success") console.log("EXITO CON ERRORES: ",jobj.errors);
                            //else
                            showFullResult(true);
                        }
                        if (jobj.list) {
                            const fileList={eName:"UL",eChilds:[]};
                            let oldDate=false;
                            let rowLi=false;
                            jobj.list.forEach(function(fileData, index) {
                                console.log("fileData "+(index+1)+" = ", fileData);
                                const name=fileData.name;
                                const size=fileData.size;
                                const date=fileData.date;
                                if (!rowLi) { rowLi={eName:"LI", eChilds:[{eText:date},{eName:"BR"}]};
                                } else if (oldDate===date) { rowLi.eChilds.push({eName:"BR"});
                                } else {
                                    fileList.eChilds.push(rowLi);
                                    rowLi={eName:"LI", eChilds:[{eText:date},{eName:"BR"}]};
                                }
                                oldDate=date;
                                const capname=name.slice(6,-4);
                                const btntext=fileData.text?fileData.text:capname.slice(0,-6)+"-"+capname.slice(-6);
                                const btnObj={eName:"BUTTON",type:"submit",className:"alink noborder nobg wid100px noFlow lefted",eText:btntext};
                                if (fileData.title) btnObj.title=fileData.title;
                                rowLi.eChilds.push({eName:"FORM",method:"POST",action:"consultas/docs.php",className:"inblock",target:"doc",onsubmit:function(){window.open('','doc');return true;},eChilds:[{eName:"INPUT",type:"hidden",name:"type",value:"application/pdf"},{eName:"INPUT",type:"hidden",name:"name",value:name},{eName:"INPUT",type:"hidden",name:"path",value:"diarios/"+name},btnObj]},{eText:" "},{eName:"SPAN",className:"smaller vAlignCenter inblock wid42 righted",eText:size},{eText:" "},{eName:"IMG",src:"imagenes/icons/deleteIcon12.png",className:"btnOp btn16",onclick:delFile},{eText:" "},{eName:"BUTTON",type:"button",className:"btnfit20 btnview1",value:capname,onclick:mailFile,eText:"✉"});
                                //fileList.eChilds.push({eName:"LI",eChilds:[]});
                            });
                            if (rowLi) fileList.eChilds.push(rowLi);
                            const tlb=ebyid("testListBlock");
                            ekfil(tlb);
                            tlb.appendChild(ecrea(fileList));
                        }
                    } catch(ex) {
                        console.log("Exception caught: ", ex, "\nOriginal Response: ", text);
                        s.appendChild(ecrea({eName:"H3",className:"marginH2",eText:"ERROR EN FORMATO DE RESPUESTA"}));
                        s.appendChild(ecrea({eText:ex.message.replaceAll("<","&lt;")}));
                        s.appendChild(ecrea({eName:"HR"}));
                        s.appendChild(ecrea({eText:text.replaceAll("<","&lt;")}));
                        cladd(s,"minHei25");
                        showFullResult(true);
                    }
                    doWait(false); // s.process=false
                } else if (state>=4||status>200) {
                    console.log("Fuera de estado ("+state+","+status+")\n"+text);
                    if (state==4&&status==204&&text==="NO CONTENT") {
                        s.appendChild(ecrea({eName:"H3",className:"marginH2",eText:"NO SE GENERARON DATOS"}));
                        cladd(s,"minHei25");
                        doWait(false);
                    } else if (state>=4) {
                        try {
                            const jobj=JSON.parse(text);
                            if (jobj.message) {
                                s.appendChild(ecrea({eName:"H3",className:"marginH2",eText:jobj.message}));
                                cladd(s,"minHei25");
                            }
                            if (jobj.errors) {
                                const errList={eName:"UL",eChilds:[]};
                                //let hasErrData=false;
                                jobj.errors.forEach(function(msg) {
                                    //if (msg!=="No se generaron datos") hasErrData=true;
                                    errList.eChilds.push(ecrea({eName:"LI",eText:msg}));
                                });
                                s.appendChild(ecrea(errList));
                                //if (hasErrData)
                                showFullResult(true);
                            }
                        } catch(ex) {
                            console.log("Exception caught: ", ex, "\nOriginal Response: ", text);
                            s.appendChild(ecrea({eName:"H3",className:"marginH2",eText:"ERROR EN FORMATO DE RESPUESTA"}));
                            s.appendChild(ecrea({eText:ex.message}));
                            s.appendChild(ecrea({eName:"HR"}));
                            s.appendChild(ecrea({eText:text}));
                            cladd(s,"minHei25");
                            showFullResult(true);
                        }
                        doWait(false); // s.process=false
                    }
                }
            },
            function(errmsg, params, evt) {
                console.log("CONNECTION ERROR: ", errmsg,"; PARAMS: ", params, "; EVENTO: ", evt);
                s.appendChild(ecrea({eName:"H3",className:"marginH2",eText:"ERROR EN CONSULTA"}));
                s.appendChild(ecrea({eText:errmsg}));
                cladd(s,"minHei25");
                showFullResult(true);
                doWait(false); // s.process=false
            }
        );
    }
    function delFile(evt) {
        if (!evt) { console.log("No file to "+action); return }
        const tgt=evt.target;
        const frm=tgt.parentNode.getElementsByTagName("FORM")[0];
        setFile(tgt,"del",{filename:frm.name.value});
    }
    function mailFile(evt) {
        if (!evt) { console.log("No file to "+action); return }
        const tgt=evt.target;
        setFile(tgt,"snd",{value:tgt.value});
    }
    var ledTO=null;
    function setFile(tgt,action,extra) {
        console.log("INI setFile "+action,extra);
        clearTimeout(ledTO);
        fee(lbycn("led"),x=>ekil(x)); // {if(x.hold){timer 2min fee(led,x=>{x.hold=false;x.src=ledred});xhr.abort;clrTime;ledTO=ekil} else ekil(x)}
        if (lbycn("led").length>0) return;
        tgt.parentNode.appendChild(ecrea({eName:"IMG",src:"imagenes/ledyellow.gif",className:"led"})); // ,hold:true
        postService(
            "consultas/CRDiario.php",
            Object.assign({action:action+"doc"},extra),
            function(text,params,state,status) {
                if (state==4&&status==200) {
                    if (text.length==0) {
                        console.log("RESPUESTA VACIA");
                        fee(lbycn("led"),x=>x.src="imagenes/ledred.gif"); // x=>{x.hold=false;x...}
                        clearTimeout(ledTO);
                        ledTO=setTimeout(function(){ fee(lbycn("led"),x=>ekil(x)); },5000);
                        return;
                    }
                    try {
                        const jobj=JSON.parse(text);
                        if (jobj.result) switch(jobj.result) {
                            case "refresh": location.reload(true); return;
                            case "error": 
                                console.log("Resultado fallido: "+JSON.stringify(jobj,jsonCircularReplacer()));
                                fee(lbycn("led"),x=>x.src="imagenes/ledred.gif"); // x=>{x.hold=false;x...}
                                clearTimeout(ledTO);
                                ledTO=setTimeout(function(){ fee(lbycn("led"),x=>ekil(x)); },5000);
                                break;
                            case "success":
                                console.log("Resultado con exito");
                                fee(lbycn("led"),x=>x.src="imagenes/ledgreen.gif"); // x=>{x.hold=false;x...}
                                clearTimeout(ledTO);
                                ledTO=setTimeout(function(a){
                                    if(a==="del") location.reload(true);
                                    fee(lbycn("led"),x=>ekil(x));
                                },3000,action);
                                break;
                            default:
                                console.log("Resultado desconocido: "+JSON.stringify(jobj,jsonCircularReplacer()));
                                fee(lbycn("led"),x=>x.src="imagenes/ledred.gif"); // x=>{x.hold=false;x...}
                                clearTimeout(ledTO);
                                ledTO=setTimeout(function(){ fee(lbycn("led"),x=>ekil(x)); },5000);
                                return;
                        } else {
                            console.log("Respuesta sin resultado: "+JSON.stringify(jobj,jsonCircularReplacer()));
                            fee(lbycn("led"),x=>x.src="imagenes/ledred.gif"); // x=>{x.hold=false;x...}
                            clearTimeout(ledTO);
                            ledTO=setTimeout(function(){ fee(lbycn("led"),x=>ekil(x)); },5000);
                            return;
                        }
                    } catch(ex) {
                        console.log("Error: ", ex, "\nRespuesta: ", text);
                        fee(lbycn("led"),x=>x.src="imagenes/ledred.gif"); // x=>{x.hold=false;x...}
                        clearTimeout(ledTO);
                        ledTO=setTimeout(function(){ fee(lbycn("led"),x=>ekil(x)); },5000);
                    }
                } else if (state>=4||status>200) {
                    console.log("Fuera de estado ("+state+","+status+")\nRespuesta: "+text);
                    if (state>=4) {
                        fee(lbycn("led"),x=>x.src="imagenes/ledred.gif"); // x=>{x.hold=false;x...}
                        clearTimeout(ledTO);
                        ledTO=setTimeout(function(){ fee(lbycn("led"),x=>ekil(x)); },5000);
                    }
                }
            },
            function(errmsg, params, evt) {
                console.log("CONNECTION ERROR: ", errmsg,"; PARAMS: ", params, "; EVENTO: ", evt);
                fee(lbycn("led"),x=>x.src="imagenes/ledred.gif"); // x=>{x.hold=false;x...}
                clearTimeout(ledTO);
                ledTO=setTimeout(function(){ fee(lbycn("led"),x=>ekil(x)); },5000);
            }
        );
    }
    function fixDate(dateStr) {
        const dateElem = strptime( date_format, dateStr );
        const dbStr = strftime(bd_format,dateElem);
        return dbStr;
    }
    function doWait(chk) {
        //clset("pdfcrResult","hidden",chk);
        clset("pdfcrLed","hidden",!chk);
    }
    function showFullResult(forceShow) {
        const r=ebyid("pdfcrResult");
        const b=ebyid("pdfcrVwHd");
        console.log("INI showFullResult");
        if (clhas(r,"expanded")&&!forceShow) {
            clrem(r,["expanded","abs_n","vhbt200","scrollauto"]);
            cladd(r,["hei22","noFlow"]);
            clrem(b,"btnImgUp");
            cladd(b,"btnImgDown");
            //clrem(r.parentNode, "relative");
        } else {
            //cladd(r.parentNode, "relative");
            clrem(r,["hei22","noFlow"]);
            cladd(r,["expanded","abs_n","vhbt200","scrollauto"]);
            clrem(b,"btnImgDown");
            cladd(b,"btnImgUp");
        }
        // ToDo: Aplicar los siguientes cambios para el elemento pdfcrResult:
        //       - Si no tiene clase expanded hacer lo siguiente:
        //         - Agregar clase expanded
        //         - A su parent agregar clase relative
        //         - Identificar ubicacion x del campo pdfcrResult y aplicarle los siguientes cambios
        //           - Agregar clase abs y reubicarlo a su posicion original x, con y=0
        //           - Quitar clases hei22 y noFlow, agregar clases hei110 y scrollauto
        //       - Si tiene clase expanded eliminar los cambios anteriores y quitar clase expanded
        //         - A su parent quitar clase relative
        //         - Quitarle clases abs, hei110 y scrollauto
        //         - Quitarle style x
        //         - Agregarle clases hei22 y noFlow 
    }
</script>
<?php
clog1seq(-1);
clog2end("templates.crdiario");
