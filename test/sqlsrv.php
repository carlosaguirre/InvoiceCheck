<?php
require_once dirname(__DIR__)."/bootstrap.php";
global $sqlsrv_dsn, $sqlsrv_username, $sqlsrv_password, $sqlsrv_base, $sqlsrv_name;
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
<?php
$steps=["NAME = '$sqlsrv_name'","BASE = '$sqlsrv_base'","USERNAME = '$sqlsrv_username'","PASSWORD = '$sqlsrv_password'","DSN = '$sqlsrv_dsn'"];
try {
    // Crear la conexión PDO
    $pdo = new PDO($sqlsrv_dsn, $sqlsrv_username, $sqlsrv_password);
    $steps[]="PDO ( '$sqlsrv_dsn', '$sqlsrv_username', '$sqlsrv_password' ) = ".(isset($pdo)?"IS SET":"IS NOT SET");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $steps[]="SET ERRMODE & EXCEPTION";
?>
        <table class="table datatable table-sm table-hover table-bordered table-striped display nowrap" id="miTabla" style=" width:100%">
          <thead>
            <tr>
              <th scope="col">#</th>
              <th scope="col" data-priority="1">Forma</th>
              <th scope="col">Status</th>
              <th scope="col">Fecha</th>
              <th scope="col">Cargo</th>
              <th scope="col">Codigo Zona</th>
            </tr>
          </thead>
          <tbody>
            <?php
            $contDat = 0;
            $query="SELECT * FROM view_Movimientos_Pagos where Forma = 'Monto de Credito' and Status = 'Activo' and Cargo = '208800.000' and fecha = '2024-23-12' and CodigoZona = 'Skarton'"; // 
            $DatT0 = $pdo->query($query);
            $steps[]="QUERY : $query";
            $steps[]="Row Count : ".$DatT0->rowCount();
            $steps[]="Result count: ".count($DatT0);
            while ($DatT1 = $DatT0->fetch(PDO::FETCH_ASSOC)) {
              $contDat = $contDat + 1;
              //$steps[]="ROW $contDat: ".json_encode($DatT1);
              /*
              "id_e":"001@06494142048@11\/11\/2024@1931C3340E5B4C7E\r@NMSC204811110000033\/\/1931D7C2981CE0BD\r\/CTC\/322\/MISCELLANEOUS\r@\/PT\/DE\/EI\/0732355 FACTURA 18942\r",
              "id_e_2":"106@1931C3340E5B4C7E\r@06494142048@11\/11\/2024@ie6wzply",
              "orden":"106",
              "Transaccion":"1931C3340E5B4C7E\r",
              "NoCuenta":"06494142048",
              "fecha":"2024-11-11 00:00:00.000",
              "Descripcion":"\/PT\/DE\/EI\/0732355 FACTURA 18942\r",
              "Forma":"Monto de Credito",
              "Cargo":"47003.20",
              "Abono":".00",
              "Total_Cargo":".00",
              "Total_Abono":".00",
              "Moneda":"MXN",
              "Archivo_Detectado":"SWIFT MT942 (Movimientos del dia)",
              "Banco":"BANAMEX",
              "Titular":"GLAMA",
              "codigobanco":"001",
              "NumCuenta":"06494142048",
              "Status":"Activo",
              "CodigoZona":"Glama",
              "id_mov":"30343485",
              "descripcion3":null,
              "Cantidad_Aplicada":null,
              "Status_Mov":"",
              "CodigoCliente":null,
              "Fecha_Aplicacion":null,
              "Num_Banco_Avance":"1",
              "tipo_cambio_mov":null,
              "CuentaContable":"110200008"}
              */
              ?>
              <tr style="font-size: 12px;">
                <td scope="row"><?=$contDat?></td>
                <th scope="col"><?=$DatT1['Forma']?></th>
                <th scope="col"><?=$DatT1['Status']?></th>
                <th scope="col"><?=$DatT1['fecha']?></th>
                <th scope="col"><?=$DatT1['Cargo']?></th>
                <th scope="col"><?=$DatT1['CodigoZona']?></th>
              </tr>
              <?php  
            }  
            ?>
          </tbody>
        </table>
<?php
} catch (PDOException $pe) {
    echo "Error en la conexión a SQL Server: " . $pe->getMessage() . "<br>";
    $steps[]="CAUGHT PDOException: ".json_encode(getErrorData($pe));
} catch (Exception $e) {
    $steps[]="CAUGHT EXCEPTION: ".json_encode(getErrorData($e));
}
?>
      </div>
      <h1>LOG</h1>
      <div>
<?php
foreach ($steps as $key => $value) {
  echo "$key : $value<br>";
}
?>
      </div>
    </div>
  </body>
</html>
