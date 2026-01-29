<?php
require_once dirname(__DIR__)."/bootstrap.php";
// Test Task
class TestTask {
    function __construct() {
      doclog("NEW INSTANCE","task");
    }
    function itest() {
      doclog("INSTANCE TEST","task");
    }
    static function stest() {
      doclog("STATIC TEST","task");
    }
}
