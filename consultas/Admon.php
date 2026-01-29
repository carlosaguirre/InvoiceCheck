<?php
$preBoot=array_key_exists("_pryNm",$GLOBALS);
if (!$preBoot) 
    require_once dirname(__DIR__)."/bootstrap.php";
$path="C:/Apache24/htdocs/invoice";
if (!hasUser()) {
    if (isset($_POST["command"])) echo json_encode(["result"=>"refresh"]);
} else if (validaPerfil("Administrador") && getUser()->nombre==="admin" && isset($_POST["command"])) switch($_POST["command"]) {
    case "findString":
        if (isset($_POST["value"])) {
            $value=trim($_POST["value"]);
            if (isset($value[0])) {
                $value="\"$value\"";
                if (strpos($value," ")!==false) $value="/c:$value";
                $cmd="findstr ";
                if (!empty($_POST["isRecursive"])) $cmd.="/s ";
                if (empty($_POST["isCaseSensitive"])) $cmd.="/i ";
                $subpath=($_POST["subpath"]??"");
                if (isset($subpath[0])) $subpath="/$subpath";
                $cmd.="$cmd $path$subpath/*.php";
                $result=exec($cmd,$output,$retvar);
                if ($retvar>0 || isset($result[0])) {
                    $result = parseResult("RESULT",$result);
                    $fixedOutput=[];
                    for($i=0;isset($output[$i]); $i++) {
                        $fixedOutput[$i]=parseResult("OUTPUT[".($i+1)."]",$output[$i]);
                    }
                    echo json_encode(["result"=>"success","n"=>count($output), "result"=>$result, "cmd"=>$cmd, "output"=>$fixedOutput, "code"=>$retvar]);
                } else
                    echo json_encode(["result"=>"success","n"=>0, "cmd"=>$cmd, "output"=>$output, "code"=>$retvar]);
            } else echoError("Valor vacío");
        } else echoError("Sin valor");
        break;
    default:
        if (!isset($_POST["command"][0])) echoError("Sin acción",$_POST);
        else echoError("Acción desconocida",$_POST);
}

if (!$preBoot && $_doDB) require_once "configuracion/finalizacion.php";
if ($_noDie) return;
die();

function parseResult($title, $result) {
    global $path,$pathlen;
    $result = trim($result);
    $fixed = ["title"=>$title];
    if (substr($result,0,$pathlen)===$path) {
        $fixed["path"]=$path;
        $result=substr($result,$pathlen+1);
        $idx = strpos($result,":");
        if ($idx!==false) {
            $fixed["file"]=substr($result,0,$idx);
            $fixed["text"]=substr($result,$idx+1);
        } else {
            $fixed["file"]="NoFile";
            $fixed["text"]=$result;
        }
    } else {
        $fixed["path"]="NoPath";
        $fixed["file"]="UnDef";
        $fixed["text"]=$result;
    }
    return $fixed;
}
function echoError($message,$params=null) {
    $result=["result"=>"error","message"=>$message];
    if (isset($params)) {
        $keys=array_keys($params);
        foreach ($keys as $value) {
            if ($value==="result"||$value==="message") continue;
            $result[$value]=$params[$value];
        }
    }
    echo json_encode($result);
}
