<?php
clog2ini("templates.calendarBlock");
clog1seq(1);
$populateScript=$esAdmin?" ondblclick=\"populateCalendar(this.year);\"":"";
?>
<table class="calendar_widget appointment centered" id="calendar_widget">
    <tr><td id='year_left' class='nav'><span onclick="year_left_click(this);" onmouseover="calendar_check(this);">&#xAB;</span></td>
        <td id='month_left' class='nav'><span onclick="month_left_click(this);" onmouseover="calendar_check(this);">&#x2039;</span></td>
        <td colspan=3 id='month_year_display' class="month_year_display"<?= $populateScript ?>>Month</td>
        <td id='month_right' class='nav'><span onclick="month_right_click(this);" onmouseover="calendar_check(this);">&#x203A;</span></td>
        <td id='year_right' class='nav'><span onclick="year_right_click(this);" onmouseover="calendar_check(this);">&#xBB;</span></td></tr>
    <tr><th class="weekday"><span>L</span></th><th class="weekday"><span>M</span></th><th class="weekday"><span>M</span></th><th class="weekday"><span>J</span></th><th class="weekday"><span>V</span></th><th class="weekend"><span>S</span></th><th class="weekend"><span>D</span></th></tr>
    <tr><td class="number weekday">1</td><td class="number weekday">2</td><td class="number weekday">3</td><td class="number weekday">4</td><td class="number weekday">5</td><td class="number weekend">6</td><td class="number weekend">7</td></tr>
    <tr><td class="number weekday">8</td><td class="number weekday">9</td><td class="number weekday">10</td><td class="number weekday">11</td><td class="number weekday">12</td><td class="number weekend">13</td><td class="number weekend">14</td></tr>
    <tr><td class="number weekday">15</td><td class="number weekday">16</td><td class="number weekday">17</td><td class="number weekday">18</td><td class="number weekday">19</td><td class="number weekend">20</td><td class="number weekend">21</td></tr>
    <tr><td class="number weekday">22</td><td class="number weekday">23</td><td class="number weekday">24</td><td class="number weekday">25</td><td class="number weekday">26</td><td class="number weekend"><span class="today">27</span></td><td class="number weekend">28</td></tr>
    <tr><td class="number weekday">29</td><td class="number weekday">30</td><td class="number weekday">31</td><td class="number weekday">32</td><td class="number weekday">33</td><td class="number weekend">34</td><td class="number weekend">35</td></tr>
    <tr><td class="number weekday">36</td><td class="number weekday">37</td><td class="number weekday">38</td><td class="number weekday">39</td><td class="number weekday">40</td><td class="number weekend">41</td><td class="number weekend">42</td></tr>
</table>
<IMG src="data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7" onload="load_calendar();display_month(display_day);ekil(this);"></IMG>
<?php
clog1seq(-1);
clog2end("templates.calendarBlock");
