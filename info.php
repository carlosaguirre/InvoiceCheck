<?php phpinfo(); 
echo "<DIV style='width: 934px;box-shadow: 1px 2px 3px rgba(0, 0, 0, 0.2);background-color: #ddd;border: 1px solid black;padding: 4px;'><PRE>";
//print_r($config);
echo "</PRE></DIV>";
echo "<HR>";
echo "<H1>INTL</H1>";
error_reporting(E_ALL);
ini_set('display_errors', 1);
var_dump(extension_loaded('intl'));
?>
