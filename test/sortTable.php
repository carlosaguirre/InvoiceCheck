<?php
?>
<html xmlns="http://www.w3.org/1999/xhtml">
  <head>
    <meta charset="utf-8">
    <base href="http://invoicecheck.dyndns-web.com:81/invoice/" target="_blank">
    <title>Sort Table Test</title>
    <link href="css/general.php" rel="stylesheet" type="text/css">
    <script>
		function fee(arrayLike, elemCallback, funcname) {
		    if (Array.from) {
		        if (funcname) Array.from(arrayLike)[funcname](elemCallback);
		        else Array.from(arrayLike).forEach(elemCallback);
		    } else if (funcname) [][funcname].call(arrayLike, elemCallback);
		    else [].forEach.call(arrayLike, elemCallback);
		}
		function lbycn(classname,baseElement,index) {
		    if (!baseElement) baseElement=document;
		    if (Array.isArray(classname)) {
		        const resPack=[];
		        fee(classname,oneclass=>{let res=lbycn(oneclass,baseElement,index);if(res)fee(res,ares=>{if(!resPack.includes(ares))resPack.push(ares);})});
		        return resPack;
		    }
		    const result=baseElement.getElementsByClassName(classname);
		    if (typeof index === 'undefined') return result;
		    if (result.length>index) return result[index];
		    else return false;
		}
		function ebyid(id) { return document.getElementById(id); }
		function cladd(elem,classname, ...params) { return clfunc(elem,classname,"add",params); }
		function clrem(elem,classname, ...params) { return clfunc(elem,classname,"remove",params); }
		function clfunc(elem,classname,funcname,params) {
		    if (classname && elem) {
		        if (typeof elem==="string") elem=ebyid(elem); // si no lo encuentra elem sería null
		        else if (Array.isArray(elem)) {
		            let count=0; elem.forEach(subelem=>{ count+=clfunc(subelem,classname,funcname,params); }); return count;
		        } else if (elem instanceof NodeList||elem instanceof HTMLCollection) {
		            let count=0; for (let n=0; n<elem.length; n++) { count+=clfunc(elem[n],classname,funcname,params); } return count;
		        }
		        if (elem && elem.classList) {
		            let neg=false;
		            let sFuncname=false;
		            if (funcname==="add") { funcname="contains"; sFuncname="add"; neg=true; }
		            else if (funcname==="remove") { funcname="contains"; sFuncname="remove"; }
		            else if (funcname==="set") {
		                if (typeof params==="boolean") funcname="toggle";
		                else return 0;
		            } else if (funcname==="replace") {
		                let retval=0;
		                let baseLen=elem.classList.length;
		                if (Array.isArray(classname)) elem.classList.remove(...classname);
		                else elem.classList.remove(classname);
		                retval=baseLen-elem.classList.length;
		                baseLen=elem.classList.length;
		                if (Array.isArray(params)) elem.classList.add(...params);
		                else elem.classList.add(params);
		                retval+=elem.classList.length-baseLen;
		                return retval;
		            }
		            if (Array.isArray(classname)) {
		                let count=0; fee(classname,cn=>{
		                    const args=[cn]; if (typeof params!=="undefined") { if (Array.isArray(params)) args.push(...params); else args.push(params); }
		                    if (!elem.classList[funcname](...args) != !neg) {
		                        if (sFuncname) {
		                            elem.classList[sFuncname](...args);
		                            //console.log("+*",sFuncname,"(",...args,")",elem);
		                        } //else console.log("+|",funcname,"(",...args,")",elem);
		                        count++;
		                    }
		                }); return count;
		            }
		            const args=[classname]; if (typeof params!=="undefined") { if (Array.isArray(params)) args.push(...params); else args.push(params); }
		            let retval=elem.classList[funcname](...args);
		            if (neg) retval=!retval;
		            if (retval && sFuncname) {
		                elem.classList[sFuncname](...args);
		                //console.log(".*",sFuncname,"(",...args,")",elem);
		            } //else if (retval) console.log(".|",funcname,"(",...args,")",elem);
		            return retval?1:0;
		        }
		    }
		    return 0;
		}
		function checkAll(evt,cls) {
		    const tgt=evt.target;
		    fee(lbycn(cls), el=>el.checked=tgt.checked);
		}
		var numericIndex=[6];
		var currencyIndex=["$","usd","eur"];
		function sortTable(evt) {
		    let tgt = evt.target;
		    let tbl = tgt.closest('table');
		    let tbd = tbl.querySelector('tbody');
		    let columnIndex = ""+tgt.cellIndex;

		    if (!tbl.coln || tbl.coln!==columnIndex) {
		    	tbl.coln=""+columnIndex;
		    	tbl.drct="1";
		    } else {
			    if (tbl.drct==="0") tbl.drct="1";
			    else if (tbl.drct==="1") tbl.drct="-1";
			    else tbl.drct="0";
		    }
		    console.log("INI sortTable ["+tbl.drct+","+tbl.coln+"]");

		    let rows = Array.from(tbd.rows); // Omitir la fila de encabezado

		    rows.sort((rowA, rowB) => {
		        let cellA = rowA.cells[columnIndex].textContent.trim().toLowerCase();
		        let cellB = rowB.cells[columnIndex].textContent.trim().toLowerCase();
		        if (tbl.drct==="1") { // ascending
		        	if (numericIndex.includes(+tbl.coln)) {
		        		let currA=cellA.slice(0,3);
		        		if (cellA.slice(0,1)==="$") currA="$";
		        		else if (!currencyIndex.includes(currA)) currA=false;
		        		let currB=cellB.slice(0,3);
		        		if (cellB.slice(0,1)==="$") currB="$";
		        		else if (!currencyIndex.includes(currB)) currB=false;
		        		if ((currA===false&&currB!==false) || currA<currB) return -1;
		        		if ((currA!==false&&currB===false) || currA>currB) return 1;
		        		cellA=+cellA.replace(',','').replace('$','').replace('usd','').replace('eur','').trim();
		        		cellB=+cellB.replace(',','').replace('$','').replace('usd','').replace('eur','').trim();
			        	if (cellA<cellB) return -1;
			        	if (cellA>cellB) return 1;
				        return 0;
		        	}
		        	console.log("Compare str "+cellA+" vs "+cellB);
		        	return cellA.localeCompare(cellB);
		        } else if (tbl.drct==="-1") { // descending
		        	if (numericIndex.includes(+tbl.coln)) {
		        		let currA=cellA.slice(0,3);
		        		if (cellA.slice(0,1)==="$") currA="$";
		        		else if (!currencyIndex.includes(currA)) currA=false;
		        		let currB=cellB.slice(0,3);
		        		if (cellB.slice(0,1)==="$") currB="$";
		        		else if (!currencyIndex.includes(currB)) currB=false;
		        		if ((currA!==false&&currB===false) || currA>currB) return -1;
		        		if ((currA===false&&currB!==false) || currA<currB) return 1;
		        		cellA=+cellA.replace(',','').replace('$','').replace('usd','').replace('eur','').trim();
		        		cellB=+cellB.replace(',','').replace('$','').replace('usd','').replace('eur','').trim();
			        	if (cellA<cellB) return 1;
			        	if (cellA>cellB) return -1;
				        return 0;
		        	}
		        	console.log("Compare str "+cellA+" vs "+cellB);
		        	return cellB.localeCompare(cellA);
		        } else {
		        	cellA = +rowA.getAttribute("idx");
		        	cellB = +rowB.getAttribute("idx");
		        	console.log("Compare idx "+cellA+" vs "+cellB);
		        	if (cellA<cellB) return -1;
		        	if (cellA>cellB) return 1;
			        return 0;
		        }
		    });
		    rows.forEach(row => tbd.appendChild(row));
		}
		document.addEventListener('DOMContentLoaded', function() {
			let firstTable=false;
			fee(lbycn("sortH"), th=>{ if (!firstTable) firstTable=th.closest('table'); th.addEventListener('click', sortTable); cladd(th,'pointer'); });
			firstTable.coln=false; firstTable.drct="0";
		});
    </script>
  </head>
  <body class="blank">
	<table class="lstpago separate0 cellborder1">
	    <thead><tr idx="0"><th class="sticky toLeft zIdx4 basicBG sortH">Folio</th><th class="sortH">Empresa</th><th class="sortH">Prov.</th><th class="sortH">Orden o Factura</th><th class="sortH">Ini.Sol.</th><th class="sortH">Pago</th><th class="sortH">Importe</th><th class="sortH">Usuario</th>
	        <th>Autoriza</th><th class="sticky toRight zIdx3 bxslft basicBG vAlignCenter sortH">Status <input type="checkbox" class="topvalign noprint" onclick="checkAll(event,'pymchk');"></th>
	    </tr></thead>
	    <tbody>
	    <tr idx="1" id="row22001" class="payBlock" folid="APS2505-030" prcid="2" sttid="63" typid="F.1" gpoid="2" prvid="180" auis="2641" tot="108289.020" mon="MXN"><td class="sticky toLeft zIdx2 basicBG"><div class="wrap100 vAlignCenter btnLt bRad2 pointer" onclick="console.log('viewForm 22001');">APS2505-030</div> </td>
	        <td title="ACABADOS DE PAPELES SATINADOS Y ABSORBENTES"><div class="wid70px">APSA</div></td>
	        <td title="COMISION FEDERAL DE ELECTRICIDAD"><div class="wid48px">C-055</div></td>
	        <td><div class="wid100px lefted" title="000302392456"><span class="pre">F ...302392456</span></div><div class="wid48px noprint"><a href="archivos/APSA/2025/05/CFE370814QI0_0302392456.xml" target="archivo"><img src="imagenes/icons/xml200.png" width="20" height="20"></a> <a href="archivos/APSA/2025/05/ST_0302392456CFE370814QI0.pdf" target="archivo" onclick="console.log('rompeSello 22001');"><img src="imagenes/icons/pdf200S3.png" width="20" height="20"></a></div></td>
	        <td><div class="wid77px">2025-05-09</div></td>
	        <td><div class="wid77px">2025-05-09</div></td>
	        <td><div class="wid135px righted">$108,289.02</div></td>
	        <td><div class="wid100px" title="Marisol San Juan">marisols</div></td>
	        <td><div class="wid100px" title="Marcos Lobatón Abadi">mlobatonapsa</div></td>
	        <td class="sticky toRight zIdx1 bxslft basicBG"><div class="wid135px centered vAlignCenter"><button type="button" class="bgbtnIO" onclick="console.log('ANEXAR COMPROBANTE 22001');" onmousedown="cladd(this,'pressed');" onmouseup="clrem(this,'pressed');" onmouseleave="clrem(this,'pressed');" title="ANEXAR COMPROBANTE"><img src="imagenes/icons/invChk200.png" width="20" height="20" style="filter:grayscale(1) brightness(0.8) contrast(2.5)"></button><button type="button" class="bgbtnIO" onclick="console.log('MARCAR PAGADA 22001');" onmousedown="cladd(this,'pressed');" onmouseup="clrem(this,'pressed');" onmouseleave="clrem(this,'pressed');" title="MARCAR PAGADA"><img src="imagenes/icons/invoiceIcon.png" width="20" height="20" style="filter:grayscale(1) brightness(0.8) contrast(2.5)"></button><button type="button" class="bgbtnIO" onclick="console.log('CANCELAR 22001');" onmousedown="cladd(this,'pressed');" onmouseup="clrem(this,'pressed');" onmouseleave="clrem(this,'pressed');" title="CANCELAR"><img src="imagenes/icons/deleteIcon20.png" width="20" height="20"></button> <input type="checkbox" id="chk22001" solid="22001" class="pymchk noprint vAlignCenter"></div></td>
	    </tr>
	    <tr idx="2" id="row21993" class="payBlock" folid="SKA2505-014" prcid="0" sttid="2" typid="O.1" gpoid="22" prvid="3429" auis="2640" tot="154669.000" mon="MXN"><td class="sticky toLeft zIdx2 basicBG"><div class="wrap100 vAlignCenter btnLt bRad2 pointer" onclick="console.log('viewForm 21993');">SKA2505-014</div> </td>
	        <td title="SKARTON"><div class="wid70px">SKARTON</div></td>
	        <td title="AGENCIA ADUANAL DEL VALLE SURESTE"><div class="wid48px">A-371</div></td>
	        <td><div class="wid100px lefted" title="25VZ1011I"><span class="pre">O 25VZ1011I</span></div><div class="wid48px noprint"> <a href="archivos/SKARTON/2025/05/ST_ord25VZ1011I_22_3429.pdf" target="archivo" onclick="console.log('rompeSello 21993');"><img src="imagenes/icons/pdf200S3.png" width="20" height="20"></a></div></td>
	        <td><div class="wid77px">2025-05-09</div></td>
	        <td><div class="wid77px">2025-05-09</div></td>
	        <td><div class="wid135px righted">$154,669.00</div></td>
	        <td><div class="wid100px" title="Diego Reyes Olvera">importaciones1</div></td>
	        <td><div class="wid100px" title="Francisco Garabana">fgarabana</div></td>
	        <td class="sticky toRight zIdx1 bxslft basicBG"><div class="wid135px centered vAlignCenter"><button type="button" class="bgbtnIO" onclick="console.log('ANEXAR COMPROBANTE 21993');" onmousedown="cladd(this,'pressed');" onmouseup="clrem(this,'pressed');" onmouseleave="clrem(this,'pressed');" title="ANEXAR COMPROBANTE"><img src="imagenes/icons/invChk200.png" width="20" height="20" style="filter:grayscale(1) brightness(0.8) contrast(2.5)"></button><button type="button" class="bgbtnIO" onclick="console.log('MARCAR PAGADA 21993');" onmousedown="cladd(this,'pressed');" onmouseup="clrem(this,'pressed');" onmouseleave="clrem(this,'pressed');" title="MARCAR PAGADA"><img src="imagenes/icons/invoiceIcon.png" width="20" height="20" style="filter:grayscale(1) brightness(0.8) contrast(2.5)"></button><button type="button" class="bgbtnIO" onclick="console.log('CANCELAR 21993');" onmousedown="cladd(this,'pressed');" onmouseup="clrem(this,'pressed');" onmouseleave="clrem(this,'pressed');" title="CANCELAR"><img src="imagenes/icons/deleteIcon20.png" width="20" height="20"></button> <input type="checkbox" id="chk21993" solid="21993" class="pymchk noprint vAlignCenter"></div></td>
	    </tr>
	    <tr idx="3" id="row21992" class="payBlock" folid="JYL2505-006" prcid="0" sttid="2" typid="O.1" gpoid="6" prvid="14" auis="2641" tot="10000.000" mon="MXN"><td class="sticky toLeft zIdx2 basicBG"><div class="wrap100 vAlignCenter btnLt bRad2 pointer" onclick="console.log('viewForm 21992');">JYL2505-006</div> </td>
	        <td title="PAPELES Y MAQUILAS NACIONALES JYL"><div class="wid70px">JYL</div></td>
	        <td title="ADMINISTRADORA DE INMUEBLES IXTAPALUCA S.A. DE C.V."><div class="wid48px">A-020</div></td>
	        <td><div class="wid100px lefted" title="080525"><span class="pre">O 080525</span></div><div class="wid48px noprint"> <a href="archivos/JYL/2025/05/ST_ord080525_6_14.pdf" target="archivo" onclick="console.log('rompeSello 21992');"><img src="imagenes/icons/pdf200S3.png" width="20" height="20"></a></div></td>
	        <td><div class="wid77px">2025-05-09</div></td>
	        <td><div class="wid77px">2025-05-09</div></td>
	        <td><div class="wid135px righted">$10,000.00</div></td>
	        <td><div class="wid100px" title="Olivia Gomez">oliviag</div></td>
	        <td><div class="wid100px" title="Marcos Lobatón Abadi">mlobatonapsa</div></td>
	        <td class="sticky toRight zIdx1 bxslft basicBG"><div class="wid135px centered vAlignCenter"><button type="button" class="bgbtnIO" onclick="console.log('ANEXAR COMPROBANTE 21992');" onmousedown="cladd(this,'pressed');" onmouseup="clrem(this,'pressed');" onmouseleave="clrem(this,'pressed');" title="ANEXAR COMPROBANTE"><img src="imagenes/icons/invChk200.png" width="20" height="20" style="filter:grayscale(1) brightness(0.8) contrast(2.5)"></button><button type="button" class="bgbtnIO" onclick="console.log('MARCAR PAGADA 21992');" onmousedown="cladd(this,'pressed');" onmouseup="clrem(this,'pressed');" onmouseleave="clrem(this,'pressed');" title="MARCAR PAGADA"><img src="imagenes/icons/invoiceIcon.png" width="20" height="20" style="filter:grayscale(1) brightness(0.8) contrast(2.5)"></button><button type="button" class="bgbtnIO" onclick="console.log('CANCELAR 21992');" onmousedown="cladd(this,'pressed');" onmouseup="clrem(this,'pressed');" onmouseleave="clrem(this,'pressed');" title="CANCELAR"><img src="imagenes/icons/deleteIcon20.png" width="20" height="20"></button> <input type="checkbox" id="chk21992" solid="21992" class="pymchk noprint vAlignCenter"></div></td>
	    </tr>
	    <tr idx="4" id="row21991" class="payBlock" folid="APS2505-028" prcid="2" sttid="63" typid="F.1" gpoid="2" prvid="162" auis="2639" tot="614.220" mon="MXN"><td class="sticky toLeft zIdx2 basicBG"><div class="wrap100 vAlignCenter btnLt bRad2 pointer" onclick="console.log('viewForm 21991');">APS2505-028</div> </td>
	        <td title="ACABADOS DE PAPELES SATINADOS Y ABSORBENTES"><div class="wid70px">APSA</div></td>
	        <td title="CARVAJAL TECNOLOGIA Y SERVICIOS, S.A. DE C.V."><div class="wid48px">C-015</div></td>
	        <td><div class="wid100px lefted" title="456168"><span class="pre">F 456168</span></div><div class="wid48px noprint"><a href="archivos/APSA/2025/05/LEV031201SE6_456168.xml" target="archivo"><img src="imagenes/icons/xml200.png" width="20" height="20"></a> <a href="archivos/APSA/2025/05/ST_456168LEV031201SE6.pdf" target="archivo" onclick="console.log('rompeSello 21991');"><img src="imagenes/icons/pdf200S3.png" width="20" height="20"></a></div></td>
	        <td><div class="wid77px">2025-05-09</div></td>
	        <td><div class="wid77px">2025-05-09</div></td>
	        <td><div class="wid135px righted">$614.22</div></td>
	        <td><div class="wid100px" title="Monserrat Lopez">monsel</div></td>
	        <td><div class="wid100px" title="Jaime Lobatón">jlobaton</div></td>
	        <td class="sticky toRight zIdx1 bxslft basicBG"><div class="wid135px centered vAlignCenter"><button type="button" class="bgbtnIO" onclick="console.log('ANEXAR COMPROBANTE 21991');" onmousedown="cladd(this,'pressed');" onmouseup="clrem(this,'pressed');" onmouseleave="clrem(this,'pressed');" title="ANEXAR COMPROBANTE"><img src="imagenes/icons/invChk200.png" width="20" height="20" style="filter:grayscale(1) brightness(0.8) contrast(2.5)"></button><button type="button" class="bgbtnIO" onclick="console.log('MARCAR PAGADA 21991');" onmousedown="cladd(this,'pressed');" onmouseup="clrem(this,'pressed');" onmouseleave="clrem(this,'pressed');" title="MARCAR PAGADA"><img src="imagenes/icons/invoiceIcon.png" width="20" height="20" style="filter:grayscale(1) brightness(0.8) contrast(2.5)"></button><button type="button" class="bgbtnIO" onclick="console.log('CANCELAR 21991');" onmousedown="cladd(this,'pressed');" onmouseup="clrem(this,'pressed');" onmouseleave="clrem(this,'pressed');" title="CANCELAR"><img src="imagenes/icons/deleteIcon20.png" width="20" height="20"></button> <input type="checkbox" id="chk21991" solid="21991" class="pymchk noprint vAlignCenter"></div></td>
	    </tr>
	    <tr idx="5" id="row21990" class="payBlock" folid="SKA2505-013" prcid="2" sttid="63" typid="F.1" gpoid="22" prvid="481" auis="2640" tot="619.170" mon="MXN"><td class="sticky toLeft zIdx2 basicBG"><div class="wrap100 vAlignCenter btnLt bRad2 pointer" onclick="console.log('viewForm 21990');">SKA2505-013</div> </td>
	        <td title="SKARTON"><div class="wid70px">SKARTON</div></td>
	        <td title="HENCO GLOBAL SA DE CV"><div class="wid48px">H-028</div></td>
	        <td><div class="wid100px lefted" title="385671"><span class="pre">F 385671</span></div><div class="wid48px noprint"><a href="archivos/SKARTON/2025/05/HGL980417J43_385671.xml" target="archivo"><img src="imagenes/icons/xml200.png" width="20" height="20"></a> <a href="archivos/SKARTON/2025/05/ST_385671HGL980417J43.pdf" target="archivo" onclick="console.log('rompeSello 21990');"><img src="imagenes/icons/pdf200S3.png" width="20" height="20"></a></div></td>
	        <td><div class="wid77px">2025-05-09</div></td>
	        <td><div class="wid77px">2025-05-09</div></td>
	        <td><div class="wid135px righted">$619.17</div></td>
	        <td><div class="wid100px" title="Diego Reyes Olvera">importaciones1</div></td>
	        <td><div class="wid100px" title="Francisco Garabana">fgarabana</div></td>
	        <td class="sticky toRight zIdx1 bxslft basicBG"><div class="wid135px centered vAlignCenter"><button type="button" class="bgbtnIO" onclick="console.log('ANEXAR COMPROBANTE 21990');" onmousedown="cladd(this,'pressed');" onmouseup="clrem(this,'pressed');" onmouseleave="clrem(this,'pressed');" title="ANEXAR COMPROBANTE"><img src="imagenes/icons/invChk200.png" width="20" height="20" style="filter:grayscale(1) brightness(0.8) contrast(2.5)"></button><button type="button" class="bgbtnIO" onclick="console.log('MARCAR PAGADA 21990');" onmousedown="cladd(this,'pressed');" onmouseup="clrem(this,'pressed');" onmouseleave="clrem(this,'pressed');" title="MARCAR PAGADA"><img src="imagenes/icons/invoiceIcon.png" width="20" height="20" style="filter:grayscale(1) brightness(0.8) contrast(2.5)"></button><button type="button" class="bgbtnIO" onclick="console.log('CANCELAR 21990');" onmousedown="cladd(this,'pressed');" onmouseup="clrem(this,'pressed');" onmouseleave="clrem(this,'pressed');" title="CANCELAR"><img src="imagenes/icons/deleteIcon20.png" width="20" height="20"></button> <input type="checkbox" id="chk21990" solid="21990" class="pymchk noprint vAlignCenter"></div></td>
	    </tr>
	    <tr idx="6" id="row21987" class="payBlock" folid="APS2505-027" prcid="2" sttid="63" typid="F.1" gpoid="2" prvid="3806" auis="2639" tot="232000.020" mon="MXN"><td class="sticky toLeft zIdx2 basicBG"><div class="wrap100 vAlignCenter btnLt bRad2 pointer" onclick="console.log('viewForm 21987');">APS2505-027</div> </td>
	        <td title="ACABADOS DE PAPELES SATINADOS Y ABSORBENTES"><div class="wid70px">APSA</div></td>
	        <td title="SISTEMAS INTEGRALES DE SEGURIDAD PRIVADA Y VIGILANCIA FRIMEX"><div class="wid48px">S-380</div></td>
	        <td><div class="wid100px lefted" title="8267"><span class="pre">F 8267</span></div><div class="wid48px noprint"><a href="archivos/APSA/2025/05/SIS070403UX3_8267.xml" target="archivo"><img src="imagenes/icons/xml200.png" width="20" height="20"></a> <a href="archivos/APSA/2025/05/ST_8267SIS070403UX3.pdf" target="archivo" onclick="console.log('rompeSello 21987');"><img src="imagenes/icons/pdf200S3.png" width="20" height="20"></a></div></td>
	        <td><div class="wid77px">2025-05-09</div></td>
	        <td><div class="wid77px">2025-05-09</div></td>
	        <td><div class="wid135px righted">$232,000.02</div></td>
	        <td><div class="wid100px" title="Monserrat Lopez">monsel</div></td>
	        <td><div class="wid100px" title="Jaime Lobatón">jlobaton</div></td>
	        <td class="sticky toRight zIdx1 bxslft basicBG"><div class="wid135px centered vAlignCenter"><button type="button" class="bgbtnIO" onclick="console.log('ANEXAR COMPROBANTE 21987');" onmousedown="cladd(this,'pressed');" onmouseup="clrem(this,'pressed');" onmouseleave="clrem(this,'pressed');" title="ANEXAR COMPROBANTE"><img src="imagenes/icons/invChk200.png" width="20" height="20" style="filter:grayscale(1) brightness(0.8) contrast(2.5)"></button><button type="button" class="bgbtnIO" onclick="console.log('MARCAR PAGADA 21987');" onmousedown="cladd(this,'pressed');" onmouseup="clrem(this,'pressed');" onmouseleave="clrem(this,'pressed');" title="MARCAR PAGADA"><img src="imagenes/icons/invoiceIcon.png" width="20" height="20" style="filter:grayscale(1) brightness(0.8) contrast(2.5)"></button><button type="button" class="bgbtnIO" onclick="console.log('CANCELAR 21987');" onmousedown="cladd(this,'pressed');" onmouseup="clrem(this,'pressed');" onmouseleave="clrem(this,'pressed');" title="CANCELAR"><img src="imagenes/icons/deleteIcon20.png" width="20" height="20"></button> <input type="checkbox" id="chk21987" solid="21987" class="pymchk noprint vAlignCenter"></div></td>
	    </tr>
	    <tr idx="7" id="row21986" class="payBlock" folid="COR2505-006" prcid="0" sttid="2" typid="O.1" gpoid="18" prvid="1903" auis="2641" tot="7782.440" mon="MXN"><td class="sticky toLeft zIdx2 basicBG"><div class="wrap100 vAlignCenter btnLt bRad2 pointer" onclick="console.log('viewForm 21986');">COR2505-006</div> </td>
	        <td title="COREPACK"><div class="wid70px">COREPACK</div></td>
	        <td title="OPERADORA DE FERIAS Y EXPOSICIONES S.A. DE C.V."><div class="wid48px">O-056</div></td>
	        <td><div class="wid100px lefted" title="0266EG"><span class="pre">O 0266EG</span></div><div class="wid48px noprint"> <a href="archivos/COREPACK/2025/05/ord0266EG_18_1903.pdf" target="archivo"><img src="imagenes/icons/pdf200.png" width="20" height="20"></a></div></td>
	        <td><div class="wid77px">2025-05-09</div></td>
	        <td><div class="wid77px">2025-05-09</div></td>
	        <td><div class="wid135px righted">$7,782.44</div></td>
	        <td><div class="wid100px" title="Rosaura Cruz">revisionapsa</div></td>
	        <td><div class="wid100px" title="Marcos Lobatón Abadi">mlobatonapsa</div></td>
	        <td class="sticky toRight zIdx1 bxslft basicBG"><div class="wid135px centered vAlignCenter"><button type="button" class="bgbtnIO" onclick="console.log('ANEXAR COMPROBANTE 21986');" onmousedown="cladd(this,'pressed');" onmouseup="clrem(this,'pressed');" onmouseleave="clrem(this,'pressed');" title="ANEXAR COMPROBANTE"><img src="imagenes/icons/invChk200.png" width="20" height="20" style="filter:grayscale(1) brightness(0.8) contrast(2.5)"></button><button type="button" class="bgbtnIO" onclick="console.log('MARCAR PAGADA 21986');" onmousedown="cladd(this,'pressed');" onmouseup="clrem(this,'pressed');" onmouseleave="clrem(this,'pressed');" title="MARCAR PAGADA"><img src="imagenes/icons/invoiceIcon.png" width="20" height="20" style="filter:grayscale(1) brightness(0.8) contrast(2.5)"></button><button type="button" class="bgbtnIO" onclick="console.log('CANCELAR 21986');" onmousedown="cladd(this,'pressed');" onmouseup="clrem(this,'pressed');" onmouseleave="clrem(this,'pressed');" title="CANCELAR"><img src="imagenes/icons/deleteIcon20.png" width="20" height="20"></button> <input type="checkbox" id="chk21986" solid="21986" class="pymchk noprint vAlignCenter"></div></td>
	    </tr>
	    <tr idx="8" id="row21980" class="payBlock" folid="APS2505-026" prcid="0" sttid="2" typid="O.1" gpoid="2" prvid="1903" auis="2641" tot="7782.440" mon="MXN"><td class="sticky toLeft zIdx2 basicBG"><div class="wrap100 vAlignCenter btnLt bRad2 pointer" onclick="console.log('viewForm 21980');">APS2505-026</div> </td>
	        <td title="ACABADOS DE PAPELES SATINADOS Y ABSORBENTES"><div class="wid70px">APSA</div></td>
	        <td title="OPERADORA DE FERIAS Y EXPOSICIONES S.A. DE C.V."><div class="wid48px">O-056</div></td>
	        <td><div class="wid100px lefted" title="0257EG"><span class="pre">O 0257EG</span></div><div class="wid48px noprint"> <a href="archivos/APSA/2025/05/ord0257EG_2_1903.pdf" target="archivo"><img src="imagenes/icons/pdf200.png" width="20" height="20"></a></div></td>
	        <td><div class="wid77px">2025-05-08</div></td>
	        <td><div class="wid77px">2025-05-08</div></td>
	        <td><div class="wid135px righted">$7,782.44</div></td>
	        <td><div class="wid100px" title="Rosaura Cruz">revisionapsa</div></td>
	        <td><div class="wid100px" title="Marcos Lobatón Abadi">mlobatonapsa</div></td>
	        <td class="sticky toRight zIdx1 bxslft basicBG"><div class="wid135px centered vAlignCenter"><button type="button" class="bgbtnIO" onclick="console.log('ANEXAR COMPROBANTE 21980');" onmousedown="cladd(this,'pressed');" onmouseup="clrem(this,'pressed');" onmouseleave="clrem(this,'pressed');" title="ANEXAR COMPROBANTE"><img src="imagenes/icons/invChk200.png" width="20" height="20" style="filter:grayscale(1) brightness(0.8) contrast(2.5)"></button><button type="button" class="bgbtnIO" onclick="console.log('MARCAR PAGADA 21980');" onmousedown="cladd(this,'pressed');" onmouseup="clrem(this,'pressed');" onmouseleave="clrem(this,'pressed');" title="MARCAR PAGADA"><img src="imagenes/icons/invoiceIcon.png" width="20" height="20" style="filter:grayscale(1) brightness(0.8) contrast(2.5)"></button><button type="button" class="bgbtnIO" onclick="console.log('CANCELAR 21980');" onmousedown="cladd(this,'pressed');" onmouseup="clrem(this,'pressed');" onmouseleave="clrem(this,'pressed');" title="CANCELAR"><img src="imagenes/icons/deleteIcon20.png" width="20" height="20"></button> <input type="checkbox" id="chk21980" solid="21980" class="pymchk noprint vAlignCenter"></div></td>
	    </tr>
	    <tr idx="9" id="row21978" class="payBlock" folid="APS2505-025" prcid="0" sttid="2" typid="O.1" gpoid="2" prvid="2541" auis="2639" tot="3855.000" mon="USD"><td class="sticky toLeft zIdx2 basicBG"><div class="wrap100 vAlignCenter btnLt bRad2 pointer" onclick="console.log('viewForm 21978');">APS2505-025<div class="abs_se badge" title="Tiene Antecedentes">+</div></div> </td>
	        <td title="ACABADOS DE PAPELES SATINADOS Y ABSORBENTES"><div class="wid70px">APSA</div></td>
	        <td title="IMPORTACIONES"><div class="wid48px">I-999</div></td>
	        <td><div class="wid100px lefted" title="ZY20250507"><span class="pre">O ZY20250507</span></div><div class="wid48px noprint"> <a href="archivos/APSA/2025/05/ST_ordZY20250507_2_2541.pdf" target="archivo" onclick="console.log('rompeSello 21978');"><img src="imagenes/icons/pdf200S3.png" width="20" height="20"></a></div></td>
	        <td><div class="wid77px">2025-05-08</div></td>
	        <td><div class="wid77px">2025-05-08</div></td>
	        <td><div class="wid135px righted">USD&nbsp;3,855.00</div></td>
	        <td><div class="wid100px" title="Rosaura Cruz">revisionapsa</div></td>
	        <td><div class="wid100px" title="Marcos Lobatón Abadi">mlobatonapsa</div></td>
	        <td class="sticky toRight zIdx1 bxslft basicBG"><div class="wid135px centered vAlignCenter"><button type="button" class="bgbtnIO" onclick="console.log('ANEXAR COMPROBANTE 21978');" onmousedown="cladd(this,'pressed');" onmouseup="clrem(this,'pressed');" onmouseleave="clrem(this,'pressed');" title="ANEXAR COMPROBANTE"><img src="imagenes/icons/invChk200.png" width="20" height="20" style="filter:grayscale(1) brightness(0.8) contrast(2.5)"></button><button type="button" class="bgbtnIO" onclick="console.log('MARCAR PAGADA 21978');" onmousedown="cladd(this,'pressed');" onmouseup="clrem(this,'pressed');" onmouseleave="clrem(this,'pressed');" title="MARCAR PAGADA"><img src="imagenes/icons/invoiceIcon.png" width="20" height="20" style="filter:grayscale(1) brightness(0.8) contrast(2.5)"></button><button type="button" class="bgbtnIO" onclick="console.log('CANCELAR 21978');" onmousedown="cladd(this,'pressed');" onmouseup="clrem(this,'pressed');" onmouseleave="clrem(this,'pressed');" title="CANCELAR"><img src="imagenes/icons/deleteIcon20.png" width="20" height="20"></button> <input type="checkbox" id="chk21978" solid="21978" class="pymchk noprint vAlignCenter"></div></td>
	    </tr>
	    <tr idx="10" id="row21977" class="payBlock" folid="CAP2505-004" prcid="2" sttid="63" typid="F.1" gpoid="25" prvid="3909" auis="2639" tot="358133.220" mon="MXN"><td class="sticky toLeft zIdx2 basicBG"><div class="wrap100 vAlignCenter btnLt bRad2 pointer" onclick="console.log('viewForm 21977');">CAP2505-004</div> </td>
	        <td title="CAPITAL HALL"><div class="wid70px">CAPITALH</div></td>
	        <td title="LEVY CHARUA MOISES"><div class="wid48px">L-156</div></td>
	        <td><div class="wid100px lefted" title="17"><span class="pre">F 17</span></div><div class="wid48px noprint"><a href="archivos/CAPITALH/2025/05/LECM8101168X1_17.xml" target="archivo"><img src="imagenes/icons/xml200.png" width="20" height="20"></a> <a href="archivos/CAPITALH/2025/05/17LECM8101168X1.pdf" target="archivo"><img src="imagenes/icons/pdf200.png" width="20" height="20"></a></div></td>
	        <td><div class="wid77px">2025-05-08</div></td>
	        <td><div class="wid77px">2025-05-08</div></td>
	        <td><div class="wid135px righted">$358,133.22</div></td>
	        <td><div class="wid100px" title="Monserrat Lopez">monsel</div></td>
	        <td><div class="wid100px" title="Jaime Lobatón">jlobaton</div></td>
	        <td class="sticky toRight zIdx1 bxslft basicBG"><div class="wid135px centered vAlignCenter"><button type="button" class="bgbtnIO" onclick="console.log('ANEXAR COMPROBANTE 21977');" onmousedown="cladd(this,'pressed');" onmouseup="clrem(this,'pressed');" onmouseleave="clrem(this,'pressed');" title="ANEXAR COMPROBANTE"><img src="imagenes/icons/invChk200.png" width="20" height="20" style="filter:grayscale(1) brightness(0.8) contrast(2.5)"></button><button type="button" class="bgbtnIO" onclick="console.log('MARCAR PAGADA 21977');" onmousedown="cladd(this,'pressed');" onmouseup="clrem(this,'pressed');" onmouseleave="clrem(this,'pressed');" title="MARCAR PAGADA"><img src="imagenes/icons/invoiceIcon.png" width="20" height="20" style="filter:grayscale(1) brightness(0.8) contrast(2.5)"></button><button type="button" class="bgbtnIO" onclick="console.log('CANCELAR 21977');" onmousedown="cladd(this,'pressed');" onmouseup="clrem(this,'pressed');" onmouseleave="clrem(this,'pressed');" title="CANCELAR"><img src="imagenes/icons/deleteIcon20.png" width="20" height="20"></button> <input type="checkbox" id="chk21977" solid="21977" class="pymchk noprint vAlignCenter"></div></td>
	    </tr>
	    <tr idx="11" id="row21976" class="payBlock" folid="APS2505-024" prcid="2" sttid="63" typid="F.1" gpoid="2" prvid="3897" auis="2641" tot="3129.680" mon="MXN"><td class="sticky toLeft zIdx2 basicBG"><div class="wrap100 vAlignCenter btnLt bRad2 pointer" onclick="console.log('viewForm 21976');">APS2505-024<div class="abs_se badge" title="Tiene Antecedentes">+</div></div> </td>
	        <td title="ACABADOS DE PAPELES SATINADOS Y ABSORBENTES"><div class="wid70px">APSA</div></td>
	        <td title="LIZARDI CASTRO GUSTAVO"><div class="wid48px">L-155</div></td>
	        <td><div class="wid100px lefted" title="3238"><span class="pre">F 3238</span></div><div class="wid48px noprint"><a href="archivos/APSA/2025/05/LICG9510011M8_3238.xml" target="archivo"><img src="imagenes/icons/xml200.png" width="20" height="20"></a> <a href="archivos/APSA/2025/05/ST_3238LICG9510011M8.pdf" target="archivo" onclick="console.log('rompeSello 21976');"><img src="imagenes/icons/pdf200S3.png" width="20" height="20"></a></div></td>
	        <td><div class="wid77px">2025-05-08</div></td>
	        <td><div class="wid77px">2025-05-08</div></td>
	        <td><div class="wid135px righted">$3,129.68</div></td>
	        <td><div class="wid100px" title="Marisol San Juan">marisols</div></td>
	        <td><div class="wid100px" title="Marcos Lobatón Abadi">mlobatonapsa</div></td>
	        <td class="sticky toRight zIdx1 bxslft basicBG"><div class="wid135px centered vAlignCenter"><button type="button" class="bgbtnIO" onclick="console.log('ANEXAR COMPROBANTE 21976');" onmousedown="cladd(this,'pressed');" onmouseup="clrem(this,'pressed');" onmouseleave="clrem(this,'pressed');" title="ANEXAR COMPROBANTE"><img src="imagenes/icons/invChk200.png" width="20" height="20" style="filter:grayscale(1) brightness(0.8) contrast(2.5)"></button><button type="button" class="bgbtnIO" onclick="console.log('MARCAR PAGADA 21976');" onmousedown="cladd(this,'pressed');" onmouseup="clrem(this,'pressed');" onmouseleave="clrem(this,'pressed');" title="MARCAR PAGADA"><img src="imagenes/icons/invoiceIcon.png" width="20" height="20" style="filter:grayscale(1) brightness(0.8) contrast(2.5)"></button><button type="button" class="bgbtnIO" onclick="console.log('CANCELAR 21976');" onmousedown="cladd(this,'pressed');" onmouseup="clrem(this,'pressed');" onmouseleave="clrem(this,'pressed');" title="CANCELAR"><img src="imagenes/icons/deleteIcon20.png" width="20" height="20"></button> <input type="checkbox" id="chk21976" solid="21976" class="pymchk noprint vAlignCenter"></div></td>
	    </tr>
	    <tr idx="12" id="row21975" class="payBlock" folid="BID2505-006" prcid="2" sttid="63" typid="F.1" gpoid="1" prvid="180" auis="2642" tot="883958.670" mon="MXN"><td class="sticky toLeft zIdx2 basicBG"><div class="wrap100 vAlignCenter btnLt bRad2 pointer" onclick="console.log('viewForm 21975');">BID2505-006</div> </td>
	        <td title="MANUFACTURERA DE PAPEL BIDASOA"><div class="wid70px">BIDASOA</div></td>
	        <td title="COMISION FEDERAL DE ELECTRICIDAD"><div class="wid48px">C-055</div></td>
	        <td><div class="wid100px lefted" title="000302391791"><span class="pre">F ...302391791</span></div><div class="wid48px noprint"><a href="archivos/BIDASOA/2025/05/CFE370814QI0_0302391791.xml" target="archivo"><img src="imagenes/icons/xml200.png" width="20" height="20"></a> <a href="archivos/BIDASOA/2025/05/ST_0302391791CFE370814QI0.pdf" target="archivo" onclick="console.log('rompeSello 21975');"><img src="imagenes/icons/pdf200S3.png" width="20" height="20"></a></div></td>
	        <td><div class="wid77px">2025-05-08</div></td>
	        <td><div class="wid77px">2025-05-08</div></td>
	        <td><div class="wid135px righted">$883,958.67</div></td>
	        <td><div class="wid100px" title="Yessica Saavedra">yessicas</div></td>
	        <td><div class="wid100px" title="LOBASH">mlobatonlaisa</div></td>
	        <td class="sticky toRight zIdx1 bxslft basicBG"><div class="wid135px centered vAlignCenter"><button type="button" class="bgbtnIO" onclick="console.log('ANEXAR COMPROBANTE 21975');" onmousedown="cladd(this,'pressed');" onmouseup="clrem(this,'pressed');" onmouseleave="clrem(this,'pressed');" title="ANEXAR COMPROBANTE"><img src="imagenes/icons/invChk200.png" width="20" height="20" style="filter:grayscale(1) brightness(0.8) contrast(2.5)"></button><button type="button" class="bgbtnIO" onclick="console.log('MARCAR PAGADA 21975');" onmousedown="cladd(this,'pressed');" onmouseup="clrem(this,'pressed');" onmouseleave="clrem(this,'pressed');" title="MARCAR PAGADA"><img src="imagenes/icons/invoiceIcon.png" width="20" height="20" style="filter:grayscale(1) brightness(0.8) contrast(2.5)"></button><button type="button" class="bgbtnIO" onclick="console.log('CANCELAR 21975');" onmousedown="cladd(this,'pressed');" onmouseup="clrem(this,'pressed');" onmouseleave="clrem(this,'pressed');" title="CANCELAR"><img src="imagenes/icons/deleteIcon20.png" width="20" height="20"></button> <input type="checkbox" id="chk21975" solid="21975" class="pymchk noprint vAlignCenter"></div></td>
	    </tr>
	    <tr idx="13" id="row21974" class="payBlock" folid="CAP2505-003" prcid="2" sttid="63" typid="F.1" gpoid="25" prvid="180" auis="2639" tot="301585.220" mon="MXN"><td class="sticky toLeft zIdx2 basicBG"><div class="wrap100 vAlignCenter btnLt bRad2 pointer" onclick="console.log('viewForm 21974');">CAP2505-003</div> </td>
	        <td title="CAPITAL HALL"><div class="wid70px">CAPITALH</div></td>
	        <td title="COMISION FEDERAL DE ELECTRICIDAD"><div class="wid48px">C-055</div></td>
	        <td><div class="wid100px lefted" title="000302389327"><span class="pre">F ...302389327</span></div><div class="wid48px noprint"><a href="archivos/CAPITALH/2025/05/CFE370814QI0_0302389327.xml" target="archivo"><img src="imagenes/icons/xml200.png" width="20" height="20"></a> <a href="archivos/CAPITALH/2025/05/ST_0302389327CFE370814QI0.pdf" target="archivo" onclick="console.log('rompeSello 21974');"><img src="imagenes/icons/pdf200S3.png" width="20" height="20"></a></div></td>
	        <td><div class="wid77px">2025-05-08</div></td>
	        <td><div class="wid77px">2025-05-08</div></td>
	        <td><div class="wid135px righted">$301,585.22</div></td>
	        <td><div class="wid100px" title="Omar Sánchez Zuñiga">osanchez</div></td>
	        <td><div class="wid100px" title="Jaime Lobatón">jlobaton</div></td>
	        <td class="sticky toRight zIdx1 bxslft basicBG"><div class="wid135px centered vAlignCenter"><button type="button" class="bgbtnIO" onclick="console.log('ANEXAR COMPROBANTE 21974');" onmousedown="cladd(this,'pressed');" onmouseup="clrem(this,'pressed');" onmouseleave="clrem(this,'pressed');" title="ANEXAR COMPROBANTE"><img src="imagenes/icons/invChk200.png" width="20" height="20" style="filter:grayscale(1) brightness(0.8) contrast(2.5)"></button><button type="button" class="bgbtnIO" onclick="console.log('MARCAR PAGADA 21974');" onmousedown="cladd(this,'pressed');" onmouseup="clrem(this,'pressed');" onmouseleave="clrem(this,'pressed');" title="MARCAR PAGADA"><img src="imagenes/icons/invoiceIcon.png" width="20" height="20" style="filter:grayscale(1) brightness(0.8) contrast(2.5)"></button><button type="button" class="bgbtnIO" onclick="console.log('CANCELAR 21974');" onmousedown="cladd(this,'pressed');" onmouseup="clrem(this,'pressed');" onmouseleave="clrem(this,'pressed');" title="CANCELAR"><img src="imagenes/icons/deleteIcon20.png" width="20" height="20"></button> <input type="checkbox" id="chk21974" solid="21974" class="pymchk noprint vAlignCenter"></div></td>
	    </tr>
	    <tr idx="14" id="row21970" class="payBlock" folid="APS2505-021" prcid="2" sttid="63" typid="F.1" gpoid="2" prvid="180" auis="2641" tot="177784.810" mon="MXN"><td class="sticky toLeft zIdx2 basicBG"><div class="wrap100 vAlignCenter btnLt bRad2 pointer" onclick="console.log('viewForm 21970');">APS2505-021</div> </td>
	        <td title="ACABADOS DE PAPELES SATINADOS Y ABSORBENTES"><div class="wid70px">APSA</div></td>
	        <td title="COMISION FEDERAL DE ELECTRICIDAD"><div class="wid48px">C-055</div></td>
	        <td><div class="wid100px lefted" title="000302391565"><span class="pre">F ...302391565</span></div><div class="wid48px noprint"><a href="archivos/APSA/2025/05/CFE370814QI0_0302391565.xml" target="archivo"><img src="imagenes/icons/xml200.png" width="20" height="20"></a> <a href="archivos/APSA/2025/05/ST_0302391565CFE370814QI0.pdf" target="archivo" onclick="console.log('rompeSello 21970');"><img src="imagenes/icons/pdf200S3.png" width="20" height="20"></a></div></td>
	        <td><div class="wid77px">2025-05-08</div></td>
	        <td><div class="wid77px">2025-05-08</div></td>
	        <td><div class="wid135px righted">$177,784.81</div></td>
	        <td><div class="wid100px" title="Marisol San Juan">marisols</div></td>
	        <td><div class="wid100px" title="Marcos Lobatón Abadi">mlobatonapsa</div></td>
	        <td class="sticky toRight zIdx1 bxslft basicBG"><div class="wid135px centered vAlignCenter"><button type="button" class="bgbtnIO" onclick="console.log('ANEXAR COMPROBANTE 21970');" onmousedown="cladd(this,'pressed');" onmouseup="clrem(this,'pressed');" onmouseleave="clrem(this,'pressed');" title="ANEXAR COMPROBANTE"><img src="imagenes/icons/invChk200.png" width="20" height="20" style="filter:grayscale(1) brightness(0.8) contrast(2.5)"></button><button type="button" class="bgbtnIO" onclick="console.log('MARCAR PAGADA 21970');" onmousedown="cladd(this,'pressed');" onmouseup="clrem(this,'pressed');" onmouseleave="clrem(this,'pressed');" title="MARCAR PAGADA"><img src="imagenes/icons/invoiceIcon.png" width="20" height="20" style="filter:grayscale(1) brightness(0.8) contrast(2.5)"></button><button type="button" class="bgbtnIO" onclick="console.log('CANCELAR 21970');" onmousedown="cladd(this,'pressed');" onmouseup="clrem(this,'pressed');" onmouseleave="clrem(this,'pressed');" title="CANCELAR"><img src="imagenes/icons/deleteIcon20.png" width="20" height="20"></button> <input type="checkbox" id="chk21970" solid="21970" class="pymchk noprint vAlignCenter"></div></td>
	    </tr>
	    <tr idx="15" id="row21957" class="payBlock" folid="APS2505-017" prcid="2" sttid="63" typid="F.1" gpoid="2" prvid="3906" auis="2639" tot="580000.000" mon="MXN"><td class="sticky toLeft zIdx2 basicBG"><div class="wrap100 vAlignCenter btnLt bRad2 pointer" onclick="console.log('viewForm 21957');">APS2505-017</div> </td>
	        <td title="ACABADOS DE PAPELES SATINADOS Y ABSORBENTES"><div class="wid70px">APSA</div></td>
	        <td title="MAIMONIDES"><div class="wid48px">M-436</div></td>
	        <td><div class="wid100px lefted" title="368"><span class="pre">F 368</span></div><div class="wid48px noprint"><a href="archivos/APSA/2025/05/MAI981019TSA_368.xml" target="archivo"><img src="imagenes/icons/xml200.png" width="20" height="20"></a> <a href="archivos/APSA/2025/05/368MAI981019TSA.pdf" target="archivo"><img src="imagenes/icons/pdf200.png" width="20" height="20"></a></div></td>
	        <td><div class="wid77px">2025-05-08</div></td>
	        <td><div class="wid77px">2025-05-08</div></td>
	        <td><div class="wid135px righted">$580,000.00</div></td>
	        <td><div class="wid100px" title="Monserrat Lopez">monsel</div></td>
	        <td><div class="wid100px" title="Jaime Lobatón">jlobaton</div></td>
	        <td class="sticky toRight zIdx1 bxslft basicBG"><div class="wid135px centered vAlignCenter"><button type="button" class="bgbtnIO" onclick="console.log('ANEXAR COMPROBANTE 21957');" onmousedown="cladd(this,'pressed');" onmouseup="clrem(this,'pressed');" onmouseleave="clrem(this,'pressed');" title="ANEXAR COMPROBANTE"><img src="imagenes/icons/invChk200.png" width="20" height="20" style="filter:grayscale(1) brightness(0.8) contrast(2.5)"></button><button type="button" class="bgbtnIO" onclick="console.log('MARCAR PAGADA 21957');" onmousedown="cladd(this,'pressed');" onmouseup="clrem(this,'pressed');" onmouseleave="clrem(this,'pressed');" title="MARCAR PAGADA"><img src="imagenes/icons/invoiceIcon.png" width="20" height="20" style="filter:grayscale(1) brightness(0.8) contrast(2.5)"></button><button type="button" class="bgbtnIO" onclick="console.log('CANCELAR 21957');" onmousedown="cladd(this,'pressed');" onmouseup="clrem(this,'pressed');" onmouseleave="clrem(this,'pressed');" title="CANCELAR"><img src="imagenes/icons/deleteIcon20.png" width="20" height="20"></button> <input type="checkbox" id="chk21957" solid="21957" class="pymchk noprint vAlignCenter"></div></td>
	    </tr>
	    <tr idx="16" id="row21951" class="payBlock" folid="FOA2505-013" prcid="0" sttid="2" typid="O.1" gpoid="24" prvid="2960" auis="2697" tot="33350.000" mon="USD"><td class="sticky toLeft zIdx2 basicBG"><div class="wrap100 vAlignCenter btnLt bRad2 pointer" onclick="console.log('viewForm 21951');">FOA2505-013</div> </td>
	        <td title="INDUSTRIAS FOAMYMEX"><div class="wid70px">FOAMYMEX</div></td>
	        <td title="VINMAR PLASTICHEM S DE RL DE CV"><div class="wid48px">V-120</div></td>
	        <td><div class="wid100px lefted" title="260-052025"><span class="pre">O 260-052025</span></div><div class="wid48px noprint"> <a href="archivos/FOAMYMEX/2025/05/ord260-052025_24_2960.pdf" target="archivo"><img src="imagenes/icons/pdf200.png" width="20" height="20"></a></div></td>
	        <td><div class="wid77px">2025-05-07</div></td>
	        <td><div class="wid77px">2025-05-07</div></td>
	        <td><div class="wid135px righted">USD&nbsp;33,350.00</div></td>
	        <td><div class="wid100px" title="Isabel Trinidad">comprasfoamy</div></td>
	        <td><div class="wid100px" title="Jacobo Romano">JRM</div></td>
	        <td class="sticky toRight zIdx1 bxslft basicBG"><div class="wid135px centered vAlignCenter"><button type="button" class="bgbtnIO" onclick="console.log('ANEXAR COMPROBANTE 21951');" onmousedown="cladd(this,'pressed');" onmouseup="clrem(this,'pressed');" onmouseleave="clrem(this,'pressed');" title="ANEXAR COMPROBANTE"><img src="imagenes/icons/invChk200.png" width="20" height="20" style="filter:grayscale(1) brightness(0.8) contrast(2.5)"></button><button type="button" class="bgbtnIO" onclick="console.log('MARCAR PAGADA 21951');" onmousedown="cladd(this,'pressed');" onmouseup="clrem(this,'pressed');" onmouseleave="clrem(this,'pressed');" title="MARCAR PAGADA"><img src="imagenes/icons/invoiceIcon.png" width="20" height="20" style="filter:grayscale(1) brightness(0.8) contrast(2.5)"></button><button type="button" class="bgbtnIO" onclick="console.log('CANCELAR 21951');" onmousedown="cladd(this,'pressed');" onmouseup="clrem(this,'pressed');" onmouseleave="clrem(this,'pressed');" title="CANCELAR"><img src="imagenes/icons/deleteIcon20.png" width="20" height="20"></button> <input type="checkbox" id="chk21951" solid="21951" class="pymchk noprint vAlignCenter"></div></td>
	    </tr>
	    <tr idx="17" id="row21947" class="payBlock" folid="SKA2505-008" prcid="2" sttid="63" typid="F.1" gpoid="22" prvid="180" auis="2640" tot="360774.410" mon="MXN"><td class="sticky toLeft zIdx2 basicBG"><div class="wrap100 vAlignCenter btnLt bRad2 pointer" onclick="console.log('viewForm 21947');">SKA2505-008</div> </td>
	        <td title="SKARTON"><div class="wid70px">SKARTON</div></td>
	        <td title="COMISION FEDERAL DE ELECTRICIDAD"><div class="wid48px">C-055</div></td>
	        <td><div class="wid100px lefted" title="000044125556"><span class="pre">F ...044125556</span></div><div class="wid48px noprint"><a href="archivos/SKARTON/2025/05/CFE370814QI0_0044125556.xml" target="archivo"><img src="imagenes/icons/xml200.png" width="20" height="20"></a> <a href="archivos/SKARTON/2025/05/ST_0044125556CFE370814QI0.pdf" target="archivo" onclick="console.log('rompeSello 21947');"><img src="imagenes/icons/pdf200S3.png" width="20" height="20"></a></div></td>
	        <td><div class="wid77px">2025-05-07</div></td>
	        <td><div class="wid77px">2025-05-07</div></td>
	        <td><div class="wid135px righted">$360,774.41</div></td>
	        <td><div class="wid100px" title="Luis Enrique Melendez Olvera">luism</div></td>
	        <td><div class="wid100px" title="Francisco Garabana">fgarabana</div></td>
	        <td class="sticky toRight zIdx1 bxslft basicBG"><div class="wid135px centered vAlignCenter"><button type="button" class="bgbtnIO" onclick="console.log('ANEXAR COMPROBANTE 21947');" onmousedown="cladd(this,'pressed');" onmouseup="clrem(this,'pressed');" onmouseleave="clrem(this,'pressed');" title="ANEXAR COMPROBANTE"><img src="imagenes/icons/invChk200.png" width="20" height="20" style="filter:grayscale(1) brightness(0.8) contrast(2.5)"></button><button type="button" class="bgbtnIO" onclick="console.log('MARCAR PAGADA 21947');" onmousedown="cladd(this,'pressed');" onmouseup="clrem(this,'pressed');" onmouseleave="clrem(this,'pressed');" title="MARCAR PAGADA"><img src="imagenes/icons/invoiceIcon.png" width="20" height="20" style="filter:grayscale(1) brightness(0.8) contrast(2.5)"></button><button type="button" class="bgbtnIO" onclick="console.log('CANCELAR 21947');" onmousedown="cladd(this,'pressed');" onmouseup="clrem(this,'pressed');" onmouseleave="clrem(this,'pressed');" title="CANCELAR"><img src="imagenes/icons/deleteIcon20.png" width="20" height="20"></button> <input type="checkbox" id="chk21947" solid="21947" class="pymchk noprint vAlignCenter"></div></td>
	    </tr>
	    <tr idx="18" id="row21944" class="payBlock" folid="APS2505-016" prcid="2" sttid="63" typid="F.1" gpoid="2" prvid="3716" auis="2641" tot="26880.000" mon="MXN"><td class="sticky toLeft zIdx2 basicBG"><div class="wrap100 vAlignCenter btnLt bRad2 pointer" onclick="console.log('viewForm 21944');">APS2505-016</div> </td>
	        <td title="ACABADOS DE PAPELES SATINADOS Y ABSORBENTES"><div class="wid70px">APSA</div></td>
	        <td title="ZAMUDIO LAZCANO OSCAR MANUEL"><div class="wid48px">Z-041</div></td>
	        <td><div class="wid100px lefted" title="1117"><span class="pre">F 1117</span></div><div class="wid48px noprint"><a href="archivos/APSA/2025/05/ZALO9012168V1_1117.xml" target="archivo"><img src="imagenes/icons/xml200.png" width="20" height="20"></a> <a href="archivos/APSA/2025/05/ST_1117ZALO9012168V1.pdf" target="archivo" onclick="console.log('rompeSello 21944');"><img src="imagenes/icons/pdf200S3.png" width="20" height="20"></a></div></td>
	        <td><div class="wid77px">2025-05-07</div></td>
	        <td><div class="wid77px">2025-05-07</div></td>
	        <td><div class="wid135px righted">$26,880.00</div></td>
	        <td><div class="wid100px" title="Rosaura Cruz">revisionapsa</div></td>
	        <td><div class="wid100px" title="Marcos Lobatón Abadi">mlobatonapsa</div></td>
	        <td class="sticky toRight zIdx1 bxslft basicBG"><div class="wid135px centered vAlignCenter"><button type="button" class="bgbtnIO" onclick="console.log('ANEXAR COMPROBANTE 21944');" onmousedown="cladd(this,'pressed');" onmouseup="clrem(this,'pressed');" onmouseleave="clrem(this,'pressed');" title="ANEXAR COMPROBANTE"><img src="imagenes/icons/invChk200.png" width="20" height="20" style="filter:grayscale(1) brightness(0.8) contrast(2.5)"></button><button type="button" class="bgbtnIO" onclick="console.log('MARCAR PAGADA 21944');" onmousedown="cladd(this,'pressed');" onmouseup="clrem(this,'pressed');" onmouseleave="clrem(this,'pressed');" title="MARCAR PAGADA"><img src="imagenes/icons/invoiceIcon.png" width="20" height="20" style="filter:grayscale(1) brightness(0.8) contrast(2.5)"></button><button type="button" class="bgbtnIO" onclick="console.log('CANCELAR 21944');" onmousedown="cladd(this,'pressed');" onmouseup="clrem(this,'pressed');" onmouseleave="clrem(this,'pressed');" title="CANCELAR"><img src="imagenes/icons/deleteIcon20.png" width="20" height="20"></button> <input type="checkbox" id="chk21944" solid="21944" class="pymchk noprint vAlignCenter"></div></td>
	    </tr>
	    <tr idx="19" id="row21943" class="payBlock" folid="CAP2505-002" prcid="2" sttid="63" typid="F.1" gpoid="25" prvid="1071" auis="2639" tot="19278.300" mon="MXN"><td class="sticky toLeft zIdx2 basicBG"><div class="wrap100 vAlignCenter btnLt bRad2 pointer" onclick="console.log('viewForm 21943');">CAP2505-002</div> </td>
	        <td title="CAPITAL HALL"><div class="wid70px">CAPITALH</div></td>
	        <td title="EDIFICIO CORPORATIVO PRIVANZA, A.C."><div class="wid48px">E-074</div></td>
	        <td><div class="wid100px lefted" title="[C78586AF34]"><span class="pre">F [8586AF34]</span></div><div class="wid48px noprint"><a href="archivos/CAPITALH/2025/05/ECP130206F78_C78586AF34.xml" target="archivo"><img src="imagenes/icons/xml200.png" width="20" height="20"></a> <a href="archivos/CAPITALH/2025/05/C78586AF34ECP130206F78.pdf" target="archivo"><img src="imagenes/icons/pdf200.png" width="20" height="20"></a></div></td>
	        <td><div class="wid77px">2025-05-07</div></td>
	        <td><div class="wid77px">2025-05-07</div></td>
	        <td><div class="wid135px righted">$19,278.30</div></td>
	        <td><div class="wid100px" title="Monserrat Lopez">monsel</div></td>
	        <td><div class="wid100px" title="Jaime Lobatón">jlobaton</div></td>
	        <td class="sticky toRight zIdx1 bxslft basicBG"><div class="wid135px centered vAlignCenter"><button type="button" class="bgbtnIO" onclick="console.log('ANEXAR COMPROBANTE 21943');" onmousedown="cladd(this,'pressed');" onmouseup="clrem(this,'pressed');" onmouseleave="clrem(this,'pressed');" title="ANEXAR COMPROBANTE"><img src="imagenes/icons/invChk200.png" width="20" height="20" style="filter:grayscale(1) brightness(0.8) contrast(2.5)"></button><button type="button" class="bgbtnIO" onclick="console.log('MARCAR PAGADA 21943');" onmousedown="cladd(this,'pressed');" onmouseup="clrem(this,'pressed');" onmouseleave="clrem(this,'pressed');" title="MARCAR PAGADA"><img src="imagenes/icons/invoiceIcon.png" width="20" height="20" style="filter:grayscale(1) brightness(0.8) contrast(2.5)"></button><button type="button" class="bgbtnIO" onclick="console.log('CANCELAR 21943');" onmousedown="cladd(this,'pressed');" onmouseup="clrem(this,'pressed');" onmouseleave="clrem(this,'pressed');" title="CANCELAR"><img src="imagenes/icons/deleteIcon20.png" width="20" height="20"></button> <input type="checkbox" id="chk21943" solid="21943" class="pymchk noprint vAlignCenter"></div></td>
	    </tr>
	    <tr idx="20" id="row21942" class="payBlock" folid="CAP2505-001" prcid="2" sttid="63" typid="F.1" gpoid="25" prvid="1071" auis="2639" tot="19278.300" mon="MXN"><td class="sticky toLeft zIdx2 basicBG"><div class="wrap100 vAlignCenter btnLt bRad2 pointer" onclick="console.log('viewForm 21942');">CAP2505-001</div> </td>
	        <td title="CAPITAL HALL"><div class="wid70px">CAPITALH</div></td>
	        <td title="EDIFICIO CORPORATIVO PRIVANZA, A.C."><div class="wid48px">E-074</div></td>
	        <td><div class="wid100px lefted" title="[F23787FBCE]"><span class="pre">F [3787FBCE]</span></div><div class="wid48px noprint"><a href="archivos/CAPITALH/2025/05/ECP130206F78_F23787FBCE.xml" target="archivo"><img src="imagenes/icons/xml200.png" width="20" height="20"></a> <a href="archivos/CAPITALH/2025/05/F23787FBCEECP130206F78.pdf" target="archivo"><img src="imagenes/icons/pdf200.png" width="20" height="20"></a></div></td>
	        <td><div class="wid77px">2025-05-07</div></td>
	        <td><div class="wid77px">2025-05-07</div></td>
	        <td><div class="wid135px righted">$19,278.30</div></td>
	        <td><div class="wid100px" title="Monserrat Lopez">monsel</div></td>
	        <td><div class="wid100px" title="Jaime Lobatón">jlobaton</div></td>
	        <td class="sticky toRight zIdx1 bxslft basicBG"><div class="wid135px centered vAlignCenter"><button type="button" class="bgbtnIO" onclick="console.log('ANEXAR COMPROBANTE 21942');" onmousedown="cladd(this,'pressed');" onmouseup="clrem(this,'pressed');" onmouseleave="clrem(this,'pressed');" title="ANEXAR COMPROBANTE"><img src="imagenes/icons/invChk200.png" width="20" height="20" style="filter:grayscale(1) brightness(0.8) contrast(2.5)"></button><button type="button" class="bgbtnIO" onclick="console.log('MARCAR PAGADA 21942');" onmousedown="cladd(this,'pressed');" onmouseup="clrem(this,'pressed');" onmouseleave="clrem(this,'pressed');" title="MARCAR PAGADA"><img src="imagenes/icons/invoiceIcon.png" width="20" height="20" style="filter:grayscale(1) brightness(0.8) contrast(2.5)"></button><button type="button" class="bgbtnIO" onclick="console.log('CANCELAR 21942');" onmousedown="cladd(this,'pressed');" onmouseup="clrem(this,'pressed');" onmouseleave="clrem(this,'pressed');" title="CANCELAR"><img src="imagenes/icons/deleteIcon20.png" width="20" height="20"></button> <input type="checkbox" id="chk21942" solid="21942" class="pymchk noprint vAlignCenter"></div></td>
	    </tr>
	    <tr idx="21" id="row21930" class="payBlock" folid="COR2505-004" prcid="2" sttid="63" typid="F.1" gpoid="18" prvid="1625" auis="2641" tot="60245.760" mon="MXN"><td class="sticky toLeft zIdx2 basicBG"><div class="wrap100 vAlignCenter btnLt bRad2 pointer" onclick="console.log('viewForm 21930');">COR2505-004</div> </td>
	        <td title="COREPACK"><div class="wid70px">COREPACK</div></td>
	        <td title="REED EXHIBITIONS MEXICO, S.A. DE C.V."><div class="wid48px">R-143</div></td>
	        <td><div class="wid100px lefted" title="5115033827"><span class="pre">F 5115033827</span></div><div class="wid48px noprint"><a href="archivos/COREPACK/2025/05/REM111216H7A_5115033827.xml" target="archivo"><img src="imagenes/icons/xml200.png" width="20" height="20"></a> <a href="archivos/COREPACK/2025/05/ST_5115033827REM111216H7A.pdf" target="archivo" onclick="console.log('rompeSello 21930');"><img src="imagenes/icons/pdf200S3.png" width="20" height="20"></a></div></td>
	        <td><div class="wid77px">2025-05-06</div></td>
	        <td><div class="wid77px">2025-05-06</div></td>
	        <td><div class="wid135px righted">$60,245.76</div></td>
	        <td><div class="wid100px" title="Angélica Torres">comprascorepack</div></td>
	        <td><div class="wid100px" title="Marcos Lobatón Abadi">mlobatonapsa</div></td>
	        <td class="sticky toRight zIdx1 bxslft basicBG"><div class="wid135px centered vAlignCenter"><button type="button" class="bgbtnIO" onclick="console.log('ANEXAR COMPROBANTE 21930');" onmousedown="cladd(this,'pressed');" onmouseup="clrem(this,'pressed');" onmouseleave="clrem(this,'pressed');" title="ANEXAR COMPROBANTE"><img src="imagenes/icons/invChk200.png" width="20" height="20" style="filter:grayscale(1) brightness(0.8) contrast(2.5)"></button><button type="button" class="bgbtnIO" onclick="console.log('MARCAR PAGADA 21930');" onmousedown="cladd(this,'pressed');" onmouseup="clrem(this,'pressed');" onmouseleave="clrem(this,'pressed');" title="MARCAR PAGADA"><img src="imagenes/icons/invoiceIcon.png" width="20" height="20" style="filter:grayscale(1) brightness(0.8) contrast(2.5)"></button><button type="button" class="bgbtnIO" onclick="console.log('CANCELAR 21930');" onmousedown="cladd(this,'pressed');" onmouseup="clrem(this,'pressed');" onmouseleave="clrem(this,'pressed');" title="CANCELAR"><img src="imagenes/icons/deleteIcon20.png" width="20" height="20"></button> <input type="checkbox" id="chk21930" solid="21930" class="pymchk noprint vAlignCenter"></div></td>
	    </tr>
	    <tr idx="22" id="row21918" class="payBlock" folid="MOR2505-003" prcid="0" sttid="2" typid="O.1" gpoid="23" prvid="1136" auis="2641" tot="7500.000" mon="MXN"><td class="sticky toLeft zIdx2 basicBG"><div class="wrap100 vAlignCenter btnLt bRad2 pointer" onclick="console.log('viewForm 21918');">MOR2505-003</div> </td>
	        <td title="MORYSAN COMERCIAL"><div class="wid70px">MORYSAN</div></td>
	        <td title="GOOGLE OPERACIONES DE MEXICO S. DE R.L. DE C.V."><div class="wid48px">G-150</div></td>
	        <td><div class="wid100px lefted" title="GOOGLE_MORYSAN_MAYO25"><span class="pre">O ...AN_MAYO25</span></div><div class="wid48px noprint"> <a href="archivos/MORYSAN/2025/05/ST_ordGOOGLE_MORYSAN_MAYO25_23_1136.pdf" target="archivo" onclick="console.log('rompeSello 21918');"><img src="imagenes/icons/pdf200S3.png" width="20" height="20"></a></div></td>
	        <td><div class="wid77px">2025-05-06</div></td>
	        <td><div class="wid77px">2025-05-06</div></td>
	        <td><div class="wid135px righted">$7,500.00</div></td>
	        <td><div class="wid100px" title="Omar Sánchez Zuñiga">osanchez</div></td>
	        <td><div class="wid100px" title="Marcos Lobatón Abadi">mlobatonapsa</div></td>
	        <td class="sticky toRight zIdx1 bxslft basicBG"><div class="wid135px centered vAlignCenter"><button type="button" class="bgbtnIO" onclick="console.log('ANEXAR COMPROBANTE 21918');" onmousedown="cladd(this,'pressed');" onmouseup="clrem(this,'pressed');" onmouseleave="clrem(this,'pressed');" title="ANEXAR COMPROBANTE"><img src="imagenes/icons/invChk200.png" width="20" height="20" style="filter:grayscale(1) brightness(0.8) contrast(2.5)"></button><button type="button" class="bgbtnIO" onclick="console.log('MARCAR PAGADA 21918');" onmousedown="cladd(this,'pressed');" onmouseup="clrem(this,'pressed');" onmouseleave="clrem(this,'pressed');" title="MARCAR PAGADA"><img src="imagenes/icons/invoiceIcon.png" width="20" height="20" style="filter:grayscale(1) brightness(0.8) contrast(2.5)"></button><button type="button" class="bgbtnIO" onclick="console.log('CANCELAR 21918');" onmousedown="cladd(this,'pressed');" onmouseup="clrem(this,'pressed');" onmouseleave="clrem(this,'pressed');" title="CANCELAR"><img src="imagenes/icons/deleteIcon20.png" width="20" height="20"></button> <input type="checkbox" id="chk21918" solid="21918" class="pymchk noprint vAlignCenter"></div></td>
	    </tr>
	    <tr idx="23" id="row21917" class="payBlock" folid="COR2505-003" prcid="0" sttid="2" typid="O.1" gpoid="18" prvid="1136" auis="2641" tot="5500.000" mon="MXN"><td class="sticky toLeft zIdx2 basicBG"><div class="wrap100 vAlignCenter btnLt bRad2 pointer" onclick="console.log('viewForm 21917');">COR2505-003</div> </td>
	        <td title="COREPACK"><div class="wid70px">COREPACK</div></td>
	        <td title="GOOGLE OPERACIONES DE MEXICO S. DE R.L. DE C.V."><div class="wid48px">G-150</div></td>
	        <td><div class="wid100px lefted" title="GOOGLE_COREPACK_MAYO25"><span class="pre">O ...CK_MAYO25</span></div><div class="wid48px noprint"> <a href="archivos/COREPACK/2025/05/ST_ordGOOGLE_COREPACK_MAYO25_18_1136.pdf" target="archivo" onclick="console.log('rompeSello 21917');"><img src="imagenes/icons/pdf200S3.png" width="20" height="20"></a></div></td>
	        <td><div class="wid77px">2025-05-06</div></td>
	        <td><div class="wid77px">2025-05-06</div></td>
	        <td><div class="wid135px righted">$5,500.00</div></td>
	        <td><div class="wid100px" title="Omar Sánchez Zuñiga">osanchez</div></td>
	        <td><div class="wid100px" title="Marcos Lobatón Abadi">mlobatonapsa</div></td>
	        <td class="sticky toRight zIdx1 bxslft basicBG"><div class="wid135px centered vAlignCenter"><button type="button" class="bgbtnIO" onclick="console.log('ANEXAR COMPROBANTE 21917');" onmousedown="cladd(this,'pressed');" onmouseup="clrem(this,'pressed');" onmouseleave="clrem(this,'pressed');" title="ANEXAR COMPROBANTE"><img src="imagenes/icons/invChk200.png" width="20" height="20" style="filter:grayscale(1) brightness(0.8) contrast(2.5)"></button><button type="button" class="bgbtnIO" onclick="console.log('MARCAR PAGADA 21917');" onmousedown="cladd(this,'pressed');" onmouseup="clrem(this,'pressed');" onmouseleave="clrem(this,'pressed');" title="MARCAR PAGADA"><img src="imagenes/icons/invoiceIcon.png" width="20" height="20" style="filter:grayscale(1) brightness(0.8) contrast(2.5)"></button><button type="button" class="bgbtnIO" onclick="console.log('CANCELAR 21917');" onmousedown="cladd(this,'pressed');" onmouseup="clrem(this,'pressed');" onmouseleave="clrem(this,'pressed');" title="CANCELAR"><img src="imagenes/icons/deleteIcon20.png" width="20" height="20"></button> <input type="checkbox" id="chk21917" solid="21917" class="pymchk noprint vAlignCenter"></div></td>
	    </tr>
	    <tr idx="24" id="row21916" class="payBlock" folid="APS2505-010" prcid="0" sttid="2" typid="O.1" gpoid="2" prvid="1136" auis="2641" tot="12150.000" mon="MXN"><td class="sticky toLeft zIdx2 basicBG"><div class="wrap100 vAlignCenter btnLt bRad2 pointer" onclick="console.log('viewForm 21916');">APS2505-010</div> </td>
	        <td title="ACABADOS DE PAPELES SATINADOS Y ABSORBENTES"><div class="wid70px">APSA</div></td>
	        <td title="GOOGLE OPERACIONES DE MEXICO S. DE R.L. DE C.V."><div class="wid48px">G-150</div></td>
	        <td><div class="wid100px lefted" title="GOOGLE_APSA_MAYO25"><span class="pre">O ...SA_MAYO25</span></div><div class="wid48px noprint"> <a href="archivos/APSA/2025/05/ST_ordGOOGLE_APSA_MAYO25_2_1136.pdf" target="archivo" onclick="console.log('rompeSello 21916');"><img src="imagenes/icons/pdf200S3.png" width="20" height="20"></a></div></td>
	        <td><div class="wid77px">2025-05-06</div></td>
	        <td><div class="wid77px">2025-05-06</div></td>
	        <td><div class="wid135px righted">$12,150.00</div></td>
	        <td><div class="wid100px" title="Omar Sánchez Zuñiga">osanchez</div></td>
	        <td><div class="wid100px" title="Marcos Lobatón Abadi">mlobatonapsa</div></td>
	        <td class="sticky toRight zIdx1 bxslft basicBG"><div class="wid135px centered vAlignCenter"><button type="button" class="bgbtnIO" onclick="console.log('ANEXAR COMPROBANTE 21916');" onmousedown="cladd(this,'pressed');" onmouseup="clrem(this,'pressed');" onmouseleave="clrem(this,'pressed');" title="ANEXAR COMPROBANTE"><img src="imagenes/icons/invChk200.png" width="20" height="20" style="filter:grayscale(1) brightness(0.8) contrast(2.5)"></button><button type="button" class="bgbtnIO" onclick="console.log('MARCAR PAGADA 21916');" onmousedown="cladd(this,'pressed');" onmouseup="clrem(this,'pressed');" onmouseleave="clrem(this,'pressed');" title="MARCAR PAGADA"><img src="imagenes/icons/invoiceIcon.png" width="20" height="20" style="filter:grayscale(1) brightness(0.8) contrast(2.5)"></button><button type="button" class="bgbtnIO" onclick="console.log('CANCELAR 21916');" onmousedown="cladd(this,'pressed');" onmouseup="clrem(this,'pressed');" onmouseleave="clrem(this,'pressed');" title="CANCELAR"><img src="imagenes/icons/deleteIcon20.png" width="20" height="20"></button> <input type="checkbox" id="chk21916" solid="21916" class="pymchk noprint vAlignCenter"></div></td>
	    </tr>
	    <tr idx="25" id="row21911" class="payBlock" folid="LAI2505-002" prcid="2" sttid="63" typid="F.1" gpoid="3" prvid="180" auis="2642" tot="520253.670" mon="MXN"><td class="sticky toLeft zIdx2 basicBG"><div class="wrap100 vAlignCenter btnLt bRad2 pointer" onclick="console.log('viewForm 21911');">LAI2505-002</div> </td>
	        <td title="LAMINAS ACANALADAS INFINITA"><div class="wid70px">LAISA</div></td>
	        <td title="COMISION FEDERAL DE ELECTRICIDAD"><div class="wid48px">C-055</div></td>
	        <td><div class="wid100px lefted" title="000302396530"><span class="pre">F ...302396530</span></div><div class="wid48px noprint"><a href="archivos/LAISA/2025/05/CFE370814QI0_0302396530.xml" target="archivo"><img src="imagenes/icons/xml200.png" width="20" height="20"></a> <a href="archivos/LAISA/2025/05/ST_0302396530CFE370814QI0.pdf" target="archivo" onclick="console.log('rompeSello 21911');"><img src="imagenes/icons/pdf200S3.png" width="20" height="20"></a></div></td>
	        <td><div class="wid77px">2025-05-06</div></td>
	        <td><div class="wid77px">2025-05-06</div></td>
	        <td><div class="wid135px righted">$520,253.67</div></td>
	        <td><div class="wid100px" title="Yessica Saavedra">yessicas</div></td>
	        <td><div class="wid100px" title="LOBASH">mlobatonlaisa</div></td>
	        <td class="sticky toRight zIdx1 bxslft basicBG"><div class="wid135px centered vAlignCenter"><button type="button" class="bgbtnIO" onclick="console.log('ANEXAR COMPROBANTE 21911');" onmousedown="cladd(this,'pressed');" onmouseup="clrem(this,'pressed');" onmouseleave="clrem(this,'pressed');" title="ANEXAR COMPROBANTE"><img src="imagenes/icons/invChk200.png" width="20" height="20" style="filter:grayscale(1) brightness(0.8) contrast(2.5)"></button><button type="button" class="bgbtnIO" onclick="console.log('MARCAR PAGADA 21911');" onmousedown="cladd(this,'pressed');" onmouseup="clrem(this,'pressed');" onmouseleave="clrem(this,'pressed');" title="MARCAR PAGADA"><img src="imagenes/icons/invoiceIcon.png" width="20" height="20" style="filter:grayscale(1) brightness(0.8) contrast(2.5)"></button><button type="button" class="bgbtnIO" onclick="console.log('CANCELAR 21911');" onmousedown="cladd(this,'pressed');" onmouseup="clrem(this,'pressed');" onmouseleave="clrem(this,'pressed');" title="CANCELAR"><img src="imagenes/icons/deleteIcon20.png" width="20" height="20"></button> <input type="checkbox" id="chk21911" solid="21911" class="pymchk noprint vAlignCenter"></div></td>
	    </tr>
	    </tbody>
	</table>
  </body>
</html>
