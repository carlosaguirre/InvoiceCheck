<?php
require_once dirname(__DIR__)."/bootstrap.php";
if(!hasUser() || !in_array(getUser()->nombre, ["admin", "sistemas"])) {
  if (hasUser()) {
    setcookie("menu_accion", "", time() - 3600);
    setcookie("menu_accion", "", time() - 3600, "/invoice");
  }
  header("Location: /".$_project_name."/");
  die("Redirecting to /".$_project_name."/");
}

$action="genEmptyAndNewPrvRegNKeys";

switch($action) {
  case "newPasskeyIfSameAsUser": fixSafeKeysView(); break;
  case "genEmptyAndNewPrvRegNKeys": genNewPrvKeys(); break;
  default: listView($action);
}
function genNewPrvKeys() {
?>
<html>
  <head>
    <title>Provider Password Fix</title>
    <script>
    </script>
  </head>
  <body>
    <div id="area_general" class="central">
      <h1>Provider Password Fix</h1>
      <div id="area_detalle">
<?php
  global $prvObj, $usrObj, $upObj, $query;
  $prvIdList=[
"A-427","B-190","C-129","C-606","C-607","C-608","D-011","D-222","E-008","E-156","E-180","E-251","E-252","F-049","F-150","G-189","G-475","I-198","K-001","L-165",
"L-166","L-169","L-170","M-452","P-395","R-016","R-024","R-085","R-107","R-364","S-119","S-413","S-414","T-055","T-233","V-155","A-431","A-433","C-609","C-611",
"D-104","D-224","F-151","G-134","G-472","G-476","G-479","L-167","M-369","M-449","M-455","O-114","P-388","P-391","R-362","R-363","S-410","S-412","S-415","A-429",
"A-430","C-289","D-203","D-223","G-478","J-079","L-168","M-264","M-448","O-117","P-385","P-386","P-392","T-010","A-425","A-426","A-428","C-055","C-175","C-605",
"C-610","C-612","D-065","G-471","G-473","H-169","M-450","O-113","O-115","P-383","P-390","P-393","R-361","R-365","S-408","S-411","S-416","S-419","S-420","T-230",
"T-237","T-239","T-240","U-018","G-462","A-421","A-424","B-185","B-186","B-188","C-284","C-576","C-597","C-598","C-599","C-601","C-603","C-613","C-616","D-220",
"D-221","E-091","E-242","E-243","E-244","E-246","E-250","G-464","G-465","H-164","H-165","H-168","I-107","I-196","I-200","K-035","M-442","M-444","M-445","M-451",
"M-454","O-116","P-382","P-384","R-355","R-356","R-359","S-401","S-403","S-405","S-407","T-040","T-228","T-229","T-231","T-241","V-152","Z-045","B-189","C-600",
"P-389","R-104","N-072","A-432","G-474","T-238","U-046","A-417","A-418","A-419","A-420","A-422","A-423","B-187","C-104","C-602","C-604","E-245","E-247","E-249",
"F-148","G-461","G-463","G-466","G-468","G-469","G-477","G-480","H-166","H-167","I-195","I-197","I-199","K-036","L-161","L-162","L-163","M-443","M-446","M-453",
"O-032","P-004","P-379","P-380","P-381","P-387","P-394","R-357","R-358","S-157","S-159","S-400","S-402","S-404","S-406","S-409","S-417","S-418","T-232","T-234",
"T-235","T-236","V-153","V-154","W-037","Z-046","H-163","L-164","P-378"
/*
    "A-027", "A-073", "A-420", "A-426", "A-427", "A-428", "A-430", "A-431", "A-432", "A-433", "B-189", "B-190", "C-129", "C-175", "C-289", "C-605", "C-606", "C-607", "C-608", "C-609",
    "C-610", "C-611", "C-612", "C-613", "D-011", "D-065", "D-104", "D-203", "D-222", "D-223", "D-224", "E-008", "E-156", "E-180", "E-250", "E-251", "E-252", "F-049", "F-150", "F-151", 
    "G-134", "G-189", "G-471", "G-472", "G-473", "G-474", "G-475", "G-476", "G-477", "G-478", "G-479", "G-480", "H-169", "I-196", "I-197", "I-198", "I-199", "I-200", "J-079", "K-001",
    "L-164", "L-165", "L-166", "L-167", "L-168", "L-169", "L-170", "M-264", "M-369", "M-448", "M-449", "M-450", "M-451", "M-452", "M-453", "M-454", "M-455", "N-072", "O-032", "O-113", 
    "O-114", "O-115", "O-116", "O-117", "P-382", "P-383", "P-384", "P-385", "P-386", "P-387", "P-388", "P-389", "P-390", "P-391", "P-392", "P-393", "P-394", "P-395", "R-016", "R-024", 
    "R-085", "R-107", "R-361", "R-362", "R-363", "R-364", "R-365", "S-119", "S-159", "S-407", "S-408", "S-409", "S-410", "S-411", "S-412", "S-413", "S-414", "S-415", "S-416", "S-417", 
    "S-418", "S-419", "S-420", "T-040", "T-230", "T-231", "T-232", "T-233", "T-234", "T-235", "T-236", "T-237", "T-238", "T-239", "T-240", "T-241", "U-018", "U-046", "Z-046",

    "A-010", "A-085", "B-036", "C-055", "C-104", "C-208", "C-269", "C-284", "C-414", "C-512", "C-576", "C-615", "C-616", "D-015", "E-091", "G-332", "H-111", "H-162", "I-107", "M-262", "M-302", "M-434", "M-446", "O-099", "R-069", "R-104", "R-355", "S-157", "S-164", "T-010", "T-028", "T-112", "T-228", "V-099",

    "A-429", "A-158", "V-155", "V-051"
*/
  ];

/*
FALTAN (78):
*/


  echo "<p>Revisión de ".count($prvIdList)." códigos</p>\n";
  if (!isset($prvObj)) { require_once "clases/Proveedores.php"; $prvObj=new Proveedores(); }
  $prvObj->rows_per_page=0;
  $prvData=$prvObj->getData("codigo in (\"".implode("\",\"", $prvIdList)."\")", 0, "id, codigo, razonSocial, rfc, correo");
  echo "<p>Se encontraron ".count($prvData)." registros existentes</p>\n";
  if (!isset($usrObj)) { require_once "clases/Usuarios.php"; $usrObj=new Usuarios(); }
  $usrObj->rows_per_page=0;
  if (!isset($upObj)) { require_once "clases/Usuarios_Perfiles.php"; $upObj=new Usuarios_Perfiles(); }
  foreach ($prvData as $idx => $prvItem) {
    try {
      $usrData=$usrObj->getData("nombre=\"".$prvItem["codigo"]."\"",0,"id, persona, password, seguro, email");
      if (isset($usrData[0]["id"])) {
        $usrData=$usrData[0];
        $needPwdFix=(empty($usrData["password"])||empty($usrData["seguro"]));
        $needNameFix=empty($usrData["persona"]);
        $needMailFix=(empty($usrData["email"])&&!empty($prvItem["correo"]));
        if ($needPwdFix||$needNameFix||$needMailFix) {
          $usrArr=["id"=>$usrData["id"]];
          if ($needNameFix) $usrArr["persona"]=$prvItem["razonSocial"];
          if ($needMailFix) $usrArr["email"]=$prvItem["correo"];
          if ($needPwdFix) {
            $salt=$usrData["seguro"];
            if (empty($salt)) {
              $salt = dechex(mt_rand(0, 2147483647)) . dechex(mt_rand(0, 2147483647));
              $usrArr["seguro"]=$salt;
            }
            $lpwd=hash("sha256",$prvItem["rfc"].$salt);
            for($rnd=0;$rnd<65536;$rnd++) $lpwd=hash("sha256",$lpwd.$salt);
            $usrArr["password"]=$lpwd;
          }
        } else $usrArr=null;
        $upData=$upObj->getData("idUsuario=$usrData[id] and idPerfil=3",0,"count(1) n");
        $needUPFix=empty($upData[0]["n"]);
        if ($needUPFix) {
          $upArr=["idUsuario"=>$usrData["id"],"idPerfil"=>3];
        } else $upArr=null;
      } else {
        $upArr=null;
        $usrArr=["nombre"=>$prvItem["codigo"], "persona"=>$prvItem["razonSocial"]];
        $needDefaultEmail=empty($prvItem["correo"]);
        $usrArr["email"]=$needDefaultEmail?"compras@dexalapa.com.mx":$prvItem["correo"];
        $salt = dechex(mt_rand(0, 2147483647)) . dechex(mt_rand(0, 2147483647));
        $usrArr["seguro"]=$salt;
        $lpwd=hash("sha256",$prvItem["rfc"].$salt);
        for($rnd=0;$rnd<65536;$rnd++) $lpwd=hash("sha256",$lpwd.$salt);
        $usrArr["password"]=$lpwd;
      }
      if (isset($usrArr)) {
        $result = $usrObj->saveRecord($usrArr);
        echo "<p>$prvItem[codigo]: ";
        $isNew=!isset($usrArr["id"]);
        if ($result) {
          echo ($isNew?"Generado":"Actualizado")." exitosamente! <!-- $query -->";
          if ($isNew) {
            if ($needDefaultEmail) echo " (con correo compras)";
          } else {
            echo " (";
            if ($needNameFix) echo "razon social";
            if ($needMailFix) echo ($needNameFix?", ":"")."correo";
            if ($needPwdFix) echo (($needNameFix||$needMailFix)?", ":"")."contraseña";
            echo ")";
          }
        } else {
          if (empty(DBi::$errno)) echo "SIN CAMBIOS en Usuarios";
          else echo "Ocurrió un ERROR en Usuarios: ".DBi::$errno." : ".DBi::$error;
        }
        echo "</p>\n";
      }
      if (isset($upArr)) {
        $result = $upObj->saveRecord($upArr);
        echo "<p>$prvItem[codigo]: ";
        if ($result) {
          echo "Asignado como proveedor <!-- $query -->";
        } else {
          if (empty(DBi::$errno)) echo "SIN CAMBIOS en Usuario-Perfil";
          else echo "Ocurrió un ERROR en Usuario-Perfil: ".DBi::$errno." : ".DBi::$error;
        }
        echo "</p>\n";
      }
    } catch (Error $err) {
      echo "<p style=\"background-color: lightred;\">$prvItem[codigo]: ERROR ".json_encode(getErrorData($e))."</p>";
    }
  }
  echo "<!-- FIN -->\n";
?>
      </div>
    </div>
  </body>
</html>
<?php
}
function fixSafeKeysView() {
  require_once "clases/Usuarios.php";
  $usrObj=new Usuarios();
  // select distinct u.nombre from usuarios u inner join usuarios_perfiles up on u.id=up.idUsuario where up.idPerfil!=3 order by u.nombre;
  $usrObj->clearOrder();
  $usrObj->addOrder("u.nombre");
  $usrData=$usrObj->getData("up.idPerfil!=3",0,"distinct u.nombre, u.password, u.seguro, u.id, u.banderas", "u inner join usuarios_perfiles up on u.id=up.idUsuario");
  require_once "clases/Proceso.php";
  $prcObj=new Proceso();
?>
<html>
  <head>
    <title>Login Password Fix</title>
    <script>
    </script>
  </head>
  <body>
    <div id="area_general" class="central">
      <h1>Login Password Fix</h1>
      <div id="area_detalle">
        <ul>
<?php // encontrar usuarios con su nombre como contraseña y requerir cambio de contraseña
  foreach ($usrData as $usrItem) {
    $uid=$usrItem["id"];
    $unm=$usrItem["nombre"];
    $pwd=$usrItem["password"];
    $salt=$usrItem["seguro"];
    $ufg=$usrItem["banderas"];
    $lpwd=hash("sha256",$unm.$salt);
    for($rnd=0;$rnd<65536;$rnd++) $lpwd=hash("sha256",$lpwd.$salt);
    if ($lpwd===$usrItem->password) {
      if($usrObj->saveRecord(["id"=>$uid,"banderas"=>($ufg^1)])) {
        $umg="Se requiere cambio de contraseña";
        $prcObj->cambioAdmin($uid, "Cambio", "SISTEMAS", "Usuario debe cambiar clave $unm : IGUAL");
      } else {
        $umg="Esperando que cambie contraseña";
                  echo "No se guardó el cambio '$abspath'. id=$row[id], uuid=$xmlData[uuid]: QUERY=$query, ERRORS=".json_encode(DBi::$errors)," ERRNO=".DBi::$errno.", ERROR=".DBi::$error;
      }
    } else $umg="Tiene contraseña válida";
?>
          <li><?= $unm ?> : <?= $umg ?></li>
<?php
  } ?>
        </ul>
      </div>
    </div>
  </body>
</html>
<?php
}

function listView($action) {
?>
<html>
  <head>
    <title>Default Action</title>
    <script>
    </script>
  </head>
  <body>
    <div id="area_general" class="central">
      <h1>Default Action</h1>
      <div id="area_detalle">
        Accion desconocida: <?= $action ?>
      </div>
    </div>
  </body>
</html>
<?php
}
