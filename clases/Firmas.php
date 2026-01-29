<?php
require_once dirname(__DIR__)."/bootstrap.php";
require_once "clases/DBObject.php";
class Firmas extends DBObject {
    function __construct() {
        $this->tablename      = "firmas";
        $this->rows_per_page  = 0;
        $this->fieldlist      = array("id", "idUsuario", "modulo", "idReferencia", "accion", "motivo", "fecha");
        $this->fieldlist['id'] = array('pkey' => 'y', 'auto' => 'y');
        $this->fieldlist['fecha'] = array('auto' => 'y');
        $this->log = "\n// xxxxxxxxxxxxxx Firmas xxxxxxxxxxxxxx //\n";
    }
}
/* modulo:
solpago : firmas de solicitud de pago, idReferencia es el id de una solicitud
factura : firmas de factura, idReferencia es el id de la factura
*/
/* accion solpago:
solicita : Usuario que crea la solicitud
autoriza : Usuario que autoriza
rechaza : Usuario que rechaza
procesa_compras : Usuario de compras que procesa la factura
procesa_contabilidad : Usuario de contabilidad que procesa la factura y la pasa a pago
paga : Usuario que marca la solicitud y factura como pagada
anexa : Usuario que agrega documentos de factura (xml y pdf) cuando se ingresó inicialmente sólo orden
*/
/* accion factura: relativo al status pero es la accion en lugar del status actual, considera todos los comprobantes fiscales
temporal : cada que se ingresa un comprobante fiscal sin concluir el proceso de asignacion de pedido y codigo de articulos
pendiente : cada que se registra de forma exitosa un comprobante fiscal
aceptado : cada que se procesa (cuando se vuelve a guardar pedido y/o codigo de articulos)
contrarecibo : cuando se genera contra recibo
exportado : cada que se exporta
respaldado : cada que se respalda
cancelado : cambia status a cancelado, ademas de un usuario puede tener "SAT" cuando se cambia de forma automática
complemento : captura de complemento de pago que cambia status actual a pagado
egreso : captura de egreso que cambia status actual a pagado
*/
/*
-- solicita : Inicia nueva solicitud cuando el solicitante ingresa una factura u orden de compra que desea sea autorizada para pago
-- autoriza : Un autorizador recibe correo con datos de una nueva solicitud y puede elegir autorizar para pago, con esto se genera contra recibo automático
-- rechaza : Un autorizador recibe correo con datos de una nueva solicitud y puede elegir rechazar el pago de la misma, con esto también se cancela la factura
--           El solicitante recibira un correo indicando que la solicitud fue cancelada. El proveedor recibirá un correo indicando que la factura fue rechazada
-- cancela : Un administrador a peticion del solicitante o de alguna autoridad marca la solicitud como cancelada, con lo que también se cancela la factura
--           El solicitante recibira un correo indicando que la solicitud fue cancelada. El proveedor recibirá un correo indicando que la factura fue rechazada
-- exporta : Una vez autorizada una solicitud con factura, el solicitante recibe correo indicando que puede enviar los datos de la factura al sistema externo Avance/E-SASA
--           El sistema permite al solicitante exportar las veces que desee, mientras no 'procese' la solicitud, por si el sistema Avance le marca error. 
--           Adicionalmente el sistema permite Exportar Datos, de cualquier factura en cualquier momento, ingresando a la opción "Exporta Datos".
--           El Respaldo de Archivos se realiza de forma automática al momento de realizar esta acción
--           El sistema tambien permite ingresar a la opcion "Respalda Archivos" para enviar en cualquier momento los archivos xml y pdf de cualquier factura al sistema externo Avance/E-SASA
-- procesa : Una vez autorizada la solicitud con factura, el solicitante debe avisar que ha realizado todas las acciones que le corresponden para esta solicitud para continuar el proceso
-- contable : Después que la solicitud con factura ha sido procesada por el solicitante, la persona asignada de contabilidad recibe correo para que reciba la factura, la valide, documente o cualquier accion que le corresponda. Al termino de sus actividades debe presionar el botón Pasar a Pago.
-- anexa : Una solicitud por orden de compra al ser autorizada pasa a pago directamente. La primer acción antes de marcar una solicitud como pagada es anexar el comprobante de pago a la misma.
-- paga : Una solicitud por factura requiere todos los pasos anteriores antes de pasar a pago. Una vez que se ha anexado el comprobante de pago hay que marcar la solicitud como pagada.
--        Los contra recibos originales de facturas ingresadas a solicitud de pago solo pueden ser vistos por alguien autorizado para marcar pagada la solicitud.
--        Después de ver un contra recibo original hay que imprimirlo de inmediato pues a partir de la siguiente consulta al contra recibo solo se obtendrá una copia.
--        Las solicitudes listas para ser pagadas aparecen con un sello de pago, para que puedan ser impresos lo antes posible
--        Una vez que se abre un documento PDF marcado con sello de pago, transcurridos 5 minutos será destruido por lo que hay que imprimirlo en ese momento.
-- restaura : Una solicitud cancelada puede regresarse a su estado anterior por un administrador pero debe ingresar un motivo indicando la persona que solicita la restauracion y un motivo breve
--             Este cambio actualmente solo puede agregarse por base de datos.
-- elimina : Cuando una solicitud cancelada requiere que la factura pueda ingresarse nuevamente hay que desligar la factura de la solicitud. Despues de esto puede eliminarse o conservarse la solicitud como referencia
--           En esta firma se recomienda incluir en el motivo el id de la factura que originalmente tenía. Este cambio actualmente solo puede agregarse por base de datos.
-- completa : Cuando una solicitud por orden de compra se paga, una vez que se recibe la factura hay que anexarla a la solicitud de pago. Esta accion registra el momento en que una "Solicitud Pagada Sin Factura" pasa a ser "Solicitud Pagada" (con factura)
-- regresa : En cualquier etapa de una solicitud puede requerirse que se regrese al estado anterior, es importante indicar el motivo. Este cambio actualmente solo puede agregarse por base de datos.
-- habilita (modulo:ea)
-- deshabilita (modulo:ea)
-- agrega (modulo:ea)
*/
