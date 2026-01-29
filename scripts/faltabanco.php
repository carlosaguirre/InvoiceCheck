<?php
require_once dirname(__DIR__)."/bootstrap.php";
if (!hasUser()) die();
header("Content-type: application/javascript; charset: UTF-8");
clog2ini("scripts.faltabanco");
clog1seq(1);

$browser = getBrowser();
?>
function checkFile(elem) {
    if (!elem) return;
    let fileData = elem.files[0];
    if (!fileData.size) return;
    let size = +fileData.size;
    let type = fileData.type;
    let errmsg = false;
    if (type!=="application/pdf") errmsg = "<p>Se requiere que el documento sea un archivo con formato PDF</p>";
    if (size>2097000) {
        if (!errmsg) errmsg="";
        errmsg+="<p>El archivo excede el tamaño máximo permitido de 2MB</p>";
    }
    if (errmsg) {
        overlayMessage(errmsg,"Error");
        let newElem = document.createElement("input");
        newElem.type = "file";
        newElem.id = elem.id;
        newElem.name = elem.name;
        newElem.size = "35";
        newElem.onchange = function() { checkFile(newElem); };
        elem.parentNode.replaceChild(newElem, elem);
    } else {
        cladd(ebyid(elem.id+'_doc'),'hidden');
    }
}
function viewImageSample(title) {
    console.log("INI function viewImageSample");
    overlayMessage("<br><img src='imagenes/caratulaEdoCta.jpg' width='600' height='300'><br>",title);
}
function validaDatos(event) {
    console.log("INI function validaDatos ",event);
    let emptyFields=[];
    let email=ebyid("user_email").value;
    if (email.length==0) emptyFields.push("correo electr&oacute;nico");
    let bank=ebyid("prov_bank").value;
    if (bank.length==0) emptyFields.push("raz&oacute;n social");
    let bankrfc=ebyid("prov_bankrfc").value;
    if (bankrfc.length==0) emptyFields.push("RFC del banco");
    let account=ebyid("prov_account").value;
    if (account.length==0) emptyFields.push("cuenta CLABE").value;
    let receipt=ebyid("prov_receipt").value;
    if (receipt.length==0) {
        let rcptNmElem=ebyid("prov_receipt_name");
        if (!rcptNmElem ||rcptNmElem.value.length==0)
            emptyFields.push("car&aacute;tula de estado de cuenta");
    }
    if (emptyFields.length>2) {
        overlayMessage("<p>Se requieren los <b><u>cinco campos</u></b> para formalizar el proceso de pago, gracias por su comprensi&oacute;n.</p>","FALTAN DATOS");
        return eventCancel(event);
    }
    if (emptyFields.length>0) {
        let errmsg="Es necesario que proporcione su <b><u>"+emptyFields[0];
        if (emptyFields.length>1) errmsg+="</u></b> y <b><u>"+emptyFields[1];
        errmsg+="</u></b> para formalizar el proceso de pago, gracias por su comprensi&oacute;n.";
        overlayMessage("<p>"+errmsg+"</p>","FALTAN DATOS");
        return eventCancel(event);
    }
    if (account.length<10) {
        overlayMessage("<p>Su n&uacute;mero de cuenta no puede tener menos de 10 d&iacute;gitos.");
        return eventCancel(event);
    }
    if (account.length>11&&account.length!=18) {
        overlayMessage("<p>Su Cuenta CLABE debe tener 18 d&iacute;gitos. Si ingresa una clave bancaria debe tener 10 u 11 d&iacute;gitos.");
        return eventCancel(event);
    }

    return true;
}
function verifyDate(tgtWdgt) {
    console.log("INI function verifyDate",tgtWdgt);
    let iniDateElem = document.getElementById("fechaOpinion");
    let endDateElem = document.getElementById("fechaVencimiento");

    let iniday = strptime(date_format, iniDateElem.value);
    let endday = strptime(date_format, endDateElem.value);
    let displayYear=display_day.getFullYear();
    let displayMonth=display_day.getMonth();
    let displayDate=display_day.getDate();
    let iniYear=iniday.getFullYear();
    let iniMonth=iniday.getMonth();
    let iniDate=iniday.getDate();
    let isGreater=(iniYear>displayYear);
    if (iniYear===displayYear) {
        isGreater=(iniMonth>displayMonth);
        if (iniMonth===displayMonth) isGreater=(iniDate>displayDate);
    }
    if (isGreater) {
        iniDateElem.value=strftime(date_format, display_day);
        iniday = display_day;
    }
    endDateElem.value=strftime(date_format, add_days(iniday,90));
}
function accountCheck(event) {
    if (event.type==="input"&&event.data&&event.data.length>0&&event.data.match(/[^0-9]/)) {
        console.log("non digit "+event.type+": '"+event.data+"'");
        let idx=event.target.selectionStart-event.data.length;
        event.target.value=event.target.value.replace(/\D/g,'');
        event.target.selectionStart=idx;
        event.target.selectionEnd=idx;
        return false;
    } else if (event.type==="paste") {
        let paste = (event.clipboardData || window.clipboardData).getData('text');
        if (paste.length>0 && paste.match(/[^0-9]/)) {
            console.log("non digit "+event.type+": '"+paste+"'");
            event.preventDefault();
            return false;
        }
    }
    return true;
}
<?php
clog1seq(-1);
clog2end("scripts.faltabanco");
