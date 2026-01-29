<?php
require_once dirname(__DIR__)."/bootstrap.php";
class CACHETEST {
  private static $cache = [];
  public static function init() {
    if (!isset(self::$cache["val1"])) self::$cache["val1"]="1";
  }
  public static function getCache() {
    return self::$cache;
  }
  public static function setCache($k, $v) {
    self::init();
    self::$cache[$k]=$v;
  }
  public static function showVal1() {
    self::init();
    return self::$cache["val1"]??null;
  }
  public static function setVal1($txt) {
    self::init();
    self::$cache["val1"]=$txt;
  }
}
?>
<html>
  <head>
    <title>Cache tests</title>
    <script>
    </script>
  </head>
  <body>
    <div id="area_general" class="central">
      <h1>CACHE TESTS</h1>
      <div id="area_detalle">
<?php
echo "<p>".CACHETEST::showVal1()."</p>";

CACHETEST::setVal1("HOLA");
echo "<p>".CACHETEST::showVal1()."</p>";

CACHETEST::getCache()["val1"]="ADIOS";
echo "<p>".CACHETEST::showVal1()."</p>";

CACHETEST::setCache("val1","MUNDO");
echo "<p>".CACHETEST::showVal1()."</p>";
?>
      </div>
    </div>
  </body>
</html>
