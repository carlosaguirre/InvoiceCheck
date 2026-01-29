<?php
require_once dirname(__DIR__)."/bootstrap.php";
header("Content-type: text/css; charset: UTF-8");
?>
.bgResBSp {
    background-color: gold;
}
.swhid, .swhfecha, .swhdocs {
    display: inline-block;
    width: 100%;
    text-align: center;
}
.swhpNum {
    display: inline-block;
    text-align: left;
    width: 50px; 
}
.swhpPym {
    display: inline-block;
    margin: 0 auto;
    text-align: center;
    width: calc(50% - 25px);
}
.swhpIns {
    display: inline-block;
    text-align: right;
    width: calc(50% - 25px);
}
.swhpPym>span.cap, .swhpIns>span.cap {
    display: inline-block;
    text-align: right;
    width: 40px;
    padding-left: 4px;
    overflow: hidden;
    vertical-align: bottom;
}
.swhpPym>span.curr, .swhpIns>span.curr {
    display: inline-block;
    text-align: right;
    width: calc(100% - 40px);
    padding-right: 4px;
    vertical-align: bottom;
}
.swheDate {
    display: inline-block;
    /*white-space: nowrap;*/
    text-align: left;
    align: left;
    width: 42%;
}
.swheNum {
    display: inline-block;
    /*white-space: nowrap;*/
    margin: 0 auto;
    text-align: center;
    align: center;
    width:24%;
}
.swheCurr {
    display: inline-block;
    /*white-space: nowrap;*/
    text-align: right;
    width: 34%;
}
