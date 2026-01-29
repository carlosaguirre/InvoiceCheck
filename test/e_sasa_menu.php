<?php
header('charset=UTF-8');
require_once dirname(__DIR__)."/bootstrap.php";
$browser = getBrowser();
$isMSIE = ($browser==="Edge" || $browser==="IE");
require_once "templates/generalScript.php";
?>
<!DOCTYPE html>
<html lang="es" xmlns="http://www.w3.org/1999/xhtml">
  <head>
    <meta charset="utf-8" />
    <base href="<?= $_SERVER['HTTP_ORIGIN'] . $_SERVER['WEB_MD_PATH'] ?>" target="_blank" />
    <title>Avance</title>
    <script type="text/javascript" src="scripts/jquery-2.2.4.min.js"></script>
    <script type="text/javascript" src="scripts/bootstrap-multiselect.js"></script>
    <script type="text/javascript" src="scripts/bootstrap-3.3.2.min.js"></script>
    <link rel="stylesheet" type="text/css" href="css/bootstrap-multiselect.css"/>
<?php
    if ($isMSIE) echoPolyfillScript();
    echoGeneralScript();
?>
    <link rel="stylesheet" type="text/css" href="css/bootstrap-3.3.2.min.css"/>
    <link href="css/general.php" rel="stylesheet" type="text/css"/>
    <script type="text/javascript">
      var eadb={arturo:{Corp:"ARTURO",par:"ABCFAFCACDBCDGN",parb:"IUIVURIEUYVVUVUU",parc:"00306USLULRULAULLULRULI",adelante:"45091060141090237"}};
      var cr3={u:"CREDITO3",P:"vero2025"};
      function setFrame(value) {
        let url=false;
        switch(value) {
        case "arturo": url="https://glama.esasacloud.com/avance/cgi-bin/e-sasa/tres?Corp=ARTURO&par=ABCFAFCACDBCDGN&Idioma=1&parb=IUIVURIEUYVVUVUU&parc=00306USLULRULAULLULRULI&adelante=45091060141090237&"; break;
                          //https://glama.esasacloud.com/avance/cgi-bin/e-sasa/
        //tres?Corp=ARTURO&par=ABCFAFCACDBCDGN&Idioma=1&parb=IUIVURIENERLVSUU&parc=03606NUNNNLNIUNINNNLNNR&adelante=34080049130104382&
        case "menuai": url="https://glama.esasacloud.com/avance/cgi-bin/e-sasa/menu?Idioma=1&Usu=LocalARTURO&Reg=LocalCORPLOBATON&Ancho=1536&Largo=864&";break;
        case "cuentaai": url="https://glama.esasacloud.com/avance/cgi-bin/e-sasa/ARinq?Idioma=1&formato=M4431+++++&Mes=5&Ano=2023&Emp=APSA&Reg=LocalCORPLOBATON&Usu=LocalARTURO&formdes=*&formage=*&tipocli=*&formdoc=*&Inq=1&pointer=-33&Opcion=4&Coin=1&TipCam=1&formctac=*&formrefe=*&IniYearF=2023&IniMonF=5&IniDayF=1&EndYearF=2023&EndMonF=5&EndDayF=31&";break;
        case "logoutai": url="https://glama.esasacloud.com/avance/cgi-bin/e-sasa/logout?Usu=LocalARTURO&";break;
        case "credito3": url="https://glama.esasacloud.com/avance/cgi-bin/e-sasa/tres?Corp=CREDITO3&par=ABCHAFCACDBCDGN&Idioma=1&parb=IUIVURIEUYRYIEUU&parc=02408UYNNUSUYVUYIUYANULNUVUAR&adelante=18064033114063372&"; break;
        case "ccxcc3": url="https://glama.esasacloud.com/avance/cgi-bin/e-sasa/ARinq?Idioma=1&formato=G442++++++&Mes=5&Ano=2023&Emp=GLAMA&Reg=LocalGLAMA&Usu=LocalCREDITO3&formdes=*&formage=*&tipocli=*&formdoc=*&Inq=1&pointer=-33&Opcion=4&Coin=1&TipCam=1&formctac=*&formrefe=*&IniYearF=2023&IniMonF=5&IniDayF=1&EndYearF=2023&EndMonF=5&EndDayF=31&"; break;
        case "logoutc3": url="https://glama.esasacloud.com/avance/cgi-bin/e-sasa/logout?Usu=LocalCREDITO3&";break;
        case "testjv": url="https://glama.esasacloud.com/avance/cgi-bin/e-sasa/menu?Idioma=1&Usu=LocalARTURO&Reg=LocalCORPLOBATON&Ancho=1920&Largo=1200&"; break;
        }
        if (url) {
          ebyid("esasa_frame").src=url;
          const eaft=ebyid("esasa_foot");
          eaft.insertBefore(ecrea({eName:"P",eText:url}), eaft);
        }
      }
      function getFrameContentLength() {
        const fro=ebyid("esasa_frame");
        const frc=fro.contentWindow.document.body.innerHTML;
        return frc.length;
      }
    </script>
  </head>
  <body>
    <div id="area_general" class="central">
      <h1>SHORTCUTS AVANCE</h1>
      <div id="bloque_central" class="noEncabezado">
        <div id="lado_izquierdo" class="shortBy3">
          <UL>
            <LI><button type="button" onclick="setFrame('arturo');">LOGIN ARTURO</button></LI>
            <LI><button type="button" onclick="setFrame('menuai');">ARTURO: Menu APSA</button></LI>
            <LI><button type="button" onclick="setFrame('cuentaai');">ARTURO: Cuenta Cliente</button></LI>
            <LI><button type="button" onclick="setFrame('logoutai');">ARTURO: LOGOUT</button></LI>
            <LI><button type="button" onclick="setFrame('credito3');">LOGIN CREDITO3</button></LI>
            <LI><button type="button" onclick="setFrame('ccxcc3');">CREDITO3: CONSULTA</button></LI>
            <LI><button type="button" onclick="setFrame('logoutc3');">CREDITO3: LOGOUT</button></LI>
            <LI><button type="button" onclick="setFrame('testjv');">ARTURO: Menu sin usuario</button></LI>
            <LI><button type="button" onclick="console.log('Frame Length ='+getFrameContentLength());">Muestra num contenido</button></LI>
          </UL>
      </div>
      <div id="principal" class="shortBy3">
        <iframe id="esasa_frame" name="avance" src="https://glama.esasacloud.com/avance/cgi-bin/e-sasa/uno?" allow="fullscreen"></iframe>
      </div>
      <div id="esasa_foot">
      </div>
    </div>
  </body>
</html>
