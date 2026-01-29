<?php
require_once dirname(__DIR__)."/bootstrap.php";
global $bd_servidor, $bd_base, $bd_usuario, $bd_clave;
$ip = (empty($_SERVER['HTTP_CLIENT_IP'])?(empty($_SERVER['HTTP_X_FORWARDED_FOR'])?($_SERVER['REMOTE_ADDR']):$_SERVER['HTTP_X_FORWARDED_FOR']):$_SERVER['HTTP_CLIENT_IP']);
echo "<H1>PDO TEST 1</H1>";
$aliasGrupo="GLAMA";
$currNum=-1;
$pdo=null;
try {
    $dsn="mysql:host=$bd_servidor;dbname=$bd_base";
    echo "<p>$dsn</p>";
    $pdo = new PDO($dsn, $bd_usuario, $bd_clave);
    echo "<p>Connected...</p>";
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
    $res=$pdo->beginTransaction();
    if ($res) {
        echo "<p>BEGIN TRANSACTION: OK</p>";
        $query="SELECT * FROM infolocal WHERE nombre=? FOR UPDATE";
        $stmt=$pdo->prepare($query);
        echo "<p>PREPARE QUERY: $query</p>";
        $stmt->execute(["_CR_{$aliasGrupo}"]);
        echo "<p>EXECUTE: \"_CR_{$aliasGrupo}\"</p>";
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row) {
            echo "<p>FOUND ROW: ".json_encode($row)."</p>";
            $currNum = +$row["valor"];
        } else echo "<p>ROW NOT FOUND</p>";
        // toDo: USE VALUE OBTAINED AND INCREMENT VALUE IN ATOMIC WAY
        $pdo->commit();
    } else echo "<p>TRANSACTION NOT STARTED</p>";
    if ($currNum<0) echo "<p>NOTHING FOUND</p>";
    else echo "<p>OBTAINED VALUE: $currNum</p>";
    $query="SELECT max(folio) mxnm from contrarrecibos WHERE aliasGrupo=?"; // FOR UPDATE
    $stmt=$pdo->prepare($query);
    echo "<p>PREPARE QUERY: $query</p>";
    $stmt->execute([$aliasGrupo]);
    echo "<p>EXECUTE: $aliasGrupo</p>";
    $maxNum=-1;
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    //foreach ($stmt as $row) {
        $maxNum = +$row["mxnm"];
    //}
    echo "<p>OBTAINED VALUE: $maxNum</p>";
    /*
    if ($currNum<0) {
        if ($maxNum<0) {
            //$currNum=0;
            $pdo->rollBack();
        } else $currNum=$maxNum;
        if ($currNum>0) {
            $stmt=$pdo->prepare("INSERT INTO infolocal (nombre,valor) VALUES (?,?)");
            $stmt->bindParam(1,"__CR__{$aliasGrupo}");
            $stmt->bindParam(2, ++$currNum);
            $stmt->execute();
        }
    } else {
        if ($currNum<$maxNum) $currNum=$maxNum;
        $stmt=$pdo->prepare("UPDATE infolocal SET valor=? WHERE nombre=?");
        $stmt->bindParam(1, ++$currNum);
        $stmt->bindParam(2,"__CR__{$aliasGrupo}");
        $stmt->execute();
    }
    $pdo->commit();
    */
} catch (Exception $e) {
    if (isset($pdo)) $pdo->rollback();
    //if (!is_null($pdo)) $pdo->rollBack();
    echo "<p>ERROR!</p>";
    echo json_encode(getErrorData($e));
}
echo "<H2>RESULT: $aliasGrupo CURR={$currNum} MAX={$maxNum}</H2>";
