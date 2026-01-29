<?php
require_once dirname(__DIR__)."/bootstrap.php";
header("Content-type: text/css; charset: UTF-8");
?>
#email {
    width: 200px;
}
#resultDiv {
    background-color: rgba(230,228,220,0.8);
    position: absolute;
    left: 0px;
    margin: 0 auto;
    text-align: center;
}
#area_detalle th {
    min-width: 100px;
    padding: 4px;
    background-color: rgb(233,233,207);
    margin: 0 auto;
    text-align: center;
    vertical-align: top;
}
#area_Detalle td {
    padding: 4px;
    background-color: rgba(255, 226, 134, 0.1);
}
#area_detalle span {
    display: inline-block;
    padding: 4px;
}
#area_detalle td>span {
    min-width: 100px;
    background-color: rgba(255, 255, 255, 0.1);
}
#resultDiv>div {
    display: inline-block;
    padding: 10px;
    border: 1px solid black;
    border-radius: 3px;
    outline: rgb(220, 217, 215) solid 3px;
    outline-offset: 0px;
    background-blend-mode: multiply;
}
#subject {
    width: 200px;
}
#message {
    width: 200px;
    margin-top: 2px;
}
#sendMailBtn {
    margin-left: 100px;
}
