<?php
require_once dirname(__DIR__)."/bootstrap.php";
header("Content-type: application/javascript; charset: UTF-8");
//require_once "scripts/calendar_tools.php";
clog2ini("scripts.calendar_widget");
clog1seq(1);
?>
    var target_widget;

    function calendar_cells() {
      var table = document.getElementById('calendar_widget');
      return grep( function(i){ return i.className == 'number'}, table.getElementsByTagName('td'));
    };


    function display_month(month) {
      if (! document.getElementById)
        return;
      var first = first_of_month( month );
      var last = day_before(first_of_month(next_month(month)));
      var today = new Date;

      if (! document.getElementById) {
        alert("document.getElementById not supported");
        alert(document);
      };

      var curr = day_before(first);
      while (curr.getDay() != 1) curr = day_before(curr);

      var table = document.getElementById('calendar_widget');
      var month_year_display = document.getElementById('month_year_display');
      // month_year_display.innerHTML = "<p>" + month_names[month.getMonth()] + " " + month.getFullYear() + "</p>";
      if (canPickMonthOrYear)
          month_year_display.innerHTML = '<table><tr><td><span onclick="javascript:month_select_click('+month.getFullYear()+','+month.getMonth()+')">'+month_names[month.getMonth()] + '</span>' + '</td><td>&nbsp;</td><td>' + '<span onclick="javascript:year_select_click('+month.getFullYear()+')">' + month.getFullYear() + '</span></td></tr></table>';
      else 
          month_year_display.innerHTML = month_names[month.getMonth()] + '&nbsp;' + month.getFullYear();

      // Find the date for the upper left corner:
      var cells = calendar_cells();
      for (var offset = 0; offset < cells.length; offset++) {
        var span_class = '';
        if (curr.getMonth() != month.getMonth()) { span_class = 'other_month' };
        if (same_day(curr,today)) { if(span_class.length>0) span_class+= ' '; span_class += 'today' };
        if (same_day(curr,current_day)) { if(span_class.length>0) span_class+= ' '; span_class += 'current_selection' };
        if (span_class.length>0) span_class = ' class="'+span_class+'"';
        cells[offset].innerHTML = '<span'+span_class+' onclick="javascript:day_select_click('+curr.getFullYear()+','+(curr.getMonth())+','+curr.getDate()+')">' + curr.getDate() + '</span>';
        curr = day_after( curr );
      };
      display_day = month;
    };

    function close_widget() {
      var parent = window.parent.document;
      parent.getElementById('calendar_widget').style.display = 'none';
      //console.log("CLOSED WIDGET");
      target_widget.focus();
    };

    window.set_edit = function (widget) {
      target_widget = widget;
      current_day = strptime( date_format, target_widget.value );
      display_month(current_day);
      canPickMonthOrYear = widget.getAttribute("canPickMonthOrYear");
      if (canPickMonthOrYear && (canPickMonthOrYear.length==0 || canPickMonthOrYear==="0" || canPickMonthOrYear.toUpperCase()==="NO")) canPickMonthOrYear=false;
      // console.log("window.set_edit '"+(canPickMonthOrYear?canPickMonthOrYear:"NO")+"'");
    };

    function update_selection(date) {
      var s = date != '' ? strftime(date_format,date) : '';
      if (target_widget.value!==s) target_widget.isUpdated=true;
      target_widget.value = s;
      // console.log("Update Selection: "+date);
      if(target_widget.hasAttribute("callbackFunc")) {
          let callbackFunc = target_widget.getAttribute("callbackFunc");
          if (target_widget.callbackFunc) callbackFunc=target_widget.callbackFunc;
          if (callbackFunc) {
              console.log("Call Callback "+(typeof callbackFunc)+": ", callbackFunc,". From Parent: ",window.parent);
              if ((typeof callbackFunc)==="function") callbackFunc();
              else window.parent[callbackFunc].call(this,target_widget);
          } else delete target_widget.isUpdated;
      } else delete target_widget.isUpdated;
    };

    function month_left_click(widget) { display_month( prev_month( display_day )); };
    function month_right_click(widget) { display_month( next_month( display_day ));};
    function year_left_click(widget) { display_month( prev_year( display_day )); };
    function year_right_click(widget) { display_month( next_year( display_day )); };
    function clear_button_click(widget) { update_selection( '' ); close_widget(); };
    function close_button_click(widget) { close_widget() };
    function day_select_click(year,month,day) {
      // console.log("Day Select Click: "+year+" / "+month+" / "+day);
      var selected = new Date(year,month,day);
      update_selection(selected);
      close_widget();
    };
    function month_select_click(year,month) {
      // console.log("Month Select Click: "+year+" / "+month);
      if(target_widget.hasAttribute("callbackFunc")) {
        var callbackFunc = target_widget.getAttribute("callbackFunc");
        var firstDate=new Date(year,month,1);
        var lastDate=day_before(new Date(year,month+1,1));
        //var lastDate=day_before(first_of_month(next_month(firstDate)));
        // console.log("Call Callback Func: "+callbackFunc+" first="+firstDate+" last="+lastDate);
        window.parent[callbackFunc].call(this,target_widget,firstDate,lastDate);
      } else {
        var date = new Date(year,month,1);
        var s = date != '' ? strftime(month_format,date) : '';
        target_widget.value = s;
      }
      close_widget();
    }
    /*
    function month_select_click(year,month,firstday,lastday) {
      var firstDate=new Date(year,month,firstday);
      var lastDate=new Date(year,month,lastday);
      update_selection_double(firstDate,lastDate);
      close_widget();
    }
    */
    function year_select_click(year) {
      // console.log("Year Select Click: "+year);
      if (target_widget.hasAttribute("callbackFunc")) {
        var callbackFunc = target_widget.getAttribute("callbackFunc");
        var firstDate=new Date(year,0,1);
        var lastDate=new Date(year,11,31);
        // console.log("Call Callback Func: "+callbackFunc+" first="+firstDate+" last="+lastDate);
        window.parent[callbackFunc].call(this,target_widget,firstDate,lastDate);
      } else {
        target_widget.value = year;
      }
      close_widget();
    }
<?php
clog1seq(-1);
clog2end("scripts.calendar_widget");
