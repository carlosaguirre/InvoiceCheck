<?php
?>
<!DOCTYPE html>
<html lang="es" xmlns="http://www.w3.org/1999/xhtml">
  <head>
    <meta charset="utf-8">
    <base href="http://invoicecheck.dyndns-web.com:81/invoice/" target="_blank">
    <title>Keyboard Tests</title>
    <script src="scripts/general.js?ver=t1"></script>
    <script>
        var currRow=false;
        var errMaxLoop=2;
        var errLoop=0;
        var validCodes={
            keydown:['code','key','keyCode','which'],
            keypress:['code','key','keyCode','which','charCode'],
            input:['data','which'],
            keyup:['code','key','keyCode','which']
        };
        function test(evt) {
            if (errLoop===errMaxLoop) {
                errLoop=0;
                return;
            }
            //console.log(evt);
            const tbdy=document.getElementById("tbdy");
            let et=evt.type;
            if (!currRow||et==="keydown") {
                const nextRow=tbdy.children.length+1;
                currRow=ecrea({eName:"TR",eChilds:[{eName:"TD",eText:""+nextRow}]});
                tbdy.appendChild(currRow);
            }
            let reqCells=0;
            const eventNames=Object.keys(validCodes);
            switch(et) {
                case "keyup":reqCells+=validCodes["input"].length+1;
                case "input":reqCells+=validCodes["keypress"].length+1;
                case "keypress":reqCells+=validCodes["keydown"].length+1;
                //case "keydown":reqCells+=1;
            }
            console.log("TEST "+et+": "+reqCells);
            if(reqCells==0 && et!=="keydown") return;
            if(!fillCells(reqCells+1,evt)) return;
            console.log("READY "+et+": "+currRow.children.length);
            const mt=getModifierText(evt);
            console.log("mod '"+mt+"'");
            appendText(mt);
            for(let i=0; i<validCodes[et].length; i++) {
                console.log(et+"["+i+"]",validCodes[et][i],evt[validCodes[et][i]]);
                appendCode(evt[validCodes[et][i]]);
            }
            /*
            appendCode(currRow,evt.code);
            appendCode(currRow,evt.key);
            appendCode(currRow,evt.data);
            appendCode(currRow,evt.keyCode);
            appendCode(currRow,evt.which);
            appendCode(currRow,evt.charCode);
            appendCode(currRow,evt.keyIdentifier);
            appendCode(currRow,evt.char);
            */
            errLoop=0;
            if (et==="keyup") currRow=false;
        }
        function appendText(txt) {
            const tbdy=document.getElementById("tbdy");
            console.log("ROW "+tbdy.children.length+", CELL "+currRow.children.length+": "+txt);
            currRow.appendChild(ecrea({eName:"TD",eText:txt}));
        }
        function appendCode(cod) {
            appendText(cod!==undefined?cod:"")
        }
        function fillCells(num,evt) {
            console.log("FILL CELLS "+num);
            const len=currRow.children.length;
            if (len>num) {
                currRow=false;
                errLoop++;
                test(evt);
                return false;
            }
            const tbdy=document.getElementById("tbdy");
            while(currRow.children.length<num) {
                console.log("ROW "+tbdy.children.length+", CELL "+currRow.children.length+": EMPTY");
                currRow.appendChild(ecrea({eName:"TD"}));
            }
            return true;
        }
        function getModifierText(evt) {
            let tx="";
            if(evt.shiftKey) tx+="SHFT";
            if (evt.altKey) {if (tx.length>0)tx+="+";tx+="ALT";}
            if (evt.ctrlKey) {if (tx.length>0)tx+="+";tx+="CTRL";}
            if (evt.metaKey) {if (tx.length>0)tx+="+";tx+="META";}
            return tx;
        }
        function appendHObj(rowObj,text,colspan) {
            const hcobj={eName:"TH",eText:text};
            if (colspan && colspan>1) hcobj.colSpan=colspan;
            rowObj.eChilds.push(hcobj);
        }
        function setHeaders() {
            const thd=ebyid("thd");
            const hrw1={eName:"TR",eChilds:[]};
            const  hrw2={eName:"TR",eChilds:[]};
            appendHObj(hrw1," ");
            appendHObj(hrw2,"#");
            const eventNames=Object.keys(validCodes);
            for(let e=0;e<eventNames.length;e++) {
                let nm=eventNames[e];
                appendHObj(hrw1,nm.toUpperCase(),validCodes[nm].length+1);
                appendHObj(hrw2,"MOD");
                for(let i=0; i<validCodes[nm].length; i++)
                    appendHObj(hrw2,validCodes[nm][i].toUpperCase());
            };
            thd.appendChild(ecrea(hrw1));
            thd.appendChild(ecrea(hrw2));
        }
    </script>
    <link href="css/general.php" rel="stylesheet" type="text/css">
  </head>
  <body onload="setHeaders();">
    <input type="text" autofocus class="adminInput marL1_5" onkeypress="test(event);" onkeydown="test(event);" onkeyup="test(event);" oninput="test(event);">
    <table class="marL1_5 fixedHeader noblk noFixHgt">
        <thead id="thd"></thead>
        <tbody id="tbdy"></tbody>
    </table>
  </body>
</html>
