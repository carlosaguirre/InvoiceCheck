<?php
global $generalJsVersion;
global $calendarConfJsVersion;
global $datePickerJsVersion;
global $polyfillVersion;
$generalJsVersion = "25.4y";
$calendarStyleCssVersion = "1.6";
$calendarConfJsVersion = "1.6";
$datePickerJsVersion = "1.6";
$polyfillVersion = "1.1";

function echoScript($basename,$type="Script") { // General | Calendar | Calendar Style | DatePicker | Polyfill
    $tracelog="echoScript basename='$basename', type='$type'";
    // doclog $tracelog
    switch($basename) {
        case "General": if ($type!=="Script") $basename=null; break;
        case "Calendar": if ($type!=="Script" && $type!=="Style") $basename=null; break;
        case "DatePicker": if ($type!=="Script") $basename=null; break;
        case "Polyfill": if ($type!=="Script") $basename=null; break;
        default: $basename=null;
    }
    if (!is_null($basename)) {
        $funcname="get{$basename}$type";
        echo "    ".$funcname()."\n";
    }
}
function echoGeneralScript() { echo "    ".getGeneralScript()."\n"; }
function echoPolyfillScript() { echo "    ".getPolyfillScript()."\n"; }
function getGeneralScript() {
    global $generalJsVersion,$waitImgName,$bkgdImgName;
    $backgroundScript=isset($waitImgName[0])?"<script type=\"text/javascript\">var _win_='$waitImgName';var _bin_='$bkgdImgName';</script>":"";
    return "{$backgroundScript}<script src=\"scripts/general.js?ver=$generalJsVersion\"></script>";
}
function getCalendarScript() {
    global $calendarConfJsVersion;
    return "<script src=\"scripts/calendar_conf.js?ver=$calendarConfJsVersion\"></script>";
}
function getCalendarStyle() {
    global $calendarStyleCssVersion;
    return "<link rel=\"stylesheet\" href=\"css/calendar-style.css?ver=$calendarStyleCssVersion\" type=\"text/css\"/>";
}
function getDatePickerScript() {
    global $datePickerJsVersion;
    return "<script src=\"scripts/date-picker.js?ver=$datePickerJsVersion\"></script>";
}
function getPolyfillScript() {
    global $polyfillVersion;
    return "<script src=\"scripts/classList.js?ver=$polyfillVersion\"></script>\n    <script src=\"scripts/polyfill.js?ver=$polyfillVersion\"></script>";
                  //      https://cdnjs.cloudflare.com/ajax/libs/classlist/1.2.20171210/classList.min.js
}
