<?php
clog2ini("templates.formapago");
clog1seq(1);
?>
<div id="area_general" class="vexpand wid400px centered inblock">
  <div id="area_top" class="basicBG padhtt zIdx1"><h1 class="nomargin">Formas de Pago</h1></div>
  <div id="area_central" class="formapago centered">
    <div id="result_headers" class="oneLine row centered">
      <div class="column col_20">&nbsp;</div>
      <div class="column col_10 boldValue">CODIGO</div>
      <div class="column col_50 boldValue">DESCRIPCION</div>
      <div class="column col_20">&nbsp;</div>
    </div>
    <div id="result_data" class="lessOneLine yFlow centered">
<?php foreach ($catData as $dataRow) {
    $isChecked=isset($mdpMap[$dataRow["codigo"]]);
?>
      <div class="row row1">
        <div class="column col_20">&nbsp;</div>
        <div class="column isdata col_5<?= $isChecked?" bggreen":"" ?>"><input type="checkbox" name="chk<?=$dataRow["id"]?>" value="<?= $dataRow["codigo"] ?>"<?= $isChecked?" class=\"isChecked\" checked":"" ?> onchange="isChecked(event);"></div>
        <div class="column isdata col_5<?= $isChecked?" bggreen":"" ?>"><?= $dataRow["codigo"] ?></div>
        <div class="column isdata col_50<?= $isChecked?" bggreen":"" ?>"> <?= $dataRow["descripcion"] ?> </div>
        <div class="column col_20">&nbsp;</div>
      </div>
<?php } ?>
    </div>
  </div>
  <div id="foot0" class="basicBG padh2 zIdx1">
    <input type="button" onclick="saveData();" value="Guardar">
  </div>
</div>
<?php
clog1seq(-1);
clog2end("templates.formapago");
