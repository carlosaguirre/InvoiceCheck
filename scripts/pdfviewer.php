<?php
require_once dirname(__DIR__)."/bootstrap.php";
header("Content-type: application/javascript; charset: UTF-8");
clog2ini("scripts.pdfviewer");
clog1seq(1);
?>
console.log("PDFVIEWER SCRIPT READY!!!");
var pdfViewerState={pdf: null, currentPage: 1, zoom: 1, renderElementId: "pdf_renderer",currentPageId: "current_page",zoomInId:"zoom_in",zoomOutId:"zoom_out",prevPgId:"go_previous",nextPgId:"go_next",tries:1,timeoutDelay:500};
function viewPDF(pdfname,currPg) {
    if (!currPg) currPg=1;
    console.log("INI function viewPDF ( "+pdfname+", "+currPg+" )");
    let loadingTask = pdfjsLib.getDocument(pdfname);
    loadingTask.promise.then((pdf) => {
        console.log("IN getDocument promise");
        pdfViewerState.pdf = pdf;
        if (currPg<1) currPg=1;
        if (currPg>pdfViewerState.pdf._pdfInfo.numPages)
            currPg=pdfViewerState.pdf._pdfInfo.numPages;
        pdfViewerState.currentPage = currPg;
        pdfViewerState.zoom = 1;
        console.log("before render (getDocument)");
        render();
        console.log("after render (getDocument)");
        const currPgEl=document.getElementById(pdfViewerState.currentPageId);
        if (pdfViewerState.pdf._pdfInfo.numPages==1) {
            currPgEl.disabled=true;
            currPgEl.classList.add("disabled","no_selection");
        } else {
            currPgEl.disabled=false;
            currPgEl.classList.remove("disabled","no_selection");
        }
        currPgEl.max=pdfViewerState.pdf._pdfInfo.numPages;
        console.log("END getDocument promise");
    }).catch((ex)=>{
        console.log("Caught Exception in pdfjsLib.getDocument",ex);
        if (pdfViewerState.tries>0) {
            console.log("Retry "+pdfViewerState.tries+" after "+(pdfViewerState.timeoutDelay/1000)+" seconds");
            pdfViewerState.tries--;
            setTimeout(viewPDF,pdfViewerState.timeoutDelay,pdfname,currPg);
        } else {
            var canvas = document.getElementById(pdfViewerState.renderElementId);
            if (canvas.onfailrender) canvas.onfailrender(ex.message);
        }
    });
}
function clearCanvas() {
    pdfViewerState.pdf=null;
    pdfViewerState.currentPage=1;
    pdfViewerState.zoom=1;
    var canvas = document.getElementById(pdfViewerState.renderElementId);
    var ctx = canvas.getContext("2d");
    ctx.clearRect(0,0,canvas.width,canvas.height);
    delete canvas.width;
    delete canvas.height;
    //const currPgEl=document.getElementById(pdfViewerState.currentPageId);
    //const prevPgEl=document.getElementById(pdfViewerState.prevPgId);
    //const nextPgEl=document.getElementById(pdfViewerState.nextPgId);
}
function render() {
    pdfViewerState.pdf.getPage(pdfViewerState.currentPage).then((page) => {
        console.log("IN getPage task");
        var canvas = document.getElementById(pdfViewerState.renderElementId);
        var ctx = canvas.getContext("2d");
        var viewport = page.getViewport(pdfViewerState.zoom);
        canvas.width = viewport.width;
        canvas.height = viewport.height;
        console.log("before render (getPage)");
        page.render({canvasContext: ctx, viewport: viewport});
        console.log("after render (getPage)");
        const currPgEl=document.getElementById(pdfViewerState.currentPageId);
        currPgEl.value = pdfViewerState.currentPage;
        const prevPgEl=document.getElementById(pdfViewerState.prevPgId);
        if (pdfViewerState.currentPage==1) prevPgEl.classList.add("disabled");
        else prevPgEl.classList.remove("disabled");
        const nextPgEl=document.getElementById(pdfViewerState.nextPgId);
        if (pdfViewerState.currentPage==pdfViewerState.pdf._pdfInfo.numPages) nextPgEl.classList.add("disabled");
        else nextPgEl.classList.remove("disabled");
        if (canvas.onrender) canvas.onrender();
        console.log("END getPage task");
    });
}
function changePage(increment) {
    if (pdfViewerState.pdf == null) return;
    var nextPage=pdfViewerState.currentPage+increment;
    if (nextPage<1 || nextPage>pdfViewerState.pdf._pdfInfo.numPages) return;
    pdfViewerState.currentPage = nextPage;
    render();
}
function changeZoom(increment) {
    if (pdfViewerState.pdf == null) return;
    var calcZoom=pdfViewerState.zoom+increment;
    if (calcZoom<=0.2) return;
    pdfViewerState.zoom=calcZoom;
    const zi=document.getElementById(pdfViewerState.zoomInId);
    const zo=document.getElementById(pdfViewerState.zoomOutId);
    zi.title=""+Math.round(100*(pdfViewerState.zoom+Math.abs(increment)))+"%";
    zo.title=""+Math.round(100*(pdfViewerState.zoom-Math.abs(increment)))+"%";
    render();
}
function setPage(e) {
    if (pdfViewerState.pdf==null) return;
    var code = (e.keyCode ? e.keyCode : e.which);
    if (code==13) {
        var desiredPage=document.getElementById(pdfViewerState.currentPageId).valueAsNumber;
        if (desiredPage>=1 && desiredPage<=pdfViewerState.pdf._pdfInfo.numPages) {
            pdfViewerState.currentPage = desiredPage;
            document.getElementById(pdfViewerState.currentPageId).value=desiredPage;
            render();
        } else document.getElementById(pdfViewerState.currentPageId).value=pdfViewerState.currentPage;
    }
}
<?php
clog1seq(-1);
clog2end("scripts.pdfviewer");
