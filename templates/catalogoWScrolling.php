<?php
clog2ini("templates.catalogo");
clog1seq(1);

$lookoutFilePath = "";
if (!empty($_SERVER['CONTEXT_DOCUMENT_ROOT'])) $lookoutFilePath = $_SERVER['CONTEXT_DOCUMENT_ROOT'];
else if (!empty($_SERVER['DOCUMENT_ROOT'])) $lookoutFilePath = $_SERVER['DOCUMENT_ROOT'];
$dir = $lookoutFilePath . "clases";
$files = scandir($dir);
?>
          <div id="area_central">
            <h1 class="txtstrk">Cat&aacute;logo</h1>
            <div id="catalog_scroll" class="scrolldiv">
            
<?php 
foreach($files as $file) {
    if ($file==="." || $file==="..") continue;
    $path = $dir . DIRECTORY_SEPARATOR . $file;
    if (!is_file($path)) continue;
    if (strtolower(pathinfo($file, PATHINFO_EXTENSION)) !== "php") continue;
    $expectedClass = pathinfo($file, PATHINFO_FILENAME);
    $contents = file_get_contents($path);
    if ($contents === false) continue;
    // Match: class <ExpectedName> ... extends DBObject
    $re = '/\bclass\s+' . preg_quote($expectedClass, '/') . '\b[^\\{;]*\bextends\s+DBObject\b/i';
    if (!preg_match($re, $contents)) continue;
    // file exists, is php, and declares "class <Filename> extends DBObject"

    include_once "clases/$file";
    $classname = substr($file, 0, -4);
    $obj = new $classname();
    $tbn = $obj->tablename;
    echo "            <div id='table_$tbn' class='cattableWrapper'>\n";
    echo "              <fieldset id='box_$tbn'>\n";
    echo "                <legend>".strtoupper($tbn)."</legend>\n";
    $fieldlist = $obj->fieldlist;
    echo "                <div class='outer-container'>\n";
    echo "                  <div class='inner-container'>\n";
    echo "                    <div class='table-header' id='headerdiv_$tbn'>\n";
    echo "                      <table id='headertable_$tbn' class='headertable'>\n";
    echo "                        <thead><tr>\n";
    foreach($fieldlist as $field) {
        if (!is_array($field)) {
            echo "                          <th class='header-cell col'>$field</th>\n";
        }
    }
    echo "                        </tr></thead>\n";
    echo "                      </table>\n";
    echo "                    </div>\n";
    $data = $obj->getData();
    echo "                    <div class='table-body' onscroll='document.getElementById(\"headerdiv_$tbn\").scrollLeft = this.scrollLeft;'>\n";
    echo "                      <table id='bodytable_$tbn' class='bodytable'>\n";
    echo "                        <tbody>\n";
    foreach($data as $row) {
        echo "                          <tr>\n";
        foreach($fieldlist as $field) {
            if (!is_array($field)) {
          // && !isset($fieldlist[$field]["pkey"]) && !isset($fieldlist[$field]["auto"]))
                echo "                    <td class='body-cell col'>\n";
                echo "                      ".$row[$field]."\n";
                echo "                    </td>\n";
            }
        }
        echo "                          </tr>\n";
    }
    echo "                        </tbody>\n";
    echo "                      </table>\n";
    echo "                    </div>\n";
    echo "                  </div>\n";
    echo "                </div>\n";
    echo "              </fieldset>\n";
    echo "            </div>\n";
    echo "            <br>\n";
}
?>
            
            </div>
          </div>
<?php
clog1seq(-1);
clog2end("templates.catalogo");
