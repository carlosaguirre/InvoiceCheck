<?php
require_once dirname(__DIR__)."/bootstrap.php";
header("Content-type: text/css; charset: UTF-8");
?>
@charset "utf-8";
/* CSS Document */

* {
    font-family: Tahoma;
}
html {
    height: 100%;
}
body:not(.blank) {
    background-image: url(imagenes/fondos/fondo1.jpg);
    background-repeat: repeat;
    color: #008;
    font-size: 14px;
    height: calc(100% - 8px); /* 100%; */ /* calc(100% - 16px); */
    overflow-y: hidden;
}
body.contrarrecibo, body.contrarrecibo th, body.contrarrecibo td {
    font-family: Book;
}
body.contrarrecibo table {
    border-spacing: 3px;
    border-collapse: separate;
}
body.contrarrecibo td {
    border: 1px solid black;
    padding: 3px;
}
#contenedor {
    width: 98%;
    height: 100%;
    margin: 0 auto;
    padding-left: 10px;
    padding-right: 10px;
/*
    background-image: url(imagenes/marcagrupo3.png);
    background-repeat: repeat;
    background-attachment: fixed;
    background-position: 15% 45%;
*/
    // border: 1px solid gray;
}
#encabezado {
    z-index: 10001;
    background-image: url(imagenes/fondos/fondo1.jpg);
    background-repeat: repeat;
    width: 100%;
    height: 98px;
}
#head_logo {
    float: left;
    width: 250px;
    height: 96px;
    padding: 0px 10px 2px 10px;
}
#head_main {
    float: left;
    width: calc(100% - 250px - 20px - 20px - 4px);
    height: 93px;
    margin-top: 5px;
    margin-left: 10px;
    margin-right: 10px;
}
#ttl_encabezado {
    width: 100%;
    margin: 0 auto;
    align-items: center;
}
#ttl_titulo {
    width: 100%;
    display: inline-block;
}
#ttl_middle {
    height: 10px;
}
#bloque_central {
    width: 100%;
    height: calc(100% - 133px);
    // border: 1px solid gold;
}
#lado_izquierdo {
    float: left;
    width: 250px;
    // border: 1px dashed orange;
}
#principal {
    float: right;
    width: calc(100% - 250px);
    height: 100%;
    margin: 0 auto;
    text-align: center;
    position: relative;
    // border: 1px dotted red;
}
#area_usuario {
    text-align: left;
    width: 500px;
    height:100%;
    left: 60px;
}
#area_central, #area_central2, #area_central3 {
    height:100%;
    width: 100%;
    top: 0px;
    display: inline-block;
    vertical-align: top;
}
#area_central h1, #area_central2 h1, #area_central3 h1 {
    width: 100%;
    height: 34px;
    vertical-align: top;
}
#area_central form {
    vertical-align: top;
    width: 100%;
    height: calc(100% - 34px - 1px);
}
#area_central2 form {
    vertical-align: top;
    width: 100%;
    height: 74px;
}
#area_central3 form {
    vertical-align: top;
    width: 100%;
    height: 70px;
}
#area_central2 div.scrolldiv {
    width: 100%;
    /* height: calc(100% - 34px - 74px - 30px -100px); */
    /* height: 300px; */
    height: calc(100% - 74px);
    overflow: auto;
    border: 1px solid lightgray;
}
#area_central3 div.scrolldiv {
    width: 100%;
    height: calc(100% - 70px - 60px);
    overflow: auto;
    border: 1px solid lightgray;
}
#area_central div.scrolldiv {
    width: 100%;
    height: calc(100% - 35px);
    overflow: auto;
    border: 1px ridge lightgray;
}
#invoice_resultarea, #ack_resultarea, #contra_resultarea {
    height: calc(100% - 34px);
}
.scrolldiv table.datatable {
    width:100%;
}
#xml_selector, #xml_insert {
    height: 22px;
}
#waiting-roll {
        margin-top: 30px;
}
/*
#table_scrollx table {
    width: 100%;
}
#table_scrollx {
    overflow-x: auto;
    width: 500px;
}
.datatable, #datatable {
    width: 100%;
}
.scrolldiv {
    overflow: auto;
} 
.scrolldiv table {
    width: 100%;
}
*/

@media (min-width: 1025px) {
    #dialogbox.help_area { width: 50%; }
    .bodyfont {
        color: #008;
        font-size: 14px;
    }
    #textarea_ayuda {
        background-color: transparent;
        width: 85%;
        height: 5em;
    }
    #img_ayuda {
        width: 300px;
        height: 231px;
    }
}
@media (min-width: 901px) and (max-width: 1024px) {
    #dialogbox.help_area { width: 50%; }
    .bodyfont {
        color: #008;
        font-size: 14px;
    }
    #textarea_ayuda {
        background-color: transparent;
        width: 85%;
        height: 5em;
    }
    #img_ayuda {
        width: 251px;
        height: 193px;
    }
}
@media (min-width: 801px) and (max-width: 900px) {
    #dialogbox.help_area { width: 50%; }
    .bodyfont {
        color: #008;
        font-size: 12px;
    }
    #textarea_ayuda {
        background-color: transparent;
        width: 85%;
        height: 5em;
        font-size: 11px;
    }
    #img_ayuda {
        width: 251px;
        height: 193px;
    }
    #dialog_tbody .bodyfont br {
        line-height: 10px;
    }
}
@media (min-width: 701px) and (max-width: 800px) {
    #dialogbox.help_area { width: 50%; }
    .bodyfont {
        color: #008;
        font-size: 10px;
    }
    #textarea_ayuda {
        background-color: transparent;
        width: 80%;
        height: 5em;
        font-size: 9px;
    }
    #img_ayuda {
        width: 200px;
        height: 154px;
    }
    #dialog_tbody .bodyfont br {
        line-height: 5px;
    }
}
@media (max-width: 700px) {
    #dialogbox.help_area { width: 338px; }
    .bodyfont {
        color: #008;
        font-size: 10px;
    }
    #textarea_ayuda {
        background-color: transparent;
        width: 80%;
        height: 5em;
        font-size: 9px;
    }
    #img_ayuda {
        width: 200px;
        height: 154px;
    }
    #dialog_tbody .bodyfont br {
        line-height: 5px;
    }
}
div.calendar_widget {
    position: absolute;
    float: left;
    top: 0px;
    left: 0px;
    width:170px;
    height: 150px;
    display: none;
}

td.semitransparent {
    background-color: rgba(255, 255, 255, 0.7);
}
#textarea_ayuda { resize: none; }
#textarea_gastos, #motivos_field {
    resize: none;
    background-color: rgba(255, 255, 255, 0.7);
    height: 45px;
    width: 350px;
}

#pie_pagina {
    position:fixed;
    vertical-align: middle;
    left:0px;
    bottom: 0px;
    width: 100%;
    height: 30px;
    border-top: 2px groove gray;
    background-image: url(imagenes/image1.png);
    background-repeat: repeat;
}
* html #pie_pagina {
    position: absolute;
    top:expression((0-(pie_pagina.offsetHeight)+(document.documentElement.clientHeight ? document.documentElement.clientHeight : document.body.clientHeight)+(ignoreMe = document.documentElement.scrollTop ? document.documentElement.scrollTop : document.body.scrollTop))+'px');
}
.pie_element {
    background-image: url(imagenes/fondos/fondo1.jpg);
    background-repeat: repeat;
}

#box_acceso.box td:first-child {
    width: 80px;
}
#box_acceso2.box table td.centered {
    margin: 0 auto;
    text-align: center;
}
.box.acceso, .bodega1, .box.puertos {
    width: 300px;
}
.boton.bodega td.centered, .box.gastos td.centered.boton, .box.cancelar td.centered.boton {
    margin: 0 auto;
    text-align: center;
}
.box.bodega:not(.boton) td:nth-child(odd) {
    white-space:nowrap;
    padding-right: 3px;
    text-align: left;
}
.box.bodega:not(.boton) td:first-child {
    width: 54px;
}
.box.bodega:not(.boton) td:nth-child(2) {
    width: 225px;
}
.box.bodega td select {
    width: 217px;
}
.box.bodega:not(.boton) td:nth-child(3) {
    width: 86px;
}

.box.bodega:not(.boton) td:nth-child(even) {
    white-space:nowrap;
    text-align: left;
}
.box.bodega:not(.boton) td:nth-child(even) input[type="text"] {
    width: 95%;
}
#localidadBodega {
    width: 85.5%;
}
#box_bodega.box.bodega {
    text-align: left;
}
.bodega2, .gastos, .cancelar {
    width: 600px;
}
.menu_izquierdo ul li .navSelected {
    background-color: rgba(180, 180, 230, 0.7);
}


/* Secciones Nivel 3+ bloque_central lado_izquierdo */
.menu_izquierdo a, .menu_izquierdo input[type="submit"] {
    font-size: 14px;
    font-weight: bold;
    font-variant: small-caps;
    color: #006;
    text-decoration: none;
    text-align: center;
    display: block;
    border: groove gray 2px;
    background-color: rgba(0, 0, 0, 0.12);
    white-space:nowrap;
    padding:5px;
    margin:5px;
}
.menu_izquierdo input[type="submit"] {
    width: 240px;
}
.menu_izquierdo a:hover, .menu_izquierdo input[type="submit"]:hover {
    background-color: rgba(100, 100, 255, 0.2);
}
.menu_izquierdo ul {
    margin: 0px;
    padding: 0px;
    list-style-type: none;
}
.menu_izquierdo li {
    margin-bottom: 10px;
}
h1, h2, h3, h4 {
    margin: 0 auto;
    text-align: center;
    float: none;
    clear: both;
    display: block;
}

/* Secciones Nivel 3+ bloque_central principal top */
#top {
    margin: 0 auto;
    align-items: center;
}
#area_titulo {
    clear: both;
    text-align: center;
    margin: 0 auto;
}
#area_folio {
    float: right;
    width: 150px;
    margin-right: 3px;
    text-align: right;
}
#folio_value {
    display: inline-block;
    text-align: center;
    border: solid black 1px;
    background-color: #999;
    width: 100px;
}
.top_area {
    display: inline-block;
    vertical-align: middle;
    white-space:nowrap;
}

/* Secciones Nivel 3+ bloque_central principal seccion */
.seccion {
    margin: 0 auto;
    display: inline-block;
    padding: 3px;
    margin-top: 0px;
    margin-bottom: 3px;
    overflow: hidden;
    display: table-cell;
    min-width: 0;
}
.hgtEPFP       { height: 121px; }
.hgtSPTP       { height: 136px; }
.hgtETW        { height: 142px; }
.hgtSCP        { height: 189px; }
.hgtSDW        { height: 230px; }
.fullbox       { width: 99%; float:left; }
.fullclearbox  { width: 100%; clear:both; display: block; }
.halfbox       { float:left; overflow: hidden; }

@media (min-width: 621px) {
  .fullbloc      { width: 100%; overflow: hidden; }
}
@media (min-width: 651px) {
  .halfbox       { width: 49%; }
}
@media (min-width: 1531px) {
  .addML_EC_Peso { margin-left: 7px; }
}
@media (min-width: 1416px) and (max-width: 1530px) { /* 115 */
  .addML_EC_Peso { margin-left: 6px; }
}
@media (min-width: 1301px) and (max-width: 1415px) { /* 115 */
  .addML_EC_Peso { margin-left: 5px; }
}
@media (min-width: 1224px) and (max-width: 1300px) { /* 77 */
  .addML_EC_Peso { margin-left: 4px; }
}
@media (min-width: 1032px) and (max-width: 1223px) { /* 192 */
  .addML_EC_Peso { margin-left: 3px; }
}
@media (min-width: 1025px) and (max-width: 1031px) { /* 7 */
  .addML_EC_Peso { margin-left: 2px; }
}
@media (min-width: 993px) and (max-width: 1024px)  { /* 32 */
  .addML_EC_Peso { margin-left: 4px; }
}
@media (min-width: 917px) and (max-width: 992px)   { /* 76 */
  .addML_EC_Peso { margin-left: 3px; }
}
@media (min-width: 766px) and (max-width: 916px)   { /* 151 */
  .addML_EC_Peso { margin-left: 2px; }
}
@media (min-width: 689px) and (max-width: 765px)   { /* 77 */
  .addML_EC_Peso { margin-left: 1px; }
}
@media (min-width: 651px) and (max-width: 688px)   { /* 38 */
  .addML_EC_Peso { margin-left: 0px; }
}

@media (min-width: 1548px) {
  .addML_EP_Peso { margin-left: 7px; }
  .addML_EP_Prod { margin-left: 7px; }
  .addML_SP_Peso { margin-left: 7px; }
  .addML_SP_Prod { margin-left: 7px; }
  .addML_SC_Peso { margin-left: 7px; }
  .addML_SC_Prod { margin-left: 13px; }
}
@media (min-width: 1433px) and (max-width: 1547px) {
  .addML_EP_Peso { margin-left: 6px; }
  .addML_EP_Prod { margin-left: 6px; }
  .addML_SP_Peso { margin-left: 6px; }
  .addML_SP_Prod { margin-left: 6px; }
  .addML_SC_Peso { margin-left: 6px; }
  .addML_SC_Prod { margin-left: 12px; }
}
@media (min-width: 1357px) and (max-width: 1432px) {
  .addML_EP_Peso { margin-left: 5px; }
  .addML_EP_Prod { margin-left: 5px; }
  .addML_SP_Peso { margin-left: 5px; }
  .addML_SP_Prod { margin-left: 5px; }
  .addML_SC_Peso { margin-left: 5px; }
  .addML_SC_Prod { margin-left: 11px; }
}
@media (min-width: 1203px) and (max-width: 1356px) {
  .addML_EP_Peso { margin-left: 4px; }
  .addML_EP_Prod { margin-left: 4px; }
  .addML_SP_Peso { margin-left: 4px; }
  .addML_SP_Prod { margin-left: 4px; }
  .addML_SC_Peso { margin-left: 4px; }
  .addML_SC_Prod { margin-left: 10px; }
}
@media (min-width: 1126px) and (max-width: 1202px) {
  .addML_EP_Peso { margin-left: 3px; }
  .addML_EP_Prod { margin-left: 3px; }
  .addML_SP_Peso { margin-left: 3px; }
  .addML_SP_Prod { margin-left: 3px; }
  .addML_SC_Peso { margin-left: 3px; }
  .addML_SC_Prod { margin-left: 9px; }
}
@media (min-width: 1025px) and (max-width: 1125px) {
  .addML_EP_Peso { margin-left: 2px; }
  .addML_EP_Prod { margin-left: 2px; }
  .addML_SP_Peso { margin-left: 2px; }
  .addML_SP_Prod { margin-left: 2px; }
  .addML_SC_Peso { margin-left: 2px; }
  .addML_SC_Prod { margin-left: 8px; }
}
@media (min-width: 934px) and (max-width: 1024px) {
  .addML_EP_Peso { margin-left: 4px; }
  .addML_EP_Prod { margin-left: 4px; }
  .addML_SP_Peso { margin-left: 3px; }
  .addML_SP_Prod { margin-left: 3px; }
  .addML_SC_Peso { margin-left: 3px; }
  .addML_SC_Prod { margin-left: 9px; }
}
@media (min-width: 884px) and (max-width: 933px) {
  .addML_EP_Peso { margin-left: 3px; }
  .addML_EP_Prod { margin-left: 3px; }
  .addML_SP_Peso { margin-left: 2px; }
  .addML_SP_Prod { margin-left: 2px; }
  .addML_SC_Peso { margin-left: 2px; }
  .addML_SC_Prod { margin-left: 8px; }
}
@media (min-width: 801px) and (max-width: 883px) {
  .addML_EP_Peso { margin-left: 2px; }
  .addML_EP_Prod { margin-left: 2px; }
  .addML_SP_Peso { margin-left: 1px; }
  .addML_SP_Prod { margin-left: 1px; }
  .addML_SC_Peso { margin-left: 1px; }
  .addML_SC_Prod { margin-left: 7px; }
}
@media (min-width: 703px) and (max-width: 800px) {
  .addML_EP_Peso { margin-left: 1px; }
  .addML_EP_Prod { margin-left: 1px; }
  .addML_SP_Peso { margin-left: 0px; }
  .addML_SP_Prod { margin-left: 0px; }
  .addML_SC_Peso { margin-left: 0px; }
  .addML_SC_Prod { margin-left: 7px; }
}
@media (min-width: 651px) and (max-width: 702px) {
  .addML_EP_Peso { margin-left: 0px; }
  .addML_EP_Prod { margin-left: 0px; }
  .addML_SP_Peso { margin-left: 0px; }
  .addML_SP_Prod { margin-left: 0px; }
  .addML_SC_Peso { margin-left: 0px; }
  .addML_SC_Prod { margin-left: 6px; }
}
@media (min-width: 621px) and (max-width: 650px) {
  .halfbox       { width: 48.5%; }
  .addML_EC_Peso { margin-left: 5px; }
  .addML_EP_Peso { margin-left: 5px; }
  .addML_SC_Peso { margin-left: 5px; }
  .addML_SP_Peso { margin-left: 5px; }
  .addML_EP_Prod { margin-left: 5px; }
  .addML_SP_Prod { margin-left: 5px; }
  .addML_SC_Prod { margin-left: 11px; }
}
@media (max-width: 620px) {
  .fullbloc      { width: 574px; overflow: hidden; }
  .halfbox       { width: 281px; }
  .addML_EC_Peso { margin-left: 0px; }
  .addML_EP_Peso { margin-left: 0px; }
  .addML_SC_Peso { margin-left: 0px; }
  .addML_SP_Peso { margin-left: 0px; }
  .addML_EP_Prod { margin-left: 0px; }
  .addML_SP_Prod { margin-left: 0px; }
  .addML_SC_Prod { margin-left: 6px; }
}

#area_transporte input[type="text"]     { width: 90%; }
#area_peso .pesotable                   { width: 97%; }
#area_peso td.pesofirsttd               { width: 80px; }
#area_peso td:nth-child(3)              { width: 1%; }
#area_botones .box                      { margin-top: -3px; padding-top: 8px; }

.box {
    border: groove gray 2px;
    background-color: rgba(0, 0, 0, 0.1);
    height: 85%;
    display: table-cell;
    min-width: 0;
}
.box table {
    border-spacing: 0px;
    border-collapse: separate;
    width: 100%;
}
.box td {
    padding: 0px;
    overflow: hidden;
    padding-bottom: 2px;
    vertical-align: top;
}
.tdcol4Name {
    width: 120px;
    padding-right: 10px;
}
.box table td:first-child:not(.noShrink) {
    padding-left: 2px;
    width: 1%;
    white-space:nowrap;
    text-align: left;
}
.box table td:nth-child(2) {
    padding-left: 2px;
    white-space:nowrap;
    text-align: left;
}
.box.usuario #nombreUsuario {
    width: 144px;
}
.box.usuario table td:nth-child(odd) {
    width: 107px;
    white-space:nowrap;
    padding: 2px;
}
.box.usuario table td {
    text-align: left;
}
.box.persona table td:nth-child(odd) {
    width: 107px;
    white-space:nowrap;
    padding: 2px;
}
.box.persona table td {
    text-align: left;
}
.box.perfil table {
    border-spacing: 20px 5px;
    border-collapse: separate;
}
.box.perfil table td {
    width: 1%;
    white-space:nowrap;
    text-align: center;
    display: table-cell;
    border: 1px groove lightgray;
    background-color: rgba(255, 255, 255, 0.5);

}
.box.perfil table td div {
    vertical-align: top;
    text-align: left;
    margin: 0 auto;
    padding-left: 5px;
    padding-right: 5px;
    display: inline-block;
    overflow: auto;
}
#accionesCell div p:first-child {
    margin-bottom: 0px;
    -webkit-margin-after: 0px;
}
#accionesCell div p:nth-child(2) {
    margin-top: 0px;
    -webkit-margin-before: 0px;
}
#accionesCell span {
    display: inline-block;
    text-align: center;
    width: 20px;
    margin-top: 0px;
}
.box.botones table td.centered {
    padding-left: 5px;
    padding-right: 5px;
    padding-top: 5px;
    text-align: center;
    margin: 0 auto;
}
.selectable:hover, p.relatedAction {
    background-color: rgba(100, 100, 255, 0.2);
}
p.relatedAction span.relatedAction {
    background-color: rgba(0, 255, 0, 0.5);
}
#dialog_tbody td:not(:last-of-type).sumaTipo {
    border-right: solid #ddf 2px;
}
#dialog_tbody td:not(:first-of-type).sumaTipo {
    border-left: solid #ddf 2px;
}
#dialog_tbody td.sumaTipo {
    font-weight: bold;
    background-color: #ddf;
    color: #005;
    white-space:nowrap;
}
#dialog_tbody td:not(:last-of-type).sumaDia {
    border-right: solid #fdd 2px;
}
#dialog_tbody td:not(:first-of-type).sumaDia {
    border-left: solid #fdd 2px;
}
#dialog_tbody td.sumaDia {
    font-weight: bold;
    background-color: #fdd;
    color: #500;
    white-space:nowrap;
}
#dialog_tbody td:not(:last-of-type).sumaTotal {
    border-right: solid #dfd 2px;
}
#dialog_tbody td:not(:first-of-type).sumaTotal {
    border-left: solid #dfd 2px;
}
#dialog_tbody td.sumaTotal {
    font-weight: bold;
    background-color: #dfd;
    color: #050;
    white-space:nowrap;
}
#dialog_tbody td.subtotal {
    border-top: solid gray 1px;
}
#transporte_field {
    text-transform: uppercase;
}
#chofer_field {
    text-transform: capitalize;
}
input {
    color: #000044;
}
input[type="text"], input[type="number"], input[type="password"], select {
    background-color: rgba(255, 255, 255, 0.7);
}
option {
    background-color: #f8f8f8;
}
input[type="text"].longtext, input[type="number"].longtext, input[type="password"].longtext {
    width: calc(100% - 8px);
}
input[type="text"].fullnametext, input[type="number"].fullnametext, input[type="password"].fullnametext {
    width: 350px;
}
input[type="text"].middletext, input[type="number"].middletext, input[type="password"].middletext {
    width: calc(50% - 8px);
}
input[type="text"].smalltext, input[type="number"].smalltext, input[type="password"].smalltext {
    width: 120px;
}
/*
input[type="submit"] {
    width: 120px;
}
*/
.vcenter {
    position: relative;
    top: 50%;
    transform: translateY(-50%);
}
.pesoField {
    margin-top: 3px; 
    padding: 2px 3px;
    width: 128px;
    font-size: 36px;
    text-align: center;
}
.inputtext {
    -moz-appearance: textfield;
    -webkit-appearance: textfield;
    color: #446;
    background-color: #DDDS; /* white; */
    background-color: -moz-field;
    border: 1px solid darkgray;
    box-shadow: 1px 1px 1px 0 lightgray inset;  
    font: -moz-field;
    font: -webkit-small-control;
    font-size: 36px;
    margin-top: 5px;
    padding: 2px 3px;
    width: 130px;
    display: inline;
    text-align: center;
}

.textarea {
    -moz-appearance: textfield-multiline;
    -webkit-appearance: textarea;
    border: 1px solid gray;
    font: medium -moz-fixed;
    font: -webkit-small-control;
    height: 28px;
    overflow: auto;
    padding: 2px;
    resize: both;
    width: 400px;
}
.no_selection {
    -webkit-user-select: none; /* webkit (safari, chrome) browsers */
    -moz-user-select: none; /* mozilla browsers */
    -khtml-user-select: none; /* webkit (konqueror) browsers */
    -ms-user-select: none; /* IE10+ */
}
#overlay {
    visibility: hidden;
    position: fixed;
    top: 0px;
    width:99%;
    margin: 0 auto;
    height:100%;
    text-align:center;
    z-index: 1000;
    font-size: 12px;
    background-color:  rgba(240, 240, 240, 0.5);
    vertical-align: middle;
}

#dialogbox {
    border:2px groove #333;
    width: 80%;
    margin: 0 auto;
    margin-top: 20px;
    background-image: url(../imagenes/fondos/fondo1.jpg);
    background-repeat: repeat;
    display: absolute;
    vertical-align: middle;
}
#close_row {
    background-color:  rgba(0, 0, 0, 0.1);
    border-bottom: 1px groove darkgray;
}
#dialog_resultarea {
    text-align: center;
    display: flex;
    align-items: center;
    float:none;
    display:block;
    clear:both;
    width: 100%;
}
#dialog_resultarea table:not(.noApply) {
    border-collapse: collapse;
    margin: 0 auto;
    width: 100%;
}
#recurso_area table:not(.noApply) {
    border-collapse: collapse;
    width: auto;
}
#dialog_resultarea td:not(.noApply), #dialog_resultarea th:not(.noApply) {
    padding: 3px;
    border-bottom: 1px solid #ddd;
    vertical-align: top;
}
#dialog_resultarea tbody tr:not(.noApply) {
    color: #557;
}
#dialog_resultarea tbody tr:nth-child(even):not(.noApply) {
    background-color:  rgba(240, 240, 200, 0.3);
}
#dialog_resultarea tbody tr:not(.nohover):hover {
    background-color: rgba(255, 255, 255, 0.3);
    color: #006;
}
#dialog_resultarea tr td:first-child:not(.noShrink) {
    width:1%;
    white-space:nowrap;
}
#dialog_resultarea tr td.shrinkCol {
    width:1%;
    white-space:nowrap;
}
#dialog_resultarea tbody td:not(.nohover):hover {
    background-color: rgba(255, 200, 200, 0.3);
}
#dialog_resultarea thead th, thead td, tfoot th, tfoot td {
    height: 10;
}
.resultarea:not(.nocenter) {
    text-align: center;
    // display: flex;
    align-items: center;
}
.resultarea table:not(.noApply), .puertosarea table:not(.noApply) {
    border-collapse: collapse;
    margin: 0 auto;
    width: 90%;
}
.resultarea td:not(.noApply), .resultarea th:not(.noApply) {
    padding: 3px;
    border-bottom: 1px solid #ddd;
    vertical-align: top;
}
.resultarea td.middle {
    padding: 3px;
    border-bottom: 1px solid #ddd;
    vertical-align: middle;
}
.resultarea tr td.shrinkCol, .shrinkCol {
    width:1%;
    white-space:nowrap;
}
.resultarea tr td:first-child:not(.noShrink) {
    width:1%;
    white-space:nowrap;
}
.resultarea tbody tr:not(.noApply):nth-child(even) {
    background-color: rgba(240, 240, 200, 0.3);
}
.resultarea tbody td:not(.nohover):hover {
    background-color: rgba(255, 200, 200, 0.3);
}
.resultarea tbody tr:not(.nohover):hover {
    background-color: rgba(255, 255, 255, 0.3);
    color: #006;
}
.resultarea tbody tr:not(.noApply) {
    color: #557;
}
.resultarea thead th, .resultarea thead td, .resultarea tfoot th, .resultarea tfoot td {
    height: 10;
}
.resultarea tbody tr:last-child th:not(.noApply) {
    height: 100%;
}

table.tableWithScrollableCells {
/*  table-layout: fixed; */
    width: 100%;
}

td div.scrollableCell {
  width: 100%;
  height: 100%;
  overflow: auto;
/* Definir estos parametros en el div o en otra clase, pues varian con el contenido
  max-width: 200px;
  max-height: 200px;
*/
}
#pie_version {
    font-size: 10px;
    width: 1px;
}
#pie_clock {
    width: 1px;
    font-weight:bold;
    font-size:12px;
    color:#006;
    -webkit-touch-callout: none;
    -webkit-user-select: none;
    -khtml-user-select: none;
    -moz-user-select: none;
    -ms-user-select: none;
    -o-user-select: none;
    user-select: none;
    pointer-events: none;
    cursor:not-allowed;
}
.optionalEditCheck {
    text-align: right;
    vertical-align:middle;
    width:1%;
    white-space:nowrap;
}
.optionalEditCheck span {
    margin-top: 10px;
    line-height: 20px;
    vertical-align: middle;
    white-space:nowrap;
    display: inline-block;
}
#pie_pagina div {
    vertical-align: middle;
    height: 100%;
    border-right: 2px groove gray;
    padding: 7px;
    display: table-cell;
    white-space: nowrap;
}
.vAlignParent {
    -webkit-transform-style: preserve-3d;
    -moz-transform-style: preserve-3d;
    transform-style: preserve-3d;
}
.searchicon {
    width: 18px;
    height: 18px;
    background-image: url('imagenes/searchicon18.png');
    cursor: pointer;
    display: inline-block;
    border: none;
    outline: none;
    line-height: 1;
    vertical-align: -3px;
}
.resourceAmount {
    display: inline-block;
    width: 170px;
    // border: 1px solid purple;
    text-align: right;
}
input[type="button"]:disabled {
    color: #999999;
}
.altafacturacell {
    white-space:nowrap;
    text-align: left;
    padding: 10px;
}
img.searchicon:hover {
    box-sizing:border-box;
    /* box-shadow: 0px 0px 10px yellow; */
    
    background: url('imagenes/searchicon18h.png') left top no-repeat;
}
.xmlerrorcell div {
    background-color: rgba(255,   0,   0, 0.1);
    font-weight: bold;
    margin-right: 5px;
}
tr.bottom-border {
    border-bottom: 3px solid red;
}
.scrolly {
    overflow: auto;
    height: calc(100% - 45px - 28px - 28px - 2px);
}
.screen {
    background-color: rgba(255, 255, 255, 0.3);
    border: 1px solid lightgray;
}
.pie_space {
    width: 100%;
}
.width80 {
    width: 100%;
}
.selector {
    height: 80%;
}
.vexpand {
    height: 100%;
}
.clear {
    float: none;
    clear: both;
}
.highlight {
    box-shadow: 0px 0px 10px yellow;
}
.navOverlayButton {
    visibility: hidden;
}
.hidden {
    display: none;
}
.shown {
    display: block;
}
.showntr {
    display: table-row;
}
.padding0 {
    padding: 0px;
}
.paddingbottom {
    padding-bottom: 5px;
}
.marginbottom {
    margin-bottom: 5px;
}
.marginbottom0 {
    margin-bottom: 0px;
}
.margintop {
    margin-top: 5px;
}
.margintop0 {
    margin-top: 0px;
}
.altafacturatable {
    margin: 0 auto;
}
.top, .optionalEditCheck img, .topvalign {
    vertical-align: top;
}
.vAlignCenter {
    vertical-align: middle;
}
.izquierdo {
    text-align: left;
}
.lefted {
    text-align: left;
    align: left;
}
tbody.centered td.righted, td.righted, .righted, .rightAligned {
    text-align: right;
}
.centered, tbody.centered td, thead.centered th {
    margin: 0 auto;
    text-align: center;
    align: center;
}
.nocenter {
    text-align: left;
    align: left;
}
.baseline {
    line-height: 1.2;
}
.wordwrap {
    white-space: pre-wrap;      /* CSS3 */   
    white-space: -moz-pre-wrap; /* Firefox */    
    white-space: -pre-wrap;     /* Opera <7 */   
    white-space: -o-pre-wrap;   /* Opera 7 */    
    word-wrap: break-word;      /* IE */
}
.nowrap {
    white-space:nowrap;
}
.bgwhite {
    background-color: rgba(255, 255, 255, 0.3);
}
.bgyellow {
    background-color: rgba(255, 255,   0, 0.1);
}
.bgmagenta {
    background-color: rgba(255,   0, 255, 0.1);
}
.bgred {
    background-color: rgba(255,   0,   0, 0.1);
}
.bgred2 {
    background-color: rgba(255, 100, 100, 0.2);
}
.bgcyan {
    background-color: rgba(  0, 255, 255, 0.1);
}
.bggreen {
    background-color: rgba(  0, 255,   0, 0.1);
}
.bgblue {
    background-color: rgba(  0,   0, 255, 0.1);
}
.bgblack {
    background-color: rgba(  0,   0,   0, 0.1);
}
.grayed {
    color: gray;
}
.redden {
    color: red;
}
.cancelLabel {
    color: darkred;
}
.cancelValue {
    color: red;
    font-weight: bold;
}
.importantLabel {
    color: rebeccapurple;
    font-weight: bold;
}
.importantValue {
    color: maroon;
    font-weight: bold;
}
.negativeValue {
    color: #800;
}
.boldValue, .footValue {
    font-weight: bold;
}
.fontPageFormat {
    font-size: 9px;
    font-weight: normal;
}
table.headertable, table.bodytable {
    border-collapse: collapse;
    width: 100%;
    border-spacing: 0px;
}
table.headertable th, table.bodytable td {
    padding: 0px;
}
table.headertable tr, table.bodytable tr {
    width: 100%;
}
.outer-container {
    position: absolute;
    top:0;
    left: 0;
    right: 300px;
    bottom:80px;
    overflow: visible;

}
.inner-container {
    width: 100%;
    height: 100%;
    position: relative;
}
.table-header {
    float:left;
    width: 100%;
    overflow:hidden;
}
.table-body {
    float:left;
    height: 100%;
    width: inherit;
    overflow-y: scroll;
    padding-right: 16px;
}
.header-cell {
    background-color: yellow;
    text-align: left;
    height: 40px;
}
.body-cell {
    text-align: left;
}
.col {
    width: 150px;
    min-width: 150px;
}
