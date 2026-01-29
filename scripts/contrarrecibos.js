function ajaxRequest() {
    let activexmodes=["Msxml2.XMLHTTP", "Microsoft.XMLHTTP"];
    if (window.ActiveXObject) {
        for (let i=0; i<activexmodes.length; i++) {
            try {
                return new ActiveXObject(activexmodes[i]);
            } catch(e) {
            }
        }
    } else if (window.XMLHttpRequest) return new XMLHttpRequest();
    return false;
}
function isPrinted(id,uid,wmk,mdp) {
    console.log("IS PRINTED "+id+" '"+wmk+"'");
    let x=ajaxRequest();
    x.timeout = 3000;
    let fd = new FormData();
    fd.append("action", "isPrinted");
    fd.append("id", id);
    fd.append("uid", uid);
    fd.append("wmk",wmk);
    fd.append("mdp",mdp);
    x.open("POST", "consultas/Contrarrecibos.php", true);
    x.onload = function() {
        console.log("ACTION ISPRINTED ONLOAD");
        addToStoredArray("CR"+document.getElementById("folio").value, getNow()+" ISPRINTED LOAD",localStorage);
        setTimeout(()=>{location.reload(true);},100);
    };
    x.onerror = function() {
        console.log("ACTION ISPRINTED ONERROR");
        let dbcl=document.body.classList;
        let tt = document.getElementById("title-cr");
        dbcl.remove("ORIGINAL");
        dbcl.remove("ORIGINALPUE");
        dbcl.add("ERROR");
        tt.textContent="CONTRA-RECIBO";
        console.log("ERROR");
        addToStoredArray("CR"+document.getElementById("folio").value, getNow()+" ISPRINTED ERROR",localStorage);
    }
    x.onabort = function() {
        console.log("ACTION ISPRINTED ONABORT");
        let dbcl=document.body.classList;
        let tt = document.getElementById("title-cr");
        dbcl.remove("ORIGINAL");
        dbcl.remove("ORIGINALPUE");
        dbcl.add("ABORT");
        tt.textContent="CONTRA-RECIBO";
        addToStoredArray("CR"+document.getElementById("folio").value, getNow()+" ISPRINTED ABORT",localStorage);
        console.log("ABORT");
    }
    x.ontimeout = function() {
        addToStoredArray("CR"+document.getElementById("folio").value, getNow()+" ISPRINTED TIMEOUT",localStorage);
        console.log("ACTION ISPRINTED ONTIMEOUT");
        setTimeout(()=>{location.reload(true);},100);
    }
    x.send(fd);
}
function getNow() {
    var today = new Date();
    var date = today.getFullYear()+'-'+("0" + (today.getMonth()+1)).slice(-2)+'-'+("0" + today.getDate()).slice(-2);
    var time = ("0" + today.getHours()).slice(-2) + ":" + ("0" + today.getMinutes()).slice(-2) + ":" + ("0" + today.getSeconds()).slice(-2);
    return date+" "+time;
}
function load(val1,val2,val3) {
    var dateTime = getNow()+" "+document.getElementById("title-cr").textContent.substr(14)+" "+val3;
    const key="CR"+val1;
    addToStoredArray(key,dateTime,localStorage);
    setTimeout( (k,v1,v2) => {
        localStorage.get(k).then( val => {
            const arr = val?JSON.parse(val):[];
            console.log("LOAD '"+v1+"', '"+v2+"' ");
            arr.forEach((d,i)=>console.log((i+1)+") "+d))
        });
    },100,key,val1,val2);
    setTimeout(()=>{location.reload(true);},300000);
    if (typeof setVal==="function") setVal(val1+"-"+val2);
}
function addExtraInfo() {
    const pageHeight=880;
    const pixMod=100;
    Array.from(document.getElementsByClassName('contrablock')).forEach(elem=>{
        const dataElem=document.createElement("SPAN");
        const watermark=elem.getAttribute("wrmrk");
        const folio=elem.id;
        const codigo=elem.getAttribute("cdprv");
        const fecha=elem.getAttribute("fecha");
        const timestamp=elem.getAttribute("tmstp");
        const total=elem.getAttribute("total");
        const blockHeight=elem.offsetHeight;
        dataElem.textContent=timestamp+"\r\n"+watermark+"\r\n"+folio+"\r\n"+codigo+"\r\nREV "+fecha+"\r\n"+total;
        dataElem.style.position="absolute";
        dataElem.style.right="0px";
        dataElem.style.fontSize="9px";
        dataElem.style.whiteSpace="pre-line";
        let fixPixMod=0;
        let i=pageHeight;
        for (; i < blockHeight; i+=pageHeight) {
            fixPixMod+=pixMod;
            let top=i+fixPixMod;
            console.log('BLOCK '+folio+', HEIGHT='+elem.offsetHeight+" > "+i+" => "+top+"px");
            const dataElemI=dataElem.cloneNode(true);
            dataElemI.style.top=top+"px";
            elem.appendChild(dataElemI);
        }
        const heightDiff=i-blockHeight;

        const oneThird=293;
        if (heightDiff>oneThird) {
            const heiElem=document.createElement("DIV");
            let newHei=oneThird;
            if (heightDiff>(2*oneThird)) newHei+=oneThird;
            newHei+=(fixPixMod-100)/2;
            heiElem.style.height=newHei+"px";
            heiElem.textContent="\u00A0";
            elem.appendChild(heiElem);
        }
    });
}
function k(e) {
    if(e.parentNode) e.parentNode.removeChild(e);
}
// STORAGE
if (!Storage.prototype.get) {
    Storage.prototype.get = function(k) {
        const val=this.getItem(k);
        return new Promise((resolve,reject)=>{
            setTimeout((v)=>resolve(v),8,val);
        });
    };
}
if (!Storage.prototype.set) {
    Storage.prototype.set = function(k, v) {
            this.setItem(k,v);
    };
}
function addToStoredArray(key, item, storage) {
    storage.get(key).then(val => {
        const arr = val?JSON.parse(val):[];
        arr.push(item);
        storage.set(key, JSON.stringify(arr));
    });
}
function getFromStoredArray(key, storage, startIdx, num) {
    let resultVal="";
    return storage.get(key).then(val => {
        const arr = val?JSON.parse(val):[];
        if (!startIdx && startIdx!==0) startIdx=-1;
        if (!num && num!==0) num=1;
        if (num===0) resultVal=arr.slice(startIdx, startIdx+1);
        else {
            resultVal=arr.splice(startIdx, num);
            storage.set(key, JSON.stringify(arr));
        }
        if (resultVal.length==1) resultVal=resultVal[0];
        return new Promise((resolve,reject)=>{setTimeout((v)=>resolve(v),8,resultVal);});
    });
}
function clearStorage() {
    const folio=document.getElementById("folio").value;
    console.log("clearStorage CR"+folio);
    localStorage.removeItem("CR"+folio);
}
