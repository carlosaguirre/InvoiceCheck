<?php
require_once dirname(__DIR__)."/bootstrap.php";
?>
<!doctype html>
<html>
  <head>
    <base href="<?= $_SERVER['HTTP_ORIGIN'] . $_SERVER['WEB_MD_PATH'] ?>" target="_blank">
    <meta charset="utf-8">
    <link rel="stylesheet" href="css/calendar-style.css?ver=1.5t" type="text/css"/>
    <script src="scripts/calendar_conf.js?ver=1.5b"></script>
    <script src="scripts/calendar_widget.php"></script>
  </head>
  <body onload="if(date_edit_elem) set_edit(date_edit_elem); display_month(display_day);">
    <table width="100%" height="100%" class="calendar_widget" id="calendar_widget">
    <!--  &#xAB; &#x2039; &bull; &#x203A; &#xBB; -->
      <tr><td id='year_left'><span onclick="javascript:year_left_click(this)">&#xAB;</span></td>
        <td id='month_left'><span onclick="javascript:month_left_click(this)">&#x2039;</span></td>
        <td colspan=3 id='month_year_display' class="month_year_display">Month</td>
        <td id='month_right'><span onclick="javascript:month_right_click(this)">&#x203A;</span></td>
        <td id='year_right'><span onclick="javascript:year_right_click(this)">&#xBB;</span></td></tr>
      <tr><td class="weekday">L</td><td class="weekday">M</td><td class="weekday">M</td><td class="weekday">J</td><td class="weekday">V</td><td class="weekend">S</td><td class="weekend">D</td></tr>
      <tr><td class="number">1</td><td class="number">2</td><td class="number">3</td><td class="number">4</td><td class="number">5</td><td class="number">6</td><td class="number">7</td></tr>
      <tr><td class="number">8</td><td class="number">9</td><td class="number">10</td><td class="number">11</td><td class="number">12</td><td class="number">13</td><td class="number">14</td></tr>
      <tr><td class="number">15</td><td class="number">16</td><td class="number">17</td><td class="number">18</td><td class="number">19</td><td class="number">20</td><td class="number">21</td></tr>
      <tr><td class="number">22</td><td class="number">23</td><td class="number"><span class="current_selection">24</span></td><td class="number">25</td><td class="number">26</td><td class="number"><span class="today">27</span></td><td class="number">28</td></tr>
      <tr><td class="number">29</td><td class="number">30</td><td class="number">31</td><td class="number">32</td><td class="number">33</td><td class="number">34</td><td class="number">35</td></tr>
      <tr><td class="number">36</td><td class="number">37</td><td class="number">38</td><td class="number">39</td><td class="number">40</td><td class="number">41</td><td class="number">42</td></tr>
      <!-- tr><td colspan=4 class="clear_button_display"><a href="#" onclick="javascript:clear_button_click(this)">[clear]</a></td><td colspan=3 class="close_button_display"><a href="#" onclick="javascript:close_button_click(this)">[close]</a></td></tr -->
    </table>
  </body>
</html>
