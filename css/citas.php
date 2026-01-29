<?php
require_once dirname(__DIR__)."/bootstrap.php";
header("Content-type: text/css; charset: UTF-8");
?>
#cita_contenido {
    margin: 0 auto;
    max-width: 950px;
    padding: 0px;
}
#calendarCheck {
    position: fixed;
    bottom: 32px;
    left: 200px;
    width: calc(100% - 202px);
    height: 15px;
    border: 1px solid lightgray;
    color: gray;
    overflow: hidden;
    font-family: monospace;
    font-size: 9px;
    text-align: left;
    white-space: nowrap;
}
.cita_bloque {
    float: left;
    height: auto;
    width: 425px;
    border: 1px solid lightgray;
    border-radius: 2px;
    background-color: rgba(255,255,255,0.1);
    margin: 5px;
    padding: 5px;
}
.cita_bloque.alone {
    float: none;
    margin: 0 auto;
}
@media (max-width: 1070px) {
    .cita_bloque {
        float: none;
        margin: 0 auto;
    }
}

table.calendar_widget * {
    font-family: Tahoma, Arial, sans-serif; font-size: 14px; border-collapse: collapse;
}
table.calendar_widget th, table.calendar_widget td {
    text-align: center;
    height: 19px;
    user-select: none;
    -webkit-user-select: none; /* webkit (safari, chrome) browsers */
    -moz-user-select: none; /* mozilla browsers */
    -khtml-user-select: none; /* webkit (konqueror) browsers */
    -ms-user-select: none; /* IE10+ */
}
table.calendar_widget td.month_year_display {
    vertical-align: middle;
}
table.calendar_widget tr>.weekday {
    background-color: #f0eded;
}
table.calendar_widget tr>.weekend {
    background-color: #b0b0b0;
    color: #777;
}
table.calendar_widget td>span, table.calendar_widget th>span {
    display: inline-block;
    width: 35px;
    height: 100%;
    cursor: default;
    background-color: transparent;
}
table.calendar_widget td.nav:not(.pastday)>span {
    cursor: pointer;
}
table.calendar_widget td.nav.pastday>span {
    cursor: default;
    color: #777;
}
table.calendar_widget td.pastday>span.pastday {
    background-color: #d0d0d0;
    color: #777;
}
table.calendar_widget td>span.titled {
    outline: 3px solid rgba(255, 215, 0, .3);
}
table.calendar_widget td.weekday>span:not(.pastday):not(.other_month):not(.occupied) {
    cursor: pointer;
}
table.calendar_widget td.weekday>span.pastday, table.calendar_widget td.weekday>span.other_month, table.calendar_widget td.weekday>span.occupied {
    background-color: #d0d0d0;
    color: #777;
}
table.calendar_widget td.weekday>span:not(.pastday):not(.other_month):not(.occupied):hover {
    background-color: rgba(255,255,200,0.7);
}
table.calendar_widget td.weekday>span.pastday:hover, table.calendar_widget td.weekday>span.other_month:hover, table.calendar_widget td.weekday>span.occupied:hover {
    background-color: rgba(0,0,0,0.1);
}
table.calendar_widget td.weekend>span:hover {
    background-color: rgba(255,255,255,0.1);
}
table.calendar_widget td>span.today {
    -webkit-box-shadow: inset 0px 0px 10px 10px rgba(200,255,200,0.5);
    -moz-box-shadow: inset 0px 0px 10px 10px rgba(200,255,200,0.5);
    box-shadow: inset 0px 0px 10px 10px rgba(200,255,200,0.5);
}
table.calendar_widget th>span {
    border: 1px solid gray !important;
}
table.calendar_widget td>span:not(.current_selection) {
    border: 1px solid transparent !important;
}
table.calendar_widget td>span.current_selection {
    border: 1px dotted gray !important;
}
