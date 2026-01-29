<?php
clog2ini("templates.casos");
clog1seq(1);
global $mail_usuario;
$exceptionList=["default","prueba"];
$mulKys = [""]+array_filter(array_keys($mail_usuario),function($el) use ($exceptionList) {
    return !in_array($el, $exceptionList);
});
sort($mulKys);
$muaKys = array_combine($mulKys, array_map('strtoupper', $mulKys));
?>
<div id="area_general" class="central">
  <h1 class="txtstrk">Casos de Uso</h1>
  <div id="area_detalle">
    <table>
        <tbody>
            <tr>
                <th><span>CORREO</span></th>
                <td>
                    <span>Origen: </span><select id="domain"><?=getHtmlOptions($muaKys)?></select><br>
                    <span>Destino: </span><input id="email" type="email" oninput="isValidEmailRT(event);"><br>
                    <span>Asunto: </span><input id="subject" type="text"><br>
                    <span>Contenido: </span><textarea id="message"></textarea><br>
                    <input id="sendMailBtn" type="button" value="Enviar" onclick="sendMail(event);">
                </td>
            </tr>
        </tbody>
    </table>
  </div>
</div>
<div id="resultDiv" class="central hidden" onclick="hideDiv(event);"><div class="basicBG"><img src="data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7" onload="cladd('principal','relative');this.parentNode.removeChild(this);">X Y Z</div></div>
<?php
clog1seq(-1);
clog2end("templates.casos");
