<?php
//require_once dirname(__DIR__)."/bootstrap.php";
clog2ini("barralateral");
clog1seq(1);
//$cliDatRes=["REMOTE_ADDR"=>["192.168.1.254"], "HTTP_USER_AGENT"=>["Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36","Mozilla/5.0 (Linux; Android 8.0.0; SM-G955U Build/R16NW) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Mobile Safari/537.36"]];
/*foreach (getClientData() as $key => $value) {
  $arr=$cliDatRes[$key]??[];
  echo "<!-- $key='$value' | ".(isset($arr[0])?(in_array($value, $arr)?"TRUE":"FALSE"):"NONE")." -->\n";
}*/
$rules="";
$enableHiddenMenu=false;
if (hasUser()) {
  $u=getUser();
  $unm=$u->nombre;
  $esAdmin = validaPerfil("Administrador");
  $esSistemas = $esAdmin||validaPerfil("Sistemas");
  $esSuperAdmin = $esAdmin && $unm==="admin";
  $esPruebas=in_array(getUser()->nombre, ["admin","sistemas","sistemas1","sistemas2","test2"]);
  $enableHiddenMenu=!isMobile();
  if (!isset($_REQUEST["oldbar"])) {
    /** /
    if ($esSuperAdmin) echo "<!-- Usuario: admin -->\n";
    if ($esAdmin && !$esSuperAdmin) echo "<!-- Perfil: Administrador -->\n";
    if ($esSistemas && !$esAdmin && !$esSuperAdmin) echo "<!-- Perfil: Sistemas -->\n";
    /**/
    if ($esSuperAdmin) {
      $rules="user.name=$unm";
      //echo "<!-- esUA: $rules -->";
    } else if ($esSistemas) {
      $rules=($esAdmin?"PAdministrador":"PSistemas");
      //echo "<!-- esSistemas: $rules -->";
    } else {
      echo "<!-- USR.PERMISOS: ".(isset($u->permisos)?json_encode($u->permisos):"-none yet-")." -->\n";
      $permisos=getPermisos()??[]; if ($permisos===false) $permisos=[];
      echo "<!-- PERMISOS(".gettype($permisos).")[".count($permisos)."]: ".implode(",", $permisos)." -->\n";
      $perfiles=getPerfiles();
      echo "<!-- PERFILES(".gettype($perfiles).")[".count($perfiles)."]: ".implode(",", $perfiles)." -->\n";
      $perfilesFix=preg_replace('/^/', 'P', $perfiles);
      echo "<!-- PERFILES FIX(".gettype($perfilesFix).")[".count($perfilesFix)."]: ".implode(",", $perfilesFix)." -->\n";
      $rules=array_merge($permisos,$perfilesFix);
      echo "<!-- REGLAS1(".gettype($rules).")[".count($rules)."]: ".implode(",", $rules)." -->\n";
      $rules[]="user.name=".$unm;
      echo "<!-- REGLAS2(".gettype($rules).")[".count($rules)."]: ".implode(",", $rules)." -->\n";
      //echo "<!-- Otros: $rules -->";
    }
    global $mnuObj;
    if (!isset($mnuObj)) {
      require_once "clases/Menu.php";
      $mnuObj=new Menu();
    }
    $mnuObj->rows_per_page=0;
    $menuList=$mnuObj->getDMenu($rules);
    global $query;
    $menuQry=$query;
    $menuLog=$mnuObj->log;
  } else {
    if (!isset($consultaAlta)) $consultaAlta = consultaValida("Alta"); //if($consultaAlta) clog1("Permiso Consulta Alta");
    if (!isset($consultaProc)) $consultaProc = consultaValida("Procesar"); //if ($consultaProc) clog1("Permiso Consulta Procesar");
    if (!isset($consultaConR)) $consultaConR = consultaValida("Contrarrecibo"); //if ($consultaConR) clog1("Permiso Consulta Contrarrecibo");
    if (!isset($consultaExpr)) $consultaExpr = consultaValida("Exportar"); //if ($consultaExpr) clog1("Permiso Consulta Exportar");
    if (!isset($consultaResp)) $consultaResp = consultaValida("Respaldar"); //if ($consultaResp) clog1("Permiso Consulta Respaldar");
    if (!isset($consultaGrpo)) $consultaGrpo = consultaValida("Grupo"); //if ($consultaGrpo) clog1("Permiso Consulta Grupo");
    if (!isset($consultaProv)) $consultaProv = consultaValida("Proveedor"); //if ($consultaProv) clog1("Permiso Consulta Proveedor");
    if (!isset($consultaUsrs)) $consultaUsrs = consultaValida("Usuarios"); //if ($consultaUsrs) clog1("Permiso Consulta Usuarios");
    if (!isset($consultaCata)) $consultaCata = consultaValida("Catalogos"); //if ($consultaCata) clog1("Permiso Consulta Catalogos");
    if (!isset($consultaData)) $consultaData = consultaValida("Datos"); //if ($consultaData) clog1("Permiso Consulta Datos");
    if (!isset($consultaPerm)) $consultaPerm = consultaValida("Permisos"); //if ($consultaPerm) clog1("Permiso Consulta Permisos");
    if (!isset($consultaRepo)) $consultaRepo = consultaValida("Reportes");
    if ($consultaRepo) clog1("Permiso Consulta Reportes");
    else clog1("Sin Permiso de Consultar Reportes");
    if (!isset($modificaConR)) $modificaConR = modificacionValida("Contrarrecibo"); //if ($modificaConR) clog1("Permiso Modifica Contrarrecibo");
    if (!isset($modificaUsrs)) $modificaUsrs = modificacionValida("Usuarios"); //if ($modificaUsrs) clog1("Permiso Modifica Usuarios");
    if (!isset($modificaGrpo)) $modificaGrpo = modificacionValida("Grupo"); //if ($modificaGrpo) clog1("Permiso Modifica Grupo");
    if (!isset($modificaProv)) $modificaProv = modificacionValida("Proveedor"); //if ($modificaProv) clog1("Permiso Modifica Proveedor");
    if (!isset($modificaPerm)) $modificaPerm = modificacionValida("Permisos"); //if ($modificaPerm) clog1("Permiso Modifica Permisos");
    if (!isset($modificaCata)) $modificaCata = modificacionValida("Catalogos"); //if ($modificaCata) clog1("Permiso Modifica Catalogos");
    if (!isset($modificaData)) $modificaData = modificacionValida("Datos"); //if ($modificaData) clog1("Permiso Modifica Datos");
    if (!isset($modificaRepo)) $modificaRepo = modificacionValida("Reportes"); //if ($modificaRepo) clog1("Permiso Modifica Reportes");
    if (!isset($consultaEmpl)) $consultaEmpl = consultaValida("Empleados");
    if (!isset($modificaEmpl)) $modificaEmpl = modificacionValida("Empleados");
    if (!isset($consultaNomi)) $consultaNomi = consultaValida("Nomina");
    if (!isset($modificaNomi)) $modificaNomi = modificacionValida("Nomina");
    $esDiseno = validaPerfil("Diseño");
    $esGestor = validaPerfil("Gestor"); //if ($esGestor) clog1("Perfil Gestor");
    $esCompras = validaPerfil("Compras"); //if ($esCompras) clog1("Perfil Compras");
    $esProveedor = validaPerfil("Proveedor"); //if ($esProveedor) clog1("Perfil Proveedor");
    $esComparaClientes=validaPerfil("Compara Clientes"); //if ($esComparaClientes) clog1("Perfil Compara Clientes");
    $esComparaProveedores=validaPerfil("Compara Proveedores"); //if ($esComparaProveedores) clog1("Perfil Compara Proveedores");
    //$esOrigenContraRecibos=validaPerfil("Origen Contra Recibos"); //if ($esOrigenContraRecibos) clog1("Perfil Origen Contra Recibos");
    $esCuentasBancarias=validaPerfil("Cuentas Bancarias"); //if ($esCuentasBancarias) clog1("Perfil Cuentas Bancarias");
    $esAltaPagos=validaPerfil("Alta Pagos");
    $esCargaEgresos=validaPerfil("Carga Egresos");
    $esSolPago=validaPerfil("Solicita Pagos");
    $esGestionaPago=validaPerfil("Gestiona Pagos");
    $esAuthPago=validaPerfil("Autoriza Pagos");
    $esRealizaPago=validaPerfil("Realiza Pagos");
    $veSolPago=validaPerfil("Consulta Solicitudes");

    $consultaViaticos=validaPerfil("Viaticos")||validaPerfil("Autoriza Viaticos");
    $consultaCajaChica=validaPerfil("Caja Chica")||validaPerfil("Autoriza Caja Chica");
    $consultaCajaReporte=validaPerfil("Caja Reporte")||validaPerfil("Caja Respaldo");

    if (!isset($generaCitas)) $generaCitas=($esProveedor&&$unm==="I-025");
    if (!isset($consultaCitas)) $consultaCitas=validaPerfil("Consulta Citas");
    if (!isset($modificaCitas)) $modificaCitas=validaPerfil("Modifica Citas");
    if ($esCompras && $unm==="compras1") {
      $consultaExpr=true;
      $consultaResp=true;
    }
    if ($esProveedor && $u->proveedor->status==="inactivo") {
    /*
    if ($esProveedor && isset($u->proveedor) && $u->proveedor->status!=="activo" && ($u->proveedor->status!=="bloqueado" || $menu_accion==="ActualizaCuentaBancaria") ) {
    */
      $consultaAlta=false;
      $consultaProc=false;
      $consultaConR=false;
      $consultaExpr=false;
      $consultaResp=false;
      $consultaGrpo=false;
      $consultaProv=false;
      $consultaUsrs=false;
      $consultaPerm=false;
      $consultaRepo=false;
      $consultaEmpl=false;
      $consultaNomi=false;
      $consultaCitas=false;
      $generaCitas=false;
      $esSolPago=false;
      $esGestionaPago=false;
      $esAuthPago=false;
      $esRealizaPago=false;
      $veSolPago=false;
    }
    //clog2(($consultaAlta?"1":"0").($consultaProc?"1":"0").($consultaConR?"1":"0").($consultaExpr?"1":"0").($consultaResp?"1":"0"));
    //clog2(($consultaGrpo?"1":"0").($consultaProv?"1":"0").($consultaUsrs?"1":"0").($consultaPerm?"1":"0").($consultaRepo?"1":"0"));
    //clog2(($modificaConR?"1":"0").($modificaUsrs?"1":"0").($modificaGrpo?"1":"0").($modificaProv?"1":"0").($modificaPerm?"1":"0"));
  }
} else $esSuperAdmin=false;
$configClass="noprint relative";
function classSelected($boolVal, $otherClasses="") {
  return $boolVal?" class=\"navSelected".(isset($otherClasses[0])?" ".$otherClasses:"")."\"":(isset($otherClasses[0])?" class=\"$otherClasses\"":"");
}
if(isset($_REQUEST["lado_izquierdo"][0])) $configClass.=" $_REQUEST[lado_izquierdo]";
if (isset($configClass[0])) $configClass=" class=\"".$configClass."\"";
?>
        <button class="mobile-menu-toggle noprint" id="mobileMenuToggle">☰</button>
        <div id="lado_izquierdo"<?=$configClass?>>
          <button class="mobile-x-toggle" id="mobileMenuX">X</button>
          <img class="scrollSubMenuBtn isUP hidden" src="imagenes/icons/upArrowB.png" alt="up" onmouseover="startScroll(-37);" onmouseout="stopScroll();" onclick="scrollMenu(-37);" ondblclick="scrollMenu(-400);"><img class="scrollSubMenuBtn isDW hidden" src="imagenes/icons/downArrowB.png" alt="down" onmouseover="startScroll(37);" onmouseout="stopScroll();" onclick="scrollMenu(37);" ondblclick="scrollMenu(400);">
<?php if ($enableHiddenMenu) { ?>
          <div class="transparent wid200px logoSpace">&nbsp;</div>
          <img class="menuHandle handleTop" src="imagenes/icons/handleIcon.png" alt="menu" onclick="wipeOff(event);">
          <img class="menuHandle handleBottom" src="imagenes/icons/handleIcon.png" alt="menu" onclick="wipeOff(event);">
<?php } ?>
          <form name="forma_menu" target="_self" method="post">
            <div id="menu" class="menu_izquierdo scrollablewrapper centered">
              <div id="menuinner" class="scrollablediv"> <br class="hideOnThinnest" />
<?php if (hasUser()) {
        if (!isset($_REQUEST["oldbar"])) {
          if (!isset($menu_accion[0])) $menu_accion="Inicio";
echo "<!-- MENU ACCION: $menu_accion -->\n";
echo "<!-- RULES: ".(is_array($rules)?implode(",",$rules):$rules)." -->\n";
echo "<!-- Qry: ".$menuQry." -->\n";
echo "<!-- Log:\n".$menuLog." -->\n";  ?>
              <ul id="top">
<?php
          foreach ($menuList as $idx => $row) {
echo "<!-- MENULIST $idx) ".json_encode($row)." -->\n";
            list("indice"=>$indice,"accion"=>$accion,"descripcion"=>$descripcion ,"estilo"=>$estilo,"titulo"=>$titulo) = $row;
            $acciones=explode(",", $accion);
            if (!isset($descripcion[0])||$acciones[0]===$descripcion) {
              $tagName="input";
              $tagEnd="/";
            } else {
              $tagName="button";
              $tagEnd=(isset($estilo)?" style='font-size: {$estilo}%'":"").">$descripcion</button";
            }
            $posIndex=strpos($indice,".");
            $isSubMenu=($posIndex!==false);
            if ($isSubMenu) {
              $parentIndex=substr($indice, 0, $posIndex);
              $prevIndex=$menuList[$idx-1]["indice"]??null;
              $nextIndex=$menuList[$idx+1]["indice"]??null;
              $isFirstItem=(!isset($prevIndex) || !isset($prevIndex[$posIndex]) || substr($prevIndex,0,$posIndex)!==$parentIndex);
              $isLastItem=(!isset($nextIndex) || !isset($nextIndex[$posIndex]) || $nextIndex[$posIndex]!=="." || substr($nextIndex,0,$posIndex)!==$parentIndex);
              echo "<!-- PARENTINDEX='$parentIndex', ".($isFirstItem?"ISFIRST(".($prevIndex??"null").")":"PREVINDEX='$prevIndex'").", ".($isLastItem?"ISLAST(".($nextIndex??"null").")":"NEXTINDEX='$nextIndex'")." -->\n";
              if (!$isFirstItem || !$isLastItem) { // si hay más de un submenu
              ?>
                  <li class="relative"><<?=$tagName?> type="submit" name="menu_accion" value="<?=$acciones[0]?>"<?= classSelected(in_array($menu_accion, $acciones)).(isset($titulo[0])?" title=\"{$titulo}\"":"").$tagEnd ?>></li>
<?php
              }
              if ($isLastItem) { ?>
                  </ul></li>
<?php
              }
            } else {
              $subindice=$indice.".";
              $subLen=strlen($subindice);
              $hasSubMenu=isset($menuList[$idx+1])?(substr($menuList[$idx+1]["indice"],0,$subLen)===$subindice):false;
              if ($hasSubMenu) {
                $nombreBloque=($tagName==="input")?$acciones[0]:$descripcion;
                $esBloque=in_array($menu_accion, $acciones);
                $subNum=0;
                // ToDo: esBloque si la accion es de un submenu
                //       pero si solo hay un submenu hay que integrarlo al menu superior.
                //       Esto funciona si menu_accion no esta en acciones
                //       Al presionar el menu superior
                // ToDo: Si se presiona el menu superior cuando solo hay un submenu, debe seguir sin mostrar el submenu y mostrar la pagina correspondiente
                for($i=$idx+1; isset($menuList[$i])&&substr($menuList[$i]["indice"],0,$subLen)===$subindice; $i++) {
                  $subMenu=$menuList[$i];
                  $subActions=explode(",",$subMenu["accion"]);
                  $subDescripcion=$subMenu["descripcion"]??"";
                  //echo "<!-- MENU:{$menu_accion}, ACCIONES:".json_encode($acciones).", SUB{$i}:".json_encode($subMenu)." -->";
                  if (!$esBloque && in_array($menu_accion, $subActions)) {
                    $esBloque=true;
                    $subTitle=$subMenu["titulo"];
                  }
                  $subNum++;
                }
                $tagAction=$acciones[0];
                $tagSelected=classSelected($esBloque,"esBloque");
                $tagTitle=(isset($titulo[0])?" title=\"{$titulo}\"":"");
echo "<!-- SUBNUM: ".$subNum." -->\n";
                if ($subNum==1) {
                  $tagType="submit";
                  if (isset($subActions[0])) {
                    $tagAction=$subActions[0];
                    $nombreBloque=$tagAction;
                  }
                  $tagEvts="";
                  if (isset($subTitle[0])) $tagTitle=" title=\"{$subTitle}\"";
                  $menuIcon="";
                  if (isset($subDescripcion[0]))
                    $nombreBloque=$subDescripcion;
                } else {
                  $tagType="button";
                  $tagEvts=" onclick=\"toggleSideMenu(this);\"";
                  $parEvts=$esBloque?"":" onmouseenter=\"displaySideMenu(this);\" onmouseleave=\"removeSideMenu(this);\" ontouchenter=\"displaySideMenu(this);\" ontouchleave=\"removeSideMenu(this);\"";
                  // toDo: quitar condicion esBloque para que siempre se llamen las funciones
                  $menuIcon="<img src=\"imagenes/icons/menu".($esBloque?"Collapse1":"Expand").".png\" alt=\"".($esBloque?"cerrar":"abrir")."\" width=\"13\" height=\"13\" style=\"vertical-align: text-bottom;\"/> ";
                } ?>
                <li class="relative"<?=$parEvts??""?>><button type="<?=$tagType?>" name="menu_accion" value="<?=$tagAction?>"<?= $tagSelected.$tagEvts.$tagTitle ?>><?=$menuIcon.$nombreBloque?></button><ul<?=($esBloque&&$subNum!=1)?"":" class=\"hidden\""?>>
<?php
              } else { ?>
                <li><<?=$tagName?> type="submit" name="menu_accion" value="<?=$acciones[0]?>"<?= classSelected(in_array($menu_accion, $acciones)).(isset($titulo[0])?" title=\"{$titulo}\"":"").$tagEnd ?>></li>
<?php
              }
            }
          } ?>
                <li><input type="submit" name="logout" value="Salir" /></li>
              </ul>
              <img src="data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7" class="size8 navIniImg" alt="." onload="navIni();">
<?php
        } else { ?>
              <ul id="top">
                <li><input type="submit" name="menu_accion" value="Inicio"<?= classSelected(empty($menu_accion)||$menu_accion==="Inicio"||$menu_accion=="ActualizaCuentaBancaria") ?>/></li>
<?php
          //SUBMENU ADMINISTRACION:
          $btnEntidades=$esSistemas||$consultaGrpo||$consultaPerm||$consultaUsrs; // ||$consultaProv
          $btnAdmFact=$esAdmin;
          $btnBitacora=false&&$consultaRepo;
          $btnCatalogo=$consultaCata||$consultaData;
          $btnDscrgXML=false&&$esAdmin&&$modificaRepo;
          $btnCorreos=false&&$esAdmin;
          $btnComparaSatA=false&&($esSistemas||$esComparaClientes);
          $btnComparaSatB=false&&($esSistemas||$esComparaClientes);
          $btnComparaSatC=false&&($esSistemas||$esComparaClientes);
          $btnComparaSatD=$esSistemas||$esComparaClientes;
          $btnComparaProv=$esSistemas||$esComparaProveedores;
          $btnAltaPagos=$esSistemas||$esAltaPagos;
          $btnCargaPagos=$esSistemas||$esCargaEgresos;
          $btnFormaPago=$esSistemas;
          $btnRespaldo=$esSistemas;
          $btnActualizacion=$esSuperAdmin;
          $btnPruebas=$esSuperAdmin;
          $btnConfig=$esSistemas;
          $btnAdmin=$btnEntidades||$btnAdmFact||$btnBitacora||$btnCatalogo||$btnDscrgXML||$btnCorreos||$btnComparaSatA||$btnComparaSatB||$btnComparaSatC||$btnComparaSatD||$btnComparaProv||$btnAltaPagos||$btnCargaPagos||$btnFormaPago||$btnRespaldo||$btnConfig||$btnActualizacion||$btnPruebas;
          if ($btnAdmin) {
              $esBloqueAdmin = ($menu_accion==="Administracion" || $menu_accion==="Admin Factura" || $menu_accion==="Bitacora" || $menu_accion==="Catalogo" || $menu_accion==="Descargar XML" || $menu_accion==="Correos" || $menu_accion==="ComparaSAT_A" || $menu_accion==="ComparaSAT_B" || $menu_accion==="ComparaSAT_C" || $menu_accion==="ComparaSAT" || $menu_accion==="ComparaSATPrv" || $menu_accion==="Alta Pagos" || $menu_accion==="Carga Pagos" || $menu_accion==="Forma Pago" || $menu_accion==="Respaldo" || $menu_accion==="Liberar" || $menu_accion==="Configuracion" || $menu_accion==="Upgrade" || $menu_accion==="Pruebas"); ?>
                <li><button type="button" name="menu_accion" value="Administracion"<?= classSelected($esBloqueAdmin,"esBloque") ?> onclick="toggleSideMenu(this);"><img src="imagenes/icons/menu<?= $esBloqueAdmin?"Collapse1":"Expand" ?>.png" alt="<?= $esBloque?"cerrar":"abrir" ?>" width="13" height="13" style="vertical-align: text-bottom;"/> Administración</button>
                  <ul id="bloqueAdmin"<?= $esBloqueAdmin?"":" class=\"hidden\"" ?>>
                    <?php if($btnEntidades){ ?><li><button type="submit" name="menu_accion" value="Administracion"<?= classSelected($menu_accion==="Administracion") ?>>Entidades</button></li><?php } ?>
                    <?php if($btnAdmFact){ ?><li><input type="submit" name="menu_accion" value="Admin Factura"<?= classSelected($menu_accion==="Admin Factura") ?>/></li><?php } ?>
                    <?php if($btnBitacora){ ?><li><input type="submit" name="menu_accion" value="Bitacora"<?= classSelected($menu_accion==="Bitacora") ?>/></li><?php } ?>
                    <?php if($btnCatalogo){ ?><li><input type="submit" name="menu_accion" value="Catalogo"<?= classSelected($menu_accion==="Catalogo") ?>/></li><?php } ?>
                    <?php if($btnDscrgXML){ ?><li><input type="submit" name="menu_accion" value="Descargar XML"<?= classSelected($menu_accion==="Descargar XML") ?>/></li><?php } ?>
                    <?php if($btnCorreos){ ?><li><input type="submit" name="menu_accion" value="Correos"<?= classSelected($menu_accion==="Correos") ?>/></li><?php } ?>
                    <?php if($btnAltaPagos){ ?><li><button type="submit" name="menu_accion" value="Alta Pagos"<?= classSelected($menu_accion==="Alta Pagos") ?> title="Carga de Comprobantes de Pago">Carga CPago</button></li><?php } ?>
                    <?php if($btnCargaPagos){ ?><li><button type="submit" name="menu_accion" value="Carga Pagos"<?= classSelected($menu_accion==="Carga Pagos") ?> title="Carga Reportes de Egresos de AVANCE">Egresos AVANCE</button></li><?php } ?>
                    <?php if($btnComparaSatA){ ?><li><input type="submit" name="menu_accion" value="ComparaSAT_A"<?= classSelected($menu_accion==="ComparaSAT_A") ?>/></li><?php } ?>
                    <?php if($btnComparaSatB){ ?><li><input type="submit" name="menu_accion" value="ComparaSAT_B"<?= classSelected($menu_accion==="ComparaSAT_B") ?>/></li><?php } ?>
                    <?php if ($btnComparaSatC){ ?><li><input type="submit" name="menu_accion" value="ComparaSAT_C"<?= classSelected($menu_accion==="ComparaSAT_C") ?>/></li><?php } ?>
                    <?php if($btnComparaSatD){ ?><li><button type="submit" name="menu_accion" value="ComparaSAT"<?= classSelected($menu_accion==="ComparaSAT") ?>>Compara Clientes</button></li><?php } ?>
                    <?php if($btnComparaProv){ ?><li><button type="submit" name="menu_accion" value="ComparaSATPrv"<?= classSelected($menu_accion==="ComparaSATPrv") ?>>Compara Proveedores</button></li><?php } ?>
                    <?php if($btnFormaPago){ ?><li><button type="submit" name="menu_accion" value="Forma Pago"<?= classSelected($menu_accion==="Forma Pago") ?>>Formas de Pago</button></li><?php } ?>
                    <?php if($btnConfig){ ?><li><button type="submit" name="menu_accion" value="Configuracion"<?= classSelected($menu_accion==="Configuracion") ?>>Configuración</button></li><?php } ?>
                    <?php if($btnRespaldo){ ?><li><button type="submit" name="menu_accion" value="Respaldo"<?= classSelected($menu_accion==="Respaldo"||$menu_accion==="Liberar") ?>>Respaldo de Sistema</button></li><?php } ?>
                    <?php if($btnActualizacion){ ?><li><button type="submit" name="menu_accion" value="Upgrade"<?= classSelected($menu_accion==="Upgrade") ?>>Actualización</button></li><?php } ?>
                    <?php if($btnPruebas){ ?><li><button type="submit" name="menu_accion" value="Pruebas"<?= classSelected($menu_accion==="Pruebas") ?>>CR Diario</button></li><?php } ?>
<?php
// TODO: Agregar botón ACTUALIZAR, para borrar cache de empresas y proveedores manualmente y que la siguiente vez que se consulten se descargue el ultimo del servidor.
//       - Crear un archivo configuracion/actualizar.php que reciba un parametro post y borre datos de session, en particular de caches.
//       - Hacer un <button> que envie postService para configuracion/actualizar.php
?>
                  </ul>
                </li><?php } ?>
<?php
          $btnAltaEmpleados=$consultaEmpl || $esSistemas;
          $btnNomina=$consultaNomi || $esSistemas;
          $btnNomina=false; // Deshabilitado hasta crear pagina
          $btnViajero=($consultaViaticos && getUser()->nombre!=="viajero") || $esSistemas;
          $btnCajaChica=$consultaCajaChica || $esSistemas;
          $btnCajaReporte=$consultaCajaReporte || $esSistemas;

          $tieneReposicion=$btnViajero||$btnCajaChica||$btnCajaReporte;
          $tieneEmpleados=$btnAltaEmpleados||$btnNomina;
          $conMenuEmpleados=$tieneEmpleados||$tieneReposicion;
          if ($conMenuEmpleados) {
            $esBloqueEmpleados = $menu_accion==="Empleados" || $menu_accion==="Nomina" || $menu_accion==="Viajero" || $menu_accion==="Caja Chica" || $menu_accion==="Caja Reporte"; ?>
                <li><button type="button"<?= classSelected($esBloqueEmpleados,"esBloque") ?> onclick="toggleSideMenu(this);"><img src="imagenes/icons/menu<?= $esBloqueEmpleados?"Collapse1":"Expand" ?>.png" alt="<?= $esBloqueEmpleados?"cerrar":"abrir" ?>" width="13" height="13" style="vertical-align: text-bottom;"/> Empleados</button><ul id="bloqueEmpleados"<?= $esBloqueEmpleados?"":" class=\"hidden\"" ?>>
<?php         if ($btnAltaEmpleados) { ?>
                  <li><button type="submit" name="menu_accion" value="Empleados"<?= classSelected($menu_accion==="Empleados") ?>>Alta y Consulta</button></li>
<?php         }
              if ($btnNomina) { ?>
                  <li><button type="submit" name="menu_accion" value="Nomina"<?= classSelected($menu_accion==="Nomina") ?>>N&oacute;mina</button></li>
<?php         }
              if ($btnViajero) { ?>
                  <li><button type="submit" name="menu_accion" value="Viajero"<?= classSelected($menu_accion==="Viajero") ?>>Vi&aacute;ticos</button></li>
<?php         }
              if ($btnCajaChica) { ?>
                  <li><button type="submit" name="menu_accion" value="Caja Chica"<?= classSelected($menu_accion==="Caja Chica") ?>>Caja Chica</button></li>
<?php         }
              if ($btnCajaReporte) { ?>
                  <li><button type="submit" name="menu_accion" value="Caja Reporte"<?= classSelected($menu_accion==="Caja Reporte") ?>>Reporte de Reembolsos</button></li>
<?php         } ?>
                </ul></li>
<?php     } ?>
<?php
          $btnReportes=$consultaRepo;
          if ($btnReportes) {
              $multiplesReportes=$consultaRepo; //$modificaRepo;
              if ($multiplesReportes) {
                $esBloqueReportes = $menu_accion==="Reportes"||$menu_accion==="Codigos"; //||$menu_accion==="VentasCliente"; // || $menu_accion==="Vencimiento"; ?>
                <li><button type="button"<?= classSelected($esBloqueReportes,"esBloque") ?> onclick="toggleSideMenu(this);"><img src="imagenes/icons/menu<?= $esBloqueReportes?"Collapse1":"Expand" ?>.png" alt="<?= $esBloqueReportes?"cerrar":"abrir" ?>" width="13" height="13" style="vertical-align: text-bottom;"/> Reportes</button>
                  <?php // ToDo: Quitar modificaRepo, permitir a todos los usuarios con consultaRepo
                      if($modificaRepo) { ?><ul id="bloqueReportes"<?= $esBloqueReportes?"":" class=\"hidden\"" ?>>
                    <li><button type="submit" name="menu_accion" value="Reportes"<?= classSelected($menu_accion==="Reportes") ?>>Acumulados</button></li>
                    <?php /*<li><input type="submit" name="menu_accion" value="Vencimiento"< ? = classSelected($menu_accion==="Vencimiento") ? >/></li> */ 
                    // ToDo: Quitar VentasCliente
                    // ToDo: Poner Reporte de Codigos de Articulos
                    ?>
                    <li><button type="submit" name="menu_accion" value="VentasCliente"<?= classSelected($menu_accion==="VentasCliente") ?>>Ventas Cliente</button></li>
                  </ul><?php 
                        } ?>
                </li><?php 
              } else { ?>
                <li><button type="submit" name="menu_accion" value="Reportes"<?= classSelected($menu_accion==="Reportes") ?>>Reportes Acumulados</button>
                </li><?php
              }
          } ?>
<?php     
          $btnRegistro=$consultaProv;
          if ($btnRegistro) { ?>
                <li><button type="submit" name="menu_accion" value="Registro"<?= classSelected($menu_accion==="Registro") ?>>Proveedores</button></li><?php } ?>
<?php     
          $btnCuentasBancarias=$esSistemas||$esCuentasBancarias;
          if ($btnCuentasBancarias) { ?>
                <li><button type="submit" name="menu_accion" value="CuentasBancarias"<?= classSelected($menu_accion==="CuentasBancarias") ?>>Cuentas Bancarias</button></li><?php } ?>
<?php
          if ($generaCitas||$consultaCitas||$modificaCitas||$esSistemas) { ?>
                <li><button type="submit" name="menu_accion" value="Citas"<?= classSelected($menu_accion==="Citas") ?>>Citas</button></li><?php } ?>
<?php
          $conMenuSolPago = $esSolPago||$esAdmin||$esSistemas;
          $sinMenuSolPago = $esAuthPago||$esRealizaPago||$esGestionaPago||$veSolPago;
          if ($conMenuSolPago) {
            $esBloqueSolPago=$menu_accion==="SolicitaPago"||$menu_accion==="ListaSolPago"; ?>
                <li><button type="submit" name="menu_accion" value="SolicitaPago"<?= classSelected($esBloqueSolPago,"esBloque") ?>><img src="imagenes/icons/menu<?= $esBloqueSolPago?"Collapse1":"Expand" ?>.png" alt="<?= $esBloqueSolPago?"cerrar":"abrir" ?>" width="13" height="13" style="vertical-align: bottom;"/> Solicitud de Pago</button>
                  <ul<?= $esBloqueSolPago?"":" class=\"hidden\"" ?>><li><button type="submit" name="menu_accion" value="ListaSolPago"<?= classSelected($menu_accion==="ListaSolPago") ?>>Lista de Solicitudes</button></li>
                  </ul>
                </li>
<?php
          } else if ($sinMenuSolPago) { ?>
                <li><button type="submit" name="menu_accion" value="ListaSolPago"<?= classSelected($menu_accion==="ListaSolPago") ?>>Solicitud de Pago</button></li><?php } ?>
<?php     
          if ($consultaAlta) { 
            $classet="";
            if ($menu_accion==="Alta Facturas") $classet="navSelected";
            if ($esProveedor) $classet.=(isset($classet[0])?" ":"")."maroon_important";
            if (isset($classet[0])) $classet=" class=\"$classet\"";
            ?>
                <li><button type="submit" name="menu_accion" value="Alta Facturas"<?= $classet ?>>Alta Facturas y Pagos</button></li>
<?php     }
          if ($consultaConR) { ?>
                <li><input type="submit" name="menu_accion" value="Contra Recibos"<?= classSelected($menu_accion==="Contra Recibos") ?>/></li>
<?php     }
          if ($modificaConR) { ?>
                <li><button type="submit" name="menu_accion" value="Generar Contra Recibos"<?= classSelected($menu_accion==="Generar Contra Recibos") ?>>Genera Contra Recibos</button></li>
<?php     }
          if ($consultaExpr) { ?>
                <li><button type="submit" name="menu_accion" value="Generar TXT"<?= classSelected($menu_accion==="Generar TXT") ?>>Exporta Datos</button></li>
<?php     }
          if ($consultaProc) { ?>
                <li><button type="submit" name="menu_accion" value="Reporte Facturas"<?= classSelected($menu_accion==="Reporte Facturas") ?>>Reporte Facturas y Pagos</button></li>
<?php     }
          if ($consultaResp) { ?>
                <li><button type="submit" name="menu_accion" value="Respalda Facturas"<?= classSelected($menu_accion==="Respalda Facturas") ?>>Respalda Archivos</button></li>
<?php     } 
          if ($esDiseno) { ?>
                <li><button type="submit" name="menu_accion" value="Animaciones" <?= classSelected($menu_accion==="Animaciones") ?>>Animaciones</button></li>
<?php     } ?>
                <li><input type="submit" name="logout" value="Salir" /></li>
              </ul>
              <img src="data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7" alt="." onload="dragIni();ekil(this);">
<?php
        }
      } else {
        /*
        $logos=[
          "APSA"=>["href"=>"www.apsa.com.mx/","src"=>"apsa.png","title"=>"ACABADOS DE PAPELES SATINADOS Y ABSORBENTES"],
          "GLAMA"=>["href"=>"productosglama.net/","src"=>"glama.png","title"=>"PRODUCTOS GLAMA"],
          "LAISA"=>["src"=>"laisa.png","title"=>"LAMINAS ACANALADAS INFINITA"],
          "MELO"=>["src"=>"melo.png","title"=>"DISTRIBUCIONES INDUSTRIALES MELO"],
          "JYL"=>["src"=>"jyl.png","title"=>"PAPELES Y MAQUILAS NACIONALES JYL"],
          "COREPACK"=>["src"=>"corepack.png","title"=>"COREPACK"],
          "RGA"=>["src"=>"rga.png","title"=>"RGA ARQUITECTOS"],
          "BIDASOA"=>["src"=>"bidasoa.png","title"=>"MANUFACTURERA DE PAPEL BIDASOA"],
          "SKARTON"=>["src"=>"skarton.png","title"=>"SKARTON"],
          "MORYSAN"=>["src"=>"morysan.png","title"=>"MORYSAN COMERCIAL"],
          "MARLOT"=>["src"=>"marlot.png","title"=>"TRANSPORTES MARLOT"],
          "FOAMYMEX"=>["src"=>"foamymex.png","title"=>"FOAMYMEX"]];
        foreach ($logos as $key => $value) {
          echo "                <div clases=\"logo centered all_space\" id=\"LOGO_$key\">";
          if (isset($value["href"][0])) echo "<a class=\"noApply all_space inblock\" href=\"http://$value[href]\">";
          if (isset($value["src"][0])) {
            echo "<img src=\"imagenes/logos/$value[src]\" name=\"$key\" alt=\"$key\" class="widavailableonly"";
            if (isset($value["title"][0])) echo " title=\"$value[title]\">";

          }
          if (isset($value["href"][0])) echo "</a>";
          echo "</div>\n";
        }
        */
    //$imageWidth = "150";
?>
<!-- APSA -->   <div class="logo centered all_space" id="LOGO_APSA"><a class="noApply all_space inblock" href="http://www.apsa.com.mx/" style="display:inline-block;"><img src="imagenes/logos/apsa.png" name="APSA" alt="APSA" class="widavailableonly" title="ACABADOS DE PAPELES SATINADOS Y ABSORBENTES"></a></div>
<!-- GLAMA -->  <div class="logo centered all_space" id="LOGO_GLAMA"><a class="noApply all_space inblock" href="http://productosglama.net/" class="widavailableonly" style="display:inline-block;"><img src="imagenes/logos/glama.png" alt="GLAMA" class="widavailableonly" title="PRODUCTOS GLAMA"></a></div>
<!-- LAISA -->  <div class="logo centered all_space" id="LOGO_LAISA"><img src="imagenes/logos/laisa.png" alt="LAISA" class="widavailableonly" title="LAMINAS ACANALADAS INFINITA"></div>
<!-- CASA - - >   <div class="logo centered all_space" id="LOGO_CASABLANCA"><img src="imagenes/logos/casablanca.png" class="widavailableonly" title="LAMINADOS CASABLANCA"></div -->
<!-- MELO -->   <div class="logo centered all_space" id="LOGO_MELO"><img src="imagenes/logos/melo.png" class="widavailableonly" title="DISTRIBUCIONES INDUSTRIALES MELO"></div>
<!-- JYL -->    <div class="logo centered all_space" id="LOGO_JYL"><img src="imagenes/logos/jyl.png" class="widavailableonly" title="PAPELES Y MAQUILAS NACIONALES JYL"></div>
<!-- ENVASES - - ><div class="logo centered all_space" id="LOGO_ENVASES"><img src="imagenes/logos/envases.png" class="widavailableonly" title="ENVASES EFICIENTES"></div -->
<!--COREPACK--> <div class="logo centered all_space" id="LOGO_COREPACK"><img src="imagenes/logos/corepack.png" class="widavailableonly" title="COREPACK"></div>
<!-- RGA -->    <div class="logo centered all_space" id="LOGO_RGA"><img src="imagenes/logos/rga.png" class="widavailableonly" title="RGA ARQUITECTOS"></div>
<!-- BIDASOA --><div class="logo centered all_space" id="LOGO_BIDASOA"><img src="imagenes/logos/bidasoa.png" class="widavailableonly" title="MANUFACTURERA DE PAPEL BIDASOA"></div>
<!-- SKARTON --><div class="logo centered all_space" id="LOGO_SKARTON"><img src="imagenes/logos/skarton.png" class="widavailableonly" title="SKARTON"></div>
<!-- morysan --><div class="logo centered all_space" id="LOGO_MORYSAN"><img src="imagenes/logos/morysan.png" class="widavailableonly" title="MORYSAN COMERCIAL"></div>
<!-- MARLOT --><div class="logo centered all_space" id="LOGO_MARLOT"><img src="imagenes/logos/marlot.png" class="widavailableonly" title="TRANSPORTES MARLOT"></div>
<!-- foamymex --><div class="logo centered all_space" id="LOGO_FOAMYMEX"><img src="imagenes/logos/foamymex.png" class="widavailableonly" title="FOAMYMEX"></div>
<?php } ?>
              </div>
            </div>
          </form>
        </div>
<script>
// Toggle del menú móvil
    document.getElementById('mobileMenuToggle').addEventListener('click', function() {
        document.getElementById('lado_izquierdo').classList.toggle('menu-mobile-visible');
        const mnOvy=document.getElementById('menuOverlay');
        if (mnOvy) mnOvy.classList.toggle('visible');
    });

// Cerrar menú al hacer clic en el overlay
    document.getElementById('mobileMenuX').addEventListener('click', function() {
        document.getElementById('lado_izquierdo').classList.remove('menu-mobile-visible');
        this.classList.remove('visible');
    });
<?php if (!hasUser()) { ?>
    ski();
<?php } ?>
</script>
<?php
clog1seq(-1);
clog2end("barralateral");

