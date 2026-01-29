<?php
require_once dirname(__DIR__)."/bootstrap.php";
if (!hasUser()) {
    echo "<img src=\"data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7\" onload=\"location.reload(true);\">";
    die();
}

clog2ini("showUsuarios");
clog1seq(1);

// Este script es un selector, funciona como componente dentro de otro php.
// Sin parámetros iniciales permite utilizarlo de forma independiente
// El parámetro selector oculta el código de página y solo proporciona la estructura de tabla
// El parámetro data oculta la estructura de tabla y proporciona solo las filas de datos, adicionalmente actualiza la sección de botones de navegación

function fillData($table, $row) {
    if (!isset($_GET["tabla"]) && !isset($_GET["datos"]))
        return "alert('".$table."[".$row["id"]."] = ".$row["nombre"]."')";
    if (!isset($row["comprasEmpresaIds"])) $row["comprasEmpresaIds"]="";
    if (!isset($row["comprasGrupo"])) $row["comprasGrupo"]="";
    $cambiaClave=$row["banderas"]&1;
    return "appendLog(' # * # * # FILLDATA $row[nombre]\\n'); resultadoAvanzado(event, 'usuario', '$row[id]|$row[nombre]|$row[persona]|$row[email]|$cambiaClave|$row[unoComo]|$row[observaciones]|$row[perfiles]|$row[perfilxgrupo]'); fillDataCheck(); overlay();";
}
if (!isset($usrObj)) {
    require_once "clases/Usuarios.php";
    $usrObj = new Usuarios();
}
/*
if (!isset($perObj)) {
    require_once "clases/Perfiles.php";
    $perObj=new Perfiles();
}
function mapRep($arr,$map) {
    $res=[];
    foreach ($arr as $idx => $val) {
        $res[$idx]=$map[$val]??$val;
    }
    return $res;
}
$perObj->rows_per_page=0;
$perMap=[]; $perIds=[]; 
foreach ($perObj->getData(false,0,"upper(nombre) nombre, id") as $perRow) {
    $perIds[$perRow["nombre"]]=$perRow["id"];
    $perMap[$perRow["id"]]=$perRow["nombre"];
}
if (!isset($gpoObj)) {
    require_once "clases/Grupo.php";
    $gpoObj = new Grupo();
}
$gpoObj->rows_per_page=0;
$gpoIds=[]; $gpoMap=[]; 
foreach ($gpoObj->getData(false,0,"upper(alias) alias, id") as $gpoRow) {
    $gpoIds[$gpoRow["alias"]]=$gpoRow["id"];
    $gpoMap[$gpoRow["id"]]=$gpoRow["alias"];
} */
if (!isset($_GET["tabla"]) && !isset($_GET["datos"])) {
?>
<html>
  <head>
    <?= isBrowser(["Edge","IE"])?"<meta http-equiv=\"x-ua-compatible\" content=\"ie=edge\" />":"" ?>
    <base href="<?= $_SERVER['HTTP_ORIGIN'] . $_SERVER['WEB_MD_PATH'] ?>" target="_blank">
    <meta charset="utf-8" />
    <title><?= $systemTitle ?></title>
    <link href="css/general.php" rel="stylesheet" type="text/css" />
<?php
    require_once "templates/generalScript.php";
    echoGeneralScript();
?>
    <script>
      window.onload = fillPaginationIndexes;
    </script>
  </head>
  <body>
    <div id="dialog_resultarea">
<?php
}
if (isset($_REQUEST["pageno"])) {
    $usrObj->pageno = $_REQUEST["pageno"];
}
if (isset($_REQUEST["limit"])) {
    $usrObj->rows_per_page = $_REQUEST["limit"];
}
$where = "(up.idPerfil is null or up.idPerfil!=3)";
$userColNames="u.id, u.nombre, u.persona, u.email, u.observaciones, u.banderas, u.unoComo, group_concat(distinct up.idPerfil order by up.idPerfil) perfiles, group_concat(DISTINCT p.nombre order by up.idPerfil) perfilesN, group_concat(distinct ug.idPerfil order by ug.idPerfil) perfilesg, group_concat(DISTINCT ug.perfil order by ug.idPerfil) perfilesgN, group_concat(distinct ug.perfilxgrupo order by ug.idPerfil) perfilxgrupo, group_concat(distinct ug.aliases order by ug.idPerfil) aliases";
$innerJoinData="u left join invoice.usuarios_perfiles up on u.id=up.idUsuario left join invoice.perfiles p on up.idPerfil=p.id left join (select usg.idUsuario, usg.idPerfil, group_concat(g.alias) aliases, p.nombre perfil, concat(usg.idPerfil,':',group_concat(usg.idGrupo order by usg.idGrupo SEPARATOR ';')) perfilxgrupo from usuarios_grupo usg inner join grupo g on usg.idGrupo=g.id inner join perfiles p on usg.idPerfil=p.id group by usg.idUsuario,usg.idPerfil) ug on u.id=ug.idUsuario";
$order = "lower(u.nombre)";
if (isset($_REQUEST["param"])) {
    $param = $_REQUEST["param"];
    foreach ($param as $pvalue) {
        $value = $_REQUEST[$pvalue];
        if (isset($value) && $value!==null && $value!==false && isset($value[0])) {
            $preMod="";
            $posMod="";
            if (isset($value[0]) && $value[0]==="%") {
                $preMod="%";
                $value=substr($value, 1);
            }
            if (isset($value[0]) && $value[strlen($value)-1]==="%") {
                $posMod="%";
                $value=substr($value,0,-1);
            }
            if (!isset($preMod[0]) && !isset($posMod[0])) {
                $preMod="%";
                $posMod="%";
            }
            if (isset($value[0])) {
                $upValue=mb_strtoupper($value);
                if ($pvalue=="perfiles") {
                    $where.=" AND (upper(p.nombre) like '{$preMod}{$upValue}{$posMod}' or upper(ug.perfil) like '{$preMod}{$upValue}{$posMod}' or upper(ug.aliases) like '{$preMod}{$upValue}{$posMod}')";
                } else {
                    if ($pvalue=="persona") $order="lower(u.persona)";
                    $where .= " AND UPPER(u." . $pvalue . ") LIKE '{$preMod}{$upValue}{$posMod}'";
                }
            }
        }
    }
}
$where.=" GROUP BY u.nombre";
$usrObj->addOrder($order);
global $query;
$data = $usrObj->getData($where,0,$userColNames,$innerJoinData);
clog2(" Q U E R Y : $query");
clog2(" NUMROWS=$usrObj->numrows");
clog2(" DATA LENGTH=".count($data));
if ($usrObj->numrows > 0) {
    if (!isset($_GET["datos"])) {
?>
      <input type="hidden" name="selectortablename" id="selectortablename" value="<?= $usrObj->tablename ?>">
      <input type="hidden" name="selectorname" id="selectorname" value="showUsuarios">
      <table onwheel="wheelPaginate(event)">
        <thead>
          <tr>
            <th>Usuario</th><th>Nombre</th><th>Correo</th><th>Perfiles</th>
          </tr>
          <tr>
            <th><input type="text" onkeyup="fillSelectorContents('filter')" name="nombre" class="longtext filter_box" id="nameFilterBox"></th>
            <th><input type="text" onkeyup="fillSelectorContents('filter')" name="persona" class="longtext filter_box" id="fullnameFilterBox"></th>
            <th><input type="text" onkeyup="fillSelectorContents('filter')" name="email" class="longtext filter_box" id="emailFilterBox"></th>
            <th><input type="text" onkeyup="fillSelectorContents('filter')" name="perfiles" class="longtext filter_box" id="profileFilterBox"></th>
        </tr>
      </thead>
      <tbody id="dialog_tbody">
<?php
    }
?>
          <input type="hidden" name="pageno" id="pageno" value="<?= $usrObj->pageno ?>" />
          <input type="hidden" name="limit" id="limit" value="<?= $usrObj->rows_per_page ?>" />
          <input type="hidden" name="lastpg" id="lastpg" value="<?= $usrObj->lastpage ?>" />
          <img src="data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7" onload="wheelLock=false;ekil(this);">
<?php
    foreach ($data as $row) {
        //echo "<!-- ".json_encode($row)." -->";
        $perfiles=isset($row["perfilesN"][0])?explode(",",$row["perfilesN"]):[];
        $perxgpo=isset($row["perfilesgN"][0])?explode(",", $row["perfilesgN"]):[];
        //if (isset($perxgpo[0])) {
        //    if (empty($perfiles)) $perfiles=$perxgpo;
        //    else 
        $perfiles=array_unique(array_merge($perfiles,$perxgpo));
        //}
        sort($perfiles);
        //echo "<!-- perfiles = ".(isset($perfiles)?"['".implode("','", $perfiles)."']":"null")." -->";
        $perfiles=implode(",", $perfiles);
        $aliases=$row["aliases"];
        if (isset($aliases[0])) $perfiles.="; ".$aliases;
?>
          <tr>
            <td ondblclick="<?= fillData('usuarios', $row); ?>"><?= $row['nombre'] ?></td>
            <td ondblclick="<?= fillData('usuarios', $row); ?>"><?= $row['persona'] ?></td>
            <td ondblclick="<?= fillData('usuarios', $row); ?>"><?= $row['email'] ?></td>
            <td ondblclick="<?= fillData('usuarios', $row); ?>"<?= isset($perfiles[30])?" style=\"max-width: 250px;\"":"" ?>><?= preg_replace('/,([\w])/', ', \1', $perfiles) ?></td>
          </tr>
<?php
    }
?>
          <tr><th></th><th></th><th></th><th></th></tr>
<?php
    if (!isset($_GET["datos"])) {
?>
        </tbody>
        <?php /* <tfoot id="dialog_tfoot">
          <tr>
            <th colspan="4" class="centered">
              <input type="button" id="navToFirst"    class="navOverlayButton" value="<<"  onclick="fillSelectorContents('first')">
              <input type="button" id="navToPrevious" class="navOverlayButton" value=" < " onclick="fillSelectorContents('prev')">
              <span id="paginationIndexes" class="fontPageFormat"> <?= $usrObj->pageno ?>/<?= $usrObj->lastpage ?> </span>
              <input type="button" id="navToNext"     class="navOverlayButton" value=" > " onclick="fillSelectorContents('next')">
              <input type="button" id="navToLast"     class="navOverlayButton" value=">>"  onclick="fillSelectorContents('last')">
            </th>
          </tr>
        </tfoot> */ ?>
      </table><img src="data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7" onload="setPageNavBlock(<?= $usrObj->pageno.",".$usrObj->lastpage ?>);ekil(this);">
<?php
    }
}
if (!isset($_GET["tabla"]) && !isset($_GET["datos"])) { 
?>
    </div><div id="mylog" class="hidden"></div>
  </body>
</html>
<?php
}

include_once ("configuracion/finalizacion.php");
clog1seq(-1);
clog2end("showUsuarios");
