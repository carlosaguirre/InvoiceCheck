<?php
if (session_status() == PHP_SESSION_NONE) {
    if (isset($_COOKIE["TokenName"])) $ssnm=$_COOKIE["TokenName"];
    else $ssnm="invoiceSVRW12SessID";
    session_name($ssnm);
    session_start();
}
echo "<TABLE><THEAD><TR><TH>KEY</TH><TH>VALUE</TH></TR></THEAD><TBODY>";
foreach ($_SESSION as $key => $value) {
    echo "<TR><TD>$key</TD><TD>$value</TD></TR>";
}
echo "</TBODY></TABLE>";