<?php
require_once dirname(__DIR__)."/bootstrap.php";

if (isset($_POST["action"])) {
    //echo "RECEIVED POST: ".http_build_query($_POST);
    switch($_POST["action"]) {
        case "user":
            if (!isset($_POST["user"][0])) errNDie("Solicitud de usuario sin nombre",["POST"=>$_POST]);
            if (!isset($usrObj)) { require_once "clases/Usuarios.php"; $usrObj=new Usuarios(); }
            //$usrData=$usrObj->getData("nombre='$_POST[user]'",0,"id");
            //if (!isset($usrData[0]["id"][0])) errNDie("No se encontro usuario",["query"=>$query]);
            $log="LOG:";
            $idUsuario=$usrObj->getValue("nombre",$_POST["user"],"id");
            $log.="\nQ: $query => $idUsuario";
            successNFlush("Usuario encontrado",["data"=>$idUsuario]);
            if (!isset($perObj)) { require_once "clases/Perfiles.php"; $perObj = new Perfiles(); }
            $perObj->rows_per_page=0;
            $perObj->clearFullMap();
            $perMap=$perObj->getFullMap("id", "nombre");
            if (!isset($upObj)) { require_once "clases/Usuarios_Perfiles.php"; $upObj = new Usuarios_Perfiles(); }
            $upObj->rows_per_page=0;
            $idPerfiles=array_column($upObj->getData("idUsuario=$idUsuario",0,"idPerfil"), "idPerfil");
            $log.="\nQ: $query => ".implode(", ",$idPerfiles);
            $perfiles=[];
            if (isset($idPerfiles[0])) {
                if (!isset($gpoObj)) { require_once "clases/Grupo.php"; $gpoObj = new Grupo(); }
                $gpoObj->rows_per_page=0;
                $gpoObj->clearFullMap();
                $gpoMap=$gpoObj->getFullMap("id", "alias");
                if (!isset($ugObj)) { require_once "clases/Usuarios_Grupo.php"; $ugObj = new Usuarios_Grupo(); }
                $ugObj->rows_per_page=0;
                foreach ($idPerfiles as $idp) {
                    $perfil=$perMap[$idp];
                    $idGrupos=array_column($ugObj->getData("idUsuario=$idUsuario and idPerfil=$idp",0,"distinct idGrupo"),"idGrupo");
                    $log.="\n$idp($perfil) Q: $query => ".implode(", ",$idGrupos);
                    if (isset($idGrupos[0])) {
                        $grupos=[];
                        foreach ($idGrupos as $idg) {
                            $grupos[]=$gpoMap[$idg];
                        }
                        $log.="\nNoSortGrp: ".implode(", ",$grupos);
                        sort($grupos);
                        $perfil.=" (".implode(", ",$grupos).")";
                    }
                    $perfiles[]=$perfil;
                }
            }
            /*successNFlush*/successNDie("Perfiles de usuario",["data"=>$perfiles,"log"=>$log]);
            break;
        default: errNDie("Accion desconocida",["POST"=>$_POST]);
    }
    //die();
}
?>
<html xmlns="http://www.w3.org/1999/xhtml">
  <head>
    <meta charset="utf-8">
    <?= isBrowser(["Edge","IE"])?"<meta http-equiv=\"x-ua-compatible\" content=\"ie=edge\" />":"" ?>
    <base href="<?= $_SERVER['HTTP_ORIGIN'] . $_SERVER['WEB_MD_PATH'] ?>" target="_blank">
    <title>Usuarios Perfiles Test</title>
    <script>
        function log(texto, ...args) { console.log(texto, ...args); }
        function isElement(o) {
            return (
                typeof HTMLElement === "object" ? o instanceof HTMLElement : //DOM2
                o && typeof o === "object" && o !== null && o.nodeType === 1 && typeof o.nodeName==="string"
            );
        }
        function ebyid(id) { return document.getElementById(id); }
        function ecrea(props) {
            if (isElement(props)) return props;
            let propNames=Object.keys(props);
            if (props.eName) {
                let idx=propNames.indexOf("eName");
                if (idx>=0) propNames.splice(idx,1);
                idx=propNames.indexOf("eText");
                if (idx>=0) propNames.splice(idx,1);
                idx=propNames.indexOf("eChilds");
                if (idx>=0) propNames.splice(idx,1);
                let newObj=document.createElement(props.eName);
                for(let i=0;i<propNames.length;i++) {
                    newObj[propNames[i]]=props[propNames[i]];
                }
                if (props.eChilds) {
                    if (Array.isArray(props.eChilds)) for (let i=0; i<props.eChilds.length; i++) {
                        let child = ecrea(props.eChilds[i]);
                        if (child) newObj.appendChild(child);
                    } else {
                        let child = ecrea(props.eChilds);
                        if (child) newObj.appendChild(child);
                    }
                } else if (props.eText) newObj.appendChild(document.createTextNode(props.eText));
                return newObj;
            } else if (props.eText) {
                let newObj=document.createTextNode(props.eText);
                return newObj;
            }
            return null;
        }
        function doclick(elem) { return elem.onclick.apply(elem); }
        function isEnter(kyCd) { return kyCd==13; }
        function ajaxRequest() {
            if (window.XMLHttpRequest) try { // if Mozilla, Safari etc
                return new XMLHttpRequest;
            } catch (e) {}
            if (window.ActiveXObject) { //Test for support for ActiveXObject in IE (ignore that XMLHttpRequest in IE7 is broken)
                const activexmodes=["Microsoft.XMLHTTP", "Msxml2.XMLHTTP"] //activeX versions to check for in IE
                for (let i=0; i<activexmodes.length; i++) try {
                    return new ActiveXObject(activexmodes[i]);
                } catch (e) {}
            }
            return false;
        }
        function postService(url, parameters, retFunc, errFunc, progressFunc) {
            let xmlHttpPost = ajaxRequest();
            let fd = new FormData();
            if (parameters) for (let key in parameters)
                if (parameters.hasOwnProperty(key) && typeof parameters[key] !== "function") {
                    if (Array.isArray(parameters[key])) {
                        let pArr=parameters[key];
                        let pKey=key+"[]";
                        for (let i=0;i<pArr.length;i++) fd.append(pKey,pArr[i]);
                    } else switch(key) {
                        case "timeout": xmlhttpPost.timeout=parameters[key]; break;
                        default: fd.append(key, parameters[key]);
                    }
                }
            xmlHttpPost.open("POST", url, true);
            xmlHttpPost.send(fd);
            xmlHttpPost.parameters=parameters;
            xmlHttpPost.onabort=function(evt) {log("INI xhp.abort: ",evt)};
            xmlHttpPost.onerror=function(evt) {if(errFunc)errFunc(evt);else log("INI xhp.error: ",evt);};
            xmlHttpPost.onload=function(evt) {if(retFunc)retFunc(evt);else log("INI xhp.load: ",evt);};
            xmlHttpPost.onloadstart=function (evt) { log("INI xhp.loadstart: ",evt);};
            xmlHttpPost.onloadend=function (evt) { log("INI xhp.loadend: ",evt);};
            xmlHttpPost.onprogress=function(evt) {if(progressFunc)progressFunc(evt);else log("INI xhp.progress: ",evt);};
            xmlHttpPost.ontimeout=function (evt) { log("INI xhp.timeout: ",evt);};
            return xmlHttpPost; // para manipular solicitud, vg llamar metodo abort()
        }
        // ------------------------------ L O C A L   F U N C T I O N S ------------------------------ //
        function getUserData() {
            let elem=ebyid("username");
            postService("test/admon.php",{action:"user",user:elem.value},resultCallback,errorCallback,progressCallback);
        }
        function setContentLine(text,properties) {
            const line={"eName":"P","eText":text};
            if (properties) for (prop in properties) {
                if (properties.hasOwnProperty(prop) && !line[prop]) line[prop]=properties[prop];
            }
            ebyid("contenido").appendChild(ecrea(line));
        }
        function setErrorLine(text) {
            setContentLine(text,{style:"color: darkred;"});
        }
        function resultCallback(evt) {
            const xhr=evt.target;
            const typ=evt.type; // load o progress
            if (!xhr.progressIndex) xhr.progressIndex=0;
            if (xhr.responseText.slice(xhr.progressIndex,1)==="#") {
                const lastProgressIndex=xhr.responseText.lastIndexOf("#");
                const responseBlock=xhr.responseText.slice(xhr.progressIndex+1,lastProgressIndex).split("#");
                responseBlock.forEach(function(chunk) {
                    try {
                        let jobj=JSON.parse(chunk);
                        if (jobj.result) {
                            if (jobj.result==="success") {
                                let message=jobj.message;
                                if (jobj.data) {
                                    if (Array.isArray(jobj.data)) message+=": "+jobj.data.join(", ");
                                    else message+=": "+jobj.data;
                                }
                                if (jobj.log) log("LOG: ",jobj.log);
                                setContentLine(message);
                            } else {
                                setErrorLine(jobj.result.toUpperCase()+": "+jobj.message);
                            }
                        } else setErrorLine("NO RESULT RESPONSE: "+chunk);
                    } catch (exc) {
                        setErrorLine(exc.getMessage());
                    }
                });
                xhr.progressIndex=lastProgressIndex;
            }
            //log("INI resultCallback: ",evt);
        }
        function errorCallback(evt) {
            log("INI errorCallback: ",evt);
        }
        function progressCallback(evt) {
            resultCallback(evt);
            //log("INI progressCallback: ",evt);
        }
        //window.onload = function(evt) { log("html loaded"); };
    </script>
  </head>
  <body>
    <h1>Prueba de Perfiles de Usuario</h1>
    <p>Ingresa un nombre de usuario: <input type="text" id="username" autofocus onkeyup="if(isEnter(event.keyCode))doclick(ebyid('gobtn'));"><button id="gobtn" type="button" onclick="getUserData();">Buscar</button></p>
    <div id="contenido">
    </div>
  </body>
</html>
<?php
