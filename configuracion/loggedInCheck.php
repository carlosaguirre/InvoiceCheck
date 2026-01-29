<?php
global $_esAdministrador, $_esSistemas, $_esSistemasX, $_esDesarrollo, $_esPruebas, $_esCompras, $_esComprasB, $_esProveedor;
$_esAdministrador = validaPerfil("Administrador");
$_esSistemas = validaPerfil("Sistemas")||$_esAdministrador;
$_esSistemasX = $_esSistemas||(hasUser()&&(getUser()->isSystem??false));
$_esDesarrollo = hasUser()&&in_array(getUser()->nombre, ["admin","sistemas"]);//getUser()->nombre==="admin";
$_esPruebas = hasUser()&&in_array(getUser()->nombre, ["admin","sistemas","test","test1","test2","test3"]); // ,"sistemas1","sistemas2"
$_esCompras = validaPerfil("Compras");
$_esComprasB = validaPerfil(["Compras","Compras Basico"]);
$_esProveedor = validaPerfil("Proveedor");
