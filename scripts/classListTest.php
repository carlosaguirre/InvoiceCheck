<?php header("Content-type: application/javascript; charset: UTF-8"); ?>
console.log("CLASSLIST TEST SCRIPT READY!!!");
function fee(arrayLike, elemCallback) { // for each class element: callback(elem[,index,array])
    if (Array.from) Array.from(arrayLike).forEach(elemCallback);
    else [].forEach.call(arrayLike, elemCallback);
}
function ebyid(id) { // element by id
    return document.getElementById(id);
}
function lbycn(classname,baseElement,index) { // list by class name
    if (!baseElement) baseElement=document;
    let result=baseElement.getElementsByClassName(classname);
    if (typeof index === 'undefined') return result;
    if (result.length>index) return result[index];
    else return false;
}
function clfunc(elem,classname,funcname,params) {
    //console.log("INI clfunc <elem>, "+classname+", "+funcname+", ",params,elem);
    if (classname && elem) {
        if (Array.isArray(elem)) {
            let count=0; elem.forEach(subelem=>{ count+=clfunc(subelem,classname,funcname,params); }); return count;
        } if (elem instanceof NodeList||elem instanceof HTMLCollection) {
            let count=0; for (let n=0; n<elem.length; n++) { count+=clfunc(elem[n],classname,funcname,params); } return count;
        } if (typeof elem==="string") elem=ebyid(elem);
        if (elem.classList) {
            let neg=false;
            let sFuncname=false;
            if (funcname==="add") { funcname="contains"; sFuncname="add"; neg=true; }
            else if (funcname==="remove") { funcname="contains"; sFuncname="remove"; }
            else if (funcname==="set") {
                if (typeof params==="boolean") funcname="toggle";
                else return 0;
            }
            if (Array.isArray(classname)) {
                let count=0; fee(classname,cn=>{
                    const args=[cn]; if (typeof params!=="undefined") { if (Array.isArray(params)) args.push(...params); else args.push(params); }
                    if (!elem.classList[funcname](...args) != !neg) { // (A xor B) => (!A != !B)
                        if (sFuncname) elem.classList[sFuncname](...args);
                        count++;
                    }
                }); return count;
            }
            const args=[classname]; if (typeof params!=="undefined") { if (Array.isArray(params)) args.push(...params); else args.push(params); }
            let retval=elem.classList[funcname](...args);
            if (neg) retval=!retval;
            if (retval && sFuncname) elem.classList[sFuncname](...args);
            console.log("classlist."+funcname+"/"+sFuncname+"(",args,") = '"+retval+"'",elem);
            return retval?1:0;
        }
    }
    return 0;
}
function clhas(elem,classname, ...params) { return clfunc(elem,classname,"contains",params); }
function clfix(elem,classname, ...params) { return clfunc(elem,classname,"toggle",params); }
function cladd(elem,classname, ...params) { return clfunc(elem,classname,"add",params); }
function clrem(elem,classname, ...params) { return clfunc(elem,classname,"remove",params); }
function clset(elem,classname,boolval) { return clfunc(elem,classname,"set",boolval); }
function ekil(elem) {
    if(elem) {
        if(elem.parentNode) elem.parentNode.removeChild(elem);
        else delete elem;
    }
}
function txe(str,sep1,sep2) {
    if (!sep1) sep1="";
    if (sep1 && !sep2) sep2=sep1;
    return document.createTextNode(sep1+str+sep2);
}
<?php
