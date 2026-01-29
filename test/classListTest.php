<?php
global $_project_name;
$_project_name="invoice";
?>
<html lang="es" xmlns="http://www.w3.org/1999/xhtml">
  <head>
    <meta charset="utf-8">
    <base href="http://invoicecheck.dyndns-web.com:81/invoice/" target="_blank">
    <title>Class List Tests</title>
    <link rel="stylesheet" type="text/css" href="css/general.php">
    <script src="scripts/classListTest.php"></script>
  </head>
  <body>
    <div id="area_general" class="central centered">
      <h1 class="nomarginblock bs">CLASS LIST TESTS</h1>
      <div id="area_detalle" class="tableWrapper yFlowi noborderi">
        <table><thead><tr><th class="width40">ORIGINAL</th><th class="wid5th">CHANGES</th><th class="width40">FIXED</th></tr></thead><tbody><tr>
            <td class="screen"><div class="inblock hi">hi1</div></td>
            <td class="centered">clhas hi true</td>
            <td class="screen"><div class="inblock hi">hi1<img src="data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7" onload="const r1=clhas(this.parentNode,'hi');this.parentNode.appendChild(txe(r1,'*'));if(r1)cladd(this.parentNode,'bggreen');else cladd(this.parentNode,'bgred');ekil(this);"></div></td>
        </tr><tr>
            <td class="screen"><div class="inblock">hi2</div></td>
            <td class="centered">clhas hi false</td>
            <td class="screen"><div class="inblock">hi2<img src="data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7" onload="const r2=clhas(this.parentNode,'hi');this.parentNode.appendChild(txe(r2,'*'));if(r2)cladd(this.parentNode,'bggreen');else cladd(this.parentNode,'bgred');ekil(this);"></div></td>
        </tr><tr>
            <td class="screen"><div class="inblock boldValue">hi3</div></td>
            <td class="centered">clfix boldValue off</td>
            <td class="screen"><div class="inblock boldValue">hi3<img src="data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7" onload="this.parentNode.appendChild(txe(clfix(this.parentNode,'boldValue'),'*'));ekil(this);"></div></td>
        </tr><tr>
            <td class="screen"><div class="inblock">hi4</div></td>
            <td class="centered">clfix boldValue on</td>
            <td class="screen"><div class="inblock">hi4<img src="data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7" onload="this.parentNode.appendChild(txe(clfix(this.parentNode,'boldValue'),'*'));ekil(this);"></div></td>
        </tr><tr>
            <td class="screen"><div class="inblock">hi5</div></td>
            <td class="centered">cladd bgBeigeSolid true</td>
            <td class="screen"><div class="inblock">hi5<img src="data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7" onload="this.parentNode.appendChild(txe(cladd(this.parentNode,'bgBeigeSolid'),'*'));ekil(this);"></div></td>
        </tr><tr>
            <td class="screen"><div class="inblock bgBeigeSolid">hi6</div></td>
            <td class="centered">cladd bgBeigeSolid false</td>
            <td class="screen"><div class="inblock bgBeigeSolid">hi6<img src="data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7" onload="this.parentNode.appendChild(txe(cladd(this.parentNode,'bgBeigeSolid'),'*'));ekil(this);"></div></td>
        </tr><tr>
            <td class="screen"><div class="inblock bgmagenta0">hi7</div></td>
            <td class="centered">clrem bgmagenta0 true</td>
            <td class="screen"><div class="inblock bgmagenta0">hi7<img src="data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7" onload="this.parentNode.appendChild(txe(clrem(this.parentNode,'bgmagenta0'),'*'));ekil(this);"></div></td>
        </tr><tr>
            <td class="screen"><div class="inblock">hi8</div></td>
            <td class="centered">clrem bgmagenta0 false</td>
            <td class="screen"><div class="inblock">hi8<img src="data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7" onload="this.parentNode.appendChild(txe(clrem(this.parentNode,'bgmagenta0'),'*'));ekil(this);"></div></td>
        </tr><tr>
            <td class="screen"><div class="inblock">hi9</div></td>
            <td class="centered">clset bgblue0 true true</td>
            <td class="screen"><div class="inblock">hi9<img src="data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7" onload="this.parentNode.appendChild(txe(clset(this.parentNode,'bgblue0',true),'*'));ekil(this);"></div></td>
        </tr><tr>
            <td class="screen"><div class="inblock bgblue0">hiA</div></td>
            <td class="centered">clset bgblue0 true false</td>
            <td class="screen"><div class="inblock bgblue0">hiA<img src="data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7" onload="this.parentNode.appendChild(txe(clset(this.parentNode,'bgblue0',true),'*'));ekil(this);"></div></td>
        </tr><tr>
            <td class="screen"><div class="inblock">hiB</div></td>
            <td class="centered">clset bgblue0 false false</td>
            <td class="screen"><div class="inblock">hiB<img src="data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7" onload="this.parentNode.appendChild(txe(clset(this.parentNode,'bgblue0',false),'*'));ekil(this);"></div></td>
        </tr><tr>
            <td class="screen"><div class="inblock bgblue0">hiC</div></td>
            <td class="centered">clset bgblue0 false true</td>
            <td class="screen"><div class="inblock bgblue0">hiC<img src="data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7" onload="this.parentNode.appendChild(txe(clset(this.parentNode,'bgblue0',false),'*'));ekil(this);"></div></td>
        </tr><tr>
            <td class="screen"><div class="inblock">hiD</div></td>
            <td class="centered">clfix bgblue0 true true</td>
            <td class="screen"><div class="inblock">hiD<img src="data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7" onload="this.parentNode.appendChild(txe(clfix(this.parentNode,'bgblue0',true),'*'));ekil(this);"></div></td>
        </tr><tr>
            <td class="screen"><div class="inblock bgblue0">hiE</div></td>
            <td class="centered">clfix bgblue0 true false</td>
            <td class="screen"><div class="inblock bgblue0">hiE<img src="data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7" onload="this.parentNode.appendChild(txe(clfix(this.parentNode,'bgblue0',true),'*'));ekil(this);"></div></td>
        </tr><tr>
            <td class="screen"><div class="inblock">hiF</div></td>
            <td class="centered">clfix bgblue0 false false</td>
            <td class="screen"><div class="inblock">hiF<img src="data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7" onload="this.parentNode.appendChild(txe(clfix(this.parentNode,'bgblue0',false),'*'));ekil(this);"></div></td>
        </tr><tr>
            <td class="screen"><div class="inblock bgblue0">hiG</div></td>
            <td class="centered">clfix bgblue0 false true</td>
            <td class="screen"><div class="inblock bgblue0">hiG<img src="data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7" onload="this.parentNode.appendChild(txe(clfix(this.parentNode,'bgblue0',false),'*'));ekil(this);"></div></td>
        </tr></tbody></table>
      </div>
    </div>
  </body>
</html>
<?php
