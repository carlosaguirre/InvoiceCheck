<?php
require_once dirname(__DIR__)."/bootstrap.php";
header("Content-type: text/css; charset: UTF-8");
//clog2ini("cuentas.style");
?>
@charset "utf-8";
/* CSS Document */
#encabezado.cuentas {
    height: 40px;
}
#bloque_cuentas {
    width: 100%;
    height: calc(100% - 73px);
}
#admin_block {
    width: 100%;
    height: 212px;
    margin: 0 auto;
    text-align: center;
}
.prv_section {
    vertical-align: top;
    width: 396px;
    margin: 0 auto;
    text-align: left;
    background-color: rgba(255, 255, 255, 0.3);
    padding: 7px 0 7px 0;
    border-left: solid 5px rgba(0,0,0,0);
    border-right: solid 7px rgba(0,0,0,0);
    overflow: hidden;
}
#proveedor_nombre_archivo_recibo {
    white-space: nowrap;
    vertical-align: middle;
    overflow: hidden;
    text-overflow: ellipsis;
    width: 297px;
}
.fileCell {
    white-space: nowrap;
    padding: 2px;
    width: 100%;
}
#manage_block {
    width: 100%;
    height: 100%;
}
#manage_block_adm {
    width: 100%;
    height: calc(100% - 212px);
}
#manage_block h2, #manage_block_adm h2 {
    height: 25px;
    margin: 0 auto;
}
#selector_account {
    height: 27px; /* +14 de padding (7 y 7) = 41px */
}
#result_account {
    height: calc(100% - 80px); /* 25 + 41 + 14 (header y selector y padding) */
    overflow: auto;
}
#result_account tbody tr:nth-child(even) {
    background-color:  rgba(240, 240, 200, 0.3);
}
#result_account tbody tr:hover {
    background-color: rgba(255, 255, 255, 0.5);
}
#result_account tr td:first-child {
    width:1%;
    white-space:nowrap;
}
#result_account tr td {
    font-size: 13px;
}

<?php
//clog2end("cuentas.style");
