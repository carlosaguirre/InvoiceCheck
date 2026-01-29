<?php
if ($showContent) {
?>
    <div class="fullArea2 relative">
        <div class="sticky toTop noFlow basicBG">
            <h1 class="txtstrk">ACTUALIZACIONES</H1>
            <form target="_self" method="POST" id="fileform" name="fileform" enctype="multipart/form-data" onsubmit="return isValidSubmit(event);">
                <p>File Pack: <input type="file" name="filepack" id="filepack" accept=".php,.js,.html,.zip"><input type="submit" name="fileButton" value="ENVIAR"></p>
                <?php /* <label><input type="checkbox" name="erollos" value="1">Para EROLLOS</label> */ ?>
            </form>
            <p id="filepackMessage"<?=$filepackClass?>><?=$filepackMessage?></p>
    <?php /* <form target="_self" method="POST" id="queryform" name="queryform" enctype="multipart/form-data" onsubmit="return isValidSubmit(event);">
        <p>Script Pack: <input type="file" name="scriptpack" id="scriptpack" accept=".sql"><input type="submit" name="scriptButton" value="ENVIAR"></p>
    </form>
    <p id="scriptpackMessage"<?=$scriptpackClass?>><?=$scriptpackMessage?></p>
    */ ?>
            <?php /* <p class="centered marbtm5"><span class="topvalign nowrap">SQL Query: </span><select id="statement" onchange="changeStatement(event);"><option value="SELECT">SELECT</option><option value="INSERT">INSERT</option><option value="UPDATE">UPDATE</option></select><span id="selectBlock"><input type="text" id="selectExpr" class="wid100px" oninput="validateText(event);"></span><span id="insertBlock" class="hidden">INSERT BLOCK</span><span id="updateBlock" class="hidden">UPDATE BLOCK</span> <input type="button" value="ENVIAR" onclick="sendQuery(this.value);"></p> */ ?>
        </div>
        <div id="queryResult"><?php /* uno<br>dos<br>tres<br>cuatro<br>cinco<br>seis<br>siete<br>ocho<br>nueve<br>diez<br>once<br>doce<br>trece<br>catorce<br>quince<br>dieciseis<br>diecisiete<br>dieciocho<br>diecinueve<br>veinte */ ?></div>
    </div>
<?php
} else {
?>
    <form target="_self" method="POST">
        C&Oacute;DIGO: <input type="password" name="keyphrase" id="keyphrase" autofocus>
        <input type="hidden" name="menu_accion" value="Upgrade">
    </form>
<?php
}
?>
    <!-- 
        -----------------------------
        POST:
        <?= arr2str($_POST); ?>

        -----------------------------
        FILES:
        <?= arr2str($_FILES); ?>

        -----------------------------
        SESSION:
        <?= arr2str($_SESSION); ?>

        -----------------------------
        COOKIES:
        <?= arr2str($_COOKIE); ?>
    -->
<?php 