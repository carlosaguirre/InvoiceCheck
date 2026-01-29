<?php
require_once dirname(__DIR__)."/bootstrap.php";
require_once "templates/generalScript.php";
$factIds=[245178,245201,245226,245227,245228,245508,245824,247364,247365,247366,247722,247726,247732,247736,247738,247739,247740,247746];
?>
<html>
<head>
<title>PDF TEST</title>
<base href="http://invoicecheck.dyndns-web.com:81/invoice/">
<meta charset="utf-8">
<?= getGeneralScript() ?>
<script>
    doShowLogs=true;
    function loadfunc() {
        console.log('...processing...');
        ekfil('result');
        document.body.insertBefore(ecrea({eName:"IMG",src:"imagenes/icons/flying.gif",id:"waiting"}),ebyid('overlay'));
        postService2('consultas/Archivos.php', {action:'pdfmerge',factIds:[<?=implode(",", $factIds)?>]},(jobj,extra)=>{
            if (jobj.archivos) {
                const res=ebyid('result');
                fee(jobj.archivos,function(a,i) {
                    res.appendChild(ecrea({eName:'TR',
                        eChilds:[
                            {eName:'TD',eText:""+i},
                            {eName:'TD',eText:a['id']},
                            {eName:'TD',eText:a['folio']},
                            {eName:'TD',eText:a['uuid']},
                            {eName:'TD',eChilds:[{eName:'A',href:a['link'],target:'docs',eText:'PDF'}]}]}));
                });
                fee(lbycn('viewWithData'),function(elem){clrem(elem,'hidden');});
            }
            if(jobj.result==='success') {
                if (!jobj.message) jobj.message={eName:'P',eText:"Las facturas fueron anexadas en un solo documento"};
                else if (!isElemObj(jobj.message) && !Array.isArray(jobj.message)) jobj.message={eName:'P',eText:jobj.message.toUpperCase()};
                if (jobj.contentObject) {
                    if (isElemObj(jobj.message)) {
                        if (isElemObj(jobj.contentObject)) jobj.message=[jobj.message,jobj.contentObject];
                        else if (Array.isArray(jobj.contentObject)) jobj.message=[jobj.message].concat(jobj.contentObject);
                    } else if (Array.isArray(jobj.message)) {
                        if (isElemObj(jobj.contentObject)) jobj.message.push(jobj.contentObject);
                        else if (Array.isArray(jobj.contentObject)) jobj.message=jobj.message.concat(jobj.contentObject);
                    }
                }
                if (jobj.webname) {
                    const ml=ebyid("mergedLink");
                    ml.href=jobj.webname;
                    const pd=ebyid("printDocument");
                    clrem([ml,pd],"hidden");
                    let mergeObject=[{eName:'P',eText:'Se generó el documento integrado: '},{eName:'A',href:jobj.webname,target:'docs',eText:(jobj.basename?jobj.basename:'PDF A IMPRIMIR')}];
                    if (isElemObj(jobj.message)) mergeObject.unshift(jobj.message);
                    else if (Array.isArray(jobj.message)) mergeObject=jobj.message.concat(mergeObject);
                    jobj.message=mergeObject;
                    doPrint();
                } else overlayMessage(jobj.message,'ÉXITO');
            } else {
                overlayMessage({eName:'P',eText:jobj.message},jobj.result.toUpperCase());
            }
            if (jobj.log) console.log("LOG:\n",jobj.log);
        },(errmsg,text,extra)=>{
            console.log('WEBERROR!');
            const pa=extra.parameters;
            overlayMessage({eName:'P',eText:pa.xmlHttpPost.readyState+'-'+pa.xmlHttpPost.status+': '+errmsg},'WEBERROR');
        });
    }
    function doPrint() {
        const ml=ebyid("mergedLink");
        if (ml.href.length>0)
            printPDF(ml.href);
    }
</script>
<link href="css/general.php" rel="stylesheet" type="text/css">
</head>
<body onload="loadfunc();" class="centered">
<table class="hidden viewWithData" border="1">
    <thead><tr><th>#</th><th>ID</th><th>FOLIO</th><th>UUID</th><th>PDF</th></tr></thead>
    <tbody id="result">
    </tbody>
</table>
<A id="mergedLink" class="hidden" href="">Documento Integrado</A><br><IMG id="printDocument" class="hidden" src="imagenes/prntricon32a.png" onclick="doPrint();" />
<?php
    include ("templates/overlay.php");
?>
    <div id="mylog" class="hidden">
    </div>
</body>
</html>
