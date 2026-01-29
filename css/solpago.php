<?php
require_once dirname(__DIR__)."/bootstrap.php";
header("Content-type: text/css; charset: UTF-8");
?>
table.solpago {
    width: 550px;
}
table.solpago>tbody>tr>th, table.solpago>tbody>tr>td, table.pad2cnw td, table.pad2cnw th {
    padding: 2px;
    white-space: nowrap;
}
table.solpago>tbody>tr.lefted>th:not(.centered), table.solpago>tbody>tr.lefted>td:not(.centered) {
    text-align: left;
}
table.solpago>tbody>tr:not(.lefted)>th, table.solpago>tbody>tr:not(.lefted)>td,  {
    text-align: center;
}
ytable.solpago>tbody>tr>th {
    user-select: none;
    -webkit-user-select: none;
    -moz-user-select: none;
    -khtml-user-select: none;
    -ms-user-select: none;
}
table.solpago>tbody>tr>th>div {
    white-space: nowrap;
}
table.solpago>tbody>tr>th:first-child>div {
    width: 104.8px;
    text-align: left;
}
table.solpago>tbody>tr>td {
    white-space: nowrap;
}
table.solpago>tbody>tr>td>select:not(.customWid) {
    width: 107px;
}
table.solpago>tbody>tr>td>div.detail {
    width: 323px;
    height: 22px;
    overflow: hidden;
    white-space: nowrap;
    text-overflow: ellipsis;
    display: inline-block;
}
table.solpago>tbody>tr>td>input.date {
    width: 94px;
    padding: 0px 2px;
    text-align: center;
}
#gpo_row, #prv_row, #prv_row2, #prv_row3 {
    text-align: left;
    align: left;    
}
#gpo_alias, #gpo_detail, #prv_detail, #prv_codigo {
    vertical-align: middle;
}
#prv_banco {
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    display: inline-block;
    vertical-align: middle;
    width: 151px;
}
#docChoice {
    vertical-align: top;
    margin-right: 3px !important;
    width: 105px;
    height: 19.2px;
    border: none;
    background-color: transparent;
}
#docMain {
    vertical-align: top;
}
#folio {
    padding: 0px 2px;
    margin-bottom: 2px;
    width: 90px;
}
#delFolioImg, #delFolioCRImg, #delUuidImg {
    display: inline-block;
    width: 12px;
    height: 12px;
    line-height: 10px;
    font-size: 9px;
    text-align: center;
    cursor: pointer;
}
#crReactButtonArea {
    position: absolute;
    right: 0px;
}
#inv2SpltTbl {
    margin: 0 auto;
    text-align: center;
    width: 426px !important;
}
