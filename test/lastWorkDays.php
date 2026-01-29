<?php
require_once dirname(__DIR__)."/bootstrap.php";
?>
<html>
  <head>
    <title>Ultimos dias laborales del año</title>
    <script>
    </script>
  </head>
  <body>
    <div id="area_general" class="central">
      <h1>Ultimos dias laborales del año</h1>
      <div id="area_detalle">
<?php
for ($i=0, $day=new DateTime(); $i<12; $i++, $day->sub(new DateInterval("P31D"))) { 
  $fmtCurrDay=$day->format("Y-m-d");
  $fmtLastDay=$day->format("Y-m-t");
  $fmtLstWDay=getLastWorkingDay($fmtCurrDay);
  $isLstWSameAsLast=(strcmp($fmtLstWDay,$fmtLastDay)===0);
  $isCurrSameAsLast=(strcmp($fmtCurrDay,$fmtLastDay)===0);
  if (!$isCurrSameAsLast) $day->modify("+".((+substr($fmtLastDay,-2))-(+substr($fmtCurrDay,-2)))." day");
  $fmtLstWDay.=" ".date('D', strtotime($fmtLstWDay));
  if (!$isLstWSameAsLast) $fmtLstWDay.=" <= ".$fmtLastDay." ".date('D', strtotime($fmtLastDay));
  echo "<p style=\"background-color: ".($isLstWSameAsLast?"cyan":"yellow").";\">$fmtLstWDay</p>";
}
 ?>
      </div>
    </div>
  </body>
</html>
