<?php
    require_once dirname(__DIR__)."/bootstrap.php";

    if (isset($_POST["action"])) {
        $absPath = "C:\\Apache24\\htdocs\\invoice\\";
        require_once "clases/Facturas.php";
        $invObj=new Facturas();
        $invObj->rows_per_page=1;
        // select id,folio,ubicacion,nombreInterno,nombreInternoPDF from facturas where tipoComprobante="p" AND fechaFactura between "2021-07-01 00:00:00" AND "2021-12-31 23:59:59" AND control is null order by id desc limit 1;
        $invData=$invObj->getData("tipoComprobante='p' AND fechaFactura between '2021-07-01 00:00:00' AND '2021-12-31 23:59:59' AND control is null",0,"id,folio,ubicacion,nombreInterno,nombreInternoPDF");
        if (!isset($invData[0])) errNDie("VACIO");
        $row=$invData[0];
        $invId=$row["id"];
        require_once "clases/FTP.php";
        $ftpObj = MiFTP::newInstanceGlama();
        // ToDo: Transferir xml y pdf
        // ToDo: guardar en bd, control=1
            $xmlName=str_replace("/","\\",$row["xml"]);
            $absName=$absPath.$xmlName;
            $cfdiObj=CFDI::newInstanceByLocalName($absName);
            if ($cfdiObj==null) {
                $result[]=["id"=>$invId,"xml"=>$xmlName,"error"=>CFDI::getLastError()];
                continue;
            }
            $regFis=$cfdiObj->get("RegimenFiscal");
            if (empty($regFis)) $regFis=-1;
            global $query;
            if (!$invObj->saveRecord(["id"=>$invId,"regimenFiscal"=>$regFis])) {
                $result[]=["id"=>$invId,"xml"=>$xmlName,"regFis"=>$regFis,"query"=>$query,"dberror"=>DBi::$errors,"doerror"=>$invObj->errors]; // ,"log"=>$invObj->log
                continue;
            }
            if ($regFis<=0) {
                $result[]=["id"=>$invId,"xml"=>$xmlName,"regFis"=>$regFis,"error"=>"Sin regimen fiscal"];
                continue;
            }
            $result[]=["id"=>$invId,"xml"=>$xmlName,"regFis"=>$regFis];

        if ($invSuccessN==0) errNDie("Proceso fallido",["num"=>0,"data"=>$result]);
        successNDie($invSuccessN<10?"Proceso parcialmente exitoso":"Proceso exitoso de 10 facturas",["num"=>$invSuccessN,"data"=>$result]);
    }
?>
<html xmlns="http://www.w3.org/1999/xhtml">
  <head>
    <meta charset="utf-8">
    <?= isBrowser(["Edge","IE"])?"<meta http-equiv=\"x-ua-compatible\" content=\"ie=edge\" />":"" ?>
    <base href="<?= $_SERVER['HTTP_ORIGIN'] . $_SERVER['WEB_MD_PATH'] ?>" target="_blank">
    <title>Respaldar Facturas JUL-DIC 2021</title>
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
        function isEnter(kyCd) { return kyCd==13; }
        function ajaxRequest() {
            if (window.XMLHttpRequest)
                try { return new XMLHttpRequest; } catch (e) {}
            if (window.ActiveXObject) {
                const aXmodes=["Microsoft.XMLHTTP","Msxml2.XMLHTTP"];
                for (let i=0; i<aXmodes.length; i++) try {
                    return new ActiveXObject(aXmodes[i]);
                } catch (e) {}
            }
            return false;
        }
        function postService(url) {
            let xmlHttpPost = ajaxRequest();
            let fd = new FormData();
            fd.append("action","1");
            xmlHttpPost.open("POST", url, true);
            xmlHttpPost.send(fd);
            xmlHttpPost.parameters={action:"1"};
            xmlHttpPost.onabort=function(evt) {log("INI xhp.abort: ",evt)};
            xmlHttpPost.onerror=function(evt) {log("INI xhp.error: ",evt);};
            xmlHttpPost.onload=resultCallback;
            xmlHttpPost.onloadstart=function (evt) { log("INI xhp.loadstart: ",evt);};
            xmlHttpPost.onloadend=function (evt) { log("INI xhp.loadend: ",evt);};
            xmlHttpPost.onprogress=function(evt) {log("INI xhp.progress: ",evt);};
            xmlHttpPost.ontimeout=function (evt) { log("INI xhp.timeout: ",evt);};
            return xmlHttpPost; // para llamar metodo abort()
        }
        // ------------------------------ L O C A L   F U N C T I O N S ------------------------------ //
        var xhp=null;
        var sto=null;
        function iniciarProceso() {
            if (sto) {
                clearTimeout(sto);
                sto=null;
            }
            if (!xhp) {
                log("INI iniciarProceso");
                xhp=postService("test/update2.php");
                log("END iniciarProceso");
            } else log("Ya existe un proceso activo");
        }
        function detenerProceso() {
            log("INI detenerProceso");
            if (xhp) {
                xhp.abort();
                xhp=null;
                log("Proceso en servidor detenido");
            }
            if (sto) {
                clearTimeout(sto);
                sto=null;
                log("Pausa en ciclo recursivo cancelada");
            }
            log("END detenerProceso");
        }
        function setContentLine(text,properties) {
            const cnt=ebyid("content");
            const prg={eName:"P",eText:text};
            if (properties) for (prop in properties) {
                if (properties.hasOwnProperty(prop) && !prg[prop]) prg[prop]=properties[prop];
            }
            cnt.appendChild(ecrea(prg));
        }
        function setErrorLine(text) {
            setContentLine(text,{style:"color: darkred;"});
        }
        function resultCallback(evt) {
            try {
                let jobj=JSON.parse(xhp.responseText);
                if (jobj.result) {
                    if (jobj.result==="success") {
                        setContentLine(jobj.message);
                    } else {
                        setErrorLine(jobj.result.toUpperCase()+": "+jobj.message);
                    }
                } else setErrorLine("NO RESULT RESPONSE: "+xhp.responseText);
            } catch (exc) {
                setErrorLine("EXCEPTION: "+exc.message);
            }
            xhp=null;
            sto=setTimeout(iniciarProceso,10);
        }
    </script>
  </head>
  <body>
    <h1>Respaldar Facturas JUL-DIC 2021</h1>
    <button id="start" type="button" onclick="iniciarProceso();">Empezar</button>
    <button id="stop" type="button" onclick="detenerProceso();">Detener</button>
    <div id="content"></div>
  </body>
</html>
