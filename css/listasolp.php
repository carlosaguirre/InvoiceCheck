<?php
require_once dirname(__DIR__)."/bootstrap.php";
header("Content-type: text/css; charset: UTF-8");
$isDeveloper=hasUser()&&in_array(getUser()->nombre, ["admin","sistemas","sistemas1","sistemas2","test2"]);
$enableHiddenMenu=hasUser()&&!isMobile();
?>
table.lstpago>thead>tr>th, table.lstpago>tbody>tr>td, table.pad2cnw td, table.pad2cnw th {
    padding: 2px;
    white-space: nowrap;
}
table.lstpago>thead>tr.lefted>th:not(.centered), table.lstpago>tbody>tr.lefted>td:not(.centered) {
    text-align: left;
}
table.lstpago>thead>tr:not(.lefted)>th, table.lstpago>tbody>tr:not(.lefted)>td {
    text-align: center;
}
table.lstpago>tbody>tr>td>div {
    overflow: hidden;
    white-space: nowrap;
    text-overflow: ellipsis;
    display: inline-block;
}
th.sortH {
    cursor: pointer;
}
th.sortH:hover {
    cursor: grab;
}
div.listfolioButton {
    width: 100px;
}
div.listStatusButton1col {
    width: 135px;
}
div.listStatusButton2col {
    width: 240px;
}
@media screen and (max-width: 600px) {
    div.listfolioButton {
        width: 85px;
    }
    table.lstpago>tbody>tr>td>div>button {
        padding-inline: 3px;
    }
}
div.listStatusButton1col>button {
    padding-inline: 3px;
}
