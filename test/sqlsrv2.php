<?php
require_once dirname(__DIR__)."/bootstrap.php";
require_once "clases/DBPDO.php";
?>
<html>
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>SQLSERVER TEST</title>
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- DataTables con Bootstrap 5 -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.2.9/css/responsive.dataTables.min.css">

    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.2.9/js/dataTables.responsive.min.js"></script>
  </head>
  <body>
    <div id="area_general" class="central">
      <h1>SQL SERVER TEST</h1>
      <div id="area_detalle" class="table-responsive">
        <ul id="miLista" style="width:100%">
<?php
$query="SELECT distinct CodigoZona FROM view_Movimientos_Pagos where Forma = 'Monto de Credito' and Status = 'Activo'"; // and fecha>'2024-01-01'";
echo "<!-- QUERY: $query -->";
DBPDO::query($query);
echo "<!-- RETRIEVED: ".DBPDO::getCount()." -->";
while ($row = DBPDO::fetch()) {
?>
              <li><?= $row["CodigoZona"] ?></li>
<?php  
}  
?>
        </ul>
      </div>
    </div>
  </body>
</html>
