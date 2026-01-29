<?php
require_once dirname(__DIR__)."/bootstrap.php";
if (!hasUser()) {
    header("Location: /$_project_name/");
    die("Redirecting to: $project_name");
}
$esSuperAdmin=getUser()->nombre==="admin";
$esAdmin=validaPerfil("Administrador");
$esSistemas=validaPerfil("Sistemas");
$esCuentas=validaPerfil("Cuentas Bancarias");
if (!$esAdmin && !$esSistemas && !$esCuentas) {
    header("Location: /$_project_name/");
    die("Redirecting to /".$_project_name."/index.php");
}
header("Content-type: application/javascript; charset: UTF-8");
clog2ini("scripts.cuentas");
clog1seq(1);
?>
var doclines;
var parameters={"command":"verificarCuentas"};
var targetFileName;
doShowLogs=true; // mostrar textos de conlog
var blockPT=false;
var resbdy;
function procesaTXT() {
    if (blockPT) {
        conlog("NOT procesaTXT (LOCKED FILEINPUT)");
        return;
    }
    blockPT=true;
    conlog("INI procesaTXT: LOCK FILEINPUT");
    doclines={total:{},count:0,busy:false};
    parameters.data=[];
    cladd("result_table","hidden");
    cladd("error_line","hidden");
    if (!resbdy) resbdy=ebyid("result_body");
    ekfil(resbdy);
    const sumDiv=ebyid("summary_account");
    cladd(sumDiv,"hidden");
    let txtfield = ebyid("archivo_txt");
    if (txtfield.files) conlog("Files length="+txtfield.files.length);
    else conlog("Archivo Txt empty!");
    readAllFiles(txtfield.files,function(){
        conlog("DATA length="+parameters.data.length);
        let url="consultas/Proveedores.php";
        let retIdx=0;let BLK="<!-- BLK -->";
        if (parameters.data.length>0) {
            conlog("DATA: ",parameters.data);
            postService(url, parameters, function(retmsg, params, readyState, status) {
                conlog("BEGIN RESULT. idx="+retIdx+", rdyStt="+readyState+", status="+status);
                if ((readyState==3||readyState==4)&&status==200) {
                    if (retIdx==0) {
                        retIdx=retmsg.indexOf(BLK);
                        if (retIdx>0) conlog("Result 0:"+retmsg.substring(0,retIdx));
                        else if (retIdx<0) conlog("NO BLOCK 0: "+retmsg);
                    }
                    if (retIdx>=0) {
                        if (retmsg.length>retIdx) {
                            let nxtIdx=retmsg.indexOf(BLK,retIdx+BLK.length);
                            if (nxtIdx>0) {
                                clrem("result_table","hidden");
                            } else conlog("NO BLOCK "+retIdx+": "+retmsg.substring(retIdx));
                        }
                        for (let blkIdx=retmsg.indexOf(BLK,retIdx+1);blkIdx>0;retIdx=blkIdx,blkIdx=retmsg.indexOf(BLK,retIdx+1)) {
                            retIdx+=12;
                            try {
                                conlog("PROCESA("+retIdx+","+blkIdx+"): "+retmsg.substring(retIdx,blkIdx));
                                procesaJSON(JSON.parse(retmsg.substring(retIdx,blkIdx)));
                            } catch(e) {
                                conlog(e);
                            }
                        }
                        fixSummary();
                        let lastStr=retmsg.substring(retIdx+BLK.length);
                        if (lastStr.length>0) conlog("END RESULT: "+lastStr);
                    }
                }
                if (readyState>=4 || status>200) {
                    conlog("UNLOCK FILEINPUT");
                    blockPT=false;
                }
            }, function(errmsg, params, evt) {
                conlog("UNLOCK FILEINPUT");
                blockPT=false;
            });
        } else {
            fillContent("error_line", "No se encontraron registros de pago");
            clrem("error_line","hidden");
            conlog("NO DATA IN PARAMETERS");
            blockPT=false;
        }
        if (!txtfield) txtfield=ebyid("archivo_txt");
        // Clear FileInput by null
        try {
            txtfield.value=null;
            conlog("FileInput set Null without error");
        } catch (ex) { }
        // Clear FileInput by type
        if (txtfield.value) {
            txtfield.type="text";
            txtfield.type="file";
            conlog("FileInput restored by type");
        }
        // Clear FileInput by form
        if (txtfield.value) {
            var form=document.createElement('form'), parentNode=f.parentNode, ref=f.nextSibling;
            form.appendChild(txtfield);
            form.reset();
            parentNode.insertBefore(txtfield,ref);
            conlog("FileInput restored by form");
        }
        // Clear FileInput by clone
        if (txtfield.value) {
            txtfield.parentNode.replaceChild(txtfield.cloneNode(true), txtfield);
            conlog("FileInput replaced by clone");
        }
        if (txtfield.value) conlog("FileInput was NOT reset!");
        else conlog("FileInput reset successfully!");
    },0);
}
function readAllFiles(files,finishCallback,fileidx) {
    if (files.length<=fileidx) {
        finishCallback();
        return;
    }
    const filedata=files[fileidx];
    if (!targetFileName || (typeof targetFileName !== "string") || targetFileName.length<=0) targetFileName=filedata.name;
    conlog("INI readAllFiles "+(fileidx+1)+"/"+files.length+": "+filedata.name);
    let reader=new FileReader();
    reader.onload=function(progressEvent) {
        let lines=this.result.split("\n");
        lines.forEach(function(txt,lni) {
            const rfc=txt.slice(48,68).trim();
            const cta=txt.slice(462,497).trim();
            let cdp=txt.slice(71,76).trim();
            if (txt.length>764 && cdp.length>4 && cta.length>0 && rfc.length>0) {
                doclines.count++;

                let txt1=txt.slice(0,25); //71); // 48); // 33);
                //let rfyr=txt.slice(25,29);
                let rf4dgcta=txt.slice(557,561);
                txt1+=rf4dgcta;                                                // 0 - 29

                let txt2=txt.slice(29,71);
                if (cdp.slice(1,2)===" ") cdp=cdp.slice(0,1)+"-"+cdp.slice(2);
                txt2+=cdp;                                                     // 29 - 76

                let txt3=txt.slice(76,147); // 48,91); // 81);
                let rf2=txt.slice(147,185).trim();
                let rf3=txt.slice(185,223).trim();
                if (rf3.length>0) {
                    let rpfx="FACTURA ";
                    if (rf3.length>30) {
                        rf3=rf3.replace(/\s/g,"");
                        if (rf3.length>30) rpfx=rpfx.slice(0,38-rf3.length);
                    }
                    rf2=rpfx+rf3;
                    rf3="";
                }
                txt3+=rf2.padEnd(38)+rf3.padEnd(38);                           // 76 - 223

                let txt4=txt.slice(223,757); // 91);
                let email=txt.slice(691,741).trim();
                let emailChk=txt.slice(757,763).trim();
                if (emailChk.length==0) {
                    if (email.length>0) emailChk="E-Mail";
                    else emailChk="None  ";
                }
                txt4+=emailChk;                                                // 223 - 763

                let txt5=txt.slice(763);                                       // 763 - ...1024

                rptaId="rpta"+("0000"+doclines.count).substr(-4);
                doclines[rptaId]=txt1+txt2+txt3+txt4+txt5;

                const ttl=txt.slice(91,106).trim();
                doclines.total[rptaId]=+ttl;
                
                const rzs=txt.slice(256,336).trim();
                resbdy.appendChild(ecrea({eName:"TR",idx:doclines.count,className:"r"+cdp,cdp:cdp,eChilds:[{eName:"TD",className:"nowrap",onclick:openProvider,eChilds:[{eName:"input",type:"checkbox",className:"topvalign",onclick:function(event){const cellElem=this.parentNode; const rowElem=cellElem.parentNode; rowElem.valid=this.checked; fixSummary(); eventStop(event);}},{eText:cdp}]},{eName:"TD",eText:cta},{eName:"TD",eText:rzs},{eName:"TD",eText:rf2},{eName:"TD",id:rptaId}]}));
                parameters.data.push([rptaId,cdp,rfc,cta,rzs]);
            }
        });
        conlog("Num lines after file "+(fileidx+1)+": "+doclines.count);
        conlog("Total after file "+(fileidx+1)+": "+Object.values(doclines.total).reduce((a,b)=>a+b,0));
        conlog("DATA length after file "+(fileidx+1)+": "+parameters.data.length);
        readAllFiles(files,finishCallback,fileidx+1);
    };
    reader.readAsText(filedata);
}
function procesaJSON(jobj) {
    conlog("INI function procesaJSON "+jobj.result+": '"+jobj.message+"'");
    if (jobj.message && jobj.rowId) {
        let rptaElem=ebyid(jobj.rowId);
        if (rptaElem) {
            let rowElem=rptaElem.parentNode;
            rowElem.valid=(jobj.result==="success"&&jobj.status==="activo"&&jobj.verificado==="1");
            if (jobj.result==="refresh") {
                location.reload(true);
            }// else if (jobj.result==="success") {
                rptaElem.innerHTML=jobj.message;
                //cladd(rowElem,"bggreenvip");
            //} else if (jobj.result==="error") {
            //    rptaElem.innerHTML=jobj.message;
                //cladd(rowElem,"bgredvip");
            //} else {
            //    rptaElem.innerHTML=jobj.message;
                //cladd(rowElem,"bgyellowvip");
            //}
            if (rowElem.valid) cladd(rowElem,"bggreenvip");
            else if (jobj.result==="success"||jobj.result==="error") cladd(rowElem,"bgredvip");
            else cladd(rowElem,"bgyellowvip");
            let codElem=rowElem.firstElementChild;
            codElem.usrId=jobj.usrId;
            codElem.email=jobj.email;
            codElem.prvId=jobj.prvId;
            codElem.codigo=jobj.prvCode;
            codElem.razsoc=jobj.prvRazSoc;
            codElem.rfc=jobj.rfc;
            codElem.banco=jobj.banco;
            codElem.rfcbanco=jobj.rfcbanco;
            codElem.cuenta=jobj.cuenta;
            codElem.edocta=jobj.edocta;
            codElem.status=jobj.status;
            codElem.verificado=jobj.verificado;
            codElem.credito=jobj.credito;
            codElem.pago=jobj.formapago;
            codElem.zona=jobj.zona;
            codElem.texto=jobj.texto;
            codElem.cumplido=jobj.cumplido;
            codElem.inicia=jobj.inicia;
            codElem.expira=jobj.expira;
            codElem.opinion=jobj.opinion;
            let chkElem=codElem.firstElementChild;
            if (chkElem.tagName==="INPUT" && rowElem.valid) chkElem.checked=true;
            let razSocElem=rptaElem.previousElementSibling;
            razSocElem.setAttribute("status",jobj.status);
            if (jobj.status && razSocElem) {
                razSocElem.title="Status: "+jobj.status.toUpperCase();
                /*switch(jobj.status) {
                    case "activo": break;
                    case "actualizar": clrem(rowElem,"bggreenvip"); cladd(rowElem,"bgredvip"); break;
                    case "bloqueado": clrem(rowElem,"bggreenvip"); cladd(rowElem,"bgredvip"); break;
                    default: clrem(rowElem,"bggreenvip"); cladd(rowElem,"bgredvip"); // inactivo
                }*/
                let ctaElem=razSocElem.previousElementSibling;
                if (ctaElem) {
                    ctaElem.verificado=jobj.verificado;
                    ctaElem.setAttribute("verificado",jobj.verificado);
                    ctaElem.appendChild(ecrea({eName:"BR"}));
                    let verifyText="";
                    let verifyClass="";
                    switch(jobj.verificado) {
                        case "1": verifyText="VERIFICADO"; break;
                        case "-1": verifyText="RECHAZADO";
                            //clrem(rowElem,"bggreenvip"); cladd(rowElem,"bgredvip");
                            break;
                        case "0": verifyText="SIN VERIFICAR";
                            //clrem(rowElem,"bggreenvip"); cladd(rowElem,"bgredvip");
                            break;
                        default: verifyText="SIN VERIFICAR";
                            //clrem(rowElem,"bggreenvip"); cladd(rowElem,"bgredvip");
                    }
                    if (jobj.result==="success"||jobj.result==="pendiente") {
                        ctaElem.appendChild(getVerifyButton(verifyText,jobj.banco,jobj.rfcbanco,jobj.edocta,jobj.cuenta,jobj.prvId,jobj.prvCode,jobj.prvRazSoc));
                    }
                }
            }
        }
    }
}
function getVerifyButton(buttonText,bank,bankRef,fileName,account,prvId,prvCode,prvRazSoc) {
    //conlog("INI function getVerifyButton (buttonText="+buttonText+", bank="+bank+", bankRef="+bankRef+",fileName="+fileName+", account="+account+", prvId="+prvId+", prvCode="+prvCode+", prvRazSoc="+prvRazSoc+" )");
    let btn = ecrea({eName:"BUTTON",eText:buttonText,cdp:prvCode,className:"b"+prvCode,account:account,filenm:fileName,onclick:function(event) {
        let tgt=event.target;
        let accmsg="Cuenta "+tgt.account;
        let filenm=tgt.filenm;
        //conlog("onclick getVerifyButton("+buttonText+"|"+prvId+"|"+bank+"|"+bankRef+"|"+filenm+"|"+accmsg+"),tgt=",tgt);
        let thisbtn=this;
        let ctaElem=thisbtn.parentNode;
        let rowElem=ctaElem.parentNode;
        let verified=(buttonText==="VERIFICADO");
        let rejected=(buttonText==="RECHAZADO");
        thisbtn.oldValue=thisbtn.innerHTML;
        rowElem.oldClass=rowElem.className;
        overlayMessage([{eName:"H1",eText:bank+". "+bankRef},{eName:"H1",eText:accmsg},{eName:"OBJECT",data:"cuentas/docs/"+filenm,type:"application/pdf",width:"95%",height:"300",eChilds:[{eName:"A",href:"cuentas/docs/"+filenm,eText:"Abrir PDF"}]},{eName:"P",id:"verifyCaption",classList:"centered vAlignCenter marginH5",eChilds:[{eText:"Verificar si la cuenta coincide."}]}],"VERIFICAR CUENTA DE "+prvCode);
        let closeButton=ebyid("closeButton");
        let closeArea=ebyid("closeButtonArea");
        cladd(closeButton,"hidden");
        while (closeButton.nextSibling) closeArea.removeChild(closeButton.nextSibling);
        closeArea.appendChild(ecrea({eName:"INPUT",type:"button",value:"ACEPTAR",onclick:function(){fee(lbycn("b"+thisbtn.cdp), function(belm){belm.innerHTML="VERIFICADO";belm.disabled=!verified;});if(thisbtn.disabled)fee(lbycn("r"+rowElem.cdp),function(relm){clrem(relm,"bggreenvip");clrem(relm,"bgredvip");cladd(relm,"bgblackvip");}); overlay();}}));
        closeArea.appendChild(ecrea({eName:"INPUT",type:"button",value:"RECHAZAR",onclick:function(){fee(lbycn("b"+thisbtn.cdp), function(belm){belm.innerHTML="RECHAZADO";belm.disabled=!rejected;});if(thisbtn.disabled)fee(lbycn("r"+rowElem.cdp),function(relm){clrem(relm,"bggreenvip");clrem(relm,"bgredvip");cladd(relm,"bgblackvip");}); overlay();}}));
        ebyid("dialog_title").title=prvRazSoc;
        ebyid("overlay").callOnClose=function() {
            ebyid("dialog_title").title=null;
            if (clhas(rowElem,"bgblackvip")) {
                conlog("CLOSE AND SAVE "+prvId+"|"+prvCode+": "+thisbtn.innerHTML);
                postService("consultas/Proveedores.php",{"command":"verificarProveedor","id":prvId,"verificado":(thisbtn.innerHTML==="VERIFICADO"?"1":(thisbtn.innerHTML==="RECHAZADO"?"-1":"0"))},function(retmsg, params, readyState, status) {
                    if (readyState==4&&status==200) {
                        try {
                            let jobj=JSON.parse(retmsg);
                            let esVerificado=(thisbtn.innerHTML==="VERIFICADO");
                            let esRechazado=(thisbtn.innerHTML==="RECHAZADO");
                            let esExito=(jobj.result==="success");
                            if (jobj.result==="refresh") {
                                location.reload(true);
                            } else if (esExito) {
                                if (esVerificado) {
                                    fee(lbycn("b"+thisbtn.cdp),function(belm){
                                        const belmCell=belm.parentNode;
                                        belmCell.verificado="1";
                                        belmCell.setAttribute("verificado","1");
                                        const belmRow=belmCell.parentNode;
                                        belmRow.valid=true;
                                        const belmChk=belmRow.firstElementChild.firstElementChild;
                                        if (belmChk.tagName==="INPUT") belmChk.checked=true;
                                    });
                                    fee(lbycn("r"+rowElem.cdp),function(relm){
                                        clrem(relm,"bgblackvip");
                                        cladd(relm,"bggreenvip");
                                    });
                                } else if (esRechazado) {
                                    fee(lbycn("b"+thisbtn.cdp),function(belm){
                                        belm.parentNode.verificado="-1";
                                        belm.parentNode.setAttribute("verificado","-1");
                                    });
                                    const isSub=(thisbtn.oldValue==="VERIFICADO");
                                    fee(lbycn("r"+rowElem.cdp),function(relm){
                                        clrem(relm,"bgblackvip");
                                        cladd(relm,"bgredvip");
                                    });
                                } else {
                                    fee(lbycn("b"+thisbtn.cdp),function(belm){
                                        if (belm.parentNode.verificado) delete belm.parentNode.verificado;
                                        if (belm.parentNode.hasAttribute("verificado"))
                                            belm.parentNode.removeAttribute("verificado");
                                    });
                                    fee(lbycn("r"+rowElem.cdp),function(relm){
                                        clrem(relm,"bgblackvip");
                                        cladd(relm,"bgyellowvip");
                                    });
                                }
                                fixSummary();
                            } else {
                                fee(lbycn("b"+thisbtn.cdp),function(belm){
                                    belm.innerHTML=thisbtn.oldValue;
                                });
                                fee(lbycn("r"+rowElem.cdp),function(relm){
                                    relm.className=rowElem.oldClass;
                                });
                            }
                            conlog(jobj.message);
                        } catch (e) {
                            fee(lbycn("b"+thisbtn.cdp),function(belm){
                                belm.innerHTML=thisbtn.oldValue;
                            });
                            fee(lbycn("r"+rowElem.cdp),function(relm){
                                relm.className=rowElem.oldClass;
                            });
                            conlog(e);
                        }
                        fee(lbycn("b"+thisbtn.cdp),function(belm){
                            belm.disabled=false;
                        });
                    }
                });
            }
        };
    }});
    return btn;
}
function fixSummary() {
    let vNum=0;
    const rows=resbdy.getElementsByTagName("tr");
    fee(rows,row=>{ if (row.valid) vNum++; });
    let sumDiv=ebyid("summary_account");
    if (sumDiv) {
        const sumOKCap = ebyid("summary_good");
        const sumOKBtn = ebyid("button_good");
        const sumERCap = ebyid("summary_error");
        const sumERBtn = ebyid("button_error");
        const sumOK = vNum;
        const sumER = rows.length-vNum;
        let msgOK=sumOK+" registro"+(sumOK==1?"":"s")+" ";
        let msgER=" y "+sumER+" registro"+(sumER==1?"":"s")+" ";
        if (sumOK<=0) {
            cladd(sumOKBtn,"hidden");
            msgOK+="A PAGAR";
        } else {
            clrem(sumOKBtn,"hidden");
        }
        if (sumER<=0) {
            cladd(sumERBtn,"hidden");
            msgER+="A IGNORAR";
        } else {
            clrem(sumERBtn,"hidden");
        }
        sumOKCap.num=sumOK;
        sumOKCap.textContent=msgOK;
        sumERCap.num=sumER;
        sumERCap.textContent=msgER;
        clrem(sumDiv,"hidden");
    }
}
function generaTxt(esPago) {
    let fullText="";
    let num=0;
    let pagoTotal=0;
    for (let lineKey in doclines) {
        if(lineKey==="total"||lineKey==="count"||lineKey==="busy") continue;
        let rElem=ebyid(lineKey);
        let pElem=rElem.parentNode;
        if (pElem) {
            const rptStr=doclines[lineKey];
            if ((!!pElem.valid)===esPago) {
                pagoTotal+=doclines.total[lineKey];
                num++;
                if(fullText.length>0) fullText+="\n";
                fullText+=rptStr;
            }
        }
    }
    if(fullText.length>0) {
        fullText+="\n";
        const fftn0="000000000000000";
        let numpad=(fftn0+num).slice(-15);
        let totalStr=(fftn0+pagoTotal).slice(-15);
        fullText+="TRL"+numpad+totalStr+fftn0+numpad+"                                     ";
    }

    creaArchivoTxt(fullText,esPago?"pago":"espera");
}
function creaArchivoTxt(fullText,titleKey) {
    conlog("INI function creaArchivoTXT "+titleKey); // +": ",fullText
    if ('Blob' in window) {
        let filename=false;
        if(targetFileName&&targetFileName.length&&targetFileName.length>0)
            filename=titleKey+"_"+targetFileName;
        else {
            filename=prompt("Ingrese nuevo nombre de archivo",titleKey+".txt");
            if (!filename) filename=titleKey+".txt";
        }
        let blobObj=new Blob([fullText],{type:'text/plain'});
        if ('msSaveOrOpenBlob' in navigator) {
            navigator.msSaveOrOpenBlob(blobObj, filename);
        } else {
            let lnkPp = {eName:"A", download:filename, eText:"Descargar Archivo"};
            let lnkElem=false;
            if ('webkitURL' in window) {
                lnkPp.href=window.webkitURL.createObjectURL(blobObj);
                lnkElem=ecrea(lnkPp);
            } else {
                lnkPp.href=window.URL.createObjectURL(blobObj);
                lnkPp.onclick=function(event){document.body.removeChild(event.target);};
                lnkPp.style.display="none";
                lnkElem=ecrea(lnkPp);
                document.body.appendChild(lnkElem);
            }
            if(lnkElem) lnkElem.click();
        }
    } else {
        let newWin=window.open("","DOC PAGO","toolbar=0,location=0,personalbar=0,directories=0,status=1,menubar=0,titlebar=1,scrollbars=1,resizable=1,width=600,height=400"); // ",top="+(screen.height-400)+",left="+(screen.width-840)
        newWin.document.write(fullText);
    }
}
var winProvRef=null;
function openProvider(evt) {
    conlog("INI function openProvider",evt.target?evt.target:"null");
    let tgt=evt.target;
    if(winProvRef == null || winProvRef.closed) {
        winProvRef = window.open("", "winProveedor", "width=550,height=450,left="+(screen.width-550)+",top="+((screen.height-450)/2)+",resizable=0,scrollbars=0,status=0,titlebar=0,menubar=0,location=0");
    } else {
        winProvRef.focus();
    };
    var provForm = ecrea({eName:"FORM",target:"winProveedor",method:"POST",eChilds:[{eName:"INPUT",type:"hidden",name:"menu_accion",value:"Registro"},{eName:"INPUT",type:"hidden",name:"view_mode",value:"readonly"},{eName:"INPUT",type:"hidden",name:"prov_id",value:tgt.prvId},{eName:"INPUT",type:"hidden",name:"prov_code",value:tgt.codigo},{eName:"INPUT",type:"hidden",name:"prov_field",value:tgt.razsoc},{eName:"INPUT",type:"hidden",name:"prov_bank",value:tgt.banco},{eName:"INPUT",type:"hidden",name:"prov_bankrfc",value:tgt.rfcbanco},{eName:"INPUT",type:"hidden",name:"prov_account",value:tgt.cuenta},{eName:"INPUT",type:"hidden",name:"prov_receipt_name",value:tgt.edocta},{eName:"INPUT",type:"hidden",name:"prov_status",value:tgt.status},{eName:"INPUT",type:"hidden",name:"acc_verified",value:tgt.verificado},{eName:"INPUT",type:"hidden",name:"user_id",value:tgt.usrId},{eName:"INPUT",type:"hidden",name:"prov_rfc",value:tgt.rfc},{eName:"INPUT",type:"hidden",name:"user_email",value:tgt.email},{eName:"INPUT",type:"hidden",name:"prov_credit",value:tgt.credito},{eName:"INPUT",type:"hidden",name:"prov_paym",value:tgt.pago},{eName:"INPUT",type:"hidden",name:"prov_zone",value:tgt.zona},{eName:"INPUT",type:"hidden",name:"prov_text",value:tgt.texto},{eName:"INPUT",type:"hidden",name:"opinion_fulfilled",value:tgt.cumplido},{eName:"INPUT",type:"hidden",name:"opinion_created",value:tgt.inicia},{eName:"INPUT",type:"hidden",name:"opinion_expired",value:tgt.expira},{eName:"INPUT",type:"hidden",name:"prov_opinion_name",value:tgt.opinion},{eName:"INPUT",type:"hidden",name:"prov_return",value:"1"},{eName:"INPUT",type:"hidden",name:"lado_izquierdo",value:"hidden"},{eName:"INPUT",type:"hidden",name:"pie_pagina",value:"hidden"},{eName:"INPUT",type:"hidden",name:"encabezado",value:"hidden"},{eName:"INPUT",type:"hidden",name:"bloque_central",value:"noHeader"},{eName:"INPUT",type:"hidden",name:"principal",value:"fullWidHigh"}]});
    document.body.appendChild(provForm); // Add the form to dom
    provForm.submit(); // Just submit
}
<?php
clog1seq(-1);
clog2end("scripts.cuentas");
