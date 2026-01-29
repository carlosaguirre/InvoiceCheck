var appointmentTimeout=false;
function calendar_cells() {
    var table = document.getElementById('calendar_widget');
    return grep( function(i){ return i.classList.contains('number'); }, table.getElementsByTagName('td'));
};
function display_month(month, idElem) {
    console.log("INI display_month: ", month);
    var first = first_of_month( month );
    var last = day_before(first_of_month(next_month(month)));
    console.log("INI first: ", first);
    console.log("INI last: ", last);

    var curr = first;//day_before(first);
    //console.log("CURR: ",curr);
    //console.log("CURR WEEKDAY: "+curr.getDay());
    while (curr.getDay() != 1) curr = day_before(curr);
    console.log("TABLE START: ",curr);
    var table = document.getElementById('calendar_widget');
    var month_year_display = document.getElementById('month_year_display');
    var monthYear=month.getFullYear();
    var monthMonth=month.getMonth();
    var actualYear=actual_day.getFullYear();
    var actualMonth=actual_day.getMonth();
    // month_year_display.innerHTML = "<p>" + month_names[monthMonth] + " " + monthYear + "</p>";
    month_year_display.innerHTML = month_names[monthMonth] + '&nbsp;' + monthYear;
    month_year_display.year=monthYear;
    const yrlf=ebyid('year_left');
    if (yrlf) {
        const yrsp=yrlf.getElementsByTagName("SPAN")[0];
        const isPastYear = (monthYear <= actualYear);
        clset(yrlf,'pastday',isPastYear);
        clset(yrsp,'pastday',isPastYear);
    }
    const mnlf=ebyid('month_left');
    if (mnlf) {
        const mnsp=mnlf.getElementsByTagName("SPAN")[0];
        const isPastMonth = (monthYear < actualYear || (monthYear == actualYear && monthMonth<=actualMonth));
        clset(mnlf,'pastday',isPastMonth);
        clset(mnsp,'pastday',isPastMonth);
    }
    // Find the date for the upper left corner:
    var cells = calendar_cells();
    let doOff=false;
    for (var offset = 0; offset < cells.length; offset++) {
        let span_class = '';
        let currs = strftime(bd_format,curr);
        let canBeOccupied=true;
        if (curr.getMonth() != monthMonth) { span_class = 'other_month'; canBeOccupied=false;}
        if (same_day(curr,actual_day)) { if(span_class.length>0) span_class+= ' '; span_class += 'today'; }
        //if (same_day(curr,current_day)) { if(span_class.length>0) span_class+= ' '; span_class += 'current_selection'; }
        if (curr.getDay()==0 || curr.getDay()==6) {
            if (span_class.length>0) span_class+=' ';
            span_class+='weekend';
            canBeOccupied=false;
            cells[offset].classList.add('weekendDay');
        }
        if (past_day(curr)) {
            if (span_class.length>0) span_class+=' ';
            span_class+='pastday';
            canBeOccupied=false;
        }
        if (canBeOccupied) {
            if (span_class.length>0) span_class+=' ';
            span_class+='checkAppt occupied';
        }
        if (span_class.length>0) span_class = ' class="'+span_class+'"';
        cells[offset].innerHTML = '<span id="d'+currs+'"'+span_class+' onclick="day_select_click('+curr.getFullYear()+','+(curr.getMonth())+','+curr.getDate()+',this);" onmouseover="calendar_check(this);">' + curr.getDate() + '</span>';
        if (offset==35) {
            if (curr.getMonth()!=monthMonth) cladd(cells[offset].parentNode,"hidden");
            else clrem(cells[offset].parentNode,"hidden");
        }
        curr = day_after( curr );
    };
    display_day = first;
    console.log("REFRESH AT: "+padDate(display_day.getMonth()+1)+"/"+display_day.getFullYear());
    clearTimeout(appointmentTimeout);
    postService("consultas/Citas.php",{action:"refresh",return:"occupied",year:display_day.getFullYear(),month:padDate(display_day.getMonth()+1)},intervalCallback);
}
function intervalCallback(textmsg, parameters, readyState, status) {
    if(readyState<4&&status<=200) return;
    if(textmsg.length==0) {
        //console.log("INI function intervalCallback. ERROR: Texto Vacío");
        return;
    }
    if (!same_day(new Date(), actual_day)) {
        location.reload();
        return;
    }
    //console.log("INI function intervalCallback. STATE: "+readyState+", STATUS: "+status+"\nPARAMETERS: ", parameters,"\nTEXT:\n",textmsg);
    console.log("INTERVAL CALLBACK "+readyState+"|"+status+" "+parameters.action+"|"+parameters.return+" "+parameters.month+"/"+parameters.year+"\n"+textmsg);
    try {
        const jobj=JSON.parse(textmsg);
        if (jobj.occupied) {
            fee(lbycn("checkAppt"),function(elem) {
                const elemId=elem.id;
                const day=elemId.slice(1);
                if (jobj.occupied[day]) {
                    const timeList=jobj.occupied[day];
                    if(timeList.length>0) {
                        clrem(elem,"occupied");
                        elem.availableTime=timeList;
                    } else {
                        cladd(elem,"occupied");
                        if (jobj.details[day]) {
                            elem.title=jobj.details[day];
                            cladd(elem,"titled");
                        }
                    }
                } else clrem(elem,"occupied");
            });
        }
        //if (jobj.log) console.log("LOG: "+jobj.log);
        //console.log("Text: "+textmsg);
    } catch(ex) {
        console.log("Exception caught: ", ex); //, "\nText: ", textmsg);
        return;
    }
    clearTimeout(appointmentTimeout);
    appointmentTimeout=setTimeout(function() { postService("consultas/Citas.php",{action:"refresh",return:"occupied",year:display_day.getFullYear(),month:padDate(display_day.getMonth()+1)},intervalCallback); },10000);
}
function month_left_click(widget) {
    if(clhas(ebyid('month_left'),'pastday')) return;
    display_month( prev_month( display_day ));
};
function month_right_click(widget) { display_month( next_month( display_day ));};
function year_left_click(widget) {
    if(clhas(ebyid('year_left'),'pastday')) return;
    display_month( prev_year( display_day ));
};
function year_right_click(widget) { display_month( next_year( display_day )); };
function day_select_click(year,month,day,elem) {
    //console.log("Day Select Click: "+year+" / "+month+" / "+day,elem,elem.availableTime);
    var selected = new Date(year,month,day);
    update_selection(selected,elem);
    //close_widget();
};
function update_selection(date,selectedElem) {
    var table = document.getElementById('calendar_widget');
    var s = date != '' ? strftime(date_format,date) : '';
    if (calendar_widget) {
        if (calendar_widget.hasAttribute("callbackFunc")) {
            const callbackFunc = calendar_widget.getAttribute("callbackFunc");
            callbackFunc(date,selectedElem);
        } else if (calendar_widget.hasOwnProperty("callbackFunc")) {
            calendar_widget.callbackFunc(date,selectedElem);
        }
    }
};
function calendar_check(elem) {
    let checkText = elem.tagName.toUpperCase();
    if (elem.id) checkText+=":"+elem.id;
    if (elem.className) checkText+=" '"+elem.className+"'";
    let parentNode = elem.parentNode;
    if (parentNode) {
        checkText += " ("+parentNode.tagName.toUpperCase();
        if (parentNode.id) checkText+=":"+parentNode.id;
        if (parentNode.className) checkText+=" '"+parentNode.className+"'";
        checkText += ")";
    }
    if (elem.availableTime) {
        if (elem.availableTime.length==0) checkText+=" SATURADO!";
        else {
            checkText+=" [";
            for(let i=0; i < elem.availableTime.length; i++) {
                if(i>0) checkText+=",";
                checkText+=elem.availableTime[i];
            }
            checkText+="] ";
        }
    } else if (elem.classList.contains("occupied")) {
        checkText+=" SATURADO!";
    } else if (elem.classList.contains("checkAppt")) {
        checkText+=" SIN CITAS!";
    }
    ebyid("calendarCheck").textContent=checkText;
}
