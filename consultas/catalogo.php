<?php
$preBoot=array_key_exists("_pryNm",$GLOBALS);
if (!$preBoot) 
    require_once dirname(__DIR__)."/bootstrap.php";
doCatalogoService();

if (!$preBoot && $_doDB) require_once "configuracion/finalizacion.php";
if ($_noDie) return;
die();

function doCatalogoService() {
    $dataObj = [];
    $logFull = true;
    catlog(false);
    if (isset($_POST["tablename"])) {
        if (isset($_POST["update"])) {
        } else if ($_POST["tablename"]==="LOGFILES") {
            $tablename = isset($_POST["tablename"])?$_POST["tablename"]:false;
            $tableviewname = isset($_POST["tableviewname"])?$_POST["tableviewname"]:false;
            $tableOptions = ["noHideColumnBtn","noShowColumnBtn"];
            $noCols = 10; //isset($_POST["noCols"])?+$_POST["noCols"]:50;
            $currPG = 1; //isset($_POST["currPg"])?+$_POST["currPg"]:1;
            $lastPG = 1; //isset($_POST["lastPg"])?+$_POST["lastPg"]:0;
            $sortBy = isset($_POST["sortBy"])?$_POST["sortBy"]:"";
            $filterBy = isset($_POST["filterBy"])?$_POST["filterBy"]:"";
            $filterItem = isset($_POST["filterItem"])?$_POST["filterItem"]:"";
            $lastAction = isset($_POST["lastAction"])?$_POST["lastAction"]:"";
            //if ($noCols<=0) $noCols=50;
            //if ($currPG<=0) $currPG=1;
            $columnNames = ["FECHA","USUARIO","HORA"];
            $logsPath = $_SERVER['DOCUMENT_ROOT']."LOGS";
            $logLen = strlen($logsPath);
            $filenames = array_diff(scandir($logsPath), array('.', '..'));
            rsort($filenames);
            $columnComment = ["FECHA"=>$filenames,"USUARIO"=>[],"HORA"=>[]];
            if (isset($filterBy[0])) {
                $filterList=[];
                foreach (explode("|", $filterBy) as $item) {
                    list($filterName,$filterValue)=explode("=", $item);
                    $filterList[$filterName]=$filterValue;
                }
            }
            if (isset($filterBy) && isset($filterItem[0])) {
                switch($filterItem) {
                    case "FECHA": unset($filterList["USUARIO"]);
                    case "USUARIO": unset($filterList["HORA"]);
                    case "HORA":
                }
            }
            $filterFecha=$filterList["FECHA"]??"";
            $filterUsuario=$filterList["USUARIO"]??"";
            $filterHora=$filterList["HORA"]??"";
            if (isset($filterHora[0])) {
                if($filterItem==="HORA"&&isset($filterHora[0])) {
                    if (preg_match("#^(\w{3})\[(\d+)]L(\d+)$#", $filterHora, $matches)>0)  {
                        $cmd=$matches[1];
                        $dts=$matches[2];
                        $lin=$matches[3];
                        $basePath=$logsPath."/".$filterFecha."/";
                        if ($filterUsuario==="INVOICE") {
                            switch($cmd) {
                                case "ERR": $filename=["error"]; break;
                                case "CON": $filename=["connection"]; break;
                                case "TSK": $filename=["tareaPagos"]; break;
                                case "PRT": $filename=["print"]; break;
                                case "CFD": $filename=["cfdi"]; break;
                                case "BKA": $filename=["backup_admin"]; break;
                                default: catlogn("Unknown file type: $cmd");
                            }
                        } else {
                            $isUsr=($filterUsuario!=="SIN SESION");
                            switch($cmd) {
                                case "ACT": if ($isUsr) $filename=["action_$filterUsuario","$filterUsuario_action"];
                                            else $filename=["action_logoff","_logoff_action"]; break;
                                case "RDB": if ($isUsr) $filename=["read_$filterUsuario","$filterUsuario_read"];
                                            else $filename=["read_logoff","_logoff_read"]; break;
                                case "WDB": if ($isUsr) $filename=[$filterUsuario]; else $filename=["_logoff"]; break;
                                default: catlogn("Unknown file type: $cmd");
                            }
                        }
                    } else catlogn("HORA NO MATCH: '$filterHora'");
                    if (isset($filename[0][0])) {
                        foreach ($filename as $fname) {
                            $absname=$logsPath."/".$filterFecha."/".$fname.".log";
                            $handle = @fopen($absname,"r");
                            if ($handle) {
                                $line=0;
                                $isSame=false;
                                while (!feof($handle)) {
                                    $buffer = fgets($handle,255);
                                    $line++;
                                    if (preg_match("#^\[{$dts}.+]#", $buffer)>0)  {
                                        if (!isset($resultData)) $resultData=[];
                                        $resultData[]=[$buffer];
                                        $isSame=true;
                                    } else if (preg_match("#^\[\d+.*]#", $buffer)>0)  {
                                        $isSame=false;
                                        break;
                                    } else if ($isSame) $resultData[]=[$buffer];
                                }
                                fclose($handle);
                            } else catlogn("FILE NOT FOUND: $absname");
                        }
                    }
                    $filterByFix="FECHA=$filterFecha|USUARIO=$filterUsuario|HORA=$filterHora";
                }
            }
            if (isset($filterUsuario[0])) {
                if ($filterUsuario==="INVOICE") {
                    $filePattern=$logsPath."/".$filterFecha."/*{error,connection,tareaPagos,print,cfdi,backup_admin,ftp}*.log";
                    $userFiles=glob($filePattern, GLOB_BRACE);
                } else if ($filterUsuario==="SIN SESION") {
                    $filePattern=$logsPath."/{$filterFecha}/*logoff*.log";
                    $userFiles=glob($filePattern);
                } else {
                    $filePattern=$logsPath."/{$filterFecha}/*{$filterUsuario}*.log";
                    $userFiles=glob($filePattern);
                }
                if ($filterItem==="HORA"&&isset($_POST["CURR_HORA"][0])) {
                    $columnComment["HORA"]=explode(",",$_POST["CURR_HORA"]);
                    //catlogn("CURR HORA : $_POST[CURR_HORA]");
                } else foreach ($userFiles as $idx => $filename) {
                    $absname=$filename;
                    $pathSeparatorIndex=strrpos($filename, "/");
                    if ($pathSeparatorIndex===false||$pathSeparatorIndex<0) $pathSeparatorIndex=0;
                    else $pathSeparatorIndex++;
                    $filename=substr($filename, $pathSeparatorIndex,-4);
                    if ($filterUsuario==="INVOICE") {
                        switch($filename) {
                            case "error": $filetype="ERR"; break;
                            case "connection": $filetype="CON"; break;
                            case "tareaPagos": $filetype="TSK"; break;
                            case "print": $filetype="PRT"; break;
                            case "cfdi": $filetype="CFD"; break;
                            case "backup_admin": $filetype="BKA"; break;
                            default: $filetype=mb_strtoupper(substr($filename,0,3));
                        }
                    } else if (substr($filename,0,7)==="action_"||substr($filename,-7)==="_action") $filetype="ACT";
                    else if (substr($filename,0,5)==="read_"||substr($filename,-5)==="_read") $filetype="RDB";
                    else if ($filename===$filterUsuario || $filterUsuario==="SIN SESION") $filetype="WDB";
                    else $filetype="UNK";
                    $handle = @fopen($absname,"r");
                    if ($handle) {
                        $lastMatch=null;
                        $line=0;
                        while (!feof($handle)) {
                            $buffer = fgets($handle,64);
                            $line++;
                            //if(strpos($buffer, $searchthis) !== FALSE) $matches[] = $buffer;
                            if (preg_match("#^\[(\d+).*]#", $buffer, $matches)>0)  {
                                $match=$matches[1];
                                if (isset($match[0]) && (!isset($lastMatch) || $match!==$lastMatch)) {
                                    $horaText="{$filetype}[$match]L$line";
                                    //catlogn("HORA MATCH: $horaText");
                                    $columnComment["HORA"][]="$horaText";
                                    $lastMatch=$match;
                                }
                            }
                        }
                        fclose($handle);
                        // TODO: Evaluar si se puede cambiar a progresivo
                    }
                }
                
                if (($filterItem==="USUARIO"&&isset($filterUsuario[0]))||($filterItem==="HORA"&&!isset($filterHora[0]))) {
                    $resultData=array_map(function($filepath){global $logLen;return [substr($filepath,$logLen+1)];}, $userFiles);
                    $filterByFix="FECHA=$filterFecha|USUARIO=$filterUsuario";
                }
            }
            if (isset($filterFecha[0])) {
                if (!isset($usrObj)) {
                    require_once "clases/Usuarios.php";
                    $usrObj=new Usuarios();
                }
                $usrObj->rows_per_page=0;
                $usrData=$usrObj->getData(false,0,"nombre");
                $usrList=array_column($usrData, "nombre");
                $filePattern=$logsPath."/".$filterFecha."/*.log";
                $fileList=glob($filePattern);
                if ($filterItem!=="FECHA"&&isset($_POST["CURR_USUARIO"][0])) $columnComment["USUARIO"]=explode(",",$_POST["CURR_USUARIO"]);
                else {
                    $usrAsArr=[];
                    $filecount=0;
                    $hasNoUser=false;
                    $hasCmd=false;
                    foreach ($fileList as $idx=>$filename) {
                        $originalFilename = $filename;
                        $lastSlashIndex=strrpos($filename, "/");
                        if ($lastSlashIndex===false) $lastSlashIndex=0;
                        else $lastSlashIndex++;
                        $filename=substr($filename, $lastSlashIndex, -4);
                        $isRWA=false;
                        if ("read_"===substr($filename, 0, 5)) { $isRWA=true; $filename=substr($filename, 5); }
                        else if ("_read"===substr($filename, -5)) { $isRWA=true; $filename=substr($filename, 0, -5); }
                        else if ("action_"===substr($filename, 0 ,7)) { $isRWA=true; $filename=substr($filename, 7); }
                        else if ("_action"===substr($filename, -7)) { $isRWA=true; $filename=substr($filename, 0, -7); }
                        if (in_array($filename, $usrList)) {
                            if (!isset($usrAsArr[$filename])) {
                                $usrAsArr[$filename]=1;
                                $filecount++;
                            } else $usrAsArr[$filename]++;
                        } else if ($filename==="logoff"||$filename==="_logoff") $hasNoUser=true;
                        else if (!$isRWA) $hasCmd=true;
                        // ToDo: else guardar log de archivos que no cumplen con lo anterior...
                    }
                    $columnComment["USUARIO"]=array_keys($usrAsArr);
                    sort($columnComment["USUARIO"]);
                    if ($hasNoUser) array_splice($columnComment["USUARIO"],0,0,"SIN SESION");
                    if ($hasCmd) array_splice($columnComment["USUARIO"],0,0,"INVOICE");
                }
                if (($filterItem==="FECHA"&&isset($filterFecha[0]))||($filterItem==="USUARIO"&&!isset($filterUsuario[0]))) {
                    $resultData=array_map(function($filepath){global $logLen;return [substr($filepath,$logLen+1)];}, $fileList);
                    $filterByFix="FECHA=$filterFecha";
                }
            }
            $columnSpan=[3];
            $columnClass=["lefted padv4i"];
            //$resultData = [];
            //catlogn("SOLICITUD DE LISTA DE ARCHIVOS.");
            //catlogn("POST = ".json_encode($_POST));
            //catlogn("PATH=$logsPath ($logLen)");
        } else {
            $tablename = isset($_POST["tablename"])?$_POST["tablename"]:false;
            $tableviewname = isset($_POST["tableviewname"])?$_POST["tableviewname"]:false;
            $editable = isset($_POST["editable"])?$_POST["editable"]:false;
            $noCols = isset($_POST["noCols"])?+$_POST["noCols"]:50;
            $currPG = isset($_POST["currPg"])?+$_POST["currPg"]:1;
            $lastPG = isset($_POST["lastPg"])?+$_POST["lastPg"]:0;
            $sortBy = isset($_POST["sortBy"])?$_POST["sortBy"]:"";
            $filterBy = isset($_POST["filterBy"])?$_POST["filterBy"]:"";
            $lastAction = isset($_POST["lastAction"])?$_POST["lastAction"]:"";
            if ($noCols<=0) $noCols=50;
            if ($currPG<=0) $currPG=1;
            if ($logFull) catlogn("TABLENAME  : $tablename");
            if ($logFull) catlogn("TABLEVIEW  : $tableviewname");
            if (!$logFull) catlogn("CATALOGO   : $tableviewname");
            catlogn("ES EDITABLE: ".($editable?"YES":"NO"));
            catlogn("NUM. COLS. : $noCols");
            catlogn("PAG. ACTUAL: $currPG");
            if ($logFull) catlogn("ULTIMA PAG.: $lastPG");
            if (!empty($sortBy)) catlogn("SCRIPT ORDEN: $sortBy");
            if (!empty($filterBy)) catlogn("SCRIPT FILTRO: $filterBy");
            if ($logFull) catlogn("LASTACTION: $lastAction");
            
            $query = "SHOW FULL COLUMNS FROM $tablename";
            if ($logFull) catlogn("QUERY: $query");
            $columnNames = [];
            $columnComment = [];
            $result = DBi::query($query);
            if (is_object($result)) while ($columnList = $result->fetch_row()) {
                // 0 : Field
                // 1 : Type
                // 2 : Collation
                // 3 : Null
                // 4 : Key
                // 5 : Default
                // 6 : Extra
                // 7 : Privileges
                // 8 : Comment
                $columnNames[] = $columnList[0]; // primer registro es el nombre de la columna
                if (!empty($columnList[8])) {
                    if ($columnList[8][0]==="[" && substr($columnList[8],-1)==="]")
                        $columnComment[$columnList[0]] = explode(",",str_replace(" ","",substr($columnList[8],1,-1)));
                }
            }
            catlogn("COLUMN LIST = ".implode(", ",$columnNames));
            if (!empty($columnComment)) {
                $strComms = json_encode($columnComment);
                catlogn("COLUMN COMMENTS = $strComms");
            }
            
            $whereQry="";
            if (!empty($filterBy)) {
                $filterBy = str_replace(["\"","'"], "", $filterBy);
                $whereList = explode("|",$filterBy);
                foreach($whereList as $whereTuple) {
                    $kvArr = explode("=",$whereTuple);
                    if (in_array($kvArr[0],$columnNames) && isset($kvArr[1])) {
                        $kvArr[1] = trim($kvArr[1]);
                        if ($kvArr[1][0]==="!") {
                            $kvArr[1] = substr($kvArr[1],1);
                            $modA="NOT ";
                            $modB="!";
                        } else {
                            $modA="";
                            $modB="";
                        }
                        if (strpos($kvArr[1],"*")!==false) {
                            $kvArr[1]=str_replace("*","%",$kvArr[1]);
                            if(isset($whereQry[0])) $whereQry.=" AND ";
                            $whereQry.="UPPER($kvArr[0]) {$modA}LIKE \"".mb_strtoupper($kvArr[1])."\"";
                        } else if (strpos($kvArr[1],"%")!==false) {
                            if(isset($whereQry[0])) $whereQry.=" AND ";
                            $whereQry.="UPPER($kvArr[0]) {$modA}LIKE \"".mb_strtoupper($kvArr[1])."\"";
                        } else if ($kvArr[1][0]==="(" && $kvArr[1][strlen($kvArr[1])-1]===")") {
                            $tupleVarList = explode(",",substr($kvArr[1],1,-1));
                            if (!empty($tupleVarList)) {
                                if(isset($whereQry[0])) $whereQry.=" AND ";
                                $whereQry.="UPPER($kvArr[0]) {$modA}IN (";
                                $first=true;
                                foreach($tupleVarList as $tupVarOption) {
                                    if ($first) $first=false;
                                    else $whereQry.=",";
                                    $whereQry.="\"".mb_strtoupper(trim($tupVarOption))."\"";
                                }
                                $whereQry.=")";
                            }
                        } else {
                            if(isset($whereQry[0])) $whereQry.=" AND ";
                            $whereQry.="UPPER($kvArr[0]){$modB}=\"".mb_strtoupper(trim($kvArr[1]))."\"";
                        }
                    }
                }
                if (isset($whereQry[0])) $whereQry=" WHERE ".$whereQry;
            }

            $query = "SELECT count(1) from $tablename$whereQry";
            if ($logFull) catlogn("QUERY: $query");
            $result = DBi::query($query);
            $row = $result->fetch_row();
            if (!empty($row)) $totReg = +($row[0]);
            else $totReg = 0;
            if ($logFull) catlogn("RESULT: $totReg");
            else catlogn("TOTAL REGS.: $totReg");
            if ($totReg<=10) {
                $noCols=10;
                catlogn("AJUSTE PAG.: $currPG");
            }
            $lastPG = (int)ceil($totReg/$noCols);
            catlogn("ULTIMA PAG.: $lastPG");
            
            if ($lastPG<=0) {
                $query = false;
            } else {
                $orderBy="";
                if(!empty($sortBy)) {
                    $sortBy = str_replace(["#","&","%","<",">"], "", $sortBy);
                    $sortList = explode("|",$sortBy);
                    foreach($sortList as $sortTuple) {
                        //catlogn("SORT BY $sortTuple");
                        $kvArr = explode(" ",$sortTuple);
                        if (in_array($kvArr[0],$columnNames) && !isset($kvArr[2]) && isset($kvArr[1]) && ($kvArr[1]==="asc"||$kvArr[1]==="desc")) {
                            if(isset($orderBy[0])) $orderBy.=", ";
                            $orderBy.=$kvArr[0]." ".$kvArr[1];
                        }
                    }
                    if (isset($orderBy[0])) $orderBy=" ORDER BY ".$orderBy;
                }
                if ($currPG>$lastPG) {
                    $currPG=$lastPG;
                    catlogn("AJUSTE PAG.: $currPG");
                }
                
                if ($lastPG===1)
                    $query = "SELECT * from $tablename$whereQry$orderBy";
                else if ($currPG===1)
                    $query = "SELECT * from $tablename$whereQry$orderBy limit $noCols";
                else {
                    $firstReg = ceil(($currPG-1)*$noCols);
                    $query = "SELECT * from $tablename$whereQry$orderBy limit $firstReg, $noCols";
                }
            
                if ($logFull) catlogn("QUERY: $query");
                $result = DBi::query($query);
                if (!empty(DBi::$error)) $error = DBi::$error;
                if (!empty(DBi::$errors)) $errors = DBi::$errors;
                if (!empty(DBi::$insert_id)) $insertId = DBi::$insert_id;
                if (!empty(DBi::$affected_rows)) $affectedRows = DBi::$affected_rows;
                if (!empty(DBi::$query_info)) $queryInfo = DBi::$query_info;
                if (!empty(DBi::$warning_count)) $warningCount = DBi::$warning_count;
                if (!empty(DBi::$warnings)) $warnings = DBi::$warnings;
                $numRows = is_object($result)?$result->num_rows:0;
                $fetchInfo = $numRows?$result->fetch_fields():[];
                $columnNames = [];
                $columnGrant = [];
                $columnTypes = [];
                $resultData = [];
                foreach ($fetchInfo as $fval) {
                    $cnm = $fval->name;
                    $lnm = strtolower($fval->name); // $fval->orgname);
                    $columnNames[] = $cnm;
                    if ($lnm==="id"||$lnm==="modifiedtime") $columnGrant[] = "r";
                    else $columnGrant[] = "w";
                    $columnTypes[] = mapType($fval->type);
                }

                // Convert result into an associative array
                if ($numRows) while ($row = $result->fetch_row()) {
                    $resultData[] = $row;
                }
                //$rtype = gettype($result);
                //if ($rtype==="object") $rtype.=":".get_class($result);
                //catlogn("RESULT TYPE=".$rtype);
                catlogn("NUM. REGS. : $numRows");
                // release result
                if ($result) $result->close();
            }
        }
        if (isset($columnNames))   $dataObj["columnNames"]=$columnNames;
        if (isset($columnGrant))   $dataObj["columnGrant"]=$columnGrant;
        if (isset($columnTypes))   $dataObj["columnTypes"]=$columnTypes;
        if (isset($columnSpan))    $dataObj["columnSpan"]=$columnSpan;
        if (isset($columnClass))   $dataObj["columnClass"]=$columnClass;
        if (!empty($columnComment)) $dataObj["columnComment"]=$columnComment;
        if (isset($filterByFix))   $dataObj["filterBy"]=$filterByFix;
        if (isset($resultData)) $dataObj["resultData"]=$resultData;
        if (isset($tablename)) $dataObj["tablename"]=$tablename;
        if (isset($tableviewname)) $dataObj["tableviewname"]=$tableviewname;
        if (isset($tableOptions))  $dataObj["tableOptions"]=$tableOptions;
        if (isset($noCols)) $dataObj["noCols"]=$noCols;
        if (isset($currPG)) $dataObj["currPG"]=$currPG;
        if (isset($lastPG)) $dataObj["lastPG"]=$lastPG;
        if (isset($totReg)) $dataObj["totReg"]=$totReg;
        $dataObj["log"]=catlog();
    }
    $data = json_encode($dataObj);
    echo $data;
}
function mapType($typeId) {
    switch($typeId) {
        case MYSQLI_TYPE_DECIMAL:
        case MYSQLI_TYPE_NEWDECIMAL:
        case MYSQLI_TYPE_FLOAT:
        case MYSQLI_TYPE_DOUBLE:
            return 'f';

        case MYSQLI_TYPE_BIT:
        case MYSQLI_TYPE_TINY:
        case MYSQLI_TYPE_SHORT:
        case MYSQLI_TYPE_LONG:
        case MYSQLI_TYPE_LONGLONG:
        case MYSQLI_TYPE_INT24:
        case MYSQLI_TYPE_YEAR:
        case MYSQLI_TYPE_ENUM:
            return 'i';

        case MYSQLI_TYPE_TIMESTAMP:
        case MYSQLI_TYPE_DATE:
        case MYSQLI_TYPE_TIME:
        case MYSQLI_TYPE_DATETIME:
        case MYSQLI_TYPE_NEWDATE:
        case MYSQLI_TYPE_INTERVAL:
            return 'd';

        case MYSQLI_TYPE_SET:
        case MYSQLI_TYPE_VAR_STRING:
        case MYSQLI_TYPE_STRING:
        case MYSQLI_TYPE_CHAR:
        case MYSQLI_TYPE_GEOMETRY:
            return 's';

        case MYSQLI_TYPE_TINY_BLOB:
        case MYSQLI_TYPE_MEDIUM_BLOB:
        case MYSQLI_TYPE_LONG_BLOB:
        case MYSQLI_TYPE_BLOB:
            return 'b';
    }
    //trigger_error("unknown type: $field_type");
    return 'e';
}
function catlog($msg=null) {
    static $logmsg = "";
    if (isset($msg)) {
        if ($msg===false) $logmsg="";
        else $logmsg .= $msg;
    }
    return $logmsg;
}
function catlogn($msg=null) {
    if (isset($msg) && $msg!==false) return catlog($msg."\n");
    else return catlog($msg);
}
