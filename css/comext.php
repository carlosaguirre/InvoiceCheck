<?php
require_once dirname(__DIR__)."/bootstrap.php";
header("Content-type: text/css; charset: UTF-8");
$fondoDialogBox = "{$bkgdImgName}.jpg";
$fondoDialogBoxD = "{$bkgdImgNameD}.jpg";
?>
#area_detalle {
    position: relative;
    width: 100%;
}
#comext_menu_toggle {
    z-index: 1000;
    position: absolute;
    left: 81px;
    top: 3px;
    padding: 3px;
    width: 15px;
    height: 15px;
    border: none;
    background-color: transparent;
    background: url(imagenes/icons/downArrowB.png) no-repeat center;
    background-size: contain;
    cursor: pointer;
    transition: transform 0.3s ease;
}
#comext_menu_down {
    position: absolute;
    left: 78px;
    top: -2px;
    width: 15px;
    height: 15px;
    border: none;
    background-color: transparent;
    background: url(imagenes/icons/downArrowB.png) no-repeat center;
    background-size: contain;
    cursor: pointer;
    transition: transform 0.3s ease;
}
#comext_menu_up {
    position: absolute;
    left: 81px;
    top: 1px;
    width: 15px;
    height: 15px;
    border: none;
    background-color: transparent;
    background: url(imagenes/icons/downArrowB.png) no-repeat center;
    background-size: contain;
    cursor: pointer;
    transition: transform 0.3s ease;
}
/* Toggle states */
.menu-open {
    transform: rotate(180deg);
}

.menu-visible {
    max-height: 225px !important; /* Adjust based on your menu's height */
}
#comext_menu_close {
    position: absolute;
    left: 5px;
    top: 5px;
    width: 92px;
    height: 10px;
    background-color: rgb(235,222,219);
    z-index: 99;
    border: none;
    border-radius: 3px;
    box-shadow: 0px 0px 1.4px 1.4px rgb(0,0,0);
}
#comext_menu {
    position: absolute;
    left: 2px;
    top: 2px;
    /* padding: 3px; */
    width: 98px;
    background-image: url(imagenes/fondos/<?= $fondoDialogBox ?>);
    background-repeat: repeat;
    z-index: 100;
    overflow: hidden;
    max-height: 0;
    transition: max-height 0.3s ease-out;
}
#comext_menu>ul {
    list-style-type: none;
    width: 100%;
    padding-inline-start: 0px;
    margin-bottom: 0px;
}
#comext_menu>ul>li {
    margin-bottom: 3px;
    width: 100%;
    padding: 3px;
}
#comext_menu>ul>li>button {
    font-size: 12px;
    text-align: left;
    width: 100%;
    background-color: rgba(255,130,100,0.1);
    border: none;
    border-radius:4px;
    box-shadow: 0px 0px 1.4px 1.4px rgb(0,0,0);
    color: #008;
}
#comext_menu>ul>li>button:hover {
    background-color: rgba(50,50,0,0.1);
    transition: 0.7s;
    color: black;
}
#comext_menu>ul>li>button:focus {
    outline-color: transparent;
    outline.style: solid;
    box-shadow: 0 0 0 2px #a77037;
    transition: 0.7s;
}
#comext_menu>ul>li>button:active {
    background-color: rgba(250,240,230,0.5);
    color: gray;
}
#comext_menu>ul>li>button.selected {
    box-shadow: 0 0 0 3px #c3b35d;
    text-weight: bold;
    color: #008;
    background-color: rgba(255,255,100,0.1);

}
#comext_page {
    text-align: center;
    margin: 0 auto;
    height: 100%;
    position: relative;
    z-index: 1;
}
#comext_overFrame {
    width: 100%;
    height: 100%;
    position: absolute;
    background-color: rgba(255,0,0,0.1);
}
#comext_frame {
    width: 100%;
    height: 100%;
}
#comext_title {
    width: 100%;
    position: sticky;
    top: 0px;
    margin: 0 auto;
    background-image: url(imagenes/fondos/<?= $fondoDialogBox ?>);
    background-repeat: repeat;
    z-index: 2;
    max-height: 26.4px;
}
@media (prefers-color-scheme: darkX) {
    #comext_menu {
        background-image: url(imagenes/fondos/<?= $fondoDialogBoxD ?>);
    }
    #comext_title {
        background-image: url(imagenes/fondos/<?= $fondoDialogBoxD ?>);
    }
}
#comext_title::before, #comext_title::after {
    content:" ";
    display: inline-block;
    width: 100px;
    vertical-align: top;
}
#comext_title>div {
    display: inline-block;
    width: calc(100% - 200px);
}
@media screen and (min-width: 600.1px) and (max-width: 863px) {
    #comext_title {
        font-size: calc(5.2vw - 20.873px);
    }
}
@media screen and (min-width: 544px) and (max-width: 600px) {
    #comext_title {
        font-size: 10.4px;
    }
}
@media screen and (max-width: 543.9px) {
    #comext_title {
        font-size: calc(5.1vw - 17.3561px);
    }
}
@media screen and (max-width: 725px) {
    #comext_title.srchexp.old {
        font-size:2.7vw;
    }
}
@media screen and (max-width: 595px) {
    #area_detalle button, #area_detalle th, #area_detalle td {
        zoom: 99%;
        font-size: 99%;
    }
    #area_detalle h2.old {
        font-size: calc(99% + 9px);
    }
}
#comext_content {
    height: calc(100% - 26.4px);
    z-index: 1;
}
#comext_content>div {
    padding-left: 100px;
    width: 100%;
    height: 100%;
}
#comext_content>div:not(.centered) {
    text-align: left;
}
#comext_content>div>table {
    font-size: 14px;
    text-align: left;
}
#comext_content>div>table:not(.maxxed) {
    margin: 0 auto;
    width: 100%;
    max-width: 376px;
}
#comext_content>div>table.maxxed {
    margin-left: 10px;
    width: calc(100% - 20px);
    height: 100%;
    table-layout: fixed;
}
#comext_content th.stretch, #comext_content td.stretch {
    width: 140px;
    white-space: nowrap;
}
#comext_content th {
    padding: 2px;
    width: fit-content;
}
#comext_content th:not(.top) {
    vertical-align: middle;
}
#comext_content td {
    padding: 2px;
    width: max-content;
    white-space: nowrap;
}
#comext_content input[type="text"]:not(.calendarV):not(.folio):not(.ordNo) {
    width: -webkit-fill-available;
    width: -moz-available;
}
.maxWid258 {
    max-width: 258px;
}
#comext_content input[type="text"].ordNo {
    width: calc(100% - 108px);
}
#comext_content label.forPDF {
    margin-left: 4px;
    width: 88px;
}
/* #comext_content input[type="file"].ordFile {
    margin-left: 4px;
    max-width: 158px;
    width: calc(100% - 120px);
} */
#comext_content input[type="text"].folio, select.operacion {
    width: 120px/*calc(100% - 20px)*/;
}
#comext_content input[type="button"] {
    margin-left: 1px;
    margin-right: 1px;
}
.comext_fixedSelect, .comext_status {
    min-width: 110px;
}
.comext_fixedSelect:not(.filterValue) {
    width: -webkit-fill-available;
    max-width: 258px;
}
.comext_fixedSelect.filterValue {
    width: max-content;
    max-width: calc(100% - 45px);
}
.comext_status, .cent {
    width: 100px !important;
}
td:has(#srchexpFilterSummary), table.vwexpdn td, table.vwexpdn th {
    vertical-align: top;
}
#srchexpFilterSummary {
    overflow: auto;
    padding-top: 6px;
}
#srchexpOpResults {
    overflow: auto;
    border: 1px inset lightgray;
    width: 100%;
    height: calc(100% - 30px);
}
#srchexpOpResults>table {
    margin: 5px;
}
#srchexpOpResults>table>thead {
    border-bottom: 1px solid lightgray;
}
#srchexpOpResults>table>thead>tr {
    background-color: rgba(200, 255, 255, 0.3);
}
#srchexpOpResults>table>tbody>tr:not(.selected) {
    cursor: pointer;
}
#srchexpOpResults>table>tbody>tr.selected {
    cursor: grabbing;
    text-shadow: -0.3px 0px 0px, 0.7px 0px 0px;
}
#srchexpOpResults>table>tbody>tr:not(.selected):nth-child(even) {
    background-color: rgba(255, 255, 200, 0.3);
}
#srchexpOpResults>table>tbody>tr.selected:nth-child(odd) {
    background-color: rgba(150, 50, 50, 0.1);
}
#srchexpOpResults>table>tbody>tr.selected:nth-child(even) {
    background-color: rgba(150, 50, 0, 0.2);
}
#srchexpOpResults>table>tbody>tr:not(.selected):hover {
    background-color: rgba(255, 255, 255, 0.4);
    outline-color: rgba(255, 200, 0, 0.6); /*transparent;*/
    outline-width: 1px;
    outline-style: solid;
}
#srchexpOpResults>table>tbody>tr.selected:hover {
    /*font-weight: bold;*/
    /*box-shadow: 0px 0px 5px 5px rgba(0,0,255,1);*/
    outline-color: rgba(0, 140, 0, 0.5); /*transparent;*/
    outline-width: 2px;
    outline-style: solid;
    text-shadow: -0.3px 0px 0.2px, 0.7px 0px 0.8px;
}
#srchexpOpReview {
    font-weight: bold;
    border: 1px inset lightgray;
    width: 100%;
    height: 30px;
}
#srchexpOpReview>div {
    margin: 3px;
    display: inline-block;
    text-align: center;
    width: 54px;
}
#srchexpOpReview>div:first-child, #srchexpOpReview>div:last-child {
    width: calc(50% - 43px);
    height: 24px;
    line-height: 24px;
}
#srchexpOpReview>div:first-child {
    text-align: left;
}
#srchexpOpReview>div:last-child {
    text-align: right;
}
#srchexpBrowseResult {
    max-width: 300px;
}
#srchexpBrowseResult td, #srchexpBrowseResult th {
    padding: 2px;
}
#fixexpdMessage {
    position: absolute;
    top: 0;
    left: 0;
    margin: 0 auto;
    cursor: url('imagenes/iconos/deleteIcon20.png');
    background-color: rgba(240,230,230,0.5);
}
#fixexpdMessage>span {
    text-align: center;
    font-size: 20px;
    font-weight: bold;
    white-space: nowrap;
    cursor: url('imagenes/iconos/deleteIcon20.png');
    display: inline-block;
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
}
#fixexpdMessage>span.success {
    border: 1px solid green;
    background-color: rgba(100,255,100,0.1);
    color: darkgreen;
}
#fixexpdMessage>span.error {
    border: 1px solid red;
    background-color: rgba(255,100,100,0.1);
    color: darkred;
}
#fixexpdMessage:has(img) {
    overflow: hidden;
}
#fixexpdMessage>img {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
}
fieldset {
    display: inline-block;
    width: initial;
    padding: 3px;
    margin: 2px;
    border: 2px groove rgb(192,192,192);
}
img.closeFxFld {
    width: 11px;
    height: 11px;
    margin: 0px;
    position: absolute;
    top: -1px;
    right: -1px;
    border-width: 2px;
    border-style: outset;
    border-color: darkgray;
    background-color: #ddd;
}
img.closeFxFld:hover {
    background-color: #ec0;
}
img.closeFxFld:active {
    border-style: inset;
}
fieldset>img.closeFld {
    width: 8px;
    height: 8px;
    margin: 2px;
    position: absolute;
    top: -4px;
    right: -4px;
}
span.fldBox {
    border: 1px solid black;
    padding: 0px 3px;
    position: relative;
    margin-right: 4px;
}
span.fldBox>img.closeFld {
    width: 10px;
    height: 10px;
    position: absolute;
    top: -5px;
    right: -5px;
    background-color: papayawhip;
    box-shadow: 2px 1px 4px 1px rgba(0, 0, 0, 0.25);
    border-radius: 5px;
    border: 1px dotted blue;
    cursor: pointer;
}
span.fldBox>img.closeFld:hover {
    background-color: honeydew;
    border: 1px solid gold;
}
span.fldBox>img.closeFld:active {
    transform: scale(0.98);
    box-shadow: 1px 1px 4px 0px rgba(0, 0, 0, 0.5);
    background-color: lightsteelblue;
    border-color: blue;
}
img.loadImg {
    width: 15px;
    height: 15px;
    margin-top: 1.5px;
    margin-left: 2px;
    vertical-align: text-top;
}
legend {
    font-size: 12px;
    display: initial;
    width: initial;
    padding: 0 2px;
    margin: 0 auto;
    margin-right: 10px;
    color: inherit;
    border: initial;
    line-height: normal;
}
