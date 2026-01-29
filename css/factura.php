<?php
require_once dirname(__DIR__)."/bootstrap.php";
header("Content-type: text/css; charset: UTF-8");
$currentBrowser = getBrowser();
$isChrome = $currentBrowser=="Chrome";
$isIE = $currentBrowser=="IE";
$isFF = $currentBrowser=="Firefox";
?>
@charset "utf-8";
/* CSS Document */
* {
    font-family: Tahoma, Arial, sans-serif !important;
}
html {
    height: 100%;
}
body:not(.blank) {
    background-color:rgba(255, 255, 255, 0.5);
    background-image: url(imagenes/fondos/fondo1.jpg);
    background-repeat: repeat;
}
body {
    color:black; 
    font-size: small;
}
#contenedor {
    background-color:rgba(255, 255, 255, 0.4);
    width: 980px;
    margin: 0 auto;
}
#contenedorCFDI {
    margin: 0 auto;
}
#agregaPdfDiv {
    background-color:rgba(255, 255, 255, 0.4);
    width: 980px;
    margin: 0 auto;
}
#pg-block0 {
    margin: 0 auto;
    width: 723px;
    height: 963px; /* 193 */
    border: 1px solid black;
    border-spacing: 0px;
    border-collapse: separate;
}
#pg-block1 {
    margin: 0 auto;
    width: 723px;
    /* height: 193px; */
    border: 1px solid black;
    border-spacing: 0px;
    border-collapse: separate;
}
#pg-block1>tbody>tr>td {
    padding: 2px;
}
#pg-logo {
    width: 150px;
}
table {
    background-color:transparent;
    font-size: small;
}

th {
    background-color:rgba(0, 0, 200, 0.7);
    color:white;
}
th.h1 {
    background-color:rgba(0, 100, 0, 0.7);
    color:white;
}
th.h2 {
    background-color:rgba(240, 240, 0, 0.7);
    color:darkblue;
}
th.h3 {
    background-color:rgba(100, 0, 0, 0.7);
    color:white;
}

td {
    background-color:transparent;
    color:darkblue;
}
td.h1 {
    background-color:transparent;
    color:darkgreen;
}
td.h3 {
    background-color:transparent;
    color:darkred;
}
hr {
    color:#b0c4de;
}
.hidden {
    display: none;
}
.footinfo {
    position:fixed;
    right:0px;
    bottom: 0px;
    font-size: 9px;
    background-color: rgba(255, 255, 255, 0.5);
}
.centered {
    margin: 0 auto;
    text-align: center;
}
.righted {
    text-align: right;
}
.highlight {
    box-shadow: 0px 0px 10px yellow;
}
@media print {
    .noprint, .noprint * {
        display: none !important;
    }
}
.wordwrap {
    white-space: pre-wrap;      /* CSS3 */   
    white-space: -moz-pre-wrap !important; /* Mozilla */    
    white-space: -pre-wrap;     /* Opera <7 */   
    white-space: -o-pre-wrap;   /* Opera 7 */    
    word-wrap: break-word;      /* IE */
    white-space: -webkit-pre-wrap; /* Chrome/Safari newer versions */
    word-break: break-all;
    white-space: normal;
}
.shrinkCol {
    width:1%;
    white-space:nowrap;
}
td.invcell, th.invcell {
    padding-right: 3px;
    padding-left: 3px;
}
td.entity {
    width: 50%;
    vertical-align: top;
}
