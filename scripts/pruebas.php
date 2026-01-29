<?php
require_once dirname(__DIR__)."/bootstrap.php";
/*
if(!hasUser()) {
    die("Empty File");
}
*/
header("Content-type: application/javascript; charset: UTF-8");
clog2ini("scripts.pruebas");
clog1seq(1);
?>
console.log("PRUEBAS SCRIPT READY!!!");
var massNum=0;
function massTest() {
    ekfil("result");
    const res=ebyid("result");
    const smm=ebyid("summary");
    progressService("consultas/ArchivosMul.php",{action:"massReqTest",inclusiveSeparator:"###"},(j,e)=>{ // rdyFunc
        let rowCount = res.querySelectorAll('TR').length;
        if (j.message!=="fullend") { massNum++; rowCount++; }
        const resTxt=JSON.stringify(j,jsonCircularReplacer());
        console.log("RDY JOBJ "+massNum+"/"+rowCount+": "+resTxt);
        res.appendChild(ecrea({eComment:"RDYFUNC "+massNum+"/"+rowCount}));
        switch(j.message) {
            case "row": res.appendChild(ecrea(getMassTestFileRowObj(j, "RDY", massNum, rowCount)));
                break;
            case "baseend":
                res.appendChild(ecrea({eComment:"RDY "+massNum+"/"+rowCount+" BASE "+j.baseIndex+" / BASE LEN="+j.baseTotN+" / FULL LEN="+j.fullTotN}));
                break;
            case "fullend":
                smm.appendChild(ecrea({eName:"TR", eChilds:[{eName:"TH", eText:""+rowCount},{eName:"TH",rowSpan:"2",eText:"RDY FULL LEN="+j.fullTotN}]}));
                break;
            default:
                res.appendChild(ecrea({eComment:j.message+": "+resTxt}));
        }
    }, (m, r, x)=> { // errFunc
        console.log("ERROR: "+m);
        console.log("TEXT: "+r);
        console.log("EXTRA: "+JSON.stringify(x,jsonCircularReplacer()));
    },(j,e)=>{ // prgFunc
        let rowCount = res.querySelectorAll('TR').length+1;
        massNum++;
        const resTxt=JSON.stringify(j,jsonCircularReplacer());
        console.log("PRG JOBJ "+massNum+"/"+rowCount+": "+resTxt);
        res.appendChild(ecrea({eComment:"PRGFUNC "+massNum+"/"+rowCount}));
        switch(j.message) {
            case "row": res.appendChild(ecrea(getMassTestFileRowObj(j, "PRG", massNum, rowCount)));
                break;
            case "baseend":
                res.appendChild(ecrea({eComment:"PRG "+massNum+"/"+rowCount+" BASE "+j.baseIndex+" / BASE LEN="+j.baseTotN+" / FULL LEN="+j.fullTotN}));
                break;
            case "fullend":
                smm.appendChild(ecrea({eName:"TR", eChilds:[{eName:"TH", eText:""+rowCount},{eName:"TH",rowSpan:"2",eText:"PRG FULL LEN="+j.fullTotN}]}));
                break;
            default:
                res.appendChild(ecrea({eComment:j.message+": "+resTxt}));
        }
    }
    );
}
function getInitials(text) {
    const words=text.split(" ");
    let initials="";
    for (let word of words) if (word.length>0) initials+=word.charAt(0);
    return initials;
}
function getMassTestFileRowObj(j, type, massNum, rowCount) {
    // j = baseIndex, baseName, globIndex, pdfData, txtName, txtData, fullTotN, baseTotN
    const txtData=j.txtData; // solLine, mesPago, fechaPago, cut, lineNum, newName, numFolio, processed, inserted, error, receiver, total, receiverAccount, payKey, payPage, payError
    const pdfData=j.pdfData; // pdfFileName, fileSize, pageCount, error, errData
    // const webPath=j.

    let info="";
    const oriName=""+j.baseName+(txtData.payKey?" "+txtData.payKey:"");
    const nspon = oriName.replace(/ /g, "");
    const nspfn=j.txtName.replace(/ /g, "");
    const docCell={eName:"TD", eChilds:[{}]};
    let txtFile=j.txtName;
    const dataCell={eName:"TD", eChilds:[{eText:getInitials(j.baseName)},{eText:"PyKy="+txtData.payKey+info}]};

    let fileName=j.baseName;
    let fileCut=fileName.substring(0, 3);
    let fileSize="";
    let className="";
    let solTitle="";
    if (txtData && txtData.solLine) {
        if (fileCut==="CPB") solTitle=txtData.solLine;
        else {
            fileName=txtData.solLine;
            fileCut="SOL";
        }
    }
    if (pdfData && pdfData.pdfFileName) {
        if ((fileCut==="CPB" || fileCut==="SOL") && fileName!==pdfData.pdfFileName) solTitle=fileName;
        fileName=pdfData.pdfFileName;
    }
    if (pdfData && pdfData.fileSize) fileSize=pdfData.fileSize;

    if (txtData && txtData.error) {
        className="bgred darkRedLabel";
        info=", XErr="+txtData.error;
    } else if (txtData && txtData.payError) {
        className="bgred darkRedLabel";
        info=", PyEr="+txtData.payError;
    } else if (pdfData && pdfData.error) {
        className="bgred darkRedLabel";
        info=", PDF Error="+pdfData.error;
        console.log("PDF ERROR: MassNum="+massNum+", RowCount="+rowCount+", BaseIndex="+j.baseIndex+", FileIndex="+j.globIndex+", PayKey="+txtData.payKey+", PDFError="+pdfData.error+", PDFErrData:",pdfData.errData);
    } else if (pdfData && pdfData.pageCount && pdfData.pageCount>1) {
        className="original bgblue";
        info=", PyCnt="+pdfData.pageCount;
    } else if (txtData && txtData.payPage) {
        info=", PyPg="+txtData.payPage;
    } else if (!pdfData) {
        className="bgred darkRedLabel";
        info=", NO PDFDATA";
    } else if (!txtData) {
        className="bgred darkRedLabel";
        info=", NO TXTDATA";
    }
    if (solTitle.length>0) fileCell.title=solTitle;
    const keyCap=type.slice(0,1)+j.result.slice(0,1).toUpperCase();
    return {eName:"TR", className:className, eChilds:[{eComment:JSON.stringify(j,jsonCircularReplacer())}, {eName:"TD", eText:""+rowCount+" "+keyCap, title:solTitle}, docCell, {eName:"TD", eText:"PyKy="+txtData.payKey+info}]};
}
<?php
clog1seq(-1);
clog2end("scripts.pruebas");
