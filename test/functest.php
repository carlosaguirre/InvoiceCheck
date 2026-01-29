<?php
function showOneFunc() {
  echo "FUNC: 'UNO'\n";
}
function showTwoFunc() {
  echo "FUNC: 'DOS'\n";
}
function showThreeFunc() {
  echo "FUNC: 'TRES'\n";
}

if ($argc>1) switch($argv[1]) {
  case "One": case "Two": case "Three": $fvar="show".$argv[1]."Func"; $fvar(); break;
  default: echo "UNKNOWN: '".json_encode($argv)."'\n";
} else echo "EMPTY: '".json_encode($argv)."'\n";
