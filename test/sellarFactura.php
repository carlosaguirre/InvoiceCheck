<?php
error_reporting(E_ALL);
ini_set('display_errors', 'On');
require_once dirname(__DIR__)."/bootstrap.php";
if (!hasUser() || !validaPerfil("Administrador")) {
    echo "No autorizado: ";
    if (!hasUser()) echo "Sin usuario";
    else echo "No admin";
    //header("Location: /".$_project_name."/");
    die(); //"Redirecting to /".$_project_name."/");
}
$solId=$_REQUEST["solId"]??"";
if (isset($solId[0])) {
    require_once "clases/SolicitudPago.php";
    $solObj=new SolicitudPago();
    $solData=$solObj->getData("id=$solId");
    if (!isset($solData[0])) {
        echo "<p>No existe la solicitud '$solId'</p>";
        echo "<pre>".$solObj->log."</pre>";
        die();
    }
    $solData=$solData[0];
    $solStatus=intval($solData["status"]??"0");
    $esCancelada = ($solStatus&SolicitudPago::STATUS_CANCELADA);
    if ($esCancelada) {
        echo "<p>La solicitud está cancelada $solStatus & {SolicitudPago::STATUS_CANCELADA}='$esCancelada'</p>".arr2List($solData);
        die();
    }
    $idFactura=$solData["idFactura"]??"";
    if (isset($idFactura[0])) {
        $solProceso=intval($solData["proceso"]??"0");
        if ($solProceso<SolicitudPago::PROCESO_COMPRAS) {
            echo "<p>La solicitud no ha sido procesada por compras</p>".arr2List($solData);
            die();
        }
        if ($solProceso<SolicitudPago::PROCESO_CONTABLE) {
            echo "<p>La solicitud no ha sido procesada por contabilidad</p>".arr2List($solData);
            die();
        }
        require_once "clases/Facturas.php";
        $invObj=new Facturas();
        $invData=$invObj->getData("id=$idFactura");
        if (!isset($invData[0])) {
            echo "<p>No existe la factura '$idFactura'</p>";
            echo "<pre>".$invObj->log."</pre>";
            die();
        }
        $invData=$invData[0];
        $invStatus=$invData["status"]??"";
        if (!isset($invStatus[0])) {
            echo "<p>La factura no tiene status</p>".arr2List($invData);
            die();
        }
        if ($invStatus==="Temporal") {
            echo "<p>Factura Temporal</p>".arr2List($solData).arr2List($invData);
            die();
        }
        $invVersion=$invData["version"]??"";
        if (!isset($invVersion[0])||$invVersion!=="4.0") {
    //        echo "<p>La versión de la factura debe ser 4.0</p>".arr2List($invData);
    //        die();
        }
        $invTC=$invData["tipoComprobante"]??"";
        if (!isset($invTC[0])||$invTC[0]!=="i") {
            echo "<p>El comprobante fiscal no es una factura</p>".arr2List($invData);
            die();
        }
        if (!isset($invData["statusn"])) {
            echo "<p>Factura sin status numérico</p>".arr2List($invData);
            die();
        }
        $invStatusN=intval($invData["statusn"]);
        if (($invStatusN&Facturas::STATUS_ACEPTADO)==0) {
            echo "<p>Factura no Aceptada</p>".arr2List($invData);
            die();
        }
        $estadoCFDI=$invData["estadoCFDI"]??"";
        if ($estadoCFDI!=="Vigente") {
            echo "<p>La factura no está Vigente ante el SAT</p>".arr2List($invData);
            die();
        }
        $ubicacion=$invData["ubicacion"]??"";
        if (!isset($ubicacion[0])) {
            echo "<p>La factura no tiene ubicacion del archivo PDF</p>".arr2List($invData);
            die();
        }
        $nombrePDF=$invData["nombreInternoPDF"]??"";
        if (!isset($nombrePDF[0])) {
            echo "<p>La factura no tiene archivo PDF</p>".arr2List($invData);
            die();
        }
        $tieneSello=intval($invData["tieneSello"]??"0");
        $selloImpreso=intval($invData["selloImpreso"]??"0");
    } else {
        $idOrden=$solData["idOrden"]??"";
        if (isset($idOrden[0])) {
            $solStatus=intval($solData["status"]??"0");
            if ($solStatus<SolicitudPago::STATUS_AUTORIZADA) {
                echo "<p>La solicitud no ha sido autorizada</p>".arr2List($solData);
                die();
            }
            $solProceso=intval($solData["proceso"]??"-1");
            if ($solProceso<SolicitudPago::PROCESO_AUTORIZADA) {
                echo "<p>La solicitud no ha sido debidamente autorizada</p>".arr2List($solData);
                die();
            }
            if ($solStatus>SolicitudPago::STATUS_AUTORIZADA) {
                echo "<p>La solicitud ya está procesada como si tuviera factura</p>".arr2List($solData);
                die();
            }
            if ($solProceso>SolicitudPago::PROCESO_AUTORIZADA) {
                echo "<p>La solicitud fue procesada como si tuviera factura</p>".arr2List($solData);
                die();
            }
            require_once "clases/OrdenesCompra.php";
            $ordObj=new OrdenesCompra();
            $ordData=$ordObj->getData("id=$idOrden");
            if (!isset($ordData[0])) {
                echo "<p>No existe la orden de compra '$idOrden'</p>";
                echo "<pre>".$ordObj->log."</pre>";
                die();
            }
            $ordData=$ordData[0];
            $ordStatus=$ordData["status"];
            if (!isset($ordStatus[0])) {
                echo "<p>La orden de compra no tiene status</p>".arr2List($ordData);
                die();
            }
            if ($ordStatus<OrdenesCompra::STATUS_AUTORIZADO) {
                echo "<p>La orden de compra no ha sido debidamente autorizada</p>".arr2List($solData);
                die();
            }
            if ($ordStatus>OrdenesCompra::STATUS_AUTORIZADO) {
                echo "<p>La orden de compra no fue debidamente autorizada</p>".arr2List($solData);
                die();
            }
            $ubicacion=$ordData["rutaArchivo"]??"";
            if (!isset($ubicacion[0])) {
                echo "<p>La orden de compra no tiene ubicacion del archivo PDF</p>".arr2List($invData);
                die();
            }
            $nombrePDF=$ordData["nombreArchivo"]??"";
            if (!isset($nombrePDF[0])) {
                echo "<p>La orden de compra no tiene archivo PDF</p>".arr2List($invData);
                die();
            }
            $tieneSello=intval($ordData["tieneSello"]??"0");
            $selloImpreso=intval($ordData["selloImpreso"]??"0");
        } else {
            echo "<p>La solicitud no tiene factura ni orden de compra</p>".arr2List($solData);
            die();
        }
    }
    $invoicePath=$_SERVER['DOCUMENT_ROOT'];
    $sitePath=$_SERVER["HTTP_ORIGIN"].$_SERVER['WEB_MD_PATH'];
    $imgPath = $invoicePath."imagenes/icons/";
    $pdfName="{$nombrePDF}.pdf";
    $fullName=$invoicePath.$ubicacion.$pdfName;
    $stampName=$invoicePath.$ubicacion."ST_".$pdfName;
    $stampInPDFName=$imgPath."sello1.png";
    $invSelloEliminado=false;
    $invSelloNoExiste=false;
    $invSelloYaExistia=false;
    $resultPageIni="<!DOCTYPE html><html lang=\"es\" xmlns=\"http://www.w3.org/1999/xhtml\"><head><meta charset=\"utf-8\"><base href=\"{$sitePath}\" target=\"_blank\"><title>SELLAR FACTURA</title><script src=\"scripts/general.js?ver=4.4s\"></script></head><body>";
    $resultPageEnd="</body></html>";

    $interactWithStamp="<img src=\"imagenes/icons/solPago200.png\" title=\"SOLICITUD\" width=\"20\" style=\"cursor:pointer;\" onclick='const f=ecrea({eName:\"FORM\",target:\"solpago\",method:\"POST\",action:\"templates/respuestaSolPago.php\",eChilds:[{eName:\"INPUT\",type:\"hidden\",name:\"SOLID\",value:\"$solId\"}]});document.body.appendChild(f);window.open(\"\",\"solpago\");f.submit();f.parentNode.removeChild(f);'>";
    if (file_exists($stampName)) {
        $invSelloYaExistia=true;
        $interactWithStamp.=" <a href=\"{$ubicacion}ST_{$pdfName}\"><img src=\"imagenes/icons/pdf200S3.png\" title=\"SELLO-PDF\" width=\"20\"></a>";
    } else if (file_exists($stampName."x")) $invSelloEliminado=true;
    else if ($tieneSello) $invSelloNoExiste=true;
    $forzar=$_REQUEST["forzar"]??"";
    if ($tieneSello==1 && !isset($forzar[0])) {
        if ($selloImpreso) {
            $interactWithStamp.=" <B>EL PDF CON SELLO YA FUE IMPRESO";
            if ($invSelloEliminado) $interactWithStamp.=" Y ELIMINADO";
            else if ($invSelloNoExiste) $interactWithStamp.=" Y ELIMINADO PERMANENTEMENTE";
            $interactWithStamp.=".</B>";
        } else if ($invSelloEliminado) $interactWithStamp.=" <B>EL PDF CON SELLO FUE ELIMINADO.</B>";
        else if ($invSelloNoExiste) $interactWithStamp.=" <B>EL PDF CON SELLO FUE ELIMINADO PERMANENTEMENTE.</B>";
        echo $resultPageIni."<p>La factura ya tiene sello: $interactWithStamp</p>";
        //echo "<H3>SERVER</H3>";
        //arrechoLiteUL($_SERVER);
        echo $resultPageEnd;
        die();
    }

    require_once "clases/Usuarios.php";
    $usrObj=new Usuarios();
    $usrData=$usrObj->getData("id=$solData[idUsuario]",0,"persona");
    if (!isset($usrData[0])) {
        echo "<p>La solicitud no tiene usuario solicitante</p>".arr2List($solData)."<pre>".$usrObj->log."</pre>";
        die();
    }
    $usrData=$usrData[0];
    require_once "clases/PDF.php";
    setlocale(LC_TIME,"es_MX.UTF-8","es_MX","esl");
    require_once "clases/Firmas.php";
    $firObj=new Firmas();
    $firData=$firObj->getData("idReferencia=$solId and accion='contable'",0,"unix_timestamp(fecha) ts");
    $ts=$firData[0]["ts"]??0;
    if ($ts>0) $formattedDate=strftime("%e %b, %Y",$ts);
    else $formattedDate=strftime("%e %b, %Y");
    try {
        $pdfObj=new PDF($fullName);
        $pdfObj->setStampFile($stampInPDFName);
        $pdfObj->addStamp($formattedDate, $usrData["persona"]);
        for($pageNo=1; $pageNo<=$pdfObj->pageCount; $pageNo++) {
            $tmplIdx=$pdfObj->importPage($pageNo);
            $pdfObj->AddPage();
            $pdfObj->useTemplate($tmplIdx);
            $pdfObj->SetFont("Helvetica","B",30);
            $pdfObj->SetTextColor(180,180,180);
            $pdfObj->SetXY(8,0);
            $pdfObj->Write(30,"X");
        }
        $pdfObj->saveFile($stampName);
        if (!file_exists($stampName)) {
            echo "<p>El PDF sellado no fue creado</p>";
            die();
        }
        if ($invSelloYaExistia)
            $interactWithStamp.=" <B>SOBREGENERADO</B>";
        else {
            $interactWithStamp.=" <a href=\"{$ubicacion}ST_{$pdfName}\"><img src=\"imagenes/icons/pdf200S3.png\" title=\"SELLO-PDF\" width=\"20\"></a>";
            if ($invSelloEliminado) $interactWithStamp.=" <B>REGENERADO</B>";
            else if ($invSelloNoExiste) $interactWithStamp.=" <B>REGENERADO+</B>";
        }
        if (isset($idFactura[0])) {
            $saveArray=["id"=>$idFactura,"tieneSello"=>1];
            if (isset($_REQUEST["reset"][0])) $saveArray["selloImpreso"]=0;
            if (!$invObj->saveRecord($saveArray) && !empty(DBi::$errno)) {
                echo "<p>Sello no guardado en factura: $interactWithStamp</p><pre>".$invObj->log."</pre>";
                die();
            }
        } else if (isset($idOrden[0])) {
            $saveArray=["id"=>$idOrden,"tieneSello"=>1];
            if (isset($_REQUEST["reset"][0])) $saveArray["selloImpreso"]=0;
            if (!$ordObj->saveRecord($saveArray) && !empty(DBi::$errno)) {
                echo "<p>Sello no guardado en orden de compra: $interactWithStamp</p><pre>".$ordObj->log."</pre>";
                die();
            }
        }
        echo $resultPageIni."<p>Sello creado: $interactWithStamp</p>".$resultPageEnd;
    } catch (Exception $ex) {
        echo "<p>Excepcion al crear sello: ".$ex->getMessage()."</p>";
    }
} else if (isset($_REQUEST["solId"])) echo "<p>Debe indicar el folio de una solicitud válida</p>";
else echo "<p>Se requiere parametro solId</p>";
