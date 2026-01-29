<?php
?>
<html xmlns="http://www.w3.org/1999/xhtml">
  <head>
    <meta charset="utf-8">
    <base href="<?= $_SERVER['HTTP_ORIGIN'] . $_SERVER['WEB_MD_PATH'] ?>" target="_blank">
    <title>JS Dyn Func Test</title>
    <script>
function mainfunc (func){
	console.log("INI mainfunc",arguments);
    this[func].apply(this, Array.prototype.slice.call(arguments, 1));
    console.log("END mainfunc");
}
function mainfuncTest(evt) {
	const cnt=document.getElementById('contenido');
	const args=['uno',' ','dos',' ','tres'];
	mainfunc.call(cnt,'append',...args);
}
function dispatch(fn, args) {
    fn = (typeof fn == "function") ? fn : window[fn];  // Allow fn to be a function object or the name of a global function
    return fn.apply(this, args || []);  // args is optional, use an empty array by default
}
function dispatchTest(evt) {
	const cnt=document.getElementById('contenido');
	const args=['alfa',' ','beta',' ','gama'];
	dispatch.call(cnt,cnt.append,args);
}
function delayed(func) { // function, seconds, function arguments
	console.log("INI delayed",arguments);
    return setTimeout(func,Array.prototype.slice.call(arguments, 1));
    console.log("END delayed");
}
function delayedTest(evt) {
	console.log("INI delayedTest",evt);
	const cnt=document.getElementById('contenido');
	const args=['uno',' ','dos',' ','tres'];
	console.log("Ready for delayed call");
	//delayed(mainfunc.call.bind(cnt),2000,cnt,'append',...args);
	//delayed(mainfuncTest,5000,evt);
	setTimeout(mainfuncTest,5000,cnt,'append',...args);
	console.log("END delayedTest");
}

/*
var that = this;
 setTimeout(function () {
     that.doStuff();
 }, 4000);

setTimeout(this.doStuff.bind(this), 4000);
*/
    </script>
  </head>
  <body>
    <h1>Prueba de funciones dinamicas</h1>
    <p>Push: <button id="go" type="button" onclick="delayedTest(event);">Mostrar</button></p>
    <div id="contenido">
    </div>
  </body>
</html>
