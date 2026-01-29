function findPos(obj) {
  const pos = new Object;
  pos.left = 0;
  pos.top = 0;
  if (obj.getBoundingClientRect) {
    const rect=obj.getBoundingClientRect();
    pos.left = rect.left;
    pos.top = rect.top;
  } else if (obj.offsetParent)	{
		while (obj.offsetParent) {
		  pos.left += obj.offsetLeft;
			pos.top  += obj.offsetTop;
			obj = obj.offsetParent;
		};
	}	else if (obj.x) {
	  pos.left = obj.x;
	  pos.top = obj.y;
	};
	return pos;
};
function parent(widget) {
  if (widget.parentElement) { return widget.parentElement };
  if (widget.parentNode) { return widget.parentNode };
  if (widget.parent) { return widget.parent };
  return;
};
function show_calendar_widget(widget, callbackFunc) {
  // console.log("show_calendar_widget: ", widget, callbackFunc);
  if (! document.getElementsByTagName) { return; };
  var edits = parent(widget).getElementsByTagName('input');
  if (edits.length > 1) {
    alert("More than one date edit field found within the enclosing element");
    return 0;
  } else if (edits.length == 0) {
    alert("No date edit field found within the enclosing element");
    return 0;
  };
  var date_edit = edits[0];
  if (callbackFunc) {
    date_edit.setAttribute("callbackFunc",callbackFunc);
    date_edit.callbackFunc=callbackFunc;
  }
  var date = date_edit.value;
  var calendar_widget = document.getElementById('calendar_widget');
  if (! calendar_widget) {
    calendar_widget = document.createElement('div');
    calendar_widget.id = 'calendar_widget';
    calendar_widget.className = 'calendar_widget';
    document.body.appendChild(calendar_widget);
    var iframe = document.createElement('iframe');
    iframe.id = 'calendar_widget_iframe';
    iframe.name = iframe.id;
    iframe.style.backgroundColor = "rgba(200, 200, 200, 0.9)";
    iframe.style.overflow = "hidden !important";
    iframe.scrolling="no";
    iframe.width = '100%';
    iframe.height = '100%';
    iframe.src = 'templates/calendar_widget.php';
    calendar_widget.appendChild( iframe );
    date_edit.classList.add("clearable");
    // console.log("CALENDAR_WIDGET: CREATED");
  } else if (calendar_widget.style.display=="block") {
    calendar_widget.style.display="none";
    date_edit.classList.remove("clearable");
    // console.log("CALENDAR_WIDGET: HIDDEN");
    return;
  } else {
    date_edit.classList.add("clearable");
    // console.log("CALENDAR_WIDGET: SHOWN");
  }
  pos = findPos(date_edit);
  if (date_edit.offsetHeight) {
    pos.top += +date_edit.offsetHeight;
  } else {
    pos.top += +date_edit.clientHeight;
  }
  if (document.all) {
    calendar_widget.style.posTop = pos.top;
    calendar_widget.style.posLeft = pos.left;
    calendar_widget.style.display = "block";
    calendar_widget.style.overflow = "hidden !important";
  } else {
    calendar_widget.style.position = 'absolute';
    calendar_widget.style.top = pos.top + "px";
    calendar_widget.style.left = pos.left + "px";
    calendar_widget.style.display = "block";
    calendar_widget.style.overflow = "hidden !important";
  }
  var iframe = document.getElementById('calendar_widget_iframe');
  if (iframe.set_edit) {
    iframe.set_edit(date_edit);
  } else if (iframe.contentWindow) {
    if (iframe.contentWindow.set_edit) iframe.contentWindow.set_edit(date_edit);
    else iframe.contentWindow.date_edit_elem = date_edit;
  }
}
function adjust_calendar(targetWidget,dateWidgetIds, properties) {
  if (!dateWidgetIds||!dateWidgetIds.length||dateWidgetIds.length<2) {
    dateWidgetIds=["fechaInicio","fechaFin"];
    //console.log("Default ids: ", dateWidgetIds);
  } //else console.log("New ids: ", dateWidgetIds);
  let freeRange=false;
  if (properties && 'freeRange' in properties) freeRange=properties.freeRange;
  const ini_date_element=document.getElementById(dateWidgetIds[0]);
  const end_date_element=document.getElementById(dateWidgetIds[1]);
  if (!ini_date_element || !end_date_element) return;

  const isIni = targetWidget && (targetWidget===ini_date_element);
  const isEnd = targetWidget && (targetWidget===end_date_element);

  const iniday=strptime(date_format, ini_date_element.value);
  const endday=strptime(date_format, end_date_element.value);
  const idt=[iniday.getMonth()+1,iniday.getFullYear()];
  const edt=[endday.getMonth()+1,endday.getFullYear()];
  if ((idt[0]!==edt[0]||idt[1]!==edt[1])&&(!freeRange||iniday>endday)) {
    if (isIni) end_date_element.value=strftime(date_format,day_before(first_of_month(next_month(iniday))));
    else if (isEnd) ini_date_element.value=strftime(date_format,first_of_month(endday));
  } else if (iniday>endday) {
    if (isIni) end_date_element.value=ini_date_element.value;
    else if (isEnd) ini_date_element.value=end_date_element.value;
  }

  //const curday=isEnd?endday:iniday;
  //const curmon=isEnd?edt[0]:idt[0];
  let prvmon=idt[0]-1; // curmon-1;
  while(prvmon<1) prvmon+=12;
  let nxtmon=edt[0]+1; // curmon+1;
  while(nxtmon>12) nxtmon-=12;
  const previous_month_icon=document.getElementById("calendar_month_prev");
  if (previous_month_icon) previous_month_icon.className="calendar_month_"+padDate(prvmon);
  const next_month_icon=document.getElementById("calendar_month_next");
  if (next_month_icon) next_month_icon.className="calendar_month_"+padDate(nxtmon);
}
function fixRange(elem, fechaIniId, fechaFinId, lowerDateLimit, upperDateLimit) {
    if (!fechaIniId) fechaIniId="fechaIni";
    if (!fechaFinId) fechaFinId="fechaFin";
    if (!upperDateLimit) upperDateLimit=actual_day;
    // console.log("INI function fixRange ( elem=",elem,", fechaIniId=",fechaIniId,", fechaFinId=",fechaFinId,", lowerDateLimit=",lowerDateLimit,", upperDateLimit=",upperDateLimit);
    const fechaIniElem=document.getElementById(fechaIniId);
    const fechaFinElem=document.getElementById(fechaFinId);
    let fechaIniTime=strptime(date_format,fechaIniElem.value).getTime();
    let fechaFinTime=strptime(date_format,fechaFinElem.value).getTime();

    if (lowerDateLimit) {
        const lowerTime=lowerDateLimit.getTime();
        const lowerValue=strftime(date_format,lowerDateLimit);
        if (upperDateLimit) {
            const upperTime=upperDateLimit.getTime();
            if (upperTime<lowerTime) upperDateLimit=false;
        }
        if (fechaIniTime<lowerTime) {
            fechaIniElem.value=lowerValue;
            fechaIniTime=lowerTime;
        }
        if (fechaFinTime<lowerTime) {
            fechaFinElem.value=lowerValue;
            fechaFinTime=lowerTime;
        }
    }
    if (upperDateLimit) {
        const upperTime=upperDateLimit.getTime();
        const upperValue=strftime(date_format,upperDateLimit);
        if (fechaIniTime>upperTime) {
            fechaIniElem.value=upperValue;
            fechaIniTime=upperTime;
        }
        if (fechaFinTime>upperTime) {
            fechaFinElem.value=upperValue;
            fechaFinTime=upperTime;
        }
    }
    if (fechaFinTime<fechaIniTime) {
        if (elem===fechaFinElem) fechaIniElem.value=fechaFinElem.value;
        else if (elem===fechaIniElem) fechaFinElem.value=fechaIniElem.value;
    }
}
