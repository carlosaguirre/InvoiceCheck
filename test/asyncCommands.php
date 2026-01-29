<?php
require_once dirname(__DIR__)."/bootstrap.php";
?>
<html>
<head>
<base href="http://invoicecheck.dyndns-web.com:81/invoice/">
<meta charset="utf-8">
<style>
html, body { height: 100%; padding: 0; margin: 0; }
.slice { width: 50%; height: 40%; float: left; }
.textblock { width: 95%; height: 95%; background: transparent; display: inline-block; position: relative; top: 2.5%; left: 2.5%; }
#divh { width: 100%; height: 15%; float: left; background: #FDA; }
#divf { width: 100%; height: 5%; float: left; background: #DAF; }
#div1 { background: #EEE; }
#div2 { background: #DDD; }
#div3 { background: #CCC; }
#div4 { background: #BBB; }
</style>
<link href="css/general.php" rel="stylesheet" type="text/css">
<script src="scripts/general.js?ver=2a"></script>
<script>
var num = 0;
setInterval(function() {
    num++;
    var cta = document.getElementById("cuenta");
    while(cta.firstChild) cta.removeChild(cta.firstChild);
    cta.appendChild(document.createTextNode(num));
}, 999);
function runTest() {
    var txbl = document.getElementsByClassName("textblock");
    var cmds = [];
    while(txbl.length>0) {
        var blk = txbl[0];
        cmds.push(blk.value);
        console.log("CMDS "+cmds.length+" w/size "+blk.value.length+" chars");
        var midiv = blk.parentNode;
        midiv.removeChild(blk);
    }
    var divs = document.getElementsByClassName("slice");
    for(var i=0; i<divs.length; i++) {
        var dv = divs[i];
        dv.classList.add("scrollauto");
        if (cmds.length>i) {
            var cmdblk = cmds[i];
            var xhr = ajaxRequest();
            xhr.ptoffset = 0;
            xhr.target = dv;
            xhr.targetIndex = i;
            var prg = document.createElement("DIV");
            prg.appendChild(document.createTextNode(cmdblk));
            dv.append(prg);
            xhr.onreadystatechange = function () {
                var prg = document.createElement("DIV");
                prg.appendChild(document.createTextNode(this.targetIndex+": RDY|STT ("+this.readyState+"|"+this.status+")"));
                this.target.appendChild(prg);
                if (this.readyState == 4 || this.readyState == 3) {
                    var block = document.createElement("PRE");
                    block.classList.add("screen");
                    block.appendChild(document.createTextNode(this.responseText));
                    this.target.appendChild(block);
                }
            };
            xhr.onerror = function() { console.log("ERROR EN AJAX REQUEST"); };
            xhr.open("POST", "/invoice/test/postCommand.php", true);
            // xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
            // xhr.setRequestHeader("Content-Type", "multipart/form-data");
            var formData = new FormData();
            formData.append("command",cmdblk);
            xhr.send(formData);
        }
    }
}
function loadFunc(evt) {
    console.log("INI loadFunc:\n"+this.responseText);
    var nLastVisit = parseFloat(window.localStorage.getItem('lm_' + this.filepath));
    var nLastModif = Date.parse(this.getResponseHeader("Last-Modified"));
    if (isNaN(nLastVisit) || nLastModif > nLastVisit) {
        window.localStorage.setItem('lm_' + this.filepath, Date.now());
        isFinite(nLastVisit) && this.callback(nLastModif, nLastVisit);
    }
    if (this.target) {
        var lines = this.responseText.split("\n");
        for (var i=0; i<lines.length; i++) {
            this.target.appendChild(document.createElement("BR"));
            this.target.appendChild(document.createTextNode(lines[i]));
        }
    }
}
function responseFunc(nModif, nVisit) {
    console.log("INI responseFunc: "+nModif+", "+nVisit);
}
</script>
</head>
<body>
<div id="divh">H<br><input type="button" value="Run" onclick="runTest();"><br><span id="cuenta" class="textStroke"></span></div>
<div id="div1" class="slice"><textarea id="cmd1" class="textblock">/bin/ls</textarea></div>
<div id="div2" class="slice"><textarea id="cmd2" class="textblock">/bin/ping -n 20 127.0.0.1</textarea></div>
<div id="div3" class="slice"><textarea id="cmd3" class="textblock">/bin/pwd</textarea></div>
<div id="div4" class="slice"><textarea id="cmd4" class="textblock">/bin/cat /bin/.perm</textarea></div>
<div id="divf" class="scrollauto ttextStroke boldValue">HOLA MUNDO</div>
</body>
</html>


<?php
/*
for($i=0; $i<3; $i++) {

echo "Running: $cmd<br>";
while (@ ob_end_flush()); // end all output buffers if any

$proc = popen($cmd, 'r');
echo '<pre>';
while (!feof($proc))
{
    echo fread($proc, 4096);
    @ flush();
}
echo '</pre>';
echo "Command Stopped<br>";

}
*/
