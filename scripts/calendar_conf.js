var current_day = new Date(); // selected date
var display_day = new Date(); // navigation date
var actual_day = new Date(); // unmoved, keeps being today, or at least the day this script was loaded
var canPickMonthOrYear = false;

var week_names = ['Domingo','Lunes','Martes','Miércoles','Jueves','Viernes','Sábado'];
var month_names = ['Enero','Febrero','Marzo','Abril','Mayo','Junio','Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre'];
var date_format = '%d/%m/%Y';
var month_format = '%m/%Y';
var std_format = '%Y%m%d';
var bd_format = '%Y-%m-%d';
var readable_format = '%W %e de %M de %Y';
var datetimeFormat = 'Y-m-d H:i:s';

function map(code,list) {
  var result = [];
  for (var i = 0; i < list.length; i++) {
    result.push( code(list[i]));
  }
  return result;
}
function grep(code,list) {
  var result = [];
  for (var i = 0; i < list.length; i++) {
    if (code(list[i])) {
      result.push( list[i]);
    }
  }
  return result;
}
function datetimeFix(dttmStr,fmtOri, fmtDes) { // fmt:y=>'y-m-d h:i:s', d=>'d/m/y h:i:s', m=>m/y/d h:i:s
  let y=0, m=0, d=0, t=dttmStr.slice(10);
  switch (fmtOri.charAt(0)) {
    case "y": y=+dttmStr.slice(0,4); m=+dttmStr.slice(5,7); d=+dttmStr.slice(8,10); break;
    case "m": m=+dttmStr.slice(0,2); y=+dttmStr.slice(3,7); d=+dttmStr.slice(8,10); break;
    case "d": d=+dttmStr.slice(0,2); m=+dttmStr.slice(3,5); y=+dttmStr.slice(6,10); break;
    default: return false;
  }
  switch (fmtDes.charAt(0)) {
    case "y": return y+"-"+m+"-"+d+t;
    case "m": return m+"/"+y+"/"+d+t;
    case "d": return d+"/"+m+"/"+y+t;
  }
  return false;
}
function strftime(format,date) {
  var result = format;
  if (!date) date=new Date();
  result = result.replace( /%Y/, date.getFullYear());
  result = result.replace( /%m/, padDate(date.getMonth()+1));
  result = result.replace( /%d/, padDate(date.getDate()));
  result = result.replace( /%e/, date.getDate());
  result = result.replace( /%M/, month_names[date.getMonth()]);
  result = result.replace( /%W/, week_names[date.getDay()]);
  result = result.replace( /%D/, date.getFullYear()+padDate(date.getMonth()+1)+padDate(date.getDate()));
  result = result.replace( /%T/, date.getFullYear()+padDate(date.getMonth()+1)+padDate(date.getDate())+padDate(date.getHours())+padDate(date.getMinutes())+padDate(date.getSeconds()));
  return result;
}
function padDate(val) {
  var str = "" + val;
  var pad = "00";
  var ans = pad.substring(0, pad.length - str.length) + str;
  return ans;
}
function strptime(format,text) {
  var result = new Date();
  // quote meta chars
  var s = '^' + format.replace( /([][.\*])/g, '\\$1') + '$';
  var re_format = new RegExp( s.replace(/%[Ymd]/g,'(\\d+)'));
  var match = text.match( re_format );
  if (match) {
    // Throw away the full string that appears for some weird reason
    match.shift();
    // we have a valid, matching date
    var date_parts = new Object();
    date_parts['Y'] = result.getFullYear();
    date_parts['m'] = result.getMonth();
    date_parts['d'] = result.getDate();
    order = format.match( /%[Ymd]/g );
    for (var i = 0; i < order.length; i++) {
      date_parts[order[i].substr(1,1)] = parseInt(match[i]);
    }
    if (date_parts['Y'] < 100) { date_parts += 2000; }
    result = new Date(date_parts.Y, date_parts.m-1, date_parts.d);
  }
  return result;
}
function add_days(date,delta) {
  const result = new Date(date.getTime());
  result.setDate(result.getDate() + delta);
  return result;
}
function day_before(date) { return add_days(date,-1); }
function day_after(date) { return add_days(date, 1); }
function first_of_month(date) {
  /*
  const result = new Date(date.getTime());
  result.setDate(1);
  result.setHours(0,0,0,0); // without reseting hours is faster, else it is slower
  return result;
  */
    return new Date ( date.getFullYear(), date.getMonth()  , 1, 0, 0, 0, 0 );
}
function last_of_month(date) {
    return new Date ( date.getFullYear(), date.getMonth()+1, 0, 0, 0, 0, 0 );
}
function prev_month(date) {
  const result = first_of_month( date );
  result.setDate(result.getDate() - 1);
  if (result.getDate()>date.getDate()) result.setDate(date.getDate());
  return result;
}
function next_month(date) {
  const result = new Date(date.getTime());
  result.setMonth(result.getMonth()+1, result.getDate());
  if ((result.getMonth()-date.getMonth())>1) {
    result.setDate(1);
    result.setDate(result.getDate()-1);
  }
  return result;
}
function prev_year(date) {
  var result = new Date(date.getTime());
  result.setFullYear( result.getFullYear()-1 );
  if (result.getDate()!==date.getDate()) result.setDate(result.getDate()-1);
  return result;
}
function next_year(date) {
  var result = new Date(date.getTime());
  result.setFullYear( result.getFullYear()+1 );
  if (result.getDate()!==date.getDate()) result.setDate(result.getDate()-1);
  return result;
}
function same_day(d1,d2) { return d1.getDate() == d2.getDate() && d1.getMonth() == d2.getMonth() && d1.getFullYear() == d2.getFullYear() }
function past_day(day) {
  const today=new Date();
  const year=day.getFullYear();
  const thisYear=today.getFullYear();
  if (year!==thisYear) return year<thisYear;
  const month=day.getMonth();
  const thisMonth=today.getMonth();
  if (month!==thisMonth) return month<thisMonth;
  return day.getDate()<today.getDate();
}
function AddMinutesToDate(date, minutes) {
     return new Date(date.getTime() + minutes*60000);
}
function dateIniSet(elemId) {
  if (!elemId) elemId="fechaInicio";
  dateSet(document.getElementById("fechaInicio"),prev_month);
  //console.log("function dateIniSet");
  //const iniDateElem = document.getElementById("fechaInicio");
  //if (iniDateElem) {
  //  let day = strptime(date_format, iniDateElem.value);
  //  setFullMonth(prev_month(day));
  //}
}
function dateEndSet(elemId) {
  if (!elemId) elemId="fechaInicio";
  dateSet(document.getElementById("fechaInicio"),next_month);
    //console.log("function dateEndSet");
    //const iniDateElem = ebyid("fechaInicio");
    //let day = strptime(date_format, iniDateElem.value);
    //setFullMonth(next_month(day));
}
function dateSet(elem,callbackFunc) {
  let f=""+callbackFunc; if(f.length>9) f=f.slice(9,f.indexOf("("));
  console.log("INI function dateSet "+(elem?"'"+elem.id+"' ":"[null] ")+f);
  if (elem && elem.value && elem.value.length>0) {
    const day = strptime(date_format, elem.value);
    if (callbackFunc) setFullMonth(callbackFunc(day));
  }
}
function setFullMonth(date,beginId,endId) {
  if (!beginId) beginId="fechaInicio";
  if (!endId) endId="fechaFin";
    var firstDay = first_of_month(date);
    var lastDay = day_before(first_of_month(next_month(date)));
    var iniDateElem = ebyid(beginId);
    var endDateElem = ebyid(endId);
    if (iniDateElem) iniDateElem.value = strftime(date_format, firstDay);
    if (endDateElem) endDateElem.value = strftime(date_format, lastDay);
    adjust_calendar(); //adjustCalMonImgs();
}
