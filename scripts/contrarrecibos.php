<?php
require_once dirname(__DIR__)."/bootstrap.php";
header("Content-type: application/javascript; charset: UTF-8");
if (!hasUser() || !isset(getUser()->persona)) {
    doReload();
}
clog2ini("scripts.contrarrecibos");
clog1seq(1);
$isManager=validaPerfil("Gestor")||validaPerfil("Origen Contra Recibos");
?>
window.onload = fillPaginationIndexes;
var entidadTipos = ["codigo", "rfc", "razon"];
var showGpo= false, showPrv=false;
doShowLogs=true;
//doShowFuncLogs=true;
function waitRollCover(el,wId) {
    //conlog("INI WAIT ROLL COVER "+wId);
    const rect=el.getBoundingClientRect();
    let lft=rect.left;
    let rgt=rect.right;fee(lbycn('colRemision'),el=>clrem(el,'hidden'));
    let wid=rgt-lft;
    lft=el.offsetLeft;
    let top=rect.top;
    let btm=rect.bottom;
    let hgt=btm-top;
    top=el.offsetTop;
    if (wid < hgt) {
        let len=(hgt-wid)/2;
        lft-=len;
        //rgt+=len;
        wid=hgt;
    } else if (wid > hgt) {
        let len=(wid-hgt)/2;
        top-=len;
        //btm+=len;
        hgt=wid;
    }
    if (top>0) {
        if (top<3) {
            lft-=top;
            wid+=(2*top);
            hgt+=(2*top);
            top=0;
        } else {
            lft-=3;
            wid+=6;
            hgt+=6;
            top-=3;
        }
    }
    //conlog("LFT="+lft+", TOP="+top+", WID="+wid+", HGT="+hgt);
    let wriElem=ebyid(wId);
    if (!wriElem) {
        const wriObj={eName:"IMG",id:wId,src:"imagenes/icons/rollwait3.gif"};
        wriElem=ecrea(wriObj);
        el.parentNode.appendChild(wriElem);
    }
    wriElem.style.position="absolute";
    wriElem.style.top=top+"px";
    wriElem.style.left=lft+"px";
    wriElem.style.width=wid+"px";
    wriElem.style.height=hgt+"px";
    return wriElem;
}
function switchPrvVal() {
    //conlog("INI function switchPrvVal");
    const crb=ebyid("contraRscpBtn");
    if (crb) {
        if (crb.switchNum && crb.switchNum>0) {
            //conlog("END function switchPrvVal "+crb.switchNum);
            return;
        }
        const lcp=lbycn("colPrv");
        crb.switchNum=lcp.length;
        const wrc=waitRollCover(crb,"waitCRB");
        if (lcp.length>0) fee(lcp,el => {
            setTimeout(function(e,c,w){
                //conlog("INI timeoutFunc waitCRB");
                if (e.hasAttribute('altval')) {
                    const val=e.textContent;
                    e.textContent=e.getAttribute('altval');
                    e.setAttribute('altval',val);
                    clfix(e,["ellipsisCel","maxWid250"]);
                }
                c.switchNum--;
                if (c.switchNum<=0) {
                    clfix(lbycn('hcEF'),'fixed77');
                    ekil(w);
                    //conlog("END timeoutFunc waitCRB ekil");
                } //else conlog("END timeoutFunc waitCRB "+c.switchNum);
            },10,el,crb,wrc);
        });
        else {
            //conlog("END WAIT ROLL COVER waitCRB. ZERO ITEMS");
            ekil(wrc);
        }
    } //conlog("Not found contraRscpBtn");
    //conlog("END function switchPrvVal");
}
var numCrChk=0;
var lstCrChk={};
var sumCrChk={};
var totSumCC=0;
function checkAll(cn,val) {
    const lst=lbycn(cn);
    if (lst.length>0) {
        fee(lst,el=>{
            if (el.checked!==val) {
                el.checked=val;
                check(el,true);
            }
        });
        clset(["authBtn","sumChkLbl"],"invisible",numCrChk==0);
        /*if (totSumCC<=0) {
            totSumCC=0;
            ebyid("sumChkLbl").textContent="";
        } else {*/
            ebyid("sumChkLbl").textContent="$"+totSumCC.toFixed(2).replace(/(\d)(?=(\d{3})+\.)/g, '$1,');
        //}
        //conlog("CHECKED "+numCrChk+", Sum="+totSumCC);
    }
}
function check(el,skipSet) {
    if (el) {
        let cll=el.classList;
        const fId=el.id.slice(4);
        const frw=ebyid("ctf_"+fId);
        const ttl=+frw.getAttribute("total");
        const tcl=ebyid("tot_"+fId);
        const cn=el.getAttribute("crIdx");
        //console.log("CHECK "+cn);
        if (el.checked) {
            numCrChk++;
            if (!(cn in lstCrChk)) lstCrChk[cn]=0;
            lstCrChk[cn]++;
            if (!(cn in sumCrChk)) sumCrChk[cn]=0;
            sumCrChk[cn]+=ttl;
            totSumCC+=ttl;
        } else {
            numCrChk--;
            if (numCrChk<0) numCrChk=0;
            lstCrChk[cn]--;
            if (lstCrChk[cn]<0) lstCrChk[cn]=0;
            sumCrChk[cn]-=ttl;
            if (sumCrChk[cn]<0) sumCrChk[cn]=0;
            totSumCC-=ttl;
        }

        // toDo: modifica Total visto dependiendo de los checkboxes marcados
        //ekfil(tcl);
        //if (sumCrChk[cn])
        //tcl.appendChild(ecrea({eName:"SPAN",className:"strikeout",eText:"Total: $"+(ttl.toFixed(2).replace(/(\d)(?=(\d{3})+\.)/g, '$1,'))}));

        //conlog("CHECKED "+numCrChk);
        //if (numCrChk<0) numCrChk=0;
        if (!skipSet) {
            clset(["authBtn","sumChkLbl"],"invisible",numCrChk==0);
            //console.log("SUM="+totSumCC);
            /*if (totSumCC<=0) {
                totSumCC=0;
                ebyid("sumChkLbl").textContent="";
            } else { */
                ebyid("sumChkLbl").textContent="$ "+totSumCC.toFixed(2).replace(/(\d)(?=(\d{3})+\.)/g, '$1,');
            //}
        }
    }
}
function sendAuth() {
    const chkd=lbycn("cfchk");
    let cfnum=[];
    fee(chkd,el=>{
        if (el.checked) {
            cfnum.push(el.id.slice(4));
        }
    });
    if (cfnum.length==0) return;
    const usr="<?=hasUser()?getUser()->persona:""?>";
    //conlog("INI function sendAuth", cfnum);
    postService("consultas/Contrafacturas.php",{action:"auth",list:cfnum},function(msg,params,state,status){
        if (state==4&&status==200) {
            if (msg.length>0) {
                try {
                    const jobj=JSON.parse(msg);
                    if (jobj.result) {
                        if (jobj.result==="refresh")
                            location.reload(true);
                        else if (jobj.result==="success") {
                            //conlog("SUCCESS!!!" + msg);
                            if (jobj.cfList) {
                                jobj.cfList.forEach(cfId=>{
                                    let el=ebyid("chk_"+cfId);
                                    if (el) {
                                        const pe=el.parentNode;
                                        const crIdx=el.getAttribute("crIdx");
                                        ekil(el);
                                        el=ecrea({eName:"IMG",src:"imagenes/icons/chkd24.png",width:"20px",height:"20px",id:"chk_"+cfId, title:"Autorizada por <?=hasUser()?(getUser()->persona??""):""?>"});
                                        el.style.width="20px";
                                        el.style.height="20px";
<?php if ($_esAdministrador) { ?>
                                        el.onclick=muestraRemoverAutorizacion;
<?php } ?>
                                        pe.appendChild(el);
                                        const ca=ebyid("chkAll_"+crIdx);
                                        if (ca) {
                                            const na=+ca.getAttribute("na")-1;
                                            if (na<=1) ekil(ca);
                                            else {
                                                ca.setAttribute("na",na);
                                            }
                                        }
                                    } else conlog("POP: No se encontró elemento chk_"+cfId);
                                });
                            }
                            numCrChk=0;
                            lstCrChk={};
                            sumCrChk={};
                            totSumCC=0;
                            ebyid("sumChkLbl").textContent="";
                            cladd(["authBtn","sumChkLbl"],"invisible");
                        } else {
                            conlog("AUTHORIZATION SUBMIT. " + jobj.result + (jobj.message?": "+jobj.message:""));
                        }
                    } else {
                        conlog("AUTHORIZATION SUBMIT. EMPTY RESULT"+(jobj.message?": "+jobj.message:""));
                    }
                } catch (ex) {
                    conlog("AUTHORIZATION SUBMIT. EXCEPTION ON PARSING: ",ex,"\nText: ",msg);
                }
            } else {
                conlog("AUTHORIZATION SUBMIT: EMPTY RESPONSE");
            }
        } else if (state>=4 && status>200) {
            conlog("AUTHORIZATION SUBMIT "+state+"/"+status+" FAILURE: "+msg,params);
        }
    },function(errmsg,params,evt){
        conlog("AUTHORIZATION SUBMIT FAILURE: "+errmsg,params,evt);
    });
}
function muestraRemoverAutorizacion(event) {
    const tgt=event.target;
    const chkId=tgt.id;
    //console.log("INI muestraRemoverAutorizacion "+chkId);
    if (tgt.nextElementSibling && clhas(tgt.nextElementSibling,"unauthBtn")) {
        //console.log("exists");
        return;
    }
    fee(lbycn("unauthBtn"),el=>ekil(el));
    const cell=tgt.parentNode;
    cladd(cell,"relative");
    const unAuthBtn=ecrea({eName:"IMG",src:"imagenes/icons/deleteIcon20.png",className:"abs unauthBtn"});
    unAuthBtn.crId=chkId.substr(4);
    unAuthBtn.onclick=preRemoverAutorizacion;
    cell.appendChild(unAuthBtn);
}
function preRemoverAutorizacion(event) {
    const tgt=event.target;
    //console.log("INI preRemoverAutorizacion "+tgt.crId,tgt);
    const row=ebyid("ctf_"+tgt.crId);
    const folio=row.firstElementChild.textContent;
    overlayConfirmation("<p>Confirme que desea desautorizar la factura "+folio+"</p><p><b>Motivo:</b> <input id='motivoDesautorizar' type='text'>", "DESAUTORIZAR FACTURA", removerAutorizacion,false,function(){tgt.motivo=ebyid('motivoDesautorizar').value;return true;});
}
function removerAutorizacion() {
    //console.log("INI removerAutorizacion");
    let crId=null,motivo=null;
    fee(lbycn("unauthBtn"),el=>{crId=el.crId;motivo=el.motivo;ekil(el);});
    //console.log("CRID="+crId+", MOTIVO="+motivo);
    //toDo: postService para desautorizar
    //success: quitar paloma y poner checkbox
}
function verificaFactura(factId) {
    console.log("INI function verificaFactura "+factId);
    let xmlhttp = ajaxRequest();
    xmlhttp.onreadystatechange = function () {
        if (xmlhttp.readyState == 4 && xmlhttp.status == 200) {
            //const txt=xmlhttp.responseText;
            //if (txt.length>400)
                //console.log("RESPONSETEXT = \'"+txt.substr(0,200).trim()+"...\n"+"..."+txt.substr(-200).trim()+"\'");
            //else console.log("RESPONSETEXT = \'"+txt+"\'");
            //console.log("READY TO SHOW OVERLAYMESSAGE");
            try {
                overlayMessage(xmlhttp.responseText, 'VALIDACI&Oacute;N DE FACTURA');
                //console.log("OVERLAYMESSAGE SENT");
            } catch (ex1) {
                console.log("OVERLAYMESSAGE WITH ERROR: ",ex1);
            }
            try {
                setTimeout(()=>{
                    console.log("BEGIN TIMEOUT in verificaFactura");
                    const divScr=ebyid("table_of_concepts").parentNode;
                    console.log("PARENT TABLE: ", divScr);
                    const cel2=divScr.parentNode;
                    const cel1=(cel2.tagName==="DIV"?cel2.previousElementSibling:cel2);
                    console.log("PREV CEL: ", cel1);

                    const ovWid=ebyid("overlay").offsetWidth;
                    const bxWid = ovWid-44;
                    let c1Wid = cel1.offsetWidth;
                    const c2Wid = bxWid-c1Wid-6-6;
                    c1Wid-=6;

                    ebyid("dialogbox").style.width=bxWid+"px";
                    ebyid("dialog_resultarea").style.width=bxWid+"px";
                    ebyid("tabla_valida_pedido").style.width=bxWid+"px";
                    cel1.style.width=c1Wid+"px";
                    cel2.style.width=c2Wid+"px";
                    divScr.style.width=c2Wid+"px";
                    console.log("FINISH TIMEOUT in verificaFactura");
                },10);
            } catch (exc) {
                console.log("OVERLAYMESSAGE TIMEOUT EXCEPTION: ", exc);
            }
        }
    };
    //console.log("READY TO OPEN verificaFactura");
    xmlhttp.open("GET","selectores/verificafactura.php?facturaId="+factId+"&ctfAuth=1&readonly", true);
    xmlhttp.send();
    console.log("END verificaFactura SENT!");
}
function saveReferral(invId) {
    const rem = ebyid("numremision");
    let deft = rem.defaultValue; if (deft.length>20) { deft = deft.slice(0, 20); rem.defaultValue=deft; }
    let text = rem.value; if (text.length>20) { text = text.slice(0,20); rem.value=text; }
    console.log("INI saveReferral: newValue='"+text+"' vs defaultValue='"+deft+"'");
    if (rem.isSaving) { console.log("IS SAVING"); return; }
    if (deft===text) { console.log("NOTHING TO SAVE"); return; }
    rem.isSaving=true;

    // Decodifica entidades HTML numéricas si existen
    const txt = text.replace(/&#\d+;/gm, function(s) { return String.fromCharCode(s.match(/\d+/gm)[0]); });

    // Codifica caracteres especiales como entidades HTML numéricas
    const enc = txt.replace(/[\u00A0-\u9999<>\&]/g, function(i) { return '&#' + i.charCodeAt(0) + ';'; });

    // Si el texto original es igual al codificado en longitud, se usa el codificado
    if (text.length > 0 && enc.length >= text.length) { text = enc; }

    // Envía los datos al servidor usando postService
    rem.style.backgroundColor="palegoldenrod";
    // No es el momento, en caso de error quiero regresar al anterior // rem.oldvalue=rem.value; // text
    readyService("consultas/Facturas.php", {action: "saveReferral", invId: invId, text: text}, function(jobj,extra) {
        if (jobj.result && jobj.result==="success") {
            console.log("Successful referral saving", jobj);
            rem.style.backgroundColor="springgreen";
            setTimeout(function(){rem.style.backgroundColor="white";rem.defaultValue=rem.value;rem.isSaving=false;},2000);
        } else {
            console.log("Failed to save Referral, resetting value to default", jobj);
            rem.style.backgroundColor="salmon";
            setTimeout(function(){rem.style.backgroundColor="white";rem.value=rem.defaultValue;rem.isSaving=false;},2000);
        }
    }, function(errmsg,respText,extra) {
        console.log("ERROR saveReferral process: "+errmsg);
        console.log("Response Text: ", respText);
        console.log("Extra: ", extra);
        rem.style.backgroundColor="salmon";
        setTimeout(function(){rem.style.backgroundColor="white";rem.value=rem.defaultValue;rem.isSaving=false;},2000);
    });
    console.log("END saveReferral: ENVIADO");
}
function addAuthCFButton(idCtf) {
    const chk=ebyid("chk_"+idCtf);
    if (!chk) {
        console.log("ERR addAuthCFButton "+idCtf+": NOT FOUND");
        return;
    }
    const isChkd=chk.checked;
    console.log("function INI addAuthCFButton chk"+idCtf+" "+(isChkd?"checked":"unchkd")+" : "+getElIdf(chk));
    if (!chk || chk.tagName!=="INPUT"/* || chk.checked*/) return;
    const ctft=ebyid("contrafooter");
    if (!ctft.crlog) ctft.crlog="";
    const cb=ebyid("closeButton");
    cb.value="CERRAR"; //OMITIR";
    //let ab=ebyid("authButton");
    //if (ab)
        ekil("authButton");
    //if (!ab) {
        ekil("prevVFButton");ekil("nextVFButton");
        const chkIdx=+chk.getAttribute("cfIdx");
        if (chkIdx>0) {
            const pv=ecrea({eName:"INPUT",id:"prevVFButton",type:"button",value:"ANTERIOR",className:"marginV1"});
            pv.onclick=function(ev) {
                overlayClose(this);
            }
            pv.callOnClose=function() {
                const abn=ebyid("authButton");
                if(!abn) {
                    console.log("PrevVFButton: NOT FOUND authButton");
                    return;
                }
                const cfid=abn.cfid;
                const chkx=ebyid("chk_"+cfid);
                const cfIdx=+chkx.getAttribute("cfIdx");
                const cfChks=lbycn("cfchk");
                if (cfIdx>0) {
                    const prvChk_pv=cfChks[cfIdx-1];
                    const prvChkId=prvChk_pv?prvChk_pv.id:"null";
                    //console.log("Call On Close: currChk=chk_"+cfid+", prevChk="+prvChkId);
                    prvChk_pv.scrollIntoView({behavior:'smooth', block:'center'});
                    ctft.crlog+="PRV"+cfIdx+":"+cfid+"|";
                    setTimeout(verificaFactura,350,prvChk_pv.getAttribute("fId"));
                } else console.log("NO PREV cfIdx="+cfIdx+", length="+cfChks.length);
                ekil(this);
            }
            cb.parentNode.insertBefore(pv,cb);
            console.log("ADDED NEW PrevVFButton "+idCtf);
        }
        const ab=ecrea({eName:"INPUT",id:"authButton",type:"button",value:(isChkd?"DESAUTORIZAR":"AUTORIZAR"),className:"marginV1",cfid:idCtf});
        ab.onclick=function(ev){
            //console.log("AuthButton.click ("+(ab.callOnClose?"has":"doesnt have")+" callOnClose)");
            const cfid=this.cfid; //console.log("LISTO PARA AUTORIZAR "+cfid);
            const chkx=ebyid("chk_"+cfid);
            if (!chkx.log) chkx.log="";
            chkx.checked=!chkx.checked;
            const cfIdx=+chkx.getAttribute("cfIdx");
            chkx.log+="X"+(chkx.checked?"1":"0")+"_"+cfIdx+":"+cfid;
            const cfChks=lbycn("cfchk");
            const params={action:"check",ctfId:cfid,chk:chkx.checked?"1":"0"};
            if (cfIdx<(cfChks.length-1)) {
                const nxtChk_ab=cfChks[cfIdx+1];
                const nxtChkId=nxtChk_ab?nxtChk_ab.id:"null";
                params.nxtCfIdx=(cfIdx+1);
                params.nxtChkId=nxtChkId.slice(4);
                chkx.log+="("+(cfIdx+1)+":"+nxtChkId.slice(4)+")";
            } else chkx.log+="(ISLASTONE)";
            if (!ab.callOnClose) {
                chkx.log+="[NO_CALLONCLOSE]";
                params.log=ctft.crlog+chkx.log;
                ctft.crlog="";chkx.log="";
                readyService("consultas/Contrafacturas.php",params,(j,x,n)=>{},(m,r,x)=>{});
            }
            overlayClose(this); //console.log("CLOSED ",this);
            ebyid("closeButton").value="Cerrar";
            check(chkx); //console.log("CHECKED ",this); //ekil(this); //console.log("KILLED ",this);
        };
        ab.callOnClose=function(){
            //console.log("AuthButton.callOnClose");
            const cfid=this.cfid;
            const chkx=ebyid("chk_"+cfid);
            const cfIdx=+chkx.getAttribute("cfIdx");
            const cfChks=lbycn("cfchk");
            const params={action:"next",ctfId:cfid,cfIdx:cfIdx,log:ctft.crlog+chkx.log};
            ctft.crlog="";chkx.log="";
            if (cfIdx<(cfChks.length-1)) {
                const nxtChk_ab=cfChks[cfIdx+1];
                const nxtChkId=nxtChk_ab?nxtChk_ab.id:"null";
                params.nxtCfIdx=(cfIdx+1);
                params.nxtChkId=nxtChkId;
                //console.log("Call On Close: currChk=chk_"+cfid+", nextChk="+nxtChkId);
                nxtChk_ab.scrollIntoView({behavior:'smooth', block:'center'});
                setTimeout(verificaFactura,400,nxtChk_ab.getAttribute("fId"));
            } else params.nxtChkId="END";//console.log("NO NEXT cfIdx="+cfIdx+", length="+cfChks.length);
            readyService("consultas/Contrafacturas.php",params,(j,x,n)=>{},(m,r,x)=>{});
        }
        cb.parentNode.insertBefore(ab,cb);
        console.log("ADDED NEW AuthButton "+idCtf);
        const allChks=lbycn("cfchk");
        if ((chkIdx+1) < allChks.length) {
            const nv=ecrea({eName:"INPUT",id:"nextVFButton",type:"button",value:"SIGUIENTE",className:"marginV1"});
            nv.onclick=function(ev) {
                overlayClose(this);
            }
            nv.callOnClose=function() {
                //console.log("NextVFButton.callOnClose");
                const abn=ebyid("authButton");
                if(!abn) {
                    //console.log("NextVFButton: NOT FOUND authButton");
                    return;
                }
                const cfid=abn.cfid;
                const chkx=ebyid("chk_"+cfid);
                const cfIdx=+chkx.getAttribute("cfIdx");
                const cfChks=lbycn("cfchk");
                if (cfIdx<(cfChks.length-1)) {
                    const nxtChk_nv=cfChks[cfIdx+1];
                    const nxtChkId=nxtChk_nv?nxtChk_nv.id:"null";
                    //console.log("Call On Close: currChk=chk_"+cfid+", nextChk="+nxtChkId);
                    nxtChk_nv.scrollIntoView({behavior:'smooth', block:'center'});
                    ctft.crlog+="NXT"+cfIdx+":"+cfid+"|";
                    setTimeout(verificaFactura,350,nxtChk_nv.getAttribute("fId"));
                } //else console.log("NO NEXT cfIdx="+cfIdx+", length="+cfChks.length);
            }
            cb.parentNode.insertBefore(nv,cb);
            console.log("ADDED NEW NextVFButton "+idCtf);
        }
    //} else {
    //    ab.cfid=idCtf;
    //    ab.value=(isChkd?"DESAUTORIZAR":"AUTORIZAR");
    //    console.log("FOUND "+getElIdf(ab));
    //}
}
if (!additionalResizeScript) additionalResizeScript=adjustFooter;
function adjustFooter() {
    const outer=ebyid("scrolltablediv900");
    const inner=ebyid("inner");
    const docsCol=ebyid("docsCol");
    const lftPad=10;
    const rgtWid=outer.offsetWidth-inner.offsetWidth+docsCol.offsetWidth+lftPad;
    //conlog("Right Width = OUTER("+outer.offsetWidth+") - INNER("+inner.offsetWidth+") + DOCS("+docsCol.offsetWidth+") + LfPd("+lftPad+") = "+rgtWid);
    const rgtSd=ebyid("rgtSide");
    rgtSd.style.width=rgtWid+"px";
    rgtSd.parentNode.style.width=rgtWid+"px";
    //conlog("Right Side = "+rgtWid);
    //const crTab=outer.firstElementChild;
    const authBtn=ebyid("authBtn");
    authBtn.style.width=authBtn.offsetWidth+"px";
    //const tsl=ebyid("sumChkLbl"); // apartar el doble del auth Button
    const midWid=authBtn.offsetWidth*2.5;
    authBtn.parentNode.style.width=(/*authBtn.offsetWidth*/ midWid+2)+"px";
    const hlfWid=(inner.offsetWidth /* -authBtn.offsetWidth */ -midWid-2)/2;
    //conlog("Half Screen = "+hlfWid);
    const sumWid=hlfWid - docsCol.offsetWidth - lftPad;
    const sumTot=ebyid("sumTot");
    sumTot.style.width=(sumWid-2)+"px";
    sumTot.parentNode.style.width=sumWid+"px";
    //conlog("Sum Tot = "+sumWid);
    const regNum=ebyid("regNum");
    const regWid=152;
    regNum.style.width=(regWid-2)+"px"; //regNum.offsetWidth+"px";
    regNum.parentNode.style.width=(regWid)+"px";
    const lftWid=hlfWid-regWid;
    const lftSd=ebyid("lftSide");
    lftSd.style.width=lftWid+"px";
    lftSd.parentNode.style.width=lftWid+"px";
    //conlog("REG NUM = "+regWid);
    //conlog("Left Side = "+lftWid);
    ftt=ebyid("contrafoottb");
    ftt.style.minWidth=inner.offsetWidth+"px";
}
function pickType(elem) {
          if (!elem || !elem.value) { return; }
          for (var i=0; i < entidadTipos.length; i++) {
              var tipo = entidadTipos[i];
              var selGpo = document.getElementById("gpot"+tipo);
              var selPrv = document.getElementById("prvt"+tipo);
              if (elem.value == "t"+tipo) {
                  showGpo = selGpo;
                  showPrv = selPrv;
              } else {
                  selGpo.classList.add("hidden");
                  selPrv.classList.add("hidden");
              }
          }
          if (showGpo) { showGpo.classList.remove("hidden"); }
          if (showPrv) { showPrv.classList.remove("hidden"); }
}
function buscaFacturas() {
    document.forms["ackfactform"].command.value="Buscar";
    var elements = document.getElementsByClassName("datatable");
    for (var i=0; i<elements.length; i++) {
        elements[i].classList.remove("hidden");
    }
}
function agregaDatoPost(name, value) {
    var formName = 'ackfactform';
    var formElem = document.getElementById(formName);
    var elem = document.getElementById(name);
    if (!elem) {
        elem = document.createElement("INPUT");
        elem.type="hidden";
        elem.name=name;
        elem.id=name;
        elem.className="datoPost";
        formElem.appendChild(elem);
    }
    elem.value=value;
}
function agregaValorPost(verifElem) {
    if (verifElem.removeSpaces||verifElem.getAttribute("removeSpaces")) verifElem.value=verifElem.value.replace(/\s/g, "");
    var f_elem = document.getElementById("f_"+verifElem.name);
    if (f_elem) f_elem.value = verifElem.value;
}
function eliminaDatosPost() {
    // var edpElems = document.getElementsByClassName("datoPost");
    // conlog(edpElems);
    removeElementsByClass("datoPost");
    // var edpElems = document.getElementsByClassName("datoPost");
    // conlog(edpElems);
}
var _forma_submitted = false;
function submitAjax(submitValue) {
    conlog("submitAjax: "+submitValue);
    var postURL = 'selectores/contrarrecibos.php';
    var formName = 'ackfactform';
    var resultDiv = 'dialog_tbody';
    var waitingHtml = '<tr><td colspan=\'12\' class=\'centered\'><img src=\'<?=$waitImgName?>\' width=\'360\' height=\'360\'></td></tr>';
    var formElem = document.forms[formName];
    formElem.command.value = submitValue;
    conlog("to ajaxPost: "+postURL+", "+formName+", "+resultDiv+". Command="+formElem.command.value);
    document.getElementById(resultDiv).innerHTML = "";
    cladd("contrafooter","hidden");
    const rcb=ebyid("contraRscpBtn");
    setLateClick(rcb,"RS");
    //const cvb=ebyid("contraViewBtn");
    //setLateClick(cvb,"-");
    //cladd([rcb,cvb],"hidden");
    cladd(rcb,"hidden");
    ajaxPost(postURL, formName, resultDiv, waitingHtml, function(xhp){ if (xhp.readyState==4 && xhp.status==200 && xhp.responseText.length>0) {
        ebyid("contrafooter").crlog="";
        clrem("contrafooter","hidden");
        lateClick(rcb);
        //lateClick(cvb);
        //clrem([rcb,cvb],"hidden");
        clrem(rcb,"hidden");
    }});
    conlog("submitAjax DONE!");
    return false;
}
function setLateClick(el,val) {
    if (el) {
        if (el.textContent===val) {
            //conlog("INI function setLateClick "+el.id+": val="+val);
            el.click();
            el.lateclick=true;
        } else {
            //conlog("INI function setLateClick "+el.id+": val!="+val);
            el.lateclick=false;
        }
    } else conlog("INI function setLateClick: No Elem");
}
function lateClick(el) {
    if (el && el.lateclick) {
        //conlog("INI function lateClick "+el.id+": Click");
        el.click();
        el.lateclick=false;
    } //else if (el && el.id) conlog("INI function lateClick "+el.id+": Nada");
    //else conlog("INI function lateClick ",el);
}
function selectedItem(prefix) {
    var forma = document.ackfactform;
    if (forma) {
        var tipoListaElem = forma.tipolista;
        if (tipoListaElem) {
            var aux = Object.prototype.toString.call(tipoListaElem); // IE=[object HtmlCollection], FF/Ch=[object RadioNodeList]
            if (aux == "[object HTMLCollection]") {
                for (var i=0; i<tipoListaElem.length; i++) {
                    if (tipoListaElem[i].checked)
                        tipoListaVal = tipoListaElem[i].value;
                }
            } else tipoListaVal = tipoListaElem.value;
        }
    }
    var itemVal = getValueTxt(prefix+tipoListaVal);
    //conlog(prefix+" "+tipoListaVal+" = "+itemVal);
    fillValue(prefix+"tcodigo", itemVal);
    fillValue(prefix+"trfc", itemVal);
    fillValue(prefix+"trazon", itemVal);
    
    var gpotc = document.getElementById(prefix+"tcodigo");
    var opts = gpotc.options;
    for (var i=0; i<opts.length; i++) {
        if (opts[i].value===itemVal) {
            //conlog("Seleccionado: "+opts[i].text);
            break;
        }
    }
}
function recalculaEmpresas() {
    //conlog("INI recalculaEmpresas");
    var xmlhttp = ajaxRequest();
    xmlhttp.onreadystatechange = function() {
        if (xmlhttp.readyState == 4 && xmlhttp.status == 200) {
            var respTxt = xmlhttp.responseText;
            var elem = document.getElementById("gpoSelectArea");
            elem.innerHTML=respTxt;
            //conlog("REFRESH complete "+respTxt.length);
        }
    };
    var tipocodigo = document.getElementById("tipocodigo");
    var tiporfc = document.getElementById("tiporfc");
    var tiporazon = document.getElementById("tiporazon");
    var tipolista = tipocodigo.checked?"tcodigo":tiporfc.checked?"trfc":tiporazon.checked?"trazon":"tcodigo";
    xmlhttp.open("GET","consultas/Grupo.php?selectorhtml=1&tipolista="+tipolista,true);
    xmlhttp.send();
}
function recalculaProveedores() {
    //conlog("INI recalculaProveedores");
    var xmlhttp = ajaxRequest();
    xmlhttp.onreadystatechange = function() {
        if (xmlhttp.readyState == 4 && xmlhttp.status == 200) {
            var respTxt = xmlhttp.responseText;
            var elem = document.getElementById("prvSelectArea");
            elem.innerHTML=respTxt;
            //conlog("REFRESH complete "+respTxt.length);
        }
    };
    var tipocodigo = document.getElementById("tipocodigo");
    var tiporfc = document.getElementById("tiporfc");
    var tiporazon = document.getElementById("tiporazon");
    var tipolista = tipocodigo.checked?"tcodigo":tiporfc.checked?"trfc":tiporazon.checked?"trazon":"tcodigo";
    xmlhttp.open("GET","consultas/Proveedores.php?selectorhtml=1&tipolista="+tipolista,true);
    xmlhttp.send();
}
function folioFix(value) {
    fee(lbycn('folioRelated'),elem=>clicksOff(elem,(value.length===0 || !value)));
    //conlog("INI function folioFix: '"+value+"' "+(typeof value));
}
function clicksOff(elem,value) {
    if (value) {
        clrem(elem,"clicksOff");
        //clrem(elem,"disabled");
    } else {
        cladd(elem,"clicksOff");
        //cladd(elem,"disabled");
    }
}
<?php
if ($_esPruebas) {
?>
function chkIfExists(img) {
  const anchor = img.parentElement;
  const url = anchor.href;
  console.log("INI function chkIfExists", img);
  let xmlhttp = ajaxRequest();
  xmlhttp.onreadystatechange = function() {
    if (xmlhttp.readyState == 4 && xmlhttp.status == 200) {
      const txt = xmlhttp.responseText.trim();
      if (txt==="0") {
        console.log("PAGE DOESNT EXIST");
        anchor.style.display="none";
      } else console.log("PAGE EXISTS");
    } else console.log("STATUS: "+xmlhttp.readyState+"/"+xmlhttp.status);
  };
  xmlhttp.open("GET",url,true);
  xmlhttp.send();
  console.log("CHECKING IF URL DATA EXISTS: "+url);
}
<?php
}
if ($_esSistemas || $isManager) { 
?>
var colorInterval;
var colorTimeout;
function overlayCR2Config(folio) {
    //conlog("INI function overlayCR2Config folio="+folio);
    ekil("authButton");
    const xmlhttp = ajaxRequest();
    xmlhttp.overrideMimeType("application/json");
    xmlhttp.onreadystatechange = function() {
        if (xmlhttp.readyState == 4 && xmlhttp.status == 200) {
            const jsobj = JSON.parse(xmlhttp.responseText);
            const drarea = document.getElementById("dialog_resultarea");
            if (drarea) {
                while(drarea.firstChild) drarea.removeChild(drarea.firstChild);
                const blk = document.createElement("DIV");
                blk.classList.add("contrarrecibo");
                let elem = document.createElement("P");
                elem.appendChild(document.createTextNode("Folio: "+jsobj.folio+" ("));
                const fixer = document.createElement("SELECT");
                fixer.className="reduced";
                let opt = document.createElement("OPTION");
                opt.value="ORIGINAL";
                opt.text="ORIGINAL";
                opt.selected = !jsobj.esCopia;
                fixer.append(opt);
                opt = document.createElement("OPTION");
                opt.value="COPIA";
                opt.text="COPIA";
                opt.selected = jsobj.esCopia;
                fixer.append(opt);
                fixer.oldvalue = jsobj.esCopia?"COPIA":"ORIGINAL";
                fixer.onchange=function() {
                    fixer.blur();
                    if (colorTimeout) {clearTimeout(colorTimeout);conlog("reset timeout");}
                    if (colorInterval) {clearInterval(colorInterval);conlog("reset interval");}
                    fixer.visibility="hidden";
                    postService("consultas/Contrarrecibos.php", {action:"fixCopy",id:jsobj.id,value:fixer.value}, function(responseText,params,readyState,hStatus) {
                        if (readyState==4 && hStatus==200) {
                            let bgc="bgredvip";
                            if (responseText && responseText.length>=10 && responseText.slice(0,10)==="SUCCESSFUL") {
                                bgc="bggreenvip";
                                fixer.oldvalue=fixer.value;
                            }
                            fixer.visibility="visible";
                            fixer.classList.add(bgc);
                            //conlog("interval started");
                            colorInterval=setInterval(toggleClass,250,fixer,bgc);
                            colorTimeout=setTimeout(function() {if(colorInterval) { clearInterval(colorInterval);fixer.classList.remove(bgc);if(bgc==="bgredvip") fixer.value=fixer.oldvalue; conlog("interval ended");}else conlog("interval not found");},1000);
                            //conlog("RESPONSE...: "+responseText);
                        }
                    });
                }
                elem.appendChild(fixer);
                elem.appendChild(document.createTextNode(", IMPRESO "+jsobj.estaImpreso+" VE"+(jsobj.estaImpreso==1?"Z":"CES")+")"));
                blk.appendChild(elem);
                elem = document.createElement("INPUT");
                elem.id="counterId";
                elem.type="hidden";
                elem.value=jsobj.id;
                blk.appendChild(elem);
                elem = document.createElement("INPUT");
                elem.id="counterCode";
                elem.type="hidden";
                elem.value=jsobj.folio;
                blk.appendChild(elem);
                elem = document.createElement("P");
                elem.appendChild(document.createTextNode("Empresa: ("+jsobj.alias+") "+jsobj.razonSocial));
                blk.appendChild(elem);
                elem = document.createElement("P");
                elem.appendChild(document.createTextNode("Proveedor: ("+jsobj.codigo+") "+jsobj.proveedor));
                blk.appendChild(elem);
                elem = document.createElement("P");
                elem.appendChild(document.createTextNode("Fecha Revision: "+jsobj.fecha));
                blk.appendChild(elem);
                if (jsobj.metodopago && jsobj.metodopago.length>0) {
                    elem = document.createElement("P");
                    elem.id = "metodoPue";
                    elem.className="importantValue fontImportant";
                    elem.appendChild(document.createTextNode("Método PUE"));
                    blk.appendChild(elem);
                }
                elem = document.createElement("P");
                elem.appendChild(document.createTextNode("Factura(s): "));
                blk.appendChild(elem);
                elem = document.createElement("DIV");
                elem.id = "counter2eraseDiv";
                elem.innerHTML = jsobj.facturas;
                blk.appendChild(elem);
                elem = document.createElement("P");
                elem.id="counterTotal";
                elem.appendChild(document.createTextNode("Total: $"+jsobj.total));
                blk.appendChild(elem);
                //if (jsobj.metodopago && jsobj.metodopago.length>0) {
                //    elem = document.createElement("P");
                //    elem.id = "metodoPue";
                //    elem.className="importantValue fontImportant";
                //    elem.appendChild(document.createTextNode("Factura PUE"));
                //    blk.appendChild(elem);
                //}
                if (jsobj.proceso) { // folio,uuid,status,detalle,fecha,nombre
                    elem = ecrea({eName:"HR"});
                    blk.appendChild(elem);
                    elem = ecrea({eName:"H3",eText:"PROCESO"});
                    blk.appendChild(elem);
                    const procList=[];
                    jsobj.proceso.forEach((row)=>{
                        let desc="";
                        if(row.detalle.slice(0,12)==="generacontra"){
                            if(row.folio && row.folio.length>0)desc="Factura "+row.folio;
                            else if(row.uuid.length>0)desc="Factura ["+row.uuid.slice(-8)+"]";
                        }else if(row.status==="Accion"||row.status=="FIXCOPY")
                            desc=row.detalle;
                        else if(row.status==="Consulta")
                            desc="Consulta "+row.detalle;
                        else if(row.status==="esCopia")
                            desc="Consulta Copia";
                        else if (row.status==="Borrado"){
                            const b=row.detalle.indexOf("BORRADO")+8;
                            if(row.detalle.slice(b,b+5)==="TOTAL")
                                desc="BORRADO TOTAL";
                            else desc="BORRADO "+row.detalle.slice(b);
                        } else if (row.status==="Autorizado") {
                            const p=row.detalle.indexOf(":");
                            desc=p>0?row.detalle.slice(0,p):row.detalle;
                        } else desc=row.status+": "+row.detalle;
                        if (desc.length>30) desc=desc.slice(0,27)+"...";
                        procList.push({eName:"TR",eChilds:[{eName:"TD",eText:row.nombre},{eName:"TD",eText:desc},{eName:"TD",eText:row.fecha}]});
                    });
                    elem = ecrea({eName:"TABLE",className:"contrarrecibo centered transparent",eChilds:[{eName:"THEAD",eChilds:[{eName:"TR",eChilds:[{eName:"TH",className:"centered",eText:"NOMBRE"},{eName:"TH",className:"centered",eText:"DESCRIPCION"},{eName:"TH",className:"centered",eText:"FECHA"}]}]},{eName:"TBODY",eChilds:procList}]});
                    blk.appendChild(elem);
                }
                if (jsobj.firmas) { // accion,nombre,fecha,texto
                    elem = ecrea({eName:"HR"});
                    blk.appendChild(elem);
                    elem = ecrea({eName:"H3",eText:"FIRMAS"});
                    blk.appendChild(elem);
                    const firmList=[];
                    jsobj.firmas.forEach((row)=>{
                        let desc="";
                        const txt=(row.texto&&row.texto.length>0)?row.texto:"-";
                        if(row.accion==="AUTORIZA") {
                            const p=txt.indexOf(":");
                            desc=(p>0?txt.slice(0,p):txt);
                        } else if (row.accion==="ELIMINA") {
                            if (txt.slice(0,7)!=="Elimina") desc="Elimina ";
                            desc=txt;
                        } else if (row.accion==="SOLICITAPAGO")
                            desc=row.accion+" "+txt;
                        else if (row.accion==="ORIGINAL"||row.accion==="COPIA"||row.accion==="PROVEEDOR") {
                            const e=txt.indexOf(" ");
                            if (e>0) desc=txt.slice(0,e)+" "+row.accion+" "+txt.slice(e+1);
                            else desc=txt+" "+row.accion;
                        } else desc=row.accion+": "+txt;
                        if (desc.length>30) desc=desc.slice(0,27)+"...";
                        firmList.push({eName:"TR",eChilds:[{eName:"TD",eText:row.nombre},{eName:"TD",eText:desc},{eName:"TD",eText:row.fecha}]});
                    });
                    elem = ecrea({eName:"TABLE",className:"contrarrecibo centered transparent",eChilds:[{eName:"THEAD",eChilds:[{eName:"TR",eChilds:[{eName:"TH",className:"centered",eText:"NOMBRE"},{eName:"TH",className:"centered",eText:"DESCRIPCION"},{eName:"TH",className:"centered",eText:"FECHA"}]}]},{eName:"TBODY",eChilds:firmList}]});
                    blk.appendChild(elem);
                }
                ekil(["eraseCounterButton","resetCounterButton"]);
                elem = document.createElement("INPUT");
                elem.type="button";
                elem.id="eraseCounterButton";
                elem.value="Eliminar";
                elem.onclick=eliminandoContrarrecibos;
                const cba=ebyid("closeButtonArea");
                cba.insertBefore(elem,cba.firstElementChild);
                //blk.appendChild(elem);
                drarea.appendChild(blk);
                const cb=ebyid("closeButton");
                cb.callOnClose=()=>ekil(["eraseCounterButton","resetCounterButton"]);
                fillInnerHtml("dialog_title", "Ajustes en Contrarrecibo");
                _dragElement = document.getElementById("dialogbox");
                _dragElement.style.top = '0px';
                _dragElement.style.left = '0px';
                _dragElement.focus();
                _dragElement.blur();
                applyVisibility("overlay");
            }
        }
    };
    
    xmlhttp.open("GET","consultas/Contrarrecibos.php?asJson&interactive&folio="+folio,true);
    xmlhttp.send();
}
function eliminandoContrarrecibos(event) {
    conlog("INI eliminandoContrarrecibos");
    if (!event) event = window.event;
    var target = event.target;
    if (target) conlog("TARGET: "+target.tagName+" "+target.id+" "+target.value);
    else conlog("NO TARGET");
    if (target && target.value === "Confirmar") {
        //conlog("CONFIRMADO PARA ELIMINAR CONTRARRECIBO");
        //conlog(target);
        if (target.hasAttribute("counter")) {
            var counterId = target.getAttribute("counter");
            //conlog("Erasing Counter "+counterId);
            var xmlhttp = ajaxRequest();
            xmlhttp.overrideMimeType("text/plain; charset=utf-8");
            xmlhttp.onreadystatechange = function() {
                if (xmlhttp.readyState == 4 && xmlhttp.status == 200) {
                    var text = xmlhttp.responseText;
                    conlog("RECIBIDO (con counter "+counterId+"): "+text);
                    toggleVisibility("overlay");
                    ekil(["eraseCounterButton","resetCounterButton"]);
                    buscaFacturas();
                    ajaxPost("selectores/contrarrecibos.php", "ackfactform", "dialog_tbody", "<tr><td colspan='12' class='centered'><img src='<?=$waitImgName?>' width='360' height='360'></td></tr>", function(xhp){ if (xhp.readyState==4 && xhp.status==200 && xhp.responseText.length>0) { ebyid("contrafooter").crlog=""; }});
                }
            };
            xmlhttp.open("GET","consultas/Contrafacturas.php?eraseCounter="+counterId,true);
            xmlhttp.send();
        } else if (target.hasAttribute("counterinvoices")) {
            var counterInvoices = target.getAttribute("counterinvoices");
            var counterId = target.getAttribute("counterId");
            var newTotal = target.getAttribute("newTotal");
            //conlog("Erasing Counter Invoices "+counterInvoices+", from counterId="+counterId+" w/newTotal="+newTotal);

            var xmlhttp = ajaxRequest();
            xmlhttp.overrideMimeType("text/plain; charset=utf-8");
            xmlhttp.onreadystatechange = function() {
                if (xmlhttp.readyState == 4 && xmlhttp.status == 200) {
                    var text = xmlhttp.responseText;
                    conlog("RECIBIDO (sin counter "+counterId+"): "+text);
                    toggleVisibility("overlay");
                    ekil(["eraseCounterButton","resetCounterButton"]);
                    buscaFacturas();
                    ajaxPost("selectores/contrarrecibos.php", "ackfactform", "dialog_tbody", "<tr><td colspan='12' class='centered'><img src='<?=$waitImgName?>' width='360' height='360'></td></tr>", function(xhp){ if (xhp.readyState==4 && xhp.status==200 && xhp.responseText.length>0) { ebyid("contrafooter").crlog=""; }});
                }
            };
            xmlhttp.open("GET","consultas/Contrafacturas.php?eraseList="+counterInvoices+"&counterId=" +counterId+ "&newTotal=" +newTotal, true);
            xmlhttp.send();
        }
        return;
    }
    var chkElems = lbycn("counter_removal");
    console.log("Counter Removal: ", chkElems);
    var counterIds = [];
    fee(chkElems, function(el) {
        let crId=el.id; // counter_del_000 (12+3)
        if (crId.length>12 && crId.slice(0,12)==="counter_del_" && el.tagName==="INPUT" && el.type==="checkbox" && el.checked) {
            crId=crId.slice(12);
            counterIds.push(crId);
        }
    });
    var counterIdsLen=counterIds.length;

    var invElems = lbycn("counterInvoiceRow");
    if (counterIdsLen===0) {
        if (invElems.length==1) {
            // Como solo hay un elemento no hay checkboxes
            let crId=invElems[0].id; // counterInvoiceRow_000 (18+3)
            if (crId.length>18 && crId.slice(0,18)==="counterInvoiceRow_") {
                crId=crId.slice(18);
                counterIds.push(crId);
                counterIdsLen++;
            } else {
                console.log("NADA QUE ELIMINAR 1", invElems);
                return;
            }
        } else if (invElems.length>0) {
            // Si hay facturas pero ninguna seleccionada, entonces cancelar borrado
            console.log("NADA QUE ELIMINAR 2", invElems);
            return;
        }
        // CR sin facturas puede ser eliminado
    }
    var counterCode = document.getElementById("counterCode");
    var counterId = document.getElementById("counterId");
    if (counterIdsLen==chkElems.length || invElems.length==1) {
        //conlog("ELIMINAR CONTRARRECIBO");
        var eraseArea = document.getElementById("counter2eraseDiv");
        while(eraseArea.firstChild) eraseArea.removeChild(eraseArea.firstChild);
        var confirmationMessage = "Confirme que desea eliminar el contrarrecibo "+counterCode.value+".";
        var boldTag = document.createElement("B");
        boldTag.appendChild(document.createTextNode(confirmationMessage));
        eraseArea.appendChild(boldTag);
        var eraseButton = document.getElementById("eraseCounterButton");
        eraseButton.value = "Confirmar";
        eraseButton.setAttribute("counter",counterId.value);
        var resetButton = document.createElement("INPUT");
        resetButton.id="resetCounterButton";
        resetButton.type = "button";
        resetButton.value = "Regresar";
        resetButton.onclick = function() { overlayCR2Config(counterCode.value); }
        eraseButton.parentNode.appendChild(resetButton);
    } else {
        //conlog("ELIMINAR ALGUNA FACTURA DEL CONTRARRECIBO"); 
        var newTotal = 0;
        var delIds = [];
        var counterInvoiceRows = document.getElementsByClassName("counterInvoiceRow");
        for(var i=0; i<counterInvoiceRows.length; i++) {
            var invRowId = counterInvoiceRows[i].id;
            var invRowTotal = counterInvoiceRows[i].getAttribute("total");
            var invId = invRowId.slice(18);
            //conlog("Verifying per counter row "+invId+" = $"+invRowTotal);
            var invEraseCheck = document.getElementById("counter_del_"+invId);
            if (invEraseCheck) {
                //conlog("Found Invoice Check: "+(invEraseCheck.checked?"CHECKED":"CLEAR"));
                if (invEraseCheck.checked) {
                    counterInvoiceRows[i].classList.add("markedForDeletion");
                    delIds.push(invId);
                } else {
                    newTotal += +invRowTotal;
                    //conlog("Acumulated new Total = "+newTotal);
                }
            }
        }
        var markedForDeletion = document.getElementsByClassName("markedForDeletion");
        while(markedForDeletion[0]) markedForDeletion[0].parentNode.removeChild(markedForDeletion[0]);
        var eraseFooter = document.getElementById("eraseFooter");
        if (eraseFooter) eraseFooter.parentNode.removeChild(eraseFooter);
        var counterTotal = document.getElementById("counterTotal");
        while (counterTotal.firstChild) counterTotal.removeChild(counterTotal.firstChild);
        counterTotal.appendChild(document.createTextNode("Total: $"+(newTotal.toFixed(2).replace(/(\d)(?=(\d{3})+\.)/g, '$1,'))));
        
        var eraseButton = document.getElementById("eraseCounterButton");
        eraseButton.value = "Confirmar";
        eraseButton.setAttribute("counterInvoices",delIds.join(","));
        eraseButton.setAttribute("counterId",counterId.value);
        eraseButton.setAttribute("newTotal",newTotal);
        var resetButton = document.createElement("INPUT");
        resetButton.type = "button";
        resetButton.value = "Regresar";
        resetButton.onclick = function() { overlayCR2Config(counterCode.value); }
        eraseButton.parentNode.appendChild(resetButton);
        var elem = document.getElementById("eraseHeader");
        if (elem) elem.parentNode.removeChild(elem);
        var itemL = document.getElementsByClassName("counterCell");
        while(itemL.item(0)) itemL[0].parentNode.removeChild(itemL[0]);
    }
}
<?php 
}
?>
var maxTimes=5;
function fixLink(elem,crId,times) {
    console.log("INI fixLink");
    if (!times) times=0;
    setTimeout(function(el,ci,tm) {
        postService("consultas/Contrarrecibos.php",{action:"isCopy",id:ci},function(msg,params,state,status){
            console.log("ACTION ISCOPY STATE:"+state+", STATUS:"+status+", MSG:"+msg);
            if (state==4&&status==200&&msg==="1") {
                if (msg==="1") {
                    const img=el.firstElementChild;
                    img.src="imagenes/icons/cr2Cop32.png";
                    el.removeAttribute("onclick");
                } else {
                    tm++;
                    if (tm<maxTimes) fixLink(el,ci,tm);
                }
            }
        });
    },100,elem,crId,times);
}
function removeAllChildNodes(node) {
    if (node) while(node.firstChild) node.removeChild(node.firstChild);
}
function triggerAllChecksByClass(value, classname) {
    var elems = document.getElementsByClassName(classname);
    for(var i=0; i<elems.length; i++) if (elems[i].tagName === "INPUT" && elems[i].type === "checkbox") elems[i].checked=value;
}
function dateIniSet() {
    //conlog("function dateIniSet");
    var iniDateElem = document.getElementById("fechaInicio");
    var day = strptime(date_format, iniDateElem.value);
    setFullMonth(prev_month(day));
}
function dateEndSet() {
    //conlog("function dateEndSet");
    var iniDateElem = document.getElementById("fechaInicio");
    var day = strptime(date_format, iniDateElem.value);
    setFullMonth(next_month(day));
}
function setFullMonth(date) {
    var firstDay = first_of_month(date);
    var lastDay = day_before(first_of_month(next_month(date)));
    var iniDateElem = document.getElementById("fechaInicio");
    var endDateElem = document.getElementById("fechaFin");
    iniDateElem.value = strftime(date_format, firstDay);
    endDateElem.value = strftime(date_format, lastDay);

    //adjustCalMonImgs();
    adjust_calendar();
}
function adjustCalMonImgs(tgtWdgt) { adjust_calendar(tgtWdgt,false,{freeRange:true}); }
/*function adjustCalMonImgs(tgtWdgt) {
    const iniDateElem = ebyid("fechaInicio");
    const endDateElem = ebyid("fechaFin");

    const iniday = strptime(date_format, iniDateElem.value);
    const endday = strptime(date_format, endDateElem.value);
    const inimon = iniday.getMonth()+1;
    const endmon = endday.getMonth()+1;
    const sameyr = (iniday.getYear()===endday.getYear());

    let curday=iniday;
    let curmon=inimon;
    if (tgtWdgt===iniDateElem) {
        if (inimon!==endmon||!sameyr) {
            const lastDay=day_before(first_of_month(next_month(iniday)));
            endDateElem.value=strftime(date_format,lastDay);
        } else if (iniday>endday) endDateElem.value=iniDateElem.value;
    } else if (tgtWdgt===endDateElem) {
        corday=endday;
        curmon=endmon;
        if (inimon!==endmon||!sameyr) {
            const firstDay=first_of_month(endday);
            iniDateElem.value=strftime(date_format,firstDay);
        } else if (iniday>endday) iniDateElem.value=endDateElem.value;
    }

    let prevMon = curmon-1;
    while(prevMon<1) prevMon+=12;
    let nextMon = curmon+1;
    while(nextMon>12) nextMon-=12;

    const prevClass = "calendar_month_"+padDate(prevMon);
    const nextClass = "calendar_month_"+padDate(nextMon);
    const calMonPrev = ebyid("calendar_month_prev");
    const calMonNext = ebyid("calendar_month_next");
    calMonPrev.className = prevClass;
    calMonNext.className = nextClass;
}*/
function validFolios(evt) {
    evt = evt || window.event;
    let key = evt.keyCode || evt.which;
    const keyCode = key;
    key = String.fromCharCode(key);
    if (isActionKey(evt)) {
        console.log("Action "+key+" | "+keyCode);
        return eventCancel(evt);
    }
    if (key.length==0) {
        console.log("empty "+keyCode);
        return eventCancel(evt);
    }
    if (/[0-9,]/.test(key)) {
        //if (evt.code) console.log(evt.code);
        //else console.log(key);
        return true;
    }
    console.log("invalid: "+key+" | "+keyCode,evt);
    return eventCancel(evt);
}
<?php
clog1seq(-1);
clog2end("scripts.contrarrecibos");
