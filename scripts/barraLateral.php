<?php
header("Content-type: application/javascript; charset: UTF-8");
$GLOBALS["_doDB"]=false;
$GLOBALS["season"]=false;
require_once dirname(__DIR__)."/bootstrap.php";
clog2ini("scripts.barraLateral");
clog1seq(1);
if (!$hasUser) { ?>
const markeeTime=100; // miliseconds
function ski() { // info
/*
    const logos=document.getElementsByClassName("logo");
    if (logos.length>0) {
        const parent=logos[0].parentNode;
        console.log("Parent OffsetTop="+parent.offsetTop+", ScrollTop="+parent.scrollTop+", Height="+parent.clientHeight);
    }
    [].forEach.call(logos, function(elem) {
        console.log(elem.id+" OffsetTop="+elem.offsetTop+", ScrollTop="+elem.scrollTop+", Height="+elem.clientHeight);
    });
*/
}
let markeeTimer=setTimeout(sk1, markeeTime);
function sk1() {
    const logos=document.getElementsByClassName("logo");
    if (logos.length>1) {
        const parent=logos[0].parentNode;
        const pTop=parent.scrollTop;
        let hgt2=logos[0].clientHeight;
        const space=logos[0].offsetTop-parent.offsetTop;
        hgt2+=space;
        if (pTop<hgt2) { // >
            parent.scrollTop+=1;
            //console.log("sk1 logosLen="+logos.length+", logosParent(scrTop)="+parent.id+"("+pTop+"=>"+parent.scrollTop+") < hgt="+hgt2);
        } else {
            logos[0].parentNode.appendChild(logos[0]);
            parent.scrollTop=space;
            //console.log("sk1 logosLen="+logos.length+", logosParent(scrTop)="+parent.id+"("+pTop+"=>"+parent.scrollTop+") >= hgt="+hgt2);
        }
        //if (parent.scrollTop%10==0) //console.log("SCROLLTOP="+pTop+" DISTANCE="+hgt2);
        markeeTimer=setTimeout(sk1, markeeTime);
    }
}
//let markeeTimer=setTimeout(sk2, markeeTime);
function sk2() {
    //console.log("sk(2)");
    const logos=document.getElementsByClassName("logo");
    if (logos.length>1) {
        const parent=logos[0].parentNode;
        const pTop=parent.scrollTop;
        const top2=logos[1].offsetTop;
        if (pTop<top2) { // >
            parent.scrollTop+=10;
            //console.log("SCROLLS "+pTop+" to "+parent.scrollTop+" < "+top2);
            markeeTimer=setTimeout(sk2, markeeTime);
        }
    }
}
<?php
} else {
// var sr=< ?=isset($_COOKIE["sessionRestart"][0])?$_COOKIE["sessionRestart"]:"0"? >;
// var me=< ?=json_encode($user)? >;
?>
var conScrTimer=null;
function startScroll(value) {
    scrollMenu(value);
    conScrTimer = setInterval(function(v){scrollMenu(v);},200,value);
}
function stopScroll() {
    if (conScrTimer) {
        clearInterval(conScrTimer);
        conScrTimer=null;
    }
}
function scrollMenu(value) {
    const mi=ebyid("menuinner");
    //console.log("INI function scrollMenu "+value+", scrollTop="+mi.scrollTop);
    const par=mi.parentNode;
    let ule=mi.firstElementChild;
    while(ule && ule.tagName!=="UL") ule=ule.nextElementSibling;
    if (!ule) {
        console.log("NO LIST");
        return;
    }
    const result = mi.scrollTop+value;
    //console.log("scrollTop="+mi.scrollTop+" + "+value+" = "+(result<0?0:result)+". miHeight="+mi.clientHeight+", ulHeight="+ule.clientHeight+", parHeight="+par.clientHeight);
    mi.scrollTop = result<0?0:(result>mi.clientHeight?mi.clientHeight:result);
    navCheck();
}
function wipeOff(evt) {
    //console.log("INI wipeOff");
    cladd("lado_izquierdo","noApply");
    setTimeout(()=>{clrem("lado_izquierdo","noApply");},300);
}
function reloadUser() {
    console.log("INI function reloadUser!");
    overlayWheel();
    postService(
        "consultas/Usuarios.php",
        {action:"reloadUser"},
        getPostRetFunc((jobj)=>{
            console.log("RELOAD RESULT!",jobj.usr);
            if (jobj.result==="success") location.reload(true);
            else overlayMessage(getParagraphObject(jobj.message),jobj.result.toUpperCase());
        },(errmsg,originalText)=>{
            overlayMessage(getParagraphObject(errmsg),"ERROR");
            postService("consultas/Errores.php",{accion:"savelog",nombre:"error",texto:"[R]"+errmsg,original:originalText});
            console.log(originalText);
        }),
        (errmsg, params, evt)=>{
            overlayMessage(getParagraphObject(errmsg),"ERROR");
            postService("consultas/Errores.php",{accion:"savelog",nombre:"error",texto:"[X]"+errmsg});
        }
    );
    console.log("END function reloadUser");
}
<?php
} ?>
function toggleSideMenuOld(toggleBtn) {
	if (toggleBtn) {
		let menuElem=toggleBtn.parentNode.nextElementSibling;
	    if (toggleBtn.classList.contains("expanded")) {
			toggleBtn.classList.remove("expanded");
			if (menuElem && menuElem.tagName==="UL" && !menuElem.classList.contains("hidden")) menuElem.classList.add("hidden");
		} else {
			toggleBtn.classList.add("expanded");
			if (menuElem && menuElem.tagName==="UL" && menuElem.classList.contains("hidden")) menuElem.classList.remove("hidden");
		}
	}
	event.preventDefault();
}
function toggleSideMenu(toggleBtn) {
	if (toggleBtn) {
		const menuElem=toggleBtn.nextElementSibling;
        const menuImg=toggleBtn.getElementsByTagName("IMG")[0];
		if (menuElem && menuElem.tagName==="UL") {
            if (menuElem.classList.contains("hidden")) {
                const uls=document.getElementsByTagName("UL");
                fee(uls, function(elem) { if (elem.id!=="top") cladd(elem,"hidden"); });
                fee(lbycn("esBloque"),function(el){clrem(el,"navSelected");});
                if (menuImg) menuImg.src="imagenes/icons/menuCollapse1.png";
            } else if (menuImg) menuImg.src="imagenes/icons/menuExpand.png";
            menuElem.classList.toggle("hidden");
            if (!menuElem.classList.contains("hidden")) cladd(toggleBtn,"navSelected");
        }
	}
}
function displaySideMenu(toggleBlk) {
    toggleFloatingMenu(toggleBlk,true);
}
function removeSideMenu(toggleBlk) {
    toggleFloatingMenu(toggleBlk,false);
}
function toggleFloatingMenu(toggleBlk,toShow) {
    if (toggleBlk) {
        const menuElems=toggleBlk.getElementsByTagName("UL");
        if (menuElems.length>0) {
            menuElem=menuElems[0];
            clset(menuElem,"hidden",!toShow);
// ToDo: la clase floating solo se debe agregar si button no tiene clase navSelected
            const prvSib=menuElem.previousElementSibling;
            if (prvSib.tagName==="BUTTON") {
                const menuImg=prvSib.getElementsByTagName("IMG")[0];
                if (menuImg) menuImg.src="imagenes/icons/menu"+(clhas(menuElem,"hidden")?"Expand":"Collapse1")+".png";
            }
            if (!clhas(prvSib,"navSelected"))
                clset(menuElem,"floating",toShow);
        }
    }
}
var mie = false; 
let pos = { top:0, y:0 };
function navIni() {
    //console.log("INI function navIni");
    navCheck();
    dragIni();
}
function navCheck() {
    //console.log("INI function navCheck");
    const mi=ebyid("menuinner");
    const smbl=lbycn("scrollSubMenuBtn");
    let ule=mi.firstElementChild;
    while(ule && ule.tagName!=="UL") ule=ule.nextElementSibling;
    if (ule) {
        const ms = mi.scrollTop;
        const dif = ule.clientHeight-mi.clientHeight;
        //console.log("scrollTop="+ms+", miHeight="+mi.clientHeight+", ulHeight="+ule.clientHeight+", difHeight="+dif);
        if (dif>0) {
            fee(smbl,function(b){
                clrem(b,"hidden");
                if (clhas(b,"isUP")) {
                    clset(b,"disabled",ms<=0);
                    //if (ms<=0&&!clhas(b,"hidden")) cladd(b,"hidden");
                    //if (ms>0&&clhas(b,"hidden")) clrem(b,"hidden");
                } else /*if (clhas(b,"isDW"))*/ {
                    clset(b,"disabled",(ms-32)>=(dif-1));
                    //if ((ms-32)>=(dif-1)&&!clhas(b,"hidden")) cladd(b,"hidden");
                    //if ((ms-32)<(dif-1)&&clhas(b,"hidden")) clrem(b,"hidden");
                }
            });
        } else {
            console.log("No Scroll");
            //fee(smbl,btn=>cladd(btn,"hidden"));
        }
    } else {
        console.log("No UList");
        //fee(smbl,btn=>cladd(btn,"hidden"));
    }
}
const dragIni = function() {
    //console.log("INI function dragIni.");
    mie=ebyid("menuinner");
    //cladd(mie,"grabbable");
    mie.onmousedown=dragStartHandler;
}
const dragStartHandler = function(e) {
    if (mie) {
        //console.log("INI function dragStartHandler pos(top:"+pos.top+", y:"+pos.y+"), scrollTop="+(Math.floor(mie.scrollTop*100)/100)+"/"+(mie.scrollTop-0.005).toFixed(2)+", clientY:"+e.clientY);
        //mie.style.cursor = 'grabbing';
        mie.style.userSelect = 'none';
        pos = { top: mie.scrollTop, y:e.clientY };
        document.addEventListener("mousemove",draggingHandler);
        document.addEventListener("mouseup",dragStopHandler);
        //console.log("END function dragStartHandler pos(top:"+pos.top+", y:"+pos.y+"), scrollTop="+(Math.floor(mie.scrollTop*100)/100)+"/"+(mie.scrollTop-0.005).toFixed(2)+", clientY:"+e.clientY);
    } else console.log("ERR function dragStartHandler: GRABBER NOT INITIALIZED", e);
};
const draggingHandler = function(e) {
    //console.log("INI function draggingHandler pos(top:"+pos.top+", y:"+pos.y+"), scrollTop="+(Math.floor(mie.scrollTop*100)/100)+"/"+(mie.scrollTop-0.005).toFixed(2)+", clientY:"+e.clientY);
    const dy = e.clientY - pos.y;
    mie.scrollTop = pos.top - dy;
    //console.log("END function draggingHandler pos(top:"+pos.top+", y:"+pos.y+"), scrollTop="+(Math.floor(mie.scrollTop*100)/100)+"/"+(mie.scrollTop-0.005).toFixed(2)+", clientY:"+e.clientY);
};
const dragStopHandler = function() {
    //console.log("INI function dragStopHandler pos(top:"+pos.top+", y:"+pos.y+"), scrollTop="+(Math.floor(mie.scrollTop*100)/100)+"/"+(mie.scrollTop-0.005).toFixed(2));
    //mie.style.cursor = 'grab';
    mie.style.removeProperty('user-select');
    document.removeEventListener("mousemove",draggingHandler);
    document.removeEventListener("mouseup",dragStopHandler);
};
<?php
clog1seq(-1);
clog2end("scripts.barraLateral");
