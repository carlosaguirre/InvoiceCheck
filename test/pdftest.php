<?php
require_once dirname(__DIR__)."/bootstrap.php";
require_once "templates/generalScript.php";
$factIds=[245178,245201,245226]; // ,245227,245228,245508,245824,247364,247365,247366,247722,247726,247732,247736,247738,247739,247740,247746
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
        postService('consultas/Archivos.php', {action:'pdfmerge',factIds:[<?=implode(",", $factIds)?>]},
            function(t,p,se,ss) {
                if(se==4&&ss==200&&t.length>0) {
                    console.log('DONE!',p,se,ss);
                    ekil('waiting');
                    try {
                        const j=JSON.parse(t);
                        console.log(j);
                        if (j.archivos) {
                            const res=ebyid('result');
                            fee(j.archivos,function(a,i) {
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
                        if(j.result==='success') {
                            if (!j.message) j.message={eName:'P',eText:"Las facturas fueron anexadas en un solo documento"};
                            else if (!isElemObj(j.message) && !Array.isArray(j.message)) j.message={eName:'P',eText:j.message.toUpperCase()};
                            if (j.contentObject) {
                                if (isElemObj(j.message)) {
                                    if (isElemObj(j.contentObject)) j.message=[j.message,j.contentObject];
                                    else if (Array.isArray(j.contentObject)) j.message=[j.message].concat(j.contentObject);
                                } else if (Array.isArray(j.message)) {
                                    if (isElemObj(j.contentObject)) j.message.push(j.contentObject);
                                    else if (Array.isArray(j.contentObject)) j.message=j.message.concat(j.contentObject);
                                }
                            }
                            if (j.webname) {
                                const ml=ebyid("mergedLink");
                                ml.href=j.webname;
                                const pd=ebyid("printDocument");
                                clrem([ml,pd],"hidden");
                                let mergeObject=[{eName:'P',eText:'Se generó el documento integrado: '},{eName:'A',href:j.webname,target:'docs',eText:(j.basename?j.basename:'PDF A IMPRIMIR')}];//j.webname}];//
                                if (isElemObj(j.message)) mergeObject.unshift(j.message);
                                else if (Array.isArray(j.message)) mergeObject=j.message.concat(mergeObject);
                                j.message=mergeObject;
                                doPrint();
                            } else overlayMessage(j.message,'ÉXITO');
                        } else {
                            overlayMessage({eName:'P',eText:j.message},j.result.toUpperCase());
                        }
                        if (j.log) console.log("LOG:\n",j.log);
                    } catch(ex) { overlayMessage({eName:'P',eText:ex.message},'EXCEPCIÓN'); }
                } else {
                    let msg="ReadyState="+se+", Status="+ss;
                    if (t && t.length>0) msg+=", TextLength="+t.length;
                    console.log(msg);
                    //if (se==4 && ss>200)
                        console.log("RESULT TEXT:\n",t);
                }
            },
            function(em,pa,ev){
                console.log('WEBERROR!');
                overlayMessage({eName:'P',eText:pa.xmlHttpPost.readyState+'-'+pa.xmlHttpPost.status+': '+em},'WEBERROR');
            }
        );
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
