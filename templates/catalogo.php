<?php
clog2ini("templates.catalogo");
clog1seq(1);

if (!isset($modificaCata)) $modificaCata = modificacionValida("Catalogos");
if (!isset($modificaData)) $modificaData = modificacionValida("Datos");

if (!isset($consultaUsrs)) $consultaUsrs = consultaValida("Usuarios");
if (!isset($consultaGrpo)) $consultaGrpo = consultaValida("Grupo");
if (!isset($consultaProv)) $consultaProv = consultaValida("Proveedor");
if (!isset($modificaUsrs)) $modificaUsrs = modificacionValida("Usuarios");
if (!isset($modificaGrpo)) $modificaGrpo = modificacionValida("Grupo");
if (!isset($modificaProv)) $modificaProv = modificacionValida("Proveedor");

if (!isset($consultaPerm)) $consultaPerm = consultaValida("Permisos");
if (!isset($modificaPerm)) $modificaPerm = modificacionValida("Permisos");

if (!empty($_POST["sortBy"])) {
    $sortValueAttribCode = " value=\"$_POST[sortBy]\"";
} else $sortValueAttribCode = "";
if (!empty($_POST["filterBy"])) {
    $filterValueAttribCode = " value=\"$_POST[filterBy]\"";
} else $filterValueAttribCode = "";
if (!empty($_POST["filterItem"])) {
    $filterItemAttribCode = " value=\"$_POST[filterItem]\"";
} else $filterItemAttribCode = "";
if (!empty($_POST["lastAction"])) {
    $lastActionAttribCode = " value=\"$_POST[lastAction]\"";
} else $lastActionAttribCode = "";
?>
    <form method="post" name="formaCatalogo" id="formaCatalogo" class="noApply" enctype="multipart/form-data">
        <input type="hidden" name="tablename" id="catalog_tablename">
        <input type="hidden" name="tableviewname" id="catalog_tableviewname">
        <input type="hidden" name="editable" id="catalog_editable">
        <input type="hidden" name="noCols" id="catalog_noCols">
        <input type="hidden" name="currPg" id="catalog_currPg">
        <input type="hidden" name="lastPg" id="catalog_lastPg">
        <input type="hidden" name="sortBy" id="catalog_sortBy"<?=$sortValueAttribCode?>>
        <input type="hidden" name="filterBy" id="catalog_filterBy"<?=$filterValueAttribCode?>>
        <input type="hidden" name="filterItem" id="catalog_filterItem"<?=$filterItemAttribCode?>>
        <input type="hidden" name="lastAction" id="catalog_lastAction"<?=$lastActionAttribCode?>>
    </form>
    <h1 class="txtstrk area_header">Cat&aacute;logo</h1>
    <div id="catalog_menu" class="lefted fltL">
<?php
$query = "SHOW TABLES";
$result = DBi::query($query) or trigger_error("SQL", E_USER_ERROR);
if ($result) {
    $catalogs = [];
    $invTables = [];
    $admTables = [];
 
    while($row = $result->fetch_assoc()) {
        foreach($row as $col=>$tabname) {
            if (substr($tabname,0,3)==="cat") $catalogs[] = $tabname;
            else $invTables[] = $tabname;
        }
    }
    
    if ($consultaCata) foreach($catalogs as $cattab) {
        $fixedname = fixName(substr($cattab, 3), true, true);
        
        $fixedtitle = "";
        if (isset($fixedname[12])) {
            $fixedtitle = " title=\"$fixedname\"";
        }
        echo "<input type='button' value='$fixedname'$fixedtitle onclick='viewTable(\"$cattab\", \"$fixedname\", \"catalog\");'/><br>";
    }
    if ($consultaCata && $consultaData) echo "<hr>";
    $tablasPermisos = ["Acciones"=>"Permisos", "Compras Grupo"=>"Permisos",
                       "Perfiles"=>"Permisos", "Permisos"=>"Permisos", "Usuarios Perfiles"=>"Permisos"];
    $tablasAdmin = ["Infolocal", "Logs", "Metodosdepago", "Proceso", "Trace", "Usuarios"=>"Usuarios", "Grupo"=>"Grupo", "Proveedores"=>"Proveedor"];
    if ($consultaData) foreach($invTables as $cattab) {
        if (strpos($cattab, "_")===false) $fixedname=fixName($cattab, true, true);
        else $fixedname = ucwords(str_replace("_", " ", $cattab));
        $fixedtitle = "";
        if (isset($fixedname[12])) {
            $fixedtitle = " title=\"$fixedname\"";
        }
        if (in_array($fixedname,$tablasAdmin) && !$esAdmin) continue;
        if (isset($tablasPermisos[$fixedname]) && !consultaValida($tablasPermisos[$fixedname])) continue;
        echo "<input type='button' value='$fixedname'$fixedtitle onclick='viewTable(\"$cattab\", \"$fixedname\", \"table\");'/><br>";
    }
}
function fixName($name, $capitalize, $all) {
    if (!isset($name[0])) return "";
    echo "<!-- INI fixName $name -->\n";
    static $words = ["autorizacion"=>0,"avance"=>0,"clave"=>0,"codigo"=>0,"compara"=>0,"config"=>0,"contenedor"=>0,"de"=>0,"forma"=>0,"historial"=>0,"lista"=>0,"material"=>0,"metodo"=>0,"num"=>0,"opciones"=>0,"ordenes"=>0,"patente"=>0,"pedimento"=>0,"prod"=>0,"regimen"=>0,"sat"=>0,"serv"=>0,"subtipo"=>0,"tasao"=>0,"tipo"=>0,"transporte"=>0,"unidad"=>0,"uso"=>0];
    if ($words["clave"] == 0) {
        foreach ($words as $key=>$num) {
            $words[$key] = strlen($key);
        }
    }
    foreach ($words as $prefix=>$len) {
        if (ord($name[0])<ord($prefix[0])) break;
        if (substr($name,0,$len)===$prefix) {
            switch($prefix) {
                case "de": if(substr($name,0,8)==="derechos") { $prename="derechos"; $len=8; break; }
                      else if (substr($name,0,7)==="deleted") { $prename="deleted"; $len=7; break; }
                case "serv": if (substr($name,0,8)==="servicio") { $prename="servicio"; $len=8; break; }
                        else if (substr($name,0,5)==="serv_") { $prename="serv"; $len=5; break; }
                case "metodo": if (substr($name,0,7)==="metodos") { $prename="metodos"; $len=7; break; }
                default:
                    $prename = substr($name,0,$len);
            }
            if (in_array($prename, ["sat"])) $prename=strtoupper($prename);
            else if ($capitalize) $prename = ucfirst($prename);
            $postname = fixName(substr($name,$len), $capitalize&&$all, $all);
            if ($prename==="Tasao") $prename="Tasa O";
            else if ($prename==="tasao") $prename="tasa o";
            return $prename." ".$postname;
        }
    }
    if (in_array($name,["69b","cp","stcc"])) return strtoupper($name);
    if ($capitalize) {
        if ($all) return ucwords($name);
        return ucfirst($name);
    }
    return $name;
}
if ($esAdmin) {
?>
      <hr>
      <input type='button' value='LOG Files' onclick='viewTable("LOGFILES","LOGS DE ARCHIVOS","");'/>
<?php
} ?>
    </div>
    <div id="catalog_content" class="fltL hidden">
      <fieldset id="catalog_fieldset">
        <legend align="left" class="uppercase boldValue" id="catalog_legend"></legend>
        <div id="catalog_column_wrapper">
        <div id="catalog_column_section" onscroll="document.getElementById('catalog_content_section').scrollLeft = this.scrollLeft;">
          <table id="catalog_column_table" class="catalog_table"><thead><tr><th></th></tr></thead></table>
        </div>
        </div>
        <div class="tableWrapper2" id="catalog_content_section" onscroll="document.getElementById('catalog_column_section').scrollLeft = this.scrollLeft;">
          <table id="catalog_content_table" class="catalog_table">
            <tbody>
              <tr><td></td></tr>
            </tbody>
          </table>
        </div>
        <div id="catalog_page_section">
          <button id="btnFrst" onclick="doPageChange('frst');" class="boldValue btn25">&laquo;</button>
          <button id="btnBack" onclick="doPageChange('prev');" class="boldValue btn25">&lt;</button>
          <span id="pagNum" class="pageCtrl">0</span> / <span id="lastPag" class="pageCtrl">0</span>
          <button id="btnFwrd" onclick="doPageChange('next');" class="boldValue btn25">&gt;</button>
          <button id="btnLast" onclick="doPageChange('last');" class="boldValue btn25">&raquo;</button>
          <div id="catalog_numRegs"><select id="numRegs" disabled class="pad1_6" onchange="document.getElementById('catalog_noCols').value=this.value; viewTable(false, false, 'page');"><option>10</option><option>20</option><option>30</option><option>40</option><option>50</option></select></div>
          <div id="catalog_commit_section"><img src="imagenes/icons/carga6.png" id="uploadCatalogIcon" class="btnFX btn25" onclick="doUploadData(event);"/>&nbsp;<img src="imagenes/icons/descarga6.png" id="downloadCatalogIcon" class="btnFX btn25" onclick="doDownloadData(event);"/>&nbsp;<img src="imagenes/icons/backArrow.png" class="btnFX btn25 disabled" onclick="doRollBack(event);"/>&nbsp;<img src="imagenes/icons/frontArrow.png" class="btnFX btn25 disabled" onclick="doCommit(event);"/></div>
        </div>
      </fieldset>
    </div>
    <div class="clear"></div>
<?php
clog1seq(-1);
clog2end("templates.catalogo");
