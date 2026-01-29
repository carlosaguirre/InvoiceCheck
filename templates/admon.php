<?php
clog2ini("templates.admon");
clog1seq(1);
?>
          <div id="area_base" class="central base clear">
            <div id="admin_head"><h1 class="txtstrk">Administraci&oacute;n</h1></div>
            <!-- input type="button" onclick="overlayWheel();" value="Overlay Test" -->
            <div id="admin_block">
<?php /* if ($consultaProv) { 
        if ($_esDesarrollo) {
          $zonaSpan="";
          $statusCel="<th class=\"shrinkCol\">Status:</th><td><select name=\"proveedor_status\" id=\"proveedor_status\">$statusOptions</select></td>";
        } else {
          $zonaSpan=" colspan=\"3\"";
          $statusCel="";
        }
?>
            <h2 class="notBorder" tabindex="-1" autofocus>Proveedores</h2>
            <form method="post" name="forma_admon_prv" id="forma_admon_prv" target="_self" onreset="resetForm(this)" enctype="multipart/form-data">
                <A name="proveedores"></A>
                <input type="hidden" name="menu_accion" value="Administracion">
                <input type="hidden" name="proveedor_id" id="proveedor_id"<?= $prvIdVal ?>>
                <table class="pad2c"><tr><th class="shrinkCol">Raz&oacute;n Social:</th><td colspan="3" class="searchblock"><input type="text" name="proveedor_field" id="proveedor_field"<?= $prvRzSocVal ?><?= $modificaProv?"":" readonly" ?>>
                <img src="imagenes/searchicon18.png" class="searchicon" height="18" width="18" alt="Buscar" onclick="overlay('showProveedores','Proveedores');"></td></tr>
                <tr><th>C&oacute;digo:</th><td><input type="text" name="proveedor_code" id="proveedor_code" size="6"<?= $prvCodVal ?><?= $modificaProv?"":" readonly" ?>></td>
                <th class="shrinkCol">RFC:</th><td><input type="text" name="proveedor_rfc" id="proveedor_rfc" size="12"<?= $prvRfcVal ?><?= $modificaProv?"":" readonly" ?>></td></tr>
                <tr><th>Zona:</th><td<?= $zonaSpan ?>><input type="text" name="proveedor_zona" id="proveedor_zona" size="12"<?= $prvZonaVal ?><?= $modificaProv?"":" readonly" ?>></td><?= $statusCel ?></tr></table>
<?php     if ($modificaProv) { ?>
                <div class="width100 centered">
                <input type="button" name="proveedor_delete" id="proveedor_delete" value="Borrar" style="display:<?= $prvDelBtnDisp ?>;" onclick="deleteDataElement('proveedor');">
                <input type="submit" name="proveedor_submit" id="proveedor_submit" value="Guardar">
                <input type="reset" name="proveedor_reset" id="proveedor_reset" value="Reset" onclick="if(document.forma_admon_prv && document.forma_admon_prv.proveedor_id) document.forma_admon_prv.proveedor_id.value='';fillDataCheck();resultadoAvanzado(false, 'proveedor', false);">
                </div>
<?php     } ?>
            </form>  <!-- FIN BLOQUE PROVEEDORES -->
            <br/>
<?php } */ ?>
<?php if ($consultaGrpo) { ?>
            <h2 class="notBorder" tabindex="-1" autofocus>Corporativo</h2>
            <form method="post" name="forma_admon_gpo" id="forma_admon_gpo" target="_self" onreset="resetForm(this)" enctype="multipart/form-data">
              <A name="corporativo"></A>
              <input type="hidden" name="menu_accion" value="Administracion">
              <input type="hidden" name="grupo_id" id="grupo_id"<?= $gpoIdVal ?>>
              <table class="pad2c"><tr><th class="shrinkCol">Raz&oacute;n Social:</th><td colspan="3" class="searchblock"><input type="text" name="grupo_field" id="grupo_field"<?= $gpoRzSocVal ?><?= $modificaGrpo?"":" readonly" ?>>
              <img src="imagenes/searchicon18.png" class="searchicon" height="18" width="18" alt="Buscar" onclick="overlay('showGrupo','Corporativo');"></td></tr>
              <tr><th>Alias:</th><td><input type="text" name="grupo_alias" id="grupo_alias" size="6"<?= $gpoBrfVal ?><?= $modificaGrpo?"":" readonly" ?>></td>
              <th class="shrinkCol">RFC:</th><td><input type="text" name="grupo_rfc" id="grupo_rfc" size="12"<?= $gpoRfcVal ?><?= $modificaGrpo?"":" readonly" ?>></td></tr>
              <tr><th>Prefijo Solicitud:</th><td><input type="text" name="grupo_cut" id="grupo_cut" size="3" maxlength="3"<?= $gpoCutVal ?><?= $modificaGrpo?"": " readonly" ?> class="uppercase"></td><?php if ($esPruebas && $esComExt) { ?><th>Comercio Exterior</th><td><label><input type="checkbox" name="grupo_filtro[]" id="grupo_filtro1" value="1"<?= $gpoFlt1Val ?>> Importación</label><br><label><input type="checkbox" name="grupo_filtro[]" id="grupo_filtro2" value="2"<?= $gpoFlt2Val ?>> Importación de Activos</label><br><label><input type="checkbox" name="grupo_filtro[]" id="grupo_filtro4" value="4"<?= $gpoFlt4Val ?>>Exportación</label></td><?php } ?></tr></table>
<?php     if ($modificaGrpo) { ?>
              <div class="width100 centered">
              <input type="button" name="grupo_delete" id="grupo_delete" value="Borrar" style="display:<?= $gpoDelBtnDisp ?>;" onclick="deleteDataElement('grupo');">
              <input type="submit" name="grupo_submit" id="grupo_submit" value="Guardar">
              <input type="reset" name="grupo_reset" id="grupo_reset" value="Reset" onclick="if(document.forma_admon_gpo && document.forma_admon_gpo.grupo_id) document.forma_admon_gpo.grupo_id.value='';fillDataCheck();resultadoAvanzado(false, 'grupo', false);">
              </div>
<?php     } ?>
            </form>  <!-- FIN BLOQUE GRUPO -->
            <br/>
<?php } ?>

<?php if ($consultaBancos) { ?>
            <h2>Bancos <IMG src="imagenes/icons/upload1.png" width="30" height="22" class="buttonLike" title="Carga Masiva de Cuentas"><input type="file" name="uploadBank" class="hidden"></h2>
            <form method="post" name="forma_admon_bnk" id="forma_admon_bnk" target="_self" onreset="resetForm(this)" enctype="multipart/form-data">
              <A name="bancos"></A>
              <input type="hidden" name="menu_accion" value="Administracion">
              <input type="hidden" name="banco_id" id="banco_id"<?= $bnkIdVal ?>>
              <table class="pad2c">
                <tr><th class="shrinkCol">Raz&oacute;n Social:</th><td colspan="3" class="searchblock"><input type="text" name="banco_field" id="banco_field"<?= $bnkRzSocVal ?><?= $modificaBancos?"":" readonly" ?>><img src="imagenes/searchicon18.png" class="searchicon" height="18" width="18" alt="Buscar" onclick="overlay('showBancos','Bancos');setUploadBankList();"></td></tr>
                <tr><th class="shrinkCol">Clave:</th><td><input type="text" name="banco_clave" id="banco_clave" size="6"<?= $bnkKeyVal ?><?= $modificaBancos?"":" readonly" ?> onclick="return eventCancel(event);"></td><th class="shrinkCol">Alias:</th><td><input type="text" name="banco_alias" id="banco_alias" size="12"<?= $bnkBrfVal ?><?= $modificaBancos?"":" readonly" ?> onclick="return eventCancel(event);"></td></tr>
                <tr><th class="shrinkCol">RFC:</th><td><input type="text" name="banco_rfc" id="banco_rfc" size="12"<?= $bnkRfcVal ?><?= $modificaBancos?"":" readonly" ?> onclick="return eventCancel(event);"></td><th class="shrinkCol">Status:</th><td><label class="marbtm0i fontNormali">Activo <input type="checkbox" class="top" name="banco_status" id="banco_status" value="1" size="12"<?= $bnkSttVal ?><?= $modificaBancos?"":" readonly" ?> onchange="this.checked=this.readOnly?!this.checked:this.checked"></label></td></tr>
<?php /*        <tr><th class="noFlow"><div class="hgtSlider hei0 relative">Alias: <img src="imagenes/icons/upArrow.png" class="abs_e btnLt t6" width="20px" onclick="fee(lbycn('hgtSlider'),el=>clfix(el,['hei0','hei24']));"></div><div class="hgtSlider hei24 relative">Clave: <img src="imagenes/icons/downArrow.png" class="abs_e btnLt t6" width="20px" onclick="fee(lbycn('hgtSlider'),el=>clfix(el,['hei0','hei24']));"></div></th><td><div class="hgtSlider hei0" onclick="fee(lbycn('hgtSlider'),el=>clfix(el,['hei0','hei24']));"><input type="text" name="banco_alias" id="banco_alias" size="12"<?= $bnkBrfVal ?><?= $modificaBancos?"":" readonly" ?> onclick="return eventCancel(event);"></div><div class="hgtSlider hei24" onclick="fee(lbycn('hgtSlider'),el=>clfix(el,['hei0','hei24']));"><input type="text" name="banco_clave" id="banco_clave" size="6"<?= $bnkKeyVal ?><?= $modificaBancos?"":" readonly" ?> onclick="return eventCancel(event);"></div></td><th class="shrinkCol">RFC:</th><td><input type="text" name="banco_rfc" id="banco_rfc" size="12"<?= $bnkRfcVal ?><?= $modificaBancos?"":" readonly" ?>></td></tr> */ ?>
                <tr><th class="shrinkCol" title="Cuenta Contable">Cuenta Cont.</th><td colspan="3"><input type="text" name="banco_cuenta" id="banco_cuenta"<?= $bnkCtaConta ?><?= $modificaBancos?"":" readonly" ?>></td></tr>
              </table>
<?php     if ($modificaBancos) { ?>
              <div class="width100 centered">
              <input type="button" name="banco_delete" id="banco_delete" value="Borrar" style="display:<?= $bnkDelBtnDisp ?>;" onclick="deleteDataElement('banco');">
              <input type="submit" name="banco_submit" id="banco_submit" value="Guardar">
              <input type="reset" name="banco_reset" id="banco_reset" value="Reset" onclick="fillValue(['banco_id','banco_field','banco_clave','banco_alias','banco_rfc','banco_cuenta'], '');changeAttribute('banco_status', 'checked', false);fillDataCheck();">
              </div>
<?php     } ?>
            </form>  <!-- FIN BLOQUE BANCOS -->
            <br/>
<?php } ?>

<?php if ($consultaUsrs) { ?>
            <h2>Usuarios</h2>
            <form method="post" name="forma_admon_user" id="forma_admon_user" target="_self" onreset="resetForm(this)" enctype="multipart/form-data">
              <A name="usuarios"></A>
              <input type="hidden" name="menu_accion" value="Administracion">
              <input type="hidden" name="user_id" id="user_id"<?= $usrIdVal ?>>
              <table class="pad2c"><tr><th>Usuario:</th><td class="searchblock"><input type="text" name="user_field" id="user_field"<?= $usernameVal ?><?= $modificaUsrs?"":" readonly" ?>>
              <img src="imagenes/searchicon18.png" class="searchicon" height="18" width="18" alt="Buscar" onclick="overlay('showUsuarios','Usuarios');"></td></tr>
<?php     if ($modificaUsrs) { ?>
              <tr><th>Contrase&ntilde;a:</th><td><input type="password" name="user_password" id="user_password"><?= $modificaUsrs?" <label class=\"fixsmall\" title=\"Se requiere cambiar contraseña al ingresar\"><input type=\"checkbox\" name=\"user_updkey\" id=\"user_updkey\" value=\"1\"{$keyUpdCheck}><input type=\"hidden\" name=\"user_updval\" id=\"user_updval\">Cambiar</label> <label class=\"fixsmall\" title=\"Permite ingresar una vez con contraseña de '$username'\"><input type=\"checkbox\" name=\"user_syskey\" id=\"user_syskey\" value=\"1\"{$keySysCheck}><input type=\"hidden\" name=\"user_sysval\" id=\"user_sysval\"><span id=\"user_sysname\">$username</span></label>":"" ?></td></tr>
<?php     } ?>
              <tr><th class="shrinkCol">Nombre Completo:</th><td><input type="text" name="user_realname" id="user_realname" size="35"<?= $userRNameVal ?><?= $modificaUsrs?"":" readonly" ?>></td></tr>
              <tr><th class="shrinkCol">Correo electr&oacute;nico:</th><td><input type="text" name="user_email" id="user_email" size="35"<?= $userEmailVal ?><?= $modificaUsrs?"":" readonly" ?>></td></tr>
              <tr><th>Observaciones:</th><td><input type="text" name="user_obs" id="user_obs" size="35"<?= $userObsVal ?><?= $modificaUsrs?"":" readonly" ?>></td></tr></table>
              <table class="nomargin">
<?php 
        $numcel=0;
        $uxg=$_POST["uxg"]??[];
        $uxgOld=$_POST["uxgOld"]??[];
        for ($ix=0; isset($prfData[$ix]); $ix++) {
          $prfRow=$prfData[$ix];
          if (intval($prfRow["estado"])==0) continue;
          if ($numcel==0) echo "<tr>";
          $prfId=$prfRow["id"];
          $isUPChecked=(!empty($_POST["user_perfil"])&&in_array($prfId, $_POST["user_perfil"], TRUE));
          $uxgVal=$uxg[$prfId]??"";
          $uxgOldVal=$uxgOld[$prfId]??"";
?>
                  <td style="text-align:left;white-space:nowrap;"><input type="checkbox" name="user_perfil[]" id="user_perfil_<?= $prfId ?>" class="user_perfil nomargini vAlignCenter<?= $isUPChecked?" bgbeige2":"" ?>" value="<?= $prfId ?>"<?= $isUPChecked?" checked":"" ?><?= $modificaUsrs?" onchange=\"clset(this.parentNode,'bgbeige2',this.checked);\"":" disabled" ?>><label class="nomargini fontMedium vAlignCenter widMin36px" for="user_perfil_<?= $prfId ?>"><?= $prfRow["nombre"] ?></label><?php if ($modificaUsrs) { ?><img src="imagenes/icons/group.png" id="gpxpr<?= $prfId ?>" nm="<?= $prfRow["nombre"] ?>" class="upxgBtn" width="20"><input type="hidden" id="user_perfil_old_<?= $prfId ?>" class="usrprfold" name="user_perfilOld[]" value="<?= $prfId ?>"<?=$isUPChecked?"":" disabled"?>><input type="hidden" id="uxg_old_<?= $prfId ?>" class="uxgold" name="uxgOld[<?= $prfId ?>]" value="<?= $uxgOldVal ?>"<?= isset($uxgOldVal[0])?"":" disabled" ?>><input type="hidden" id="uxg_<?= $prfId ?>" class="uxg" name="uxg[<?= $prfId ?>]" value="<?= $uxgVal ?>"<?= isset($uxgVal[0])?"":" disabled" ?>><?php } ?></td>
<?php
          $numcel=($numcel+1)%3;
          if ($numcel==0) echo "</tr>";
        }
        if ($numcel>0) { // solo puede ser 1 o 2
          echo "<td";
          if ($numcel==1) echo " colspan=\"2\""; // solo se ha ocupado la primer celda, faltan 2
          echo "></td></tr>";
        }
?>
              </table><?= isset($usrIdVal[0])&&$modificaUsrs?"<img src=\"data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7\" onload=\"enableProfilesPerUser();ekil(this);\">":"" ?>
<?php /*
        $classGrupo = " class=\"hidden\"";
        $num = count($prfObj->data_array);
        if (!empty($_POST["user_perfil"])) {
            for ($ix=0; $ix<$num; $ix++) {
                if($prfObj->data_array[$ix]["nombre"]=="Compras" && in_array($prfObj->data_array[$ix]["id"], $_POST["user_perfil"], TRUE)) {
                    $classGrupo = "";
                }
            }
        }
?>
              <table style="margin: 0 auto;">
                <tr>
                  <td style="text-align:left;"><?php
                    ?><input type="checkbox" name="user_perfil[]" id="user_perfil_<?= $prfObj->data_array[0]["id"] ?>" class="user_perfil" value="<?= $prfObj->data_array[0]["id"] ?>"<?= (!empty($_POST["user_perfil"])&&in_array($prfObj->data_array[0]["id"], $_POST["user_perfil"], TRUE)?" checked":"") ?> onclick="<?= $modificaUsrs?"validaCompras(this.checked, '".$prfObj->data_array[0]["nombre"]."');":"return false;" ?>"<?= $modificaUsrs?"":" disabled" ?>><?= $prfObj->data_array[0]["nombre"] ?></td>
                  <td style="text-align:left;"><input type="checkbox" name="user_perfil[]" id="user_perfil_<?= $prfObj->data_array[3]["id"] ?>" class="user_perfil" value="<?= $prfObj->data_array[3]["id"] ?>"<?= (!empty($_POST["user_perfil"])&&in_array($prfObj->data_array[3]["id"], $_POST["user_perfil"], TRUE)?" checked":"") ?> onclick="<?= $modificaUsrs?"validaCompras(this.checked, '".$prfObj->data_array[3]["nombre"]."');":"return false;" ?>"<?= $modificaUsrs?"":" disabled" ?>><?= $prfObj->data_array[3]["nombre"] ?></td>
                  <td style="text-align:left;" id="aliasListaComprasGrupoCell"<?= empty($aliasListaComprasGrupo)?"":" title=\"$aliasListaComprasGrupo\"" ?>><input type="checkbox" name="user_perfil[]" id="user_perfil_<?= $prfObj->data_array[4]["id"] ?>" class="user_perfil" value="<?= $prfObj->data_array[4]["id"] ?>"<?= (!empty($_POST["user_perfil"])&&in_array($prfObj->data_array[4]["id"], $_POST["user_perfil"], TRUE)?" checked":"") ?> onclick="<?= $modificaUsrs?"validaCompras(this.checked, '".$prfObj->data_array[4]["nombre"]."');":"return false;" ?>"<?= $modificaUsrs?"":" disabled" ?>><?= $prfObj->data_array[4]["nombre"] ?></td>
                </tr>
                <tr>
                  <td style="text-align:left;"><input type="checkbox" name="user_perfil[]" id="user_perfil_<?= $prfObj->data_array[5]["id"] ?>" class="user_perfil" value="<?= $prfObj->data_array[5]["id"] ?>"<?= (!empty($_POST["user_perfil"])&&in_array($prfObj->data_array[5]["id"], $_POST["user_perfil"], TRUE)?" checked":"") ?><?= $modificaUsrs?"":" disabled" ?>><?= $prfObj->data_array[5]["nombre"] ?></td>
                  <td style="text-align:left;"><input type="checkbox" name="user_perfil[]" id="user_perfil_<?= $prfObj->data_array[7]["id"] ?>" class="user_perfil" value="<?= $prfObj->data_array[7]["id"] ?>"<?= (!empty($_POST["user_perfil"])&&in_array($prfObj->data_array[7]["id"], $_POST["user_perfil"], TRUE)?" checked":"") ?><?= $modificaUsrs?"":" disabled" ?>><?= $prfObj->data_array[7]["nombre"] ?></td>
                  <td><?php     if ($modificaUsrs) {
                    ?><input type="button" name="selectorEmpresas" id="selectorEmpresas"<?= $classGrupo ?> value="Empresas" onclick="overlay('showCompraGrupo','Permiso para Facturar a Empresas', false, 'lista='+document.getElementById('listaComprasGrupoId').value);ebyid('dialogbox').style.height='calc(100% - 44px)';"><?php
                    } else echo "&nbsp;";
                  ?><input type="hidden" name="listaComprasGrupoId" id="listaComprasGrupoId" value="<?= $aliasListaComprasGrupo ?>"></td>
                </tr>
<?php
        $numcel=0;
        for ($ix=8; $ix<$num; $ix++) {
          $perData=$prfObj->data_array[$ix];
          if (intval($perData["estado"])==0) continue;
          if ($numcel==0) echo "<tr>";
?>
                  <td style="text-align:left;white-space:nowrap;"><input type="checkbox" name="user_perfil[]" id="user_perfil_<?= $perData["id"] ?>" class="user_perfil" value="<?= $perData["id"] ?>"<?= (!empty($_POST["user_perfil"])&&in_array($perData["id"], $_POST["user_perfil"], TRUE)?" checked":"") ?><?= $modificaUsrs?"":" disabled" ?>><?= $perData["nombre"] ?></td>
<?php
          $numcel=($numcel+1)%3;
          if ($numcel==0) echo "</tr>";
        }
        if ($numcel>0) { // solo puede ser 1 o 2
          echo "<td";
          if ($numcel==1) echo " colspan=\"2\""; // solo se ha ocupado la primer celda, faltan 2
          echo "></td></tr>";
        }
?>
                <tr id="userByGroupRow" class="hidden"><td colspan="3" class="centered"><input type="button" id="user_group" class="padv02 marbtm5" value="Permisos X Grupo" onclick="overlayMessage(showUsuariosGrupo(),'Permisos por Empresa');ebyid('dialogbox').style.height='calc(100% - 44px)';"></td></tr>
              </table>
<?php */ ?>
<?php     if ($modificaUsrs) { ?>
              <div class="width100 centered">
              <input type="button" name="user_delete" id="user_delete" value="Borrar" style="display:<?= $usrDelBtnDisp ?>;" onclick="deleteDataElement('user');">
              <input type="submit" name="user_submit" id="user_submit" value="Guardar">
              <input type="reset" name="user_reset" id="user_reset" value="Reset" onclick="if(document.forma_admon_user && document.forma_admon_user.user_id) document.forma_admon_user.user_id.value='';fillDataCheck();resultadoAvanzado(false, 'usuario', false);">
              </div>
<?php     } ?>
            </form>  <!-- FIN BLOQUE USUARIOS -->
            <br/>
<?php } ?>
<?php if ($consultaPerm) { ?>
            <h2 onclick="ebyid('actionName').focus();">Acciones</h2>
            <div class="blk">
              <div class="inblock wid110px topvalign">
                <select name="actions" id="actions" nombre="Acción" genero="f" prefixId="action" minPrefixId="act" class="topvalign wid110px" clase="Acciones" onclick="ebyid('actionName').focus();" onchange="pick(this);" size="4"><?= $actOptions ?></select></div> 
              <div class="inblock wid335px topvalign">
                <div class="hei24 lefted nowrap"><div class="inblock boldValue lefted wid90px hei24">Nombre: </div><input type="text" name="actionName" id="actionName" class="topvalign hei20 wid245px" maxlength="45"></div>
                <div class="hei24 lefted nowrap"><div class="inblock boldValue lefted wid90px hei24">Descripción: </div><input type="text" name="actionDesc" id="actionDesc" class="topvalign hei20 wid245px" maxlength="100"></div>
                <div class="hei24 centered"><input type="button" name="delAction" id="delAction" value="Eliminar" onclick="remove('actions')"> <input type="button" name="saveAction" id="saveAction" value="Guardar" onclick="save('actions')"></div>
              </div>
            </div>
            <br/>
            <h2 onclick="ebyid('profileName').focus();">Perfiles</h2>
            <div class="blk">
              <div class="inblock wid110px topvalign"><select name="profiles" id="profiles" nombre="Perfil" genero="m" prefixId="profile" minPrefixId="prf" class="topvalign wid110px" clase="Perfiles" onclick="ebyid('profileName').focus();" onchange="pick(this);" size="11"><?= $prfOptions ?></select></div>
              <div class="inblock wid335px topvalign">
                <div class="hei24 lefted nowrap"><div class="inblock boldValue lefted wid90px hei24">Nombre: </div><input type="text" name="profileName" id="profileName" class="topvalign hei20 wid245px" maxlength="45"></div>
                <div class="hei24 lefted nowrap"><div class="inblock boldValue lefted wid90px hei24">Detalle: </div><input type="text" name="profileDesc" id="profileDesc" class="topvalign hei20 wid245px" maxlength="100"></div>
                <div class="inblock wid108px topvalign"><div class="boldValue centered hei24">Estado</div>
                  <div class="widfit nomargin izquierdo"><input type="radio" id="profileOn" name="profileState" class="profileState vAlignCenter martop5i marbtm0i" value="1" checked> <label for="profileOn">Activo</label><br><input type="radio" id="profileOff" name="profileState" class="profileState vAlignCenter martop5i marbtm0i" value="0"> <label for="profileOff">Inactivo</label></div></div>
                <div class="inblock wid108px topvalign"><div class="boldValue centered hei24">Consultar</div><select name="profileRead" id="profileRead" size="5" multiple class="topvalign hei20 wid95"><?= $actOptions1 ?></select></div>
                <div class="inblock wid108px topvalign"><div class="boldValue centered hei24">Modificar</div><select name="profileWrite" id="profileWrite" size="5" multiple class="topvalign hei20 wid95"><?= $actOptions1 ?></select></div>
                <div class="hei24 centered martop10"><input type="button" name="delProfile" id="delProfile" value="Eliminar" onclick="remove('profiles')"> <input type="button" name="saveProfile" id="saveProfile" value="Guardar" onclick="save('profiles')"></div>
              </div>
            </div>
            <br/>
<?php } ?>
<?php if ($_esDesarrollo) { ?>
            <h2>Cuentas Contables Fijas</h2>
            <form method="post" name="forma_accounts" id="forma_accounts" target="_self" onreset="resetForm(this)" enctype="multipart/form-data">
              <A name="cuentas"></A>
              <input type="hidden" name="menu_accion" value="Administracion">
              <div class="blk">
                <div class="inblock wid110px topvalign"><select name="accountList" id="accountList" nombre="Cuenta" genero="f" prefixId="account" minPrefixId="acc" class="topvalign wid110px" onclick="ebyid('accountName').focus();" onchange="pick(this);" size="4"><?= $accOptions ?></select></div>
                <div class="inblock wid335px topvalign">
                  <div class="hei24 lefted nowrap"><div class="inblock boldValue lefted wid90px hei24">Nombre: </div><input type="hidden" name="accountId" id="accountId"><input type="text" name="accountName" id="accountName" class="topvalign hei20 wid245px" maxlength="45"></div>
                  <div class="hei24 lefted nowrap"><div class="inblock boldValue lefted wid90px hei24">Cuenta: </div><input type="text" name="accountNumber" id="accountNumber" class="topvalign hei20 wid245px" maxlength="45"></div>
                </div>
                  <div class="hei24 centered"><input type="submit" name="cuenta_delete" id="cuenta_delete" value="Eliminar" onclick="remove('account')"> <input type="button" name="cuenta_submit" id="cuenta_submit" value="Guardar" onclick="save('account')"> <input type="reset" name="cuenta_reset" id="cuenta_reset" value="Reset" onclick="const accL=ebyid('accountList'); accL.value=''; pick(this);"></div>
              </div>
            </form>
            <br/>
            <h2>Tipos Contables para Proveedores</h2>
            <div class="blk">
              <div class="inblock wid110px topvalign"><select name="provtype" id="provtype" class="topvalign wid110px" onclick="ebyid('tipoName').focus();" onchange="pick(this);" size="4"><?= $tprvOptions ?></select></div>
              <div class="inblock wid335px topvalign">
                <div class="hei24 lefted nowrap"><div class="inblock boldValue lefted wid90px hei24">Nombre: </div><input type="text" name="tipoName" id="tipoName" class="topvalign hei20 wid245px" maxlength="45"></div>
                <div class="hei24 lefted nowrap"><div class="inblock boldValue lefted wid90px hei24">Cuentas: </div><input type="text" name="tipoCuenta" id="tipoCuenta" class="topvalign hei20 wid245px"></div>
                <div class="lefted nowrap"><div class="inblock lefted wid90px hei24"><select name="accname" id="accname" class="topvalign wid90px" onclick="ebyid('accountName').focus();"></select></div></div>
                <div class="hei24 centered"><input type="button" name="delProvType" id="delProvType" value="Eliminar" onclick="remove('provtype')"> <input type="button" name="saveProvType" id="saveProvType" value="Guardar" onclick="save('provtype')"></div>
              </div>
            </div>
            <br/>
<?php /*
              <div class="inblock wid110px topvalign"><select name="actions" id="actions" nombre="Acción" genero="f" prefixId="action" minPrefixId="act" class="topvalign wid110px" clase="Acciones" onclick="ebyid('actionName').focus();" onchange="pick(this);" size="4"><?= $actOptions ?></select></div> 
              <div class="inblock wid335px topvalign">
                <div class="hei24 lefted nowrap"><div class="inblock boldValue lefted wid90px hei24">Nombre: </div><input type="text" name="actionName" id="actionName" class="topvalign hei20 wid245px" maxlength="45"></div>
                <div class="hei24 lefted nowrap"><div class="inblock boldValue lefted wid90px hei24">Descripción: </div><input type="text" name="actionDesc" id="actionDesc" class="topvalign hei20 wid245px" maxlength="100"></div>
                <div class="hei24 centered"><input type="button" name="delAction" id="delAction" value="Eliminar" onclick="remove('actions')"> <input type="button" name="saveAction" id="saveAction" value="Guardar" onclick="save('actions')"></div>
              </div>
*/ ?>
<?php } ?>

<?php if ($modificaProv && $availableCheckMetodoPago) { ?>
            <script>
            document.write("<h2>Variables</h2>");
            document.write("<form method='post' name='forma_admon_data' id='forma_admon_data' target='_self' onreset='resetForm(this)' enctype='multipart/form-data'>");
            document.write("<A name='variables'></A><input type='hidden' name='menu_accion' value='Administracion'>");
            document.write("Validar M&eacute;todo de Pago: <input type='checkbox' name='metodoPago' id='metodoPago' value='SI' onclick='guardaValor(this);'<?= $defaultCheckMetodoPago ?>>");
            document.write("</form>");
            document.write("<br/>");
            </script>
<?php } ?>
<?php if ($_esDesarrollo) { ?>
            <button type="button" name="ejecuta" value="1" onclick="doRootProcess();">Realizar Proceso</button>
            <br/>&nbsp;
            <button type="button" name="test" value="1" onclick="doTest();">Pruebas</button>
<?php } ?>
            </div>
            
          </div>
<?php
/* <img src="data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7" onload="traceActiveElement();ekil(this);"> */
clog1seq(-1);
clog2end("templates.admon");
