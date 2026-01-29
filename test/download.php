<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once "../clases/RegLista69B.php";
?>
<html>
  <head>
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
  </head>
  <body>
    <h1>RegLista69B TEST</h1>
<?php
RegLista69B::$debug=true;
RegLista69B::updateDocuments();
                      // bool:download??true,
                      // bool:parsing??true,
                      // bool:saving??true
?>
  </body>
</html>
