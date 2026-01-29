<?php
clog2ini("templates.catalogo");
clog1seq(1);

$lookoutFilePath = "";
if (!empty($_SERVER['CONTEXT_DOCUMENT_ROOT'])) $lookoutFilePath = $_SERVER['CONTEXT_DOCUMENT_ROOT'];
else if (!empty($_SERVER['DOCUMENT_ROOT'])) $lookoutFilePath = $_SERVER['DOCUMENT_ROOT'];
$files = scandir($lookoutFilePath."clases/");
$tableorder = ["Facturas","Conceptos","Contrarrecibos","Contrafacturas","Proveedores","Grupo","MetodosDePago","InfoLocal","Usuarios","Perfiles","Acciones","Usuarios_Perfiles","Usuarios_Grupo","Permisos","Proceso","Trace","Logs"];
?>
          <div id="area_central_cat" class="central">
            <h1 class="txtstrk">Cat&aacute;logo</h1>
            <form method="post" name="formaCatalogo" id="formaCatalogo" class="noApply" enctype="multipart/form-data">
                <input type="hidden" name="catalogo_admin" id="catalogo_admin" value="1">
            </form>
            <div id="catalog_area">
              <div id="catalog_menu" class="fltL"><br/>
                <img src="imagenes/icons/backArrow.png" class="btnFX hidden" onclick="doRollBack();"/>&nbsp;<img src="imagenes/icons/frontArrow.png" class="btnFX hidden" onclick="doCommit();"/><br/>
<?php
foreach($tableorder as $tabname) {
    echo "<input type='button' value='$tabname' onclick='scrollToSection(\"table_$tabname\");'/><br/>";
}
?>
              </div>
              <div id="catalog_scroll" class="scrolldiv fltL">
<?php 
foreach($tableorder as $classname) {
    $file = $classname.".php";
    if (in_array($file, $files)) {
        include_once "clases/$file";
        $obj = new $classname();
        echo "            <div id='table_$classname' class='cattableWrapper'>\n";
        echo "              <fieldset id='box_$classname'>\n";
        echo "                <legend align='left'><b>".strtoupper($classname)."</b></legend>\n";
        $fieldlist = $obj->fieldlist;
        echo "                <div class='tableWrapper' id='tabwrp_$classname'>\n";
        echo "                  <table>\n";
        echo "                    <thead><tr>\n";
        $numCols=0;
        foreach($fieldlist as $field) {
            if (!is_array($field) && !isset($fieldlist[$field]["pkey"]) && !isset($fieldlist[$field]["auto"])) {
                $numCols++;
                echo "              <th onclick='sortPageBy(\"$classname\",\"$field\", this);'>".$field."</th>\n";
            }
        }
        echo "                    </tr></thead>\n";
        echo "                    <input type='hidden' name='noColsVal_$classname' id='noColsVal_$classname' value='$numCols'>\n";
        $data = $obj->getData();
        echo "                    <tbody id='tbody_$classname'>\n";
        echo "                    <input type='hidden' name='currPgVal_$classname' id='currPgVal_$classname' value='$obj->pageno'>\n";
        echo "                    <input type='hidden' name='lastPgVal_$classname' id='lastPgVal_$classname' value='$obj->lastpage'>\n";

        foreach($data as $row) {
            echo "                    <tr id='row{$classname}_$row[id]'>\n";
            foreach($fieldlist as $field) {
                //if (!is_array($field)) {
                if (!is_array($field) && !isset($fieldlist[$field]["pkey"]) && !isset($fieldlist[$field]["auto"])) {
                    echo "                      <td id='cell{$classname}_$row[id]_{$field}' class='nowrap' ondblclick='changeToEditable(this, \"$classname\", \"$row[id]\", \"$field\");'>";
                    echo $row[$field]."</td>\n";
                }
            }
            echo "                    </tr>\n";
        }
        echo "                    </tbody>\n";
        echo "                  </table>\n";
        echo "                </div>\n";
        echo "              </fieldset>\n";
        echo "<button id='btnFrst_$classname' onclick='doPageChange(\"$classname\",\"frst\");'".($obj->pageno<=2?" class='invisible'":"").">&laquo;</button>";
        echo "<button id='btnBack_$classname' onclick='doPageChange(\"$classname\",\"prev\");'".($obj->pageno<=1?" class='invisible'":"").">&lt;</button>";
        echo " <span id='pagNum_$classname' class='pageCtrl'>$obj->pageno</span> / <span id='lastPag_$classname' class='pageCtrl'>$obj->lastpage</span> ";
        echo "<button id='btnFwrd_$classname' onclick='doPageChange(\"$classname\",\"next\");'".($obj->pageno>=$obj->lastpage?" class='invisible'":"").">&gt;</button>\n";
        echo "<button id='btnLast_$classname' onclick='doPageChange(\"$classname\",\"last\");'".(($obj->pageno+1)>=$obj->lastpage?" class='invisible'":"").">&raquo;</button>\n";
        echo "            </div>\n";
        echo "            <br>\n";
    }
}
?>
                <br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br>
              </div>
              <div class="clear"></div>
            </div>
          </div>
<?php
clog1seq(-1);
clog2end("templates.catalogo");
