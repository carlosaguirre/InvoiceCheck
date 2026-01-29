<?php
require_once dirname(__DIR__)."/bootstrap.php";
require_once "clases/DBPDO.php";
abstract class PDOTable {
    var $pdo;
    var $tablename;
    var $rows_per_page;
    var $orderlist;
    function __construct() {
        $this->pdo=DBPDO::getInstance();
        $this->tablename = "dbobject";
        $this->rows_per_page = 0;
        $this->fieldlist = ["id", "codigo", "nombre"];
        $this->fieldlist["id"] = ["pkey"=>true,"auto"=>true];
        $this->fieldlist["modifiedTime"] = ["auto"=>true];
        $this->fieldlist["codigo"] = ["skey"=>true];
        $this->orderlist=["id"=>"desc"];
        doclog("NEW DB OBJECT","dbpdo",["tablename"=>$this->tablename,"fieldlist"=>$this->fieldlist]);
    }
    function clearOrder() {
        unset($this->orderlist);
    }
    function addOrder($fieldname, $direction="asc") {
        $this->orderlist[strtolower($fieldname)] = $direction;
    }
    function exists($where, $extraFrom="", $groupStr="") {
        global $query;
        $query="SELECT count(1) n FROM $this->tablename";
        if (isset($extraFrom[0])) $query.=" $extraFrom";
        if (isset($where[0])) $query.=" WHERE $where";
        if (isset($groupStr[0])) $query.=" GROUP BY $groupStr";

    }
}
