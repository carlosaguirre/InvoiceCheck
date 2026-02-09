<?php
require_once dirname(__DIR__)."/bootstrap.php";
require_once "clases/Tokens.php";
global $tokenReturn;
$tokenReturn=[];
if (true) {
    $refId=7;
    $usrList=[2146, 2443,2444];
    $modList=["PruebaOn","PruebaOff"];
    $usos=1;
    $tokList=[];
    $tokList=creaToken($refId,$usrList,$modList,$usos);
    echo "<H1>Crea Tokens</H1>";
    foreach ($tokenReturn as $key => $value) {
        echo "<H2>$key</H2>";
        if (is_array($value)) foreach ($value as $vkey => $vval) {
            echo "<H3>$vkey</H3>$vval";
        } else if ($key==="log") {
            echo "<div style='white-space:pre;'>$value</div>";
        } else echo $value;
    }
    $tokUsr=$tokList[2146]??null;
    if (isset($tokUsr)) {
        $token=$tokUsr["PruebaOff"]??null;
        if (isset($token)) {
            $tokenReturn=[];
            echo "<HR>";
            eligeToken($token);
            echo "<H1>Elige Token</H1>";
            foreach ($tokenReturn as $key => $value) {
                echo "<H2>$key</H2>";
                if (is_array($value)) foreach ($value as $vkey => $vval) {
                    echo "<H3>$vkey</H3>$vval";
                } else echo $value;
            }
        } else echo "<H1>No existe modulo PruebaOn</H1>";
    } else echo "<H1>No existe token de usuario</H1>";
    die();
}
if (!isset($_POST["accion"])) {
?>
<!doctype html>
<HTML xmlns="http://www.w3.org/1999/xhtml">
<HEAD>
    <META charset="utf-8">
    <BASE href="http://invoicecheck.dyndns-web.com:81/invoice/"><!-- %BASEURI% -->
    <SCRIPT src="scripts/general.js?v=4.4l"></SCRIPT>
    <LINK href="css/general.php" rel="stylesheet" type="text/css">
    <SCRIPT>
        function cambiaAccion(valor,data) {
            const params={"accion":valor};
            for (let key in data) {
                if (data.hasOwnProperty(key) && typeof data[key] !== "function") params[key]=data[key];
            }
            //console.log("cambiaAccion:",params);
            postService("test/token.php",params,function(msg,pars,state,status) {
                //console.log(state+","+status);
                const errElem=ebyid("error");
                const useElem=ebyid("usage");
                const resElem=ebyid("result");
                const logElem=ebyid("log");
                const hisElem=ebyid("history");
                ekfil(errElem);
                ekfil(useElem);
                ekfil(resElem);
                ekfil(logElem);
                if (state==4&&status==200) {
                    //console.log("msg:'"+(msg.length>50?msg.slice(0,30)+"..["+msg.length+"].."+msg.slice(-12):msg)+"',pars:",pars,"state:"+state+",status:"+status);
                    try {
                        const jobj=JSON.parse(msg);
                        if (jobj.error) errElem.innerHTML=jobj.error; //.appendChild(ecrea({eName:"P",eText:jobj.error}));
                        if (jobj.usage) useElem.innerHTML=jobj.usage; //.appendChild(ecrea(jobj.usage));
                        if (jobj.result) resElem.innerHTML=jobj.result; //.appendChild(ecrea(jobj.result));
                        if (jobj.log) logElem.innerHTML=jobj.log; //.appendChild(ecrea(jobj.log));
                        if (jobj.history) hisElem.innerHTML+=jobj.history; //.appendChild(ecrea(jobj.history));
                    } catch (ex) {
                        errElem.appendChild(ecrea({eName:"P",eText:ex.getMessage()}));
                    }
                } else { //if (state>4||status>200) {
                    errElem.appendChild(ecrea({eName:"P",eText:"STATE="+state+", STATUS="+status}));
                }
            },function(msg,pars,evt) {
                const errElem=ebyid("error");
                errElem.appendChild(ecrea({eName:"P",eText:msg}));
            });
        }
        function validReference() {
            const refObj=ebyid("ref");
            if (refObj) {
                if (refObj.reportValidity())
                    refObj.defaultValue=refObj.value;
                else
                    refObj.value=refObj.defaultValue;
            }
        }
        function mytests() {
            //console.log("mytests:");
        }
        function showUserList() {
            const usrListElem=ebyid("usrlist");
            if (usrListElem.options.length>0)
                clrem(usrListElem,"hidden");
        }
        function hideUserList() {
            setTimeout(function() {
                const usrElem=ebyid("usr");
                const usrListElem=ebyid("usrlist");
                if(document.activeElement!==usrElem && document.activeElement!==usrListElem)
                    cladd(usrListElem,'hidden');
            }, 100);
        }
        function setNoUser() {
            const usrElem=ebyid("usr");
            delete usrElem.valueId;
            delete usrElem.fullname;
        }
        function clearUser() {
            const usrElem=ebyid("usr");
            delete usrElem.valueId;
            usrElem.value="";
            delete usrElem.fullname;
        }
        function clearUserList() {
            const usrListElem=ebyid("usrlist");
            const addUsrElem=ebyid('addUsr');
            ekfil(usrListElem);
            cladd([usrListElem,addUsrElem],'hidden');
        }
        function pickUser(optElem) {
            if (optElem) {
                const usrElem=ebyid("usr");
                const usrListElem=ebyid("usrlist");
                const addUsrElem=ebyid('addUsr');
                usrElem.valueId=optElem.value;
                usrElem.value=optElem.text;
                usrElem.fullname=optElem.fullname;
                cladd(usrListElem,'hidden');
                clrem(addUsrElem,'hidden');
            } else clearUser();
        }
        function addUser() {
            const usrElem=ebyid("usr");
            const addUsrElem=ebyid("addUsr");
            const accUsrElem=ebyid("acceptedUsers");
            if (usrElem.value.length>0) {
                let opts=accUsrElem.options;
                let optLen=opts.length;
                while(optLen--) {
                    if (opts[optLen].value===usrElem.valueId) return;
                }
                accUsrElem.appendChild(ecrea({eName:"OPTION",value:usrElem.valueId,fullname:usrElem.fullname,eChilds:[{eText:usrElem.value}]}));
                clrem(accUsrElem,"hidden");
            }
            clearUser();
        }
        function delUser() {
            const usrElem=ebyid("usr");
            const addUsrElem=ebyid("addUsr");
            const addUsrElem=ebyid("delUsr");
            const accUsrElem=ebyid("acceptedUsers");
        }
        function browseUser() {
            const usrElem=ebyid("usr");
            if (usrElem) {
                const usrListElem=ebyid("usrlist");
                const addUsrElem=ebyid('addUsr');
                let usrname=usrElem.value;
                if (usrname.length==0) {
                    clearUserList();
                    setNoUser();
                    return;
                }
                if (!usrname.includes("*") && !usrname.includes("%")) usrname+="%";
                postService("consultas/Usuarios.php", {accion:"browseUserName",nombre:usrname,onlyName:1,sortList:"nombre"}, function(text,pars,state,status){
                    if (state<4||status<200) return;
                    if (state!=4||status!=200) {
                        console.log(state+"/"+status+":"+text);
                        clearUserList();
                        setNoUser();
                        return;
                    }
                    if (text.length==0) {
                        console.log("empty: ",pars);
                        clearUserList();
                        setNoUser();
                        return;
                    }
                    try {
                        let response=JSON.parse(text);
                        if (response.result && response.result==="success") {
                            if (!response.data) {
                                console.log("Corrupt Response: "+text);
                                clearUserList();
                                setNoUser();
                                return;
                            }
                            //if (response.message) console.log("Message: "+response.message);
                            //if (response.query) console.log("Query: "+response.query);
                            //console.log("DATA: "+JSON.stringify(response.data));
                            if (response.data.length===0) {
                                clearUserList();
                                setNoUser();
                                return;
                            }
                            if (response.data.length===1 && !usrElem.value.includes("*") && !usrElem.value.includes("%")) {
                                ekfil(usrListElem);
                                const oldValue=usrElem.value;
                                // ToDo: seleccionar texto añadido
                                usrElem.value=response.data[0]["nombre"];
                                usrElem.valueId=response.data[0]["id"];
                                usrElem.fullname=response.data[0]["persona"];
                                cladd(usrListElem,'hidden');
                                clrem(addUsrElem,'hidden');
                            } else if (response.data.length>0) {
                                ekfil(usrListElem);
                                exactIdx=false;
                                fee(response.data,function(elem,idx){
                                    if (usrElem.value===elem.nombre) exactIdx=idx;
                                    usrListElem.appendChild(ecrea({eName:"OPTION",value:elem.id,fullname:elem.persona,eChilds:[{eText:elem.nombre}]}));
                                });
                                if (exactIdx!==false) {
                                    usrListElem.value=usrElem.value;
                                    usrElem.valueId=response.data[exactIdx].id;
                                    usrElem.fullname=response.data[exactIdx].persona;
                                } else {
                                    setNoUser();
                                    usrListElem.value="";
                                    usrListElem.selectedIndex=-1;
                                }
                                if (response.data.length>3) usrListElem.setAttribute("size",3);
                                //console.log(usrListElem.offsetLeft+","+usrListElem.offsetTop+" vs "+usrElem.offsetLeft+","+usrElem.offsetTop);
                                showUserList();
                            } else setNoUser();
                            /*
                            const rect=filterLineElem.getBoundingClientRect();
                            const size=Math.min(5,response.data.length);
                            let divWidth = rect.right-rect.left;
                            let difHeight = (rect.bottom-rect.top);
                            let divTop = rect.top+difHeight+1;
                            let divLeft = rect.left;
                            let divHeight=size*difHeight;
                            const optionsArr=[];
                            fee(response.data, function(elem) {
                                optionsArr.push({eName:"DIV",style:"display:flex;justify-content:flex-start;align-items:center;padding:5px;cursor:pointer;",userId:elem.id,userName:elem.nombre,eText:elem.persona,className:"hoverDark2",
                                onclick:appendUser});
                            });
                            const resultListObj={eName:"DIV",id:"resultList",style:"position:fixed;z-index:8898;width:"+divWidth+"px;height:"+divHeight+"px;top:"+divTop+"px;left:"+divLeft+"px;box-shadow:0 1px 3px 0 rgba(0,0,0,.25);overflow-x: hidden;overflow-y: auto;border-radius: 6px;padding:5px;",className:"basicBG",eChilds:optionsArr,onclick:function(evt){return eventCancel(evt);}};
                            ekil(ebyid("resultList"));
                            filterLineElem.parentNode.appendChild(ecrea(resultListObj));
                            */
                        } else {
                            console.log("FAILURE: "+text);
                            clearUserList();
                            setNoUser();
                            //ekil(ebyid("resultList"));
                        }
                    } catch (ex) {
                        console.log("error: ",ex,pars.xmlHttpPost.responseText);
                        clearUserList();
                        setNoUser();
                    }
                });
            }
        }
    </SCRIPT>
</HEAD>
<BODY class="w8marginx">
    <DIV id="contenedor" class="centered">
        <div id="encabezado">
            <div id="head_logo" class="centered"><a href="http://invoicecheck.dyndns-web.com:81/invoice/" target="_self" width="96" height="96" style="display:inline-block;"><img src="imagenes/logos/invoiceCheck.png" width="96" height="96" alt="Invoice Check" longdesc="Logo InvoiceCheck"></a></div>
            <div id="head_main"><h1 class="centered">Validación de Facturas Electrónicas del Corporativo</h1></div>
            <br class="clear">
        </div>
        <div id="bloque_central" class="noEncabezado">
            <div class="sticky toTop basicBG">
                <H1 class="nomarginblock"><select class="likebh1" id="accion" onchange="cambiaAccion(this.value);"><option>TOKENS</option><option value="crea">CREAR TOKEN</option><option value="elige">ELIGE TOKEN</option><option value="lista">LISTADO TOKENS</option></select></H1>
                <div id="error"></div>
                <div id="usage"><?= showUsage() ?></div>
            </div>
            <div id="result"></div>
            <div id="log"></div>
            <div id="history" class="hidden"></div>
        </div>
    </DIV>
</BODY>
</HTML>
<?php
    die();
}
$accion=strtolower($_POST["accion"]??"");
switch($accion) {
    case "crea":
        if (!isset($_POST["ref"][0])||!isset($_POST["usrs"][0])||!isset($_POST["mod"][0])) showUsage("crea");
        else creaToken($_POST["ref"],explode(",",$_POST["usrs"]),$_POST["mod"],$_POST["uso"]??null);
        break;
    case "elige":
        if (isset($_POST["token"][0])) eligeToken($_POST["token"],$_POST["status"]??"ocupado");
        else if (isset($_POST["tokid"][0])) eligeTokenId($_POST["tokid"],$_POST["status"]??"ocupado");
        else showUsage("elige");
        break;
    default: // lista
        showUsage("lista");
        listaTokens();
}
echo json_encode($tokenReturn);
die();

function creaToken($referenceId,$userIdList,$moduleName,$usageKey) {
    global $tokenReturn;
    $tokObj=new Tokens();
    $tokList=$tokObj->creaAccion($referenceId,$userIdList,$moduleName,$usageKey);
    $tokenReturn["result"]="";
    foreach ($tokList as $usrId => $tokData) {
        if (is_array($tokData)) {
            foreach ($tokData as $modulo => $token) {
                $tokenReturn["result"].="<p>{$usrId} - {$modulo} - <b>$token</b></p>";
            }
        } else $tokenReturn["result"].="<p>{$usrId} - <b>$tokData</b></p>";
    }
    showLogs($tokObj);
    return $tokList;
}
function eligeTokenId($tokid,$status="ocupado") {
    global $tokenReturn;
    $tokObj=new Tokens();
    $tokData=$tokObj->getData("id=$tokid",0,"token");
    if (isset($tokData[0]["token"][0])) eligeToken($tokData[0]["token"],$status);
    else {
        if (!isset($tokenReturn["error"])) $tokenReturn["error"]="";
        $tokenReturn["error"].="<p>Token Id desconocido: $tokid</p>";
        showLogs($tokObj);
    }
}
function eligeToken($token,$status="ocupado") {
    global $tokenReturn;
    $tokObj=new Tokens();
    if ($tokObj->eligeToken($token,$status)) {
        if (!DBi::isAutocommit()) DBi::commit();
        $tokenReturn["result"]="<p>Token Elegido: $token</p>";
    } else {
        if (!DBi::isAutocommit()) DBi::rollback();
        if (!isset($tokenReturn["error"])) $tokenReturn["error"]="";
        $tokenReturn["error"].="<p>Token no elegido: $token</p>";
    }
    showLogs($tokObj);
}
// "refId", "usrId", "modulo", "status", "usos"
function listaTokens() {
    global $tokenReturn;
    $refId=$_POST["refId"]??null;
    $usrId=$_POST["usrId"]??null;
    $modulo=$_POST["modulo"]??null;
    $status=$_POST["status"]??null;
    $usos=$_POST["usos"]??null;
    $pageno=$_POST["pagenum"]??null;
    $rows_per_page=$_POST["rowsnum"]??null;
    $tokObj=new Tokens();
    $tokObj->rows_per_page=0;
    if ($pageno!==null) {
        $tokObj->pageno=+$pageno;
        if ($rows_per_page!==null) $tokObj->rows_per_page=+$rows_per_page;
    }
    if ($refId!==null) $whr="refId='$refId'";
    if ($usrId!==null) {
        if (isset($whr[0])) $whr.="and"; else $whr="";
        $whr.="usrId='$usrId'";
    }
    if ($modulo!==null) {
        if (isset($whr[0])) $whr.="and"; else $whr="";
        $whr.="modulo='$modulo'";
    }
    if ($status!==null) {
        if (isset($whr[0])) $whr.="and"; else $whr="";
        $whr.="status='$status'";
    }
    if ($usos!==null) {
        if (isset($whr[0])) $whr.="and"; else $whr="";
        $whr.="usos='$usos'";
    }
    $tokData=$tokObj->getData($whr??null);
    if (isset($tokData[0])) {
        $tokenReturn["result"]="<TABLE>";
        $hasHeaders=false;
        $options=[];
        foreach ($tokData as $idx=>$row) {
            if (!$hasHeaders) {
                $tokenReturn["result"].="<THEAD>";
                $firstRow="";
            }
            $tokenReturn["result"].="<TR>";
            foreach ($row as $key=>$value) {
                if ($key==="id"||$key==="modifiedTime") continue;

                if (!isset($options[$key])) $options[$key]=[];
                if (!$hasHeaders) {
                    if ($key==="token") {
                        $tokenReturn["result"].="<TH>".strtoupper($key)."</TH>";
                    } else {
                        $tokenReturn["result"].="<TH>%%{$key}%%</TH>";
                        $options[$key][$value]=1;
                    }
                    $firstRow.="<TD>$value</TD>";
                } else {
                    if (!in_array($key, ["id","token","modifiedTime"])) {
                        if (isset($options[$key][$value])) $options[$key][$value]++;
                        else $options[$key][$value]=1;
                    }
                    $tokenReturn["result"].="<TD>$value</TD>";
                }
            }
            $tokenReturn["result"].="</TR>";
            if (!$hasHeaders) {
                $tokenReturn["result"].="</THEAD><TBODY><TR>$firstRow</TR>";
                $hasHeaders=true;
            }
        }
        foreach ($options as $key => $list) {
            ksort($options[$key]);
            $listHtml="<select class='liketh' id='$key' onchange=\"cambiaAccion('lista',{{$key}:this.value});\"><option>".strtoupper($key)."</option>";
            foreach ($options[$key] as $value => $count) {
                $listHtml.="<option value='$value' count='$count'>$value</option>";
            }
            $listHtml.="</select>";
            $tokenReturn["result"]=str_replace("%%{$key}%%", $listHtml, $tokenReturn["result"]);
        }
        if ($hasHeaders) $tokenReturn["result"].="</TBODY>";
        $tokenReturn["result"].="</TABLE>";
    } else {
        unset($tokenReturn["result"]);
        if (!isset($tokenReturn["error"])) $tokenReturn["error"]="";
        $tokenReturn["error"].="VACIO";
    }
}
function showLogs($tokObj) {
    global $tokenReturn, $query;
    if (!isset($tokenReturn["log"])) $tokenReturn["log"]="";
    $tokenReturn["log"].="$query\n".$tokObj->log."\n";
    if (isset($tokObj->errors[0][0])) {
        if (!isset($tokenReturn["error"])) $tokenReturn["error"]="";
        $tokenReturn["error"].="<H3>ERRORS 1</H3><UL>";
        foreach ($tokObj->errors as $idx => $errtxt) {
            $tokenReturn["error"].="<LI> $idx : $errtxt</LI>";
        }
        $tokenReturn["error"].="</UL>";
    } else if (!empty(DBi::$errors)) {
        if (!isset($tokenReturn["error"])) $tokenReturn["error"]="";
        $tokenReturn["error"].="<H3>ERRORS 2</H3><UL>";
        foreach (DBi::$errors as $code => $text) {
            $tokenReturn["error"].="<LI> $code : $text<br>$query</LI>";
        }
        $tokenReturn["error"].="</UL>";
    } else {
        if (!isset($tokenReturn["error"])) $tokenReturn["error"]="";
        $errno=DBi::getErrno();
        $error=DBi::getError()??"";
        if (!empty($errno)) $tokenReturn["error"].="<H3>ERROR</H3><UL><LI> $errno : $error</LI></UL>";
    }
}
function showUsage($accion=null) {
    global $tokenReturn;
    $tokenReturn["usage"]="";
    switch($accion) {
        case "crea":
            $tokenReturn["usage"].="<p><b>Referencia:</b><input id=\"ref\" type=\"text\" pattern=\"[0-9]+\" oninput=\"validReference();\"></p>";
            $tokenReturn["usage"].="<div><b>Usuarios:</b><input id=\"usr\" type=\"text\" class=\"folio\" oninput=\"browseUser();\" onfocus=\"showUserList();\" onblur=\"hideUserList();\"><div id=\"addUsr\" class=\"hidden inblock boldValue switchColor transparent br2so0 no_selection pointer marginV2\" onmouseenter=\"clrem(this,['transparent','bggold']);cladd(this,'bgblack');\" onmouseleave=\"clrem(this,['bgblack','bggold']);cladd(this,'transparent');\" onmousedown=\"clrem(this,['transparent','bgblack']);cladd(this,'bggold');\" onmouseup=\"clrem(this,['transparent','bggold']);cladd(this,'bgblack');\" onclick=\"addUser();\">+</div><select id=\"acceptedUsers\" class=\"hidden folio\"></select><div id=\"delUsr\" class=\"hidden inblock boldValue switchColor transparent br2so0 no_selection pointer marginV2\" onmouseenter=\"clrem(this,['transparent','bggold']);cladd(this,'bgblack');\" onmouseleave=\"clrem(this,['bgblack','bggold']);cladd(this,'transparent');\" onmousedown=\"clrem(this,['transparent','bgblack']);cladd(this,'bggold');\" onmouseup=\"clrem(this,['transparent','bggold']);cladd(this,'bgblack');\" onclick=\"delUser();\">-</div><div class=\"relative\"><select id=\"usrlist\" class=\"folio abs hidden\" onchange=\"pickUser(this.options[this.selectedIndex]);\" onblur=\"hideUserList();\"></select><img src=\"data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7\" onload=\"const uls=ebyid('usrlist').style;uls.left=ebyid('usr').offsetLeft+'px';uls.outline='white solid 1px';mytests();ekil(this);\"></div></div>";
            $tokenReturn["usage"].="<p><b>mod</b> = <u>[a-zA-Z]+</u></p>";
            $tokenReturn["usage"].="<p><b>uso</b> (opcional) = <u>[0-9]+</u></p>";
            break;
        case "elige":
            $tokenReturn["usage"].="<p><b>token</b> = <u>.+</u></p>";
            $tokenReturn["usage"].="<p>O</p>";
            $tokenReturn["usage"].="<p><b>tokid</b> = <u>/d+</u></p>";
            break;
        case "lista":
            //$tokenReturn["usage"].="<p>SIN PARAMETROS</p>";
            //$tokenReturn["usage"].="<p>O</p>";
            //$tokenReturn["usage"].="<p><b>ref</b> = <u>[0-9]+</u></p>";
            break;
        default:
            if (!isset($tokenReturn["error"])) $tokenReturn["error"]="";
            $tokenReturn["error"].="<p>Uso incorrecto con accion '$accion'</p>";
    }
}
