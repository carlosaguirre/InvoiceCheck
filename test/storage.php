<?php
require_once dirname(__DIR__)."/bootstrap.php";
require_once "templates/generalScript.php";
?>
<html>
<head>
<base href="http://invoicecheck.dyndns-web.com:81/invoice/">
<meta charset="utf-8">
<?= getGeneralScript() ?>
<script type="text/javascript">
var lastResult=false;
function storageTest(times) {
    var today = new Date();
    var date = today.getFullYear()+'-'+("0" + (today.getMonth()+1)).slice(-2)+'-'+("0" + today.getDate()).slice(-2);
    var time = ("0" + today.getHours()).slice(-2) + ":" + ("0" + today.getMinutes()).slice(-2) + ":" + ("0" + today.getSeconds()).slice(-2);
    var dateTime = date +' '+time;

    if (!times) {
        times=1;
        localStorage.clear();
    } else times++;

    lastResult=false;
    switch(times) {
        case 3: case 6: case 8: case 9: getFromStoredArray("test",localStorage,(Math.trunc(times/3)-2),times%3?0:1).then(val => { const rrt=document.getElementById("rr"+times);if(rrt) rrt.textContent=""+val; else lastResult=val; }); break;
        default:
            addToStoredArray("test",dateTime,localStorage); break;
    }
    setTimeout(function(i){
        localStorage.get("test").then(val=>{
            const rb=document.getElementById("retBody");
            const row=document.createElement("TR");
            const cell1=document.createElement("TD");
            cell1.textContent=""+i;
            row.appendChild(cell1);
            const cell2=document.createElement("TD");
            cell2.textContent=val;
            row.appendChild(cell2);
            const cell3=document.createElement("TD");
            cell3.id="rr"+i;
            if (lastResult) cell3.textContent=""+lastResult;
            row.appendChild(cell3);
            rb.appendChild(row);
        });
    },500,times);
    if (times<10) setTimeout(storageTest,1000+100*Math.floor(Math.random()*30),times);
}
</script>
</head>
<body onload="storageTest();">
    <H1>Storage Test</H1>
    <TABLE>
        <THEAD><TR><TH>ID</TH><TH>VALUES</TH><TH>RESULT</TH></TR></THEAD>
        <TBODY id="retBody">
        </TBODY>
        </TBODY>
    </TABLE>
</body>
</html>
