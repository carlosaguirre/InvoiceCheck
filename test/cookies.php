<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require_once dirname(__DIR__)."/bootstrap.php";
?>
<html>
  <head>
    <base href="http://invoicecheck.dyndns-web.com:81/invoice/">
    <meta charset="utf-8">
    <style>
        .function { color: blue; }
        .error { color: red; }
        .data { color: green; }
        .black { color: black; }
        .red { color: red; }
        .magenta { color: magenta; }
        .maroon { color: maroon; }
        code { color: darkgray; }
        ul { margin-block-start: 0; }
    </style>
    <script src="scripts/general.js?ver=2b"></script>
  </head>
  <body>
    <h1>Cookies</h1>
    <h2>PHP</h2>
<?php
  echo arr2List($_COOKIE);
                      // bool:download??true,
                      // bool:parsing??true,
                      // bool:saving??true
?>
  <h2>JAVASCRIPT</h2>
  <script>
    //const flspValue=getCookie("filtroListaSolP");
    //document.body.appendChild(ecrea({eName:"P",eText:"filtroListaSolP = "+flspValue}));
    //delCookie("filtroListaSolP");
    //document.cookie="filtroListaSolP=; expires=Thu, 01 Jan 1970 00:00:00 GMT; path=/invoice";
    document.body.appendChild(ecrea({eName:"PRE",eText:document.cookie}));
    const jsCookies={eName:"UL",eChilds:[]};
    const ca=document.cookie.split(";");
    for (let i=0; i<ca.length; i++) {
      let c=ca[i];
      while (c.charAt(0)==' ') c=c.substring(1,c.length);
      name=c.slice(0,c.indexOf("="));
      value=c.slice(name.length+1);
      jsCookies.eChilds.push({eName:"LI",className:"bodycolor",eChilds:[{eName:"B",eText:name},{eText:" = "+decodeURIComponent(value)}]});
    }
    document.body.appendChild(ecrea(jsCookies));
  </script>
  </body>
</html>
