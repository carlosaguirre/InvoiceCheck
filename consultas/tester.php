<?php
require_once dirname(__DIR__)."/bootstrap.php";

set_error_handler("errorHandler");
function errorHandler ($errno, $errstr, $errfile, $errline, $errcontext) {
    $errname=false;
    switch ($errno) {
        case E_USER_WARNING: if (!$errname) $errname="E_USER_WARNING";
        case E_USER_NOTICE: if (!$errname) $errname="E_USER_NOTICE";
        case E_WARNING: if (!$errname) $errname="E_WARNING";
        case E_NOTICE: if (!$errname) $errname="E_NOTICE";
        case E_CORE_WARNING: if (!$errname) $errname="E_CORE_WARNING";
        case E_COMPILE_WARNING: if (!$errname) $errname="E_COMPILE_WARNING";
            echo PHP_EOL."WARNING $errno $errname: $errstr. line $errline. file $errfile".PHP_EOL."<!--".(is_array($errcontext)?json_encode($errcontext):$errcontext).PHP_EOL." -->";
            break;
        case E_USER_ERROR: if (!$errname) $errname="E_USER_ERROR";
        case E_ERROR: if (!$errname) $errname="E_ERROR";
        case E_PARSE: if (!$errname) $errname="E_PARSE";
        case E_CORE_ERROR: if (!$errname) $errname="E_CORE_ERROR";
        case E_COMPILE_ERROR: if (!$errname) $errname="E_COMPILE_ERROR";
            echo PHP_EOL."ERROR $errno $errname: $errstr. line $errline. file $errfile";
            echo PHP_EOL."<!--".PHP_EOL.(is_array($errcontext)?json_encode($errcontext):$errcontext).PHP_EOL." -->";
            echo PHP_EOL."SCRIPT: $_SERVER[PHP_SELF]";
            echo PHP_EOL."BACKTRACE:";
            echo PHP_EOL.generateBackTrace();
            echo PHP_EOL."CALLTRACE:";
            echo PHP_EOL.generateCallTrace();
            break;
        default:
            if ($errno)
              echo "Generic Error $errno: $errstr \n error on line $errline in file $errfile ".PHP_EOL;
            else
              echo "Generic Unknown Error: $errstr \n error on line $errline in file $errfile ".PHP_EOL;
            break;
    } // switch
}
function generateBackTrace() {
    $trace = debug_backtrace();
    return fixTrace($trace);
}
function generateCallTrace() {
    $e = new Exception();
    $trace = explode("\n", $e->getTraceAsString());
    return fixTrace($trace);
}
function fixTrace($trace) {
    $trace = array_reverse($trace);
    array_shift($trace); // remove {main}
    array_pop($trace); // remove call to this method
    $length = count($trace);
    $result = array();
    
    for ($i = 0; $i < $length; $i++)
    {
        $result[] = ($i + 1)  . ')' . substr($trace[$i], strpos($trace[$i], ' ')); // replace '#someNum' with '$i)', set the right ordering
    }
    
    return "\t" . implode("\n\t", $result);
}
?>
<!doctype html>
<html style="height:100%;">
  <head>
    <base href="<?= $_SERVER['HTTP_ORIGIN'] . $_SERVER['WEB_MD_PATH'] ?>" target="_blank">
    <meta charset="utf-8">
    <title><?= $systemTitle ?></title>
    <link href="css/general.php" rel="stylesheet" type="text/css">
<?php
    require_once "templates/generalScript.php";
    echoScript("General");
?>
    <script>
      var now = new Date(<?php echo time() * 1000; ?>);
      startInterval(); //start it right away
    </script>
  </head>
  <body>
    <div id="contenedor">
<?php
    $navIdx=0;
    include "templates/encabezado.php";
?>
      <div id="bloque_central">
<?php include "templates/barraLateral.php"; ?>

        <div id="principal">
<?php
    if ($errorDetail)
        echo $errorDetail;

    if ($errorTrace)
        clog1($errorTrace);
?>
        </div>
<br><br>
      </div>
<?php
    include ("templates/piePagina.php");
//    clog1seq();
?>
    </div>
    <div id="mylog" class="hidden">
    </div>
  </body>
</html>
<?php
