<?php
require_once dirname(__DIR__)."/bootstrap.php";
include_once "clases/ComExtExpediente.php";
$statuses=ComExtExpediente::STATUSES;
$output1=http_build_query(ComExtExpediente::STATUSES, "", ", ");
$output2 = implode(', ', array_map(function ($v, $k) { return $k.':"'.$v.'"'; }, $statuses, array_keys($statuses)));
?>
<html>
  <head>
    <title>Pruebas de Comercio Exterior</title>
    <style type="text/css">
      #area_comext>div {
        background-color: rgba(255, 255, 255, 0.3);
        border: 1px solid lightgray;
      }
      #chkBdy {
        margin-left: 3.8px;
        margin-right: 3.8px;
      }
      #chkHdr {
        margin-left: 7px;
        margin-right: 7px;
      }
      #chkBdy *, #chkHdr * {
        padding: 1px;
      }
      .hCell {
        font-weight: bold;
        display: inline-block;
      }
      .bCell {
        margin-left: 2.2px;
        margin-right: 2.2px;
        display: inline-block;
      }
      .rCell {
        margin-left: 3px;
        margin-right: 5.4px;
        margin: 0 auto;
        text-align: center;
        align: center;
      }
      .bCell:not(.err), .hCell:not(.err) {
        border: 1px solid #000;
      }
      .bCell.err, .rCell.err {
        border: 1px solid darkred;
        color: darkred;
        background-color: rgba(255, 0, 0, 0.1);
      }
      .bCell.a, .bCell.b, .hCell.a, .hCell.b {
        width: 50px;
      }
      .bCell.c, .hCell.c {
        width: 100px;
      }
      .bCell.d {
        width: calc(100% - 232px);
      }
      .hCell.d {
        width: calc(100% - 227.6px);
      }
    </style>
    <link rel="stylesheet" type="text/css" href="../css/general.php"></link>
    <script src="../scripts/general.js?ver=cex05"></script>
    <script>
      doShowFuncLogs=true;
      function check() {
        const bb=ebyid("chkBdy"); // body block
        ekfil(bb);
        const url="../consultas/ComExt.php";
        const parameters={action:"Consulta",type:"foreign",foreignCode:"X-990"};
        console.log("CHECK ComExt Service",parameters);
        postService(url,parameters,
          function (msg, parameters, state, status) {
            console.log("RDYFUNC "+state+"/"+status+"\nmsg="+msg+"\nparameters=",parameters);
            if (clhas("chkHdr","hidden")) clrem("chkHdr","hidden");
            if (clhas(bb,"hidden")) clrem(bb,"hidden");
            const rw=ecrea({eName:"DIV"});
            rw.appendChild(ecrea({eName:"DIV", className:"bCell a", eText:state}));
            rw.appendChild(ecrea({eName:"DIV", className:"bCell b", eText:status}));
            if (!msg || msg.length==0) {
              rw.appendChild(ecrea({eName:"DIV", className:"bCell c err", eText:"EMPTY"}));
              rw.appendChild(ecrea({eName:"DIV", className:"bCell d err", eText:"-"}));
            } else {
              if (msg===parameters.xmlHttpPost.responseText) try {
                const jobj=JSON.parse(msg);
                const hasResult=!!jobj.result;
                const hasMessage=!!jobj.message;
                rw.appendChild(ecrea({eName:"DIV", className:"bCell c"+(hasResult?"":" err"), eText:hasResult?jobj.result:"noResult"}));
                rw.appendChild(ecrea({eName:"DIV", className:"bCell d"+(hasMessage?"":" err"), eText:hasMessage?jobj.message:"noMessage"}));
              } catch(ex) {
                rw.appendChild(ecrea({eName:"DIV", className:"bCell c err", eText:"EXCEPTION"}));
                rw.appendChild(ecrea({eName:"DIV", className:"bCell d err", eText:ex.getMessage?ex.getMessage():(ex.message?ex.message:ex)}));
                console.log("EXCEPTION MESSAGE: ",msg);
              } else {
                rw.appendChild(ecrea({eName:"DIV", className:"bCell c err", eText:"textResult"}));
                rw.appendChild(ecrea({eName:"DIV", className:"bCell d err", eText:msg}));
              }
            }
            bb.append(rw);
          },
          function (errmsg, parameters, evt) {
            console.log("ERROR event",evt);
            bb.appendChild(ecrea({eName:"DIV", className:"rCell err", eText:errmsg}));
          }
        );
      }
    </script>
  </head>
  <body>
    <div id="area_general" class="central">
      <h1 class="marginV7" onclick="check();">Pruebas de Comercio Exterior</h1>
      <div id="area_comext">
<?php /*        { <?= $output2 ?> }   */ ?>
        <DIV id="chkHdr" class="hidden">
          <DIV class="hCell a">STATE</DIV>
          <DIV class="hCell b">STTS</DIV>
          <DIV class="hCell c">RESULT</DIV>
          <DIV class="hCell d">MESSAGE</DIV>
        </DIV>
        <DIV id="chkBdy" class="hidden">
        </DIV>
      </div>
    </div>
  </body>
</html>
