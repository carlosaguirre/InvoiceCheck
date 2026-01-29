<?php
if (isset($_POST["accion"])) {
    require_once dirname(__DIR__)."/bootstrap.php";
    switch($_POST["accion"]) {
        case "esasaSubmit1":
            $x=$_POST["x"]??"0";
            $y=$_POST["y"]??"0";
            clickMouse($x, $y, true, true, $msgs); // 0,344,360,1
            $result=[];
            if (isset($msgs["ERROR"][0])) {
                $result["result"]="failure";
                $result["error"]=$msgs["ERROR"];
            }
            if (isset($msgs["CONTENTS"][0])) {
                $result["result"]="success";
                $result["message"]=$msgs["CONTENTS"];
            }
            if (isset($msgs["COMMAND"][0])) {
                $result["cmd"]=$msgs["COMMAND"];
            }
            if (isset($msgs["RETURN"][0])) {
                $result["return"]=$msgs["RETURN"];
            }
            echo json_encode($result);
        break;
        default:
            echo json_encode(["result"=>"failure","message"=>"UNKNOWN ACTION $_POST[accion]"]);
    }
    die();
} else if (is_cli()) {
    clickMouse(344,360,true,true,$mess);
    if (isset($mess["COMMAND"][0])) echo "CMD = $mess[COMMAND]. ";
    if (isset($mess["CONTENTS"][0])) echo "CON = $mess[CONTENTS]. ";
    if (isset($mess["ERROR"][0])) echo "ERR = $mess[ERROR]. ";
    if (isset($mess["RETURN"][0])) echo "RET = $mess[RETURN].";
}
die();
function clickMouse($x, $y, $isLeftMouse, $isCursorKept, &$messages) {
    $descspec=[["pipe","r"],["pipe","w"],["pipe","w"]];
    $command="C:\\Windows\\MouseClick.exe ".($isLeftMouse?"0":"1").",$x,$y,".($isCursorKept?"1":"0");
    // 0,344,360,1
    //$command="C:\\InvoiceCheckShare\\mc.lnk ".($isLeftMouse?"0":"1").",$x,$y,".($isCursorKept?"1":"0");
    $messages["COMMAND"]=$command;
    $process=proc_open($command, $descspec, $pipes, NULL, NULL, ['bypass_shell' => TRUE]);
    if (is_resource($process)) {
        fclose($pipes[0]);
        $messages["CONTENTS"]=stream_get_contents($pipes[1])??"";
        fclose($pipes[1]);
        $messages["ERROR"]=stream_get_contents($pipes[2])??"";
        fclose($pipes[2]);
        $return_value=proc_close(($process));
        $messages["RETURN"]="$return_value";
        return true;
    }
    $messages["ERROR"]="NO RESOURCE";
    return false;
}
function is_cli() {
    if (defined('STDIN')) return true;
    if (empty($_SERVER['REMOTE_ADDR']) && !isset($_SERVER['HTTP_USER_AGENT']) && count($_SERVER['argv'])>0) return true;
    return false;
}
