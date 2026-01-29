<?php
require_once dirname(__DIR__)."/bootstrap.php";
abstract class DBObject {
    var $tablename;
    var $rows_per_page;
    var $pageno;
    var $lastpage;
    var $numrows;
    var $fieldlist;
    var $orderlist = array();
    var $data_array;
    var $lastResultValue;
    var $fetch_headers;
    var $errors = array();
    var $lastId;
    var $affectedrows;
    var $log = NULL;
    var $INSERT_OR_UPDATE = 0;
    var $ONLY_INSERT = 1;
    var $ONLY_UPDATE = 2;
    var $fullmap = NULL;
    var $savedValues = array();
    
    function __construct() {
        $this->tablename      = "dbobject";
        $this->rows_per_page  = 10;
        $this->fieldlist = array("id", "codigo", "nombre");
        $this->fieldlist["id"] = array("pkey" => "y", "auto" => "y");
        $this->fieldlist['modifiedTime'] = array('auto' => 'y');
        $this->fieldlist['codigo'] = array("skey" => "y");
        $this->log = "\n// xxxxxxxxxxxxxx DBObject xxxxxxxxxxxxxx //\n";
        //doclog("DBObject::construct","test",["tablename"=>$this->tablename]);
    }

    function clearFullMap() {
        unset($this->fullmap);
    }
    function getFullMap($key, $value, $where=false) {
        if (!isset($this->fullmap) || empty($this->fullmap)) {
            $this->fullmap=$this->getMap($key, $value, $where);
        }
        return $this->fullmap;
    }
    function getMap($key, $value, $where=false) {
        $data=$this->getData($where,0,"$key, $value");
        $map=[];
        foreach ($data as $row) {
            $map[$row[$key]]=$row[$value];
        }
        return $map;
    }
    function getAttributes($fieldarray) {
        clog2("DBObject getAttributes(".implode(",",$fieldarray).")");
        $attributes = ["pkey"=>[], "skey"=>[], "auto"=>[], "fields"=>[], "sets"=>[]];
        $attribnames = ["pkey", "skey", "auto"];
        $debugStr = strtoupper($this->tablename).": ";
        if ($fieldarray===null || ( !is_array($fieldarray)
            && !($fieldarray instanceof Traversable)
            && !($fieldarray instanceof Iterator)
            && !($fieldarray instanceof IteratorAggregate))) {
            $this->log .= "// Invalid array list\n";
            return false;
        }
        foreach ($fieldarray as $item => $value) {
            $debugStr.=$item."=>".$value;
            if (in_array($item, $this->fieldlist)) {
                $debugStr.="<IN>";
                $isKey=false;
                $attributes["fields"][] = $item;
                $debugStr.="{";
                foreach ($attribnames as $attr) {
                    $debugStr.=$attr;
                    if (isset($this->fieldlist[$item][$attr]) && !empty($value)) {
                        $debugStr.="-SI";
                        $attributes[$attr][] = $item;
                        $isKey=true;
                    } else {
                        $debugStr.="-NO";
                    }
                    $debugStr.=",";
                }
                $debugStr.="}";
                if (!$isKey) $attributes["sets"][] = $item;
            }
            $debugStr.="; ";
        }
        clog2($debugStr);
        clog2("fields:[".implode(",",$attributes["fields"])."]");
        clog2("sets:[".implode(",",$attributes["sets"])."]");
        clog2("pkey:[".implode(",",$attributes["pkey"])."]");
        clog2("skey:[".implode(",",$attributes["skey"])."]");
        clog2("auto:[".implode(",",$attributes["auto"])."]");
        return $attributes;
    }
    function getList($keyElement, $keyValue, $searchElement, $additionalWhere=false, $additionalSql=false) {
        $this->log .= "// INI getList ('$keyElement', '".(is_array($keyValue)?json_encode($keyValue):$keyValue)."', '$searchElement', '$additionalWhere', '$additionalSql')\n";
        global $query, $query_b;
        $whereStr = $this->getWhereCondition($keyElement, $keyValue);
        $whereStr_b = $this->getWhereCondition_b($keyElement, $keyValue);
        if (empty($additionalWhere)) {
            $whereStr = rtrim($whereStr, " AND ");
            $whereStr_b = rtrim($whereStr_b, " AND ");
        } else {
            $whereStr .= $additionalWhere;
            $whereStr_b .= $additionalWhere;
        }
        if (!empty($whereStr)) $whereStr = " WHERE ".$whereStr;
        if (!empty($whereStr_b)) $whereStr_b = " WHERE ".$whereStr_b;
        
        $query = "SELECT $searchElement FROM $this->tablename".$whereStr;
        $query_b = "SELECT $searchElement FROM $this->tablename".$whereStr_b;
        if ($additionalSql) {
            $query .= " ".$additionalSql;
            $query_b .= " ".$additionalSql;
        }
        $this->log .= "// Qry: $query\n";
        $result = DBi::query($query); // or trigger_error("SQL", E_USER_ERROR);
        $this->log .= DBi::get_info();
        $this->affectedrows = DBi::$affected_rows;
        $this->lastResultValue = "";
        if (is_object($result)) {
            $this->log .= "// Has result\n";
            $finfo = $result->fetch_fields();
            $retArr = [];
            foreach ($finfo as $val) {
                $retArr[] = $val->name;
            }
            if (!empty($retArr)) {
                if (count($retArr)>1) $this->log .= count($retArr)." results\n";
                else $this->log .= "1 result\n";
            } else $this->log .= "0 results\n";
            while ($row = $result->fetch_assoc()) {
                foreach($retArr as $elem) {
                    if (strlen($this->lastResultValue)>0) $this->lastResultValue.="|";
                    $this->lastResultValue .= $row[$elem];
                }
            }
            $result->close();
        }
        $this->log .= "// END getList result: ".$this->lastResultValue."\n";
        return $this->lastResultValue;
    }
    function hasOrder($fieldname=null) {
        if (!isset($fieldname[0])) return isset(array_keys($this->orderlist)[0]);
        if (isset($this->orderlist[$fieldname]))
            return $this->orderlist[$fieldname];
        return false;
    }
    function addOrder($fieldname, $direction='asc') {
        $this->orderlist[$fieldname] = $direction;
    }
    function delOrder($fieldname) {
        unset($this->orderlist[$fieldname]);
//            $this->orderlist = array();
    }
    function clearOrder() {
        unset($this->orderlist);
    }
    function setOrderList($list) {
        $this->orderlist=$list;
    }
    function getValue ($keyElement, $keyValue, $returnElements, $additionalWhere=false, $additionalSql=false, $fullData=false, $tableExtraStr="") {
        $this->log .= "// INI getValue ('$keyElement', '".(is_array($keyValue)?json_encode($keyValue):$keyValue)."', '$returnElements', '$additionalWhere', '$additionalSql', $fullData)\n";
        global $query, $query_b;
        $whereStr = $this->getWhereCondition($keyElement, $keyValue);
        $whereStr_b = $this->getWhereCondition_b($keyElement, $keyValue);
        if (empty($additionalWhere)) {
            $whereStr = rtrim($whereStr, " AND ");
            $whereStr_b = rtrim($whereStr_b, " AND ");
        } else {
            $whereStr .= $additionalWhere;
            $whereStr_b .= $additionalWhere;
        }
        if (!empty($whereStr)) $whereStr = " WHERE ".$whereStr;
        if (!empty($whereStr_b)) $whereStr_b = " WHERE ".$whereStr_b;

        if (isset($tableExtraStr[0]) && $tableExtraStr[0]!=" ") $tableExtraStr=" $tableExtraStr";
        $query = "SELECT $returnElements FROM $this->tablename".$tableExtraStr.$whereStr;
        $query_b = "SELECT $returnElements FROM $this->tablename".$tableExtraStr.$whereStr_b;
        if ($additionalSql) $query .= " ".$additionalSql;
        if ($additionalSql) $query_b .= " ".$additionalSql;
        $this->log .= "// Qry: $query\n";
        $result = DBi::query($query);// or trigger_error("SQL", E_USER_ERROR);
        $this->log .= DBi::get_info();
        $this->affectedrows = DBi::$affected_rows;
        if (is_object($result)) {
            $this->log .= "Has result\n";
            $finfo = $result->fetch_fields();
            $retArr = [];
            foreach ($finfo as $val) {
                $retArr[] = $val->name;
            }
            $this->lastResultValue = "";
            while($row = $result->fetch_assoc()) {
                if (count($row)>0) {
                    $retVal = [];
                    foreach($retArr as $elem) {
                        $retVal[$elem] = $row[$elem];
                        $this->log .= " // $elem = $row[$elem]\n";
                    }
                    if (strlen($this->lastResultValue)>0) $this->lastResultValue .= "|";
                    $this->lastResultValue .= implode ("|", $retVal);
                }
                if (!$fullData) break;
            }
            $this->log .= "// END getValue result: ".str_replace("|",",",$this->lastResultValue)."\n";
            $result->close();
            return $this->lastResultValue;
        }
        $this->log .= "// END getValue result empty\n";
        return "";
    }
    function exists($where, $extraFrom="", $group_str="") {
        $this->log .= "// INI exists\n";
        
        global $query;
        
        $query = "SELECT count(1) n FROM $this->tablename";
        if (isset($extraFrom[0])) $query .= " $extraFrom"; // alias1 inner join otherTable alias2 on alias1.key=alias2.fKey
        if (isset($where[0])) $query .= " WHERE $where";
        if (isset($group_str[0])) $query .= " GROUP BY $group_str";
        $this->log .= "// CountQry: $query\n";
        $result = DBi::query($query);// or trigger_error("SQL", E_USER_ERROR);
        $this->log .= "// Num rows      = ".DBi::$num_rows."\n";
        $this->log .= "// Inserted id   = ".DBi::$insert_id."\n";
        $this->log .= "// Affected rows = ".DBi::$affected_rows."\n";
        $this->log .= "// Info = ".DBi::$query_info."\n";
        $this->log .= "// Warnings = ".DBi::$warning_count;
        if (DBi::$warning_count && DBi::$warnings)
            $this->log .= " : " . DBi::$warnings;
        $this->log .= "\n";
        if (is_object($result)) {
            $count_array=[];
            while ($row = $result->fetch_row()) {
                $count_array[] = $row;
            }
            if (isset($count_array[0][0])) {
                if (!isset($count_array[1])) $this->numrows = (int)$count_array[0][0];
                else $this->numrows = count($count_array);
            } else $this->numrows = 0;
            //$query_data = $result->fetch_row();
            //$this->numrows = $query_data[0]??0;
            $this->log.="// RESULT IS OBJECT: ".json_encode($this->numrows)."\n";
        } else {
            $this->log.="// RESULT IS NOT OBJECT: ".json_encode($result)."\n";
            $this->numrows = 0;
        }
        $this->log .= "// ROWS = ".$this->numrows."\n";
        if (is_object($result)) $result->close();
        //else trigger_error("SQL", E_USER_ERROR);
        $this->log .= "// END exists: " . ($this->numrows > 0?"true":"false"). "\n";
        if ($this->numrows <= 0) return false;
        return true;
    }
    function getDataFromTemp($where_str=false, $fieldNames="*", $extraFrom="", $group_str="", $having_str="") {
        if (empty($fieldNames)) $fieldNames="*";
        else if (is_array($fieldNames)) $fieldNames=implode(",",$fieldNames);
        $this->log .= "// INI getDataFromTemp ($where_str, SL ($fieldNames), FT '$extraFrom', GB \"$group_str\", HV \"$having_str\" )\n";
        $this->data_array = array();
        global $query;
        $currMillis = round(microtime(true) * 1000);
        $startOfDay = strtotime("today") * 1000;
        $todayMillis = $currMillis - $startOfDay;
        $tmpTableName="tmp_".$this->tablename."_".$todayMillis;
        $query0 = "CREATE TEMPORARY TABLE $tmpTableName AS (";
        $query = "SELECT $fieldNames FROM $this->tablename";
        if (isset($extraFrom[0])) $query .= " $extraFrom";
        if (isset($where_str[0])) $query .= " WHERE $where_str";
        if (isset($group_str[0])) $query .= " GROUP BY $group_str";
        if (isset($having_str[0])) $query .= " HAVING $having_str";
        $query0.= $query.")";
        $this->log .= "// Qry0: $query0\n";
        $noresult = DBi::query($query0);// or trigger_error("SQL", E_USER_ERROR);
        //$this->log .= DBi::get_info();
        //$this->affectedrows = DBi::$affected_rows;
        $affectedRows0 = DBi::$affected_rows;

        // Contar resultados
        $query1 = "SELECT COUNT(*) AS num FROM $tmpTableName";
        $this->log .= "// Qry1: $query1\n";
        $result1 = DBi::query($query1);
        $num = $result1->fetch_assoc()['num'];

        $pageno=$this->pageno;
        $rows_per_page=$this->rows_per_page;
        $this->numrows=$num;
        $this->lastpage=0;

        if ($this->numrows <=0) {
            $this->pageno=0;
            $this->log .= "// END getDataFromTemp result empty\n";
            return $this->data_array;
        }
        if ($rows_per_page > 0) { $this->lastpage = ceil($this->numrows/$rows_per_page); }
        else                    { $this->lastpage = 1; }

        if ($pageno=="" OR $pageno<="1") { $pageno=1; }
        elseif ($pageno > $this->lastpage) { $pageno = $this->lastpage; }
        $this->pageno = $pageno;

        if ($rows_per_page>0) { $limit_str = "".($pageno-1)*$rows_per_page.", ".$rows_per_page; }
        else                  { $limit_str = NULL; }
        $this->log.= "// ROWS PER PAGE = '$rows_per_page'\n";
        $this->log.= "// PAGE NO.      = '$pageno'\n";
        $this->log.= "// NUM ROWS      = '$num'\n";
        if (isset($limit_str)) $this->log.= "// LIMIT           = '$limit_str'\n";

        if(empty($this->orderlist)) { $order_str = NULL; }
        else {
            $order_str = "";
            foreach ($this->orderlist as $key => $value) {
                if (strlen($order_str)>0) $order_str.=",";
                $order_str.=$key;
                if($value) $order_str.=" $value";
            }
        }

        // Obtener página 1
        $query2 = "SELECT * FROM $tmpTableName";
        $query = "SELECT * FROM ($query)";
        if (isset($order_str[0])) { $query2 .= " ORDER BY $order_str"; $query.=" ORDER BY $order_str"; }
        if (isset($limit_str[0])) { $query2 .= " LIMIT $limit_str"; $query.=" LIMIT $limit_str"; }
        $this->log .= "// Qry2: $query2\n";
        $result2 = DBi::query($query2);
        $affectedRows2 = DBi::$affected_rows;
        if ($result2) {
            $fetchInfo = $result2->fetch_fields();
            $this->fetch_headers = [];
            foreach ($fetchInfo as $fval) {
                if (!isset($this->fieldlist[$fval->name]) || !isset($this->fieldlist[$fval->name]["auto"]))
                    $this->fetch_headers[] = $fval->name;
            }

            // Convert result into an associative array
            while ($row = $result2->fetch_assoc()) {
                $this->data_array[] = $row;
            }
        
            $this->log .= "// END getData result. data length: ".count($this->data_array).", affected rows: ".$affectedRows2."/".$affectedRows0." | ".($rows_per_page>0?$rows_per_page:$this->numrows)."/".$this->numrows."\n";
            // release result
            $result2->close();
        }
        // OPCIONAL: eliminar antes del cierre, aunque no es obligatorio
        DBi::query("DROP TEMPORARY TABLE IF EXISTS $tmpTableName");
        $this->log .= "// Qry3: DROP TEMPORARY TABLE IF EXISTS $tmpTableName\n";
        //$mysqli->close(); // Aquí también se destruye si no hiciste DROP explícito            
        return $this->data_array;
    }
    function getData($where_str=false, $_num_rows_preset=0, $fieldNames="*", $extraFrom="", $group_str="", $having_str="") {
        if (empty($fieldNames)) $fieldNames="*";
        else if (is_array($fieldNames)) $fieldNames=implode(",",$fieldNames);
        $this->log .= "// INI getData ($where_str, # $_num_rows_preset, SL ($fieldNames), FT '$extraFrom', GB \"$group_str\", HV \"$having_str\" )\n";
        // local variables
        //doclog("DBObject::getData","test",["tablename"=>$this->tablename,"classname"=>get_class($this)]);
        $this->data_array = array();
        if (empty($this->pageno)) $this->pageno=0;
        $pageno           = $this->pageno;
        $rows_per_page    = $this->rows_per_page;
        $this->numrows    = (int)$_num_rows_preset;
        $this->lastpage   = 0;
        $this->log.= "// ROWS PER PAGE 0 = $rows_per_page\n";
        $this->log.= "// PAGE NO.      0 = $pageno\n";
        $this->log.= "// NUM ROWS      0 = $pageno\n";
        
        global $query;

        if ($this->numrows<=0 && ($this->exists($where_str, $extraFrom, $group_str) === false)) {
            $this->log .= "// END getData result NOT EXISTS\n";
            return $this->data_array;
        }
        
        // Exit if no data available
        if ($this->numrows <= 0) {
            $this->pageno = 0;
            $this->log .= "// END getData result empty\n";
            return $this->data_array;
        }
        
        // Calculate how many pages it will take based on $rows_per_page
        if ($rows_per_page > 0) { $this->lastpage = ceil($this->numrows/$rows_per_page); }
        else                    { $this->lastpage = 1; }
        
        // Ensure the requested page number is within range. Default start is at page 1.
        if ($pageno == "" OR $pageno <= "1") { $pageno = 1; }
        else if ($pageno > $this->lastpage)  { $pageno = $this->lastpage; }
        $this->pageno = $pageno;
        
        // Construct LIMIT clause
        if ($rows_per_page>0) { $limit_str = "" . ($pageno - 1) * $rows_per_page . ", " . $rows_per_page; }
        else if ($_num_rows_preset>0) { $limit_str = "$_num_rows_preset"; }
        else                  { $limit_str = NULL; }
        $this->log.= "// ROWS PER PAGE 1 = $rows_per_page\n";
        $this->log.= "// PAGE NO.      1 = $pageno\n";
        $this->log.= "// NUM ROWS      1 = $pageno\n";
        if (isset($limit_str)) $this->log.= "// LIMIT           = '$limit_str'\n";

        if(empty($this->orderlist)) { $order_str = NULL; }
        else {
            $order_str = "";
            foreach ($this->orderlist as $key => $value) {
                if (strlen($order_str)>0) $order_str.=",";
                $order_str.=$key;
                if($value) $order_str.=" $value";
            }
        }
        
        // Build query string and run it
        $query = "SELECT $fieldNames FROM $this->tablename";
        if (isset($extraFrom[0])) $query .= " $extraFrom"; // alias1 inner join otherTable alias2 on alias1.key=alias2.fKey
        if (isset($where_str[0])) $query .= " WHERE $where_str";
        if (isset($group_str[0])) $query .= " GROUP BY $group_str";
        if (isset($having_str[0])) $query .= " HAVING $having_str";
        if (isset($order_str[0])) $query .= " ORDER BY $order_str";
        if (isset($limit_str[0])) $query .= " LIMIT $limit_str";

        // $query = "SELECT $select_str FROM $from_str $where_str $group_str $having_str $order_str $limit_str";
        $this->log .= "// Qry: $query\n";
        $result = DBi::query($query);// or trigger_error("SQL", E_USER_ERROR);
        $this->log .= DBi::get_info();
        $this->affectedrows = DBi::$affected_rows;
        
        // Obtain column names matching fieldlist values with no attribute auto
        if ($result) {
            $fetchInfo = $result->fetch_fields();
            $this->fetch_headers = [];
            foreach ($fetchInfo as $fval) {
                if (!isset($this->fieldlist[$fval->name]) || !isset($this->fieldlist[$fval->name]["auto"]))
                    $this->fetch_headers[] = $fval->name;
            }

            // Convert result into an associative array
            while ($row = $result->fetch_assoc()) {
                $this->data_array[] = $row;
            }
        
            $this->log .= "// END getData result. affected rows: ".$this->numrows."\n";
            // release result
            $result->close();
        }
        return $this->data_array;
    }
    function getDataByFieldArray($fieldarray, $_num_rows_preset=0, $fieldNames="*") {
        DBi::clearErrors();
        $this->log .= "// INI getDataByFieldArray (".json_encode($fieldarray).")\n";
        $this->log .= "//  Valid  Field  List   : (".json_encode($this->fieldlist).")\n";
        if ($fieldarray===null || ( !is_array($fieldarray)
            && !($fieldarray instanceof Traversable)
            && !($fieldarray instanceof Iterator)
            && !($fieldarray instanceof IteratorAggregate))) {
            $this->log .= "// Invalid array list\n";
            return false;
        }
        $where = "";
        global $query_b;
        $query_b = "";
        foreach($fieldarray as $item => $value) if(in_array($item, $this->fieldlist)) {
            $where .= $this->getWhereCondition($item, $value);
            $query_b .= $this->getWhereCondition_b($item, $value);
        }
        if (isset($where[0])) $where = rtrim($where, " AND ");
        if (isset($query_b[0])) {
            $query_b = "SELECT $fieldNames FROM $this->tablename WHERE ".rtrim($query_b, " AND ");
        }
        return $this->getData($where, $_num_rows_preset, $fieldNames);
    }
    function saveRecord ($fieldarray, $saveType=0, $toWhere=[]) {
        DBi::clearErrors();
        $this->log .= "// INI saveRecord\n";
        $this->log .= "// fieldarray\n".arr2str($fieldarray, " //", "");
        $fieldlist = $this->fieldlist;
        $this->log .= "// fieldlist\n".arr2str($fieldlist, " //", "");
        $where = "";
        $second = "";
        $additional = "";
        $addList=[];
        if ($fieldarray===null || ( !is_array($fieldarray)
            && !($fieldarray instanceof Traversable)
            && !($fieldarray instanceof Iterator)
            && !($fieldarray instanceof IteratorAggregate))) {
            $this->log .= "// Invalid array list\n";
            return false;
        }
        foreach ($fieldarray as $item => $value) {
            if (isset($fieldlist[$item]["pkey"]) && !empty($value)) {
                $where .= $this->getWhereCondition($item, $value);
                $addList[]=$item;
            }
            if (isset($fieldlist[$item]["skey"]) && !empty($value)) {
                $second .= $this->getWhereCondition($item, $value);
                $addList[]=$item;
            }
            if (!isset($fieldlist[$item]["pkey"]) && !isset($fieldlist[$item]["skey"])) {
                if (is_array($value) || in_array($item, $toWhere)) {
                    // ToDo: Verificar funcionalidad, (No está contemplado aún para insert, en ese caso no aceptar)
                    //       Pensado para Recuperar Solicitudes Canceladas: consultas/Facturas:3385 => $tokObj->saveRecord("refId"=>$solId, "modulo"=>["autorizaPago","rechazaPago"],"status"=>"activo","usos"=>null);
                    // Update tokens set status="activo", usos=null where refId=$solId and modulo in ("autorizaPago","rechazaPago");
                    $additional.=$this->getWhereCondition($item, $value);
                    $addList[]=$item;
                } else if (is_object($value)) {
                    // ToDo: if is_object ignore DBExpression
                    ;
                }
            }
        }
        $this->log .= "// where: ".$where."\n";
        $this->log .= "// second: ".$second."\n";
        $this->log .= "// additional: ".$additional."\n";
        if (isset($second[0])) $second = rtrim($second, " AND ");
        if (isset($where[0])) {
            $where = rtrim($where, " AND ");
        } else if (isset($second[0])) {
            $where = $second;
        }
        if (isset($additional[0])) {
            $additional=rtrim($additional, " AND ");
            if (isset($where[0])) {
                $where.=" AND ".$additional;
            } else $where=$additional;
        } else $addList=[];
        $returnValue = false;

        if (empty($where) || !$this->exists($where)) {
            if ($saveType != $this->ONLY_UPDATE) {
                //flog("SaveRecord: INSERT","debug");
                $returnValue = $this->insertRecord($fieldarray);
                //flog("ERRORS: ".DBi::$errno.": ".DBi::$error,"debug");
            } else
                $this->log .= "// IS NEW BUT ONLY UPDATE FLAG IS ON!\n";
        } else {
            if ($saveType != $this->ONLY_INSERT) {
                //flog("SaveRecord: UPDATE","debug");
                $returnValue = $this->updateRecord($fieldarray,$addList);
                //flog("ERRORS: ".DBi::$errno.": ".DBi::$error,"debug");
            } else
                $this->log .= "// EXISTS BUT ONLY INSERT FLAG IS ON!\n";
        }
        if (empty($returnValue)) {
            if (!empty(DBi::$errors)) {
                $this->log .= "// ERRORS: \n";
                foreach(DBi::$errors as $sErn=>$sErr) $this->log .= "   //   - ".$sErn." : ".$sErr."\n";
            } else if(!empty($this->errors)) {
                $this->log .= "// ERRORS: \n";
                foreach($this->errors as $error) $this->log .= "   //   - $error\n";
            } else {
                $this->log .= "// ERROR: ".DBi::getErrno()." : ".DBi::getError()."\n";
            }
            $this->log .= "// RETURN VALUE: ".($returnValue===false?"FAILURE!":"EMPTY")."\n";
        } else {
            $this->log .= "// RETURN VALUE: ".($returnValue===true?"SUCCESS!":$returnValue)."\n";
        }
        $this->log .= "// END saveRecord\n";
        return $returnValue;
    }

    // This function considers the usage of the $_POST array as the input of the function
    function insertRecord ($fieldarray) {
        $this->log .= "// INI insertRecord\n";
        // Initialize array of potential error messages
        // $this->errors = array();
        
        global $query;

        // Filter out items which do not belong in table from $_PUSH values like submit button
        $fieldlist = $this->fieldlist;
        if ($fieldarray===null || ( !is_array($fieldarray)
            && !($fieldarray instanceof Traversable)
            && !($fieldarray instanceof Iterator)
            && !($fieldarray instanceof IteratorAggregate))) {
            $this->log .= "// Invalid array list\n";
            return false;
        }
        foreach ($fieldarray as $field => $fieldvalue) {
            if (!in_array($field, $fieldlist)) {
                unset ($fieldarray[$field]);
            }
        }
        if (empty($fieldarray)) {
            $this->errors[] = "No hay datos v&aacute;lidos a insertar.";
            return false;
        }
        
        // Construct query string to insert new record // INSERT INTO tbl_name (a,b,c) VALUES(1,2,3),(4,5,6),(7,8,9);
        $query = "INSERT INTO $this->tablename SET ";
        foreach ($fieldarray as $item => $value) {
            if (is_array($value)) {
                if (isset($value[0]) && !isset($value[1])) {
                    doclog("real_escape_string expects string, array given","warning",["item"=>$item,"value"=>$value,"trace"=>debug_backtrace()]);
                    $value=$value[0];
                    // ToDo: Debería crear varios inserts con cada elemento del arreglo, pero hay que contemplar que pasa si hay más de un arreglo...
                    //       Se debe suponer que son arreglos numéricos, si hay más de uno se supondrá que tienen el mismo tamaño
                    //       
                }
            }
            if (!isset($fieldlist[$item]["auto"])) {
                if (is_object($value) && get_class($value)=="DBExpression") {
                    $this->log .= "// DBEXPRESSION IN INSERT '$value'\n";
                    $valval = $value->value;
                    if (strpos($valval, $item)!==false) {
                        $value=str_replace($item, "0", $valval);
                        $this->log .= "// DBEXPRESSION FIX: '$value'\n";
                    }
                    $value = DBi::real_escape_string($value);
                    $query .= "$item=$value, ";
                } else {
                    $value = DBi::real_escape_string($value);
                    $query .= "$item='$value', ";
                }
            } $this->log .= "// FIELD $item is auto\n";
        }
        $query = rtrim($query, ", ");
        
        // Execute query
        $this->log .= "// Qry: $query\n";
        if ($result = DBi::query($query,$this)) {
            $this->log .= DBi::get_info();
            if (is_numeric($result)) {
                $this->lastId = $result;
                $this->log.="// Last id = ".$this->lastId."\n";
            } else { //if (is_bool($result)) {
                $this->lastId = false; //DBi::$insert_id;
                doclog("SIN LAST ID: result:'".(is_bool($result)?($result?"TRUE":"FALSE"):$result)."', qry:$query");
            }
            $this->affectedrows = DBi::$affected_rows;
            if (mysqli_errno(DBi::$conn) <> 0) {
                $this->errors[] = DBi::getErrno() . " : " . DBi::getError();
                if (mysqli_errno(DBi::$conn) == 1062) {
                    $this->errors[] = "Ya existe un registro con este ID.";
                }
                $this->log .= "// END insertRecord ERROR\n";
                if (is_object($result)) $result->close();
                return false; // trigger_error("SQL", E_USER_ERROR);
            }
            $this->log .= "// END insertRecord\n";
            if (is_object($result)) $result->close();
            if (is_scalar($result)) return $result;
            return true;
        }
        return false; // trigger_error("SQL", E_USER_ERROR);
    }
    // $kind es SET, WHERE o null
    function getQueryExpression($item, $value, $kind=null) {
        if (empty($item)) {
            $this->log.="// QryExp empty item: '$item'\n";
            return "";
        }
        $this->log.="// QryExp $kind: '$item' = (".gettype($value).")".json_encode($value)."\n";
        if ($kind==="WHERE") $separator = " AND ";
        else if ($kind==="SET") $separator = ", ";
        else $separator="";
        $expression = "";
        if (empty($value)) {
            if ($value==='0' || $value===0 || $value===0.0) {
                $expression .= $item."='0'$separator";
            } else if (is_bool($value)) {
                $expression .= $item."='0'$separator";
            } else if ($value === NULL) {
                if ($kind==="SET") $expression .= $item."=NULL$separator";
                else               $expression .= $item." IS NULL$separator";
            } else {
                doclog("EMPTY VALUE",false,["item"=>$item, "value"=>$value, "valtype"=>gettype($value)]);
                $expression .= $item."=''$separator";
            }
        } else if (is_array($value)) { // SOLO PARA kind==WHERE
            if ($kind==="SET") return "";
            if (!isset($value[1])) return $this->getQueryExpression($item, $value[0]??null, $kind);
            array_walk($value, function(&$val, $idx) { $val = DBi::real_escape_string($val); });
            $totext = implode("','",$value);
            $expression .= "$item IN ('$totext')$separator";
        } else if (is_object($value) && get_class($value)=="DBExpression") {
            doclog("DBEXPRESSION",false,["expValue"=>$value->value,"expOp"=>$value->op,"kind"=>$kind,"item"=>$item]);
            $op = "=";
            if (!empty($value->op)) $op = $value->op;
            if ($kind==="SET" && $op!=="=") $expression="";
            else if ($kind==="WHERE" && $op==="REPLACE") {
                $valval = $value->value;
                $expression = $valval.$separator;
            } else {
                $valval = $value->value;
                $expression .= $item.$op.$valval.$separator;
            }
        } else if (is_string($value)) {
            $value = DBi::real_escape_string($value);
            if (strpos($value,"%")!==false) { // if (substr($value,0,1)==='%' || substr($value,-1)==='%') {
                if ($kind==="SET") {
                    //$expression=""; // conservar expresion, el % será parte del texto
                    $expression = "$item='$value'$separator";
                } else
                    $expression = "$item LIKE '$value'$separator";
            }
            else
                $expression = "$item='$value'$separator";
        } else {
            if (is_bool($value)) $value=1;
            $expression .= "$item='$value'$separator";
        }
        return $expression;
    }
    function getQueryExpression_b($item, $value, $kind=null) {
        if (empty($item)) return "";
        if ($kind==="WHERE"||$kind==="AND") $separator = " AND ";
        else if ($kind==="SET") $separator = ", ";
        else if ($kind==="OR") $separator = " OR ";
        else $separator="";
        $expression = "";
        if (empty($value)) {
            if ($value==='0' || $value===0 || $value===0.0)
                return $item."='0'$separator";
            if (is_bool($value))
                return $item."='".($value?"1":"0")."'$separator";
            if ($value === NULL) {
                if ($kind==="SET") return $item."=NULL$separator";
                return $item." IS NULL$separator";
            }
            doclog("EMPTY VALUE",false,["item"=>$item, "value"=>$value, "valtype"=>gettype($value)]);
            return $item."=''$separator";
        }
        if (is_array($value)) { // SOLO PARA kind==WHERE
            if ($kind==="SET") return "";
            /*
            if (!isset($value[0])) {
                if (isset($value["OP"]) && isset($value["VALUE"])) $value["VALUE"]=new DBExpression($value["VALUE"],$value["OP"])
                if ((isset($value["OR"])||isset($value["AND"]))&&())
                if (isset($value["ITEM"]) && isset($value["VALUE"]))
                    $rv = $this->getQueryExpression_b($value["ITEM"],$value["VALUE"],$kind);
                else if (isset($value["OR"]))
                    $rv = $this->getQueryExpression_b($item,$value["OR"],"OR");
                else if (isset($value["AND"]))
                    $rv = $this->getQueryExpression_b($item,$value["AND"],"AND");
                else return "";
            } else if (!isset($value[1]))
                return $this->getQueryExpression_b($item,$value[0],$kind);
            else if (($item==="AND"||$item==="OR")) {
                // toDo: sin resolver, debería ser válido solo cuando $value es un arreglo de objetos [item,value(,op)]
                if ($item!==$kind)
                    return "(".$this->getQueryExpression_b($item,$value,$item).")";
                return "";
            }
            */
            array_walk($value, function(&$val, $idx) { $val = DBi::real_escape_string($val); });
            $totext = implode("','",$value);
            return "$item IN ('$totext')$separator";
        }
        if (is_object($value) && get_class($value)=="DBExpression") {
            doclog("DBEXPRESSION",false,["expValue"=>$value->value,"expOp"=>$value->op,"kind"=>$kind,"item"=>$item]);
            $op = "=";
            if (!empty($value->op)) $op = $value->op;
            if ($kind==="SET" && $op!=="=") return "";
            if ($op==="REPLACE" && isset($separator[0]))
                return $value->value.$separator;
            return $item.$op.$value->value.$separator;
        }
        if (is_string($value)) {
            $value = DBi::real_escape_string($value);
            if (substr($value,0,1)==='%' || substr($value,-1)==='%') {
                if ($kind==="SET") return "";
                return "$item LIKE '$value'$separator";
            }
        } else if (is_bool($value)) $value=1;
        return "$item='$value'$separator";
    }
    function getSetExpression($item, $value) {
        return $this->getQueryExpression($item, $value, "SET");
    }
    function getSetExpression_b($item, $value) {
        return $this->getQueryExpression_b($item, $value, "SET");
    }
    function getWhereCondition($item, $value) {
        return $this->getQueryExpression($item, $value, "WHERE");
    }
    function getWhereCondition_b($item, $value) {
        return $this->getQueryExpression_b($item, $value, "WHERE");
    }
    function updateValue ($fieldName, $oldValue, $newValue, $additionalWhere) {
        $this->log .= "// INI updateValue $fieldName, $oldValue, $newValue, $additionalWhere \n";
        global $query, $query_b;
        
        $update = $this->getSetExpression($fieldName, $newValue);
        $update_b = $this->getSetExpression_b($fieldName, $newValue);
        if (isset($oldValue)) {
            $where = $this->getWhereCondition($fieldName, $oldValue);
            $where_b = $this->getWhereCondition_b($fieldName, $oldValue);
        } else {
            $where = "";
            $where_b = "";
        }
        if ($update) $update = rtrim($update, ", ");
        if ($update_b) $update_b = rtrim($update_b, ", ");

        if (!empty($additionalWhere)) {
            $where .= (empty($where)?"":" ").$additionalWhere;
            $where_b .= (empty($where_b)?"":" ").$additionalWhere;
        } else {
            if (!empty($where))  $where  = rtrim($where, " AND ");
            if (!empty($where_b))  $where_b  = rtrim($where_b, " AND ");
        }

        // Execute query
        $query = "UPDATE $this->tablename SET $update WHERE $where";
        $this->log .= "// Qry: $query\n";
        $result = DBi::query($query,$this);
        $this->log .= DBi::get_info();
        $this->affectedrows = DBi::$affected_rows;

        if (is_object($result)) $result->close();

        if ($this->affectedrows<=0) {
            $this->log .= "// END updateValue ZERO/FALSE\n";
            return false;
        }
        if (is_numeric($result)) {
            $this->lastId = $result;
            $this->log.="// Last id = ".$this->lastId."\n";
        } else if (isset($fieldarray["id"])) {
            $this->lastId = $fieldarray["id"];
        } else if ((empty($update) && !empty($where)) || $this->affectedrows == 1) {
            $tmpQry = $query;
            $tempId = $this->getValue (false, false, 'id', $where);
            if (strlen($tempId)>0) {
                if (strpos($tempId, "|") === false) $this->lastId = $tempId;
                else $this->lastId = explode("|", $tempId)[0];
            }
            $query=$tmpQry;
        }
        $this->log .= "// Last id = ".$this->lastId."\n";
        $this->log .= "// END updateValue\n";
        return true;
    }
    function updateRecord ($fieldarray,$whereList=[]) {
        $this->log .= "// INI updateRecord arr:".json_encode($fieldarray).(isset($whereList[0])?", whL:".json_encode($whereList):"")."\n";
        global $query, $query_b;
        $fieldlist = array_map("strtolower", array_filter(array_values($this->fieldlist), "is_string"));
        $this->log .= "// FieldList check: ".implode(",", $fieldlist)."\n";
        if ($fieldarray===null || ( !is_array($fieldarray)
            && !($fieldarray instanceof Traversable)
            && !($fieldarray instanceof Iterator)
            && !($fieldarray instanceof IteratorAggregate))) {
            $this->log .= "// Invalid array list\n";
            $this->errors[] = "Lista de datos inválida";
            return false;
        }
        foreach ($fieldarray as $field => $fieldvalue) {
            if (!in_array(strtolower($field), $fieldlist)) {
                unset ($fieldarray[$field]);
            }
        }
        $this->log .= "// FieldArray keys result: ".implode(",",array_keys($fieldarray))."\n";
        $where = "";
        $update = "";
        $secondSet = "";
        $secondWhere = "";
        $where_b = "";
        $update_b = "";
        $secondSet_b = "";
        $secondWhere_b = "";
        /* // Buscar todas las instancias donde se ocupa updateRecord que no se ocupe $whereList ["item"=>null] o ["item"=>false] o ["item"=>0]
        // ToDo: Mas bien, cambiar uso de $whereList para contener explicitamente lo que se desea comparar y que en fieldarray se encuentre lo que se desea modificar, aunque si es primary key (o si es secondary key y no se incluye primary key) se manda a whereList
        $hasPresetWhereList1 = false;
        if (is_array($whereList)) foreach ($whereList as $item => $value) {
            if (!is_numeric($item) && in_array(strtolower($field), $fieldlist) && !isset($fieldarray[$item])) {
                $where .= $this->getWhereCondition($item, $value);
                $where_b .= $this->getWhereCondition_b($item, $value);
                unset($whereList[$item]);
                $hasPresetWhereList1=true;
            }
        }
        */
        $hasPresetWhereList = is_array($whereList) && isset($whereList[0][0]);
        foreach ($fieldarray as $item => $value) {
            if ($hasPresetWhereList) {
                if (isset($whereList[$item])) {
                    $update .= $this->getSetExpression($item, $value);
                    $where .= $this->getWhereCondition($item,$whereList[$item]);
                    $update_b .= $this->getSetExpression_b($item, $value);
                    $where_b .= $this->getWhereCondition_b($item,$whereList[$item]);
                    $this->log .= "// is preset key in whereList: '$item'\n";
                } else if (in_array($item, $whereList)) {
                    $where .= $this->getWhereCondition($item, $value);
                    $where_b .= $this->getWhereCondition_b($item, $value);
                    $this->log .= "// is preset value in whereList: '$item'\n";
                } else {
                    $update .= $this->getSetExpression($item, $value);
                    $update_b .= $this->getSetExpression_b($item, $value);
                    $this->log .= "// is preset not in whereList: '$item'\n";
                }
            } else {
                $isKey = false;
                if (isset($this->fieldlist[$item]["pkey"])) {
                    //$this->log .= "//       is PKey\n";
                    $where .= $this->getWhereCondition($item, $value);
                    $where_b .= $this->getWhereCondition_b($item, $value);
                    $isKey = true;
                    $this->log .= "// is pk field: '$item'\n";
                }
                if (isset($this->fieldlist[$item]["skey"])) {
                    //$this->log .= "//       is SKey\n";
                    $secondSet .= $this->getSetExpression($item, $value);
                    $secondWhere .= $this->getWhereCondition($item, $value);
                    $secondSet_b .= $this->getSetExpression_b($item, $value);
                    $secondWhere_b .= $this->getWhereCondition_b($item, $value);
                    $isKey = true;
                    $this->log .= "// is sk field: '$item'\n";
                }
                if (!$isKey && !isset($this->fieldlist[$item]["auto"])) {
                    //$this->log .= "//       is FieldToUpdate\n";
                    $update .= $this->getSetExpression($item, $value);
                    $update_b .= $this->getSetExpression_b($item, $value);

                    $this->log .= "// not key not auto: '$item'='$value' => '$update'\n";
                } else if (!$isKey) {
                    $this->log.="// not key, but is auto: '$item' ".array_keys($this->fieldlist[$item])."\n";
                }
            }
        }
        $this->log.="\\ where is '$where'\n";
        $this->log.="\\ update is '$update'\n";
        $this->log.="\\ secondSet is '$secondSet'\n";
        $this->log.="\\ secondWhere is '$secondWhere'\n";

        if ($hasPresetWhereList && !isset($where[0])) {
            $this->errors[] = "Las condiciones predefinidas no son v&aacute;lidas";
            return false;
        }
        if ($where) {
            $where = rtrim($where, " AND ");
            $update .= $secondSet;
        } else if ($secondWhere) {
            $where = rtrim($secondWhere, " AND ");
        }
        $update = rtrim($update, ", ");
        $this->log.="// trimmed update: '$update'\n";
        if ($where_b) {
            $where_b = rtrim($where_b, " AND ");
            $update_b .= $secondSet_b;
        } else if ($secondWhere_b) {
            $where_b = rtrim($secondWhere_b, " AND ");
        }
        $update_b = rtrim($update_b, ", ");

        if (empty($update)) {
            $this->errors[] = "No hay datos v&aacute;lidos a actualizar";
            return false;
        }

        // Execute query
        $query = "UPDATE $this->tablename SET $update WHERE $where";
        $query_b = "UPDATE $this->tablename SET $update_b WHERE $where_b";
        $this->log .= "// Qry: $query\n// Class: ".get_class($this)."\n";
        if (hasUser()) $this->log .= "// USER: ".getUser()->nombre."\n";
        else $this->log .= "// NO USER\n";
        $result = DBi::query($query,$this);
        //doclog("QUERY: $query","log");
        $this->log .= DBi::get_info();
        $this->affectedrows = DBi::$affected_rows;
        //doclog("Result: ".(is_bool($result)?($result?"TRUE":"FALSE"):$result),"log");
        //doclog("Affected Rows: $this->affectedrows","log");

        if (is_object($result)) $result->close();

        if ($this->affectedrows>0) {
            if (is_numeric($result)) {
                $this->lastId = $result;
            } else if (isset($fieldarray["id"])) {
                $this->lastId = $fieldarray["id"];
            } else if ((empty($update) && !empty($where)) || $this->affectedrows == 1) {
                $tmpQry = $query;
                $tmpQry_b = $query_b;
                $tmpErn = DBi::$errno;
                $tmpErr = DBi::$error;
                $tempId = $this->getValue (false, false, 'id', $where);
                if (isset($tempId[0])) {
                    if (strpos($tempId, "|") === false) $this->lastId = $tempId;
                    else $this->lastId = explode("|", $tempId)[0];
                }
                $query = $tmpQry;
                $query_b = $tmpQry_b;
                DBi::$errno = $tmpErn;
                DBi::$error = $tmpErr;
            }
            $this->log .= "// Last id = ".(is_array($this->lastId)?json_encode($this->lastId):$this->lastId)."\n";
            $this->log .= "// END updateRecord\n";
            return true;
        }
        $this->log .= "// END updateRecord. Result is FALSE\n";
        return false; // trigger_error("SQL", E_USER_ERROR);
    }

    // This function inserts multiple records ($columns = ["a","b","c"], $values = [ ["a1","b1","c1"], ["a2","b2","c2"], ["a3","b3","c3"] ] ]
    // Generates the query: INSERT INTO $this->tablename ("a", "b", "c") VALUES ("a1", "b1", "c1"),("a2", "b2", "c2"),("a3", "b3", "c3");
    // It is expected values elements are arrays of the same size and corresponding type of columns array
    // It is expected result will not fail because of duplicated values
    // This function is not prepared for usage with $_POST array, parameters must be worked in before calling function

    function insertMultipleRecords ($columns, $valuesArray, $additionalSql=false) {
        $this->log .= "// INI insertMultipleRecords(".json_encode($columns).", ".json_encode($valuesArray).")\n";
        // $this->errors = array(); // Initialize array of potential error messages
        global $query;
        if (empty($valuesArray)) { $this->errors[] = "No hay datos v&aacute;lidos a insertar."; return false; }
        $query = "INSERT INTO $this->tablename";
        if (!empty($columns)) {
            $query .= " (";
            for ($i=0; $i<count($columns); $i++) {
                if ($i>0) $query.=",";
                $query.=$columns[$i];
            }
            $query .= ")";
        }
        $query.=" VALUES ";
        for ($i=0; $i<count($valuesArray); $i++) {
            if ($i>0) $query.=",";
            $query.="(";
            $this->log .= "Processing Values[$i]: ".json_encode($valuesArray[$i])."\n";
            for ($j=0; $j<count($valuesArray[$i]); $j++) {	
                if ($j>0) $query.=",";
                $imr_val = $valuesArray[$i][$j];
                $this->log .= "Processing Values[$i][$j]: ".($imr_val===false?$columns[$j]:$imr_val)."\n";
                if ($imr_val === false) $query.=$columns[$j];
                else if ($imr_val===null) $query.="NULL";
                else if (!is_object($imr_val) ) $query.="'".str_replace("'","''",$imr_val)."'";
                else if (get_class($imr_val)=="DBName") $query.=$imr_val->name;
                else $query.="'".$imr_val."'";
            }
            $query.=")";
        }
        if ($additionalSql) $query.=" ".$additionalSql;
        $this->log .= "// Qry: $query\n";
        if ($result = DBi::query($query,$this)) {
            $this->log .= DBi::get_info();
            if (is_numeric($result)) $this->lastId=$result;
            else $this->lastId = DBi::$insert_id;
            $this->affectedrows = DBi::$affected_rows;
            if (mysqli_errno(DBi::$conn) <> 0) {
                if (mysqli_errno(DBi::$conn) == 1062) {
                    $this->errors[] = "Ya existe un registro con este ID.";
                } else {
                    $this->errors[] = DBi::$conn->error;
                    // return false; // trigger_error("SQL", E_USER_ERROR);
                }
                $this->log .= "// END insertMultipleRecords ERROR\n";
            } else $this->log .= "// END insertMultipleRecords\n";
        
            if (is_object($result)) $result->close();
            
            return $this->returnWithErrors(true);
/*
            if (empty($this->errors)) return true;
            $this->returnWithErrors(false);
            return false;
*/
        } else {
            $qryError = DBi::getError();
            $this->log .= "Error(F) ".DBi::getErrno().": ".$qryError."\n";
            $this->log .= "Error(V) ".DBi::$errno.": ".DBi::$error."\n";
            //$qryErrno = DBi::$conn->errno;
            //$qryError = DBi::$conn->error;
            $this->errors[] = $qryError;
            //$this->log .= "Error $qryErrno: $qryError\n";
        }
        return false; // trigger_error("SQL", E_USER_ERROR);
    }
    function updateMultipleRecordSet($columns, $valuesBlock, $ignoreErrors=false) {
        $this->log .= "// INI updateMultipleRecordSet ( columns:".json_encode($columns)." , values:".json_encode($columns)." )\n";
        $oneGood=false;
        if ($valuesBlock===null || ( !is_array($valuesBlock)
            && !($valuesBlock instanceof Traversable)
            && !($valuesBlock instanceof Iterator)
            && !($valuesBlock instanceof IteratorAggregate))) {
            $this->log .= "// Invalid valuesBlock list\n";
            return false;
        }
        foreach($valuesBlock as $valuesArray) {
            $result = $this->updateRecord(array_combine($columns, $valuesArray));
            if (is_numeric($result)) $this->lastId=$result;
            else $this->lastId = DBi::$insert_id;
            if (!$ignoreErrors && !$result) {
                $this->log .= "// END updateMultipleRecordSet. Al Primer Error Truena.";
                return false;
            }
            $oneGood=($oneGood||$result);
        }
        $this->log .= "// END updateMultipleRecordSet oneGood is ".($oneGood?"TRUE":"FALSE");
        return $oneGood;
    }
    function updateMultipleRecords($dataList, $ignoreErrors=false) {
        $this->log .= "// INI updateMultipleRecords ( dataList: ".json_encode($dataList)." )\n";
        $oneGood=false;
        if ($dataList===null || ( !is_array($dataList)
            && !($dataList instanceof Traversable)
            && !($dataList instanceof Iterator)
            && !($dataList instanceof IteratorAggregate))) {
            $this->log .= "// Invalid valuesBlock list\n";
            return false;
        }
        foreach($dataList as $dataRow) {
            $result = $this->updateRecord($dataRow);
            if (is_numeric($result)) $this->lastId=$result;
            else $this->lastId = DBi::$insert_id;
            if (!$ignoreErrors && !$result) {
                $this->log .= "// END updateMultipleRecords. Al Primer Error Truena.";
                return false;
            }
            $oneGood=($oneGood||$result);
        }
        $this->log .= "// END updateMultipleRecords oneGood is ".($oneGood?"TRUE":"FALSE");
        return $oneGood;
    }
    function deleteRecord ($fieldarray) {
        $this->log .= "// INI deleteRecord ".arr2str($fieldarray)."\n";
        global $query, $query_b;
        $fieldlist = $this->fieldlist;
        $where = "";
        $where_b = "";
        if ($fieldarray===null || ( !is_array($fieldarray)
            && !($fieldarray instanceof Traversable)
            && !($fieldarray instanceof Iterator)
            && !($fieldarray instanceof IteratorAggregate))) {
            $this->log .= "// Invalid array list\n";
            return false;
        }
        foreach ($fieldarray as $item => $value) {
            $where .= $this->getWhereCondition($item, $value);
            $where_b .= $this->getWhereCondition_b($item, $value);
        }
        if (isset($where[0])) $where = rtrim($where, " AND ");
        if (isset($where_b[0])) $where_b = rtrim($where_b, " AND ");
        $this->log .= "// where: ".$where."\n";
        if (empty($where)) {
            $this->errors[] = "No hay datos v&aacute;lidos a borrar.";
            return $this->returnWithErrors(false);
        }
        
        $query = "DELETE FROM $this->tablename WHERE $where";
        $query_b = "DELETE FROM $this->tablename WHERE $where_b";
        $this->log .= "// Qry: $query\n";
        if ($result = DBi::query($query,$this)) {
            $this->log .= DBi::get_info();
            $this->affectedrows = DBi::$affected_rows;
            if (is_object($result)) $result->close();
            $this->log .= "// END deleteRecord\n";
            return $this->returnWithErrors(true);
        }
        $this->log .= "// END\n";
        return $this->returnWithErrors(false); // trigger_error("SQL", E_USER_ERROR);
    }

    // This function inserts multiple records ($columns = ["a","b","c"], $values = [ ["a1","b1","c1"], ["a2","b2","c2"], ["a3","b3","c3"] ] ]
    // Generates the query: INSERT INTO $this->tablename ("a", "b", "c") VALUES ("a1", "b1", "c1"),("a2", "b2", "c2"),("a3", "b3", "c3");
    // It is expected values elements are arrays of the same size and corresponding type of columns array
    // It is expected result will not fail because of duplicated values
    // This function is not prepared for usage with $_POST array, parameters must be worked in before calling function

    function deleteMultipleRecords ($columns, $valuesArray) {
        $this->log .= "// INI deleteMultipleRecords\n";
        // Initialize array of potential error messages
        // $this->errors = array();
        
        global $query;

        if (empty($columns) || empty($valuesArray)) {
            $this->errors[] = "No hay datos v&aacute;lidos a borrar.";
            return false;
        }

        $query = "DELETE FROM $this->tablename";
        $query .= " WHERE (";
        for ($i=0; $i<count($columns); $i++) {
            if ($i>0) $query.=",";
            $query.=$columns[$i];
        }
        $query .= ")";
        $query.=" IN (";
        for ($i=0; $i<count($valuesArray); $i++) {
            if ($i>0) $query.=",";
            $query.="(";
            for ($j=0; $j<count($valuesArray[$i]); $j++) {
                if ($j>0) $query.=",";
                $query.="'".$valuesArray[$i][$j]."'";
            }
            $query.=")";
        }
        $query.=")";
        
        $this->log .= "// Qry: $query\n";
        if ($result = DBi::query($query,$this)) {
            $this->log .= DBi::get_info();
            $this->lastId = DBi::$insert_id;
            $this->affectedrows = DBi::$affected_rows;
            if (mysqli_errno(DBi::$conn) <> 0) {
                if (mysqli_errno(DBi::$conn) == 1062) {
                    $this->errors[] = "Ya existe un registro con este ID.";
                } else {
                    trigger_error("SQL", E_USER_ERROR);
                }
                $this->log .= "// END deleteMultipleRecords ERROR\n";
            } else $this->log .= "// END deleteMultipleRecords\n";
        
            if (is_object($result)) $result->close();
            return $this->returnWithErrors(true);
        }
        return $this->returnWithErrors(false); // trigger_error("SQL", E_USER_ERROR);
    }
    
    function returnWithErrors($returnValue) {
        if (!empty(DBi::$errors)) {
            $this->log .= "// DBiERRORS: \n";
            foreach(DBi::$errors as $sErn=>$sErr) $this->log .= "   //   - ".$sErn." : ".$sErr."\n";
            $returnValue=false;
        } else if(!empty($this->errors)) {
            $this->log .= "// ERRORS: \n";
            foreach($this->errors as $error) $this->log .= "   //   - $error\n";
            $returnValue=false;
        } else {
            $this->log .= "// ERROR: ".DBi::getErrno()." : ".DBi::getError()."\n";
        }
        $this->log .= "// RETURN VALUE: ".($returnValue===false?"FAILURE!":"EMPTY")."\n";
        return $returnValue;
    }

    function autocommit($binval) {
        DBi::autocommit($binval);
    }
    function commit() {
        DBi::commit();
    }
    function rollback() {
        DBi::rollback();
    }
    public function __toString() {
        return $this->tablename."\nLog:\n".$this->log."\n";
    }
    public function get_class() {
        static $classname = false;
        if (!$classname) $classname = get_class($this);
        return $classname;
    } // $this->get_class()
}
class DBExpression {
    var $value;
    var $op;
    function __construct() {
        $a = func_get_args();
        $i = func_num_args();
        if ($i>0) $this->value=$a[0];
        if ($i>1) $this->op=$a[1];
    }
    public function __toString() {
        return "DBExpression. value:{$this->value}, op:{$this->op}\n";
    }
}
class DBName {
    var $name;
    function __construct() {
        $n = func_num_args();
        $args = func_get_args();
        if ($n>0) $this->name=$args[0];
    }
    public function __toString() {
        return $this->name;
    }
}
