<?php
  clog2ini("seccionEmergente");
  clog1seq(1);
?>
    <!-- NOMAIL INI --><div id="overlay" class="noprint" style="visibility:hidden;">
      <div id="dialogbox" >
        <div id="close_row" onmousedown="mouseIsDown(event);" >
          <div id="dialog_title">Aviso</div>
          <div id="dialog_corner">
            <!-- input type="button" id="closeOverlay" value="X" onclick="overlay()" -->
            <button id="closeOverlay" onclick="overlayClose(ebyid('closeButton'));">X</button></div>
        </div>
        <div id="dialog_resultarea">
          <p>Content you want the user to see goes here.</p>
        </div>
        <div id="closeButtonArea"><input type="button" id="closeButton" value="Cerrar" class="marginV1" onclick="overlayClose(this);"></div>
      </div>
      <div id="wheelbox" class="hidden">
          <img src="<?=$waitImgName??"imagenes/icons/flying.gif"?>"<?=isset($waitClass[0])?" class=\"$waitClass\"":""?>/>
      </div>
    </div>
    <!-- NOMAIL END -->
<?php
  clog1seq(-1);
  clog2end("seccionEmergente");
