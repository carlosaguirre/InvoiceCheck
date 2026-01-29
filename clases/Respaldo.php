<?php
require_once dirname(__DIR__)."/bootstrap.php";
class Respaldo {
    private $config;
    public function __construct(string $configPath) {
        $this->config = $this->leerConfiguracion($configPath);
    }
    private function leerConfiguracion(string $ruta): array {
        $extension = pathinfo($ruta, PATHINFO_EXTENSION);
        if (!file_exists($ruta)) {
            throw new Exception("Archivo de configuración no encontrado: $ruta");
        }
        if ($extension === 'ini') {
            return parse_ini_file($ruta); //, true); // $ruta es el nombre del archivo de configuración a analizar. Si se utiliza una ruta relativa, se evalúa relativa a la carpeta actual, luego según el include_path.
        } elseif ($extension === 'json') {
            $contenido = file_get_contents($ruta);
            return json_decode($contenido, true);
        } else {
            throw new Exception("Formato de configuración no soportado: $extension");
        }
    }
    private function validarRuta(string $ruta): bool {
        $validaUnidadExternaORed=false;
        if ($validaUnidadExternaORed) {
            $real = realpath($ruta);
            return $real !== false && is_dir($real) && is_readable($real);
        }
        return is_dir($ruta) && is_readable($ruta);
    }
    public function ejecutar(): void {
        $origen = $this->config['origen'] ?? '';
        $destino = $this->config['destino'] ?? '';
        if (!$this->validarRuta($origen)) {
            throw new Exception("Ruta de origen inválida o inaccesible: $origen");
        }
        if (!is_dir($destino)) {
            mkdir($destino, 0777, true);
        }
        $this->log("Inicia copia de archivos"); // aparentemente no necesario pero es preferible que si falla arroje la excepcion aqui
        $this->copiarArchivosFlexible($origen, $destino);
        $this->cerrarLog();
    }
    private function copiarArchivosFlexible($origen, $destino) {
        $desde = $this->config['desde'] ?? null;
        $hasta = $this->config['hasta'] ?? null;
        $patron = $this->config['patron'] ?? '*';
        $recursivo = $this->config['recursivo'] ?? false;
        $lote = $this->config['lote'] ?? 0;
        $pausa = $this->config['pausa'] ?? 0;
        if (substr($origen, -1) !== "/") {
            $origen .= "/";
        }
        if (!is_dir($destino)) {
            mkdir($destino, 0777, true);
        }
        $archivos = glob($origen . $patron);
        $contador=0;
        foreach ($archivos as $archivo) {
            if (is_file($archivo)) {
                $modificado = filemtime($archivo);
                // Filtrado por fecha
                if ($desde && $modificado < strtotime($desde)) continue;
                if ($hasta && $modificado > strtotime($hasta)) continue;
                $nombreArchivo = basename($archivo);
                $destinoFinal = $destino . "/" . $nombreArchivo;
                if (copy($archivo, $destinoFinal)) {
                    $this->log("Copiado: $archivo -> $destinoFinal");
                    if (!empty($lote) && !empty($pausa)) {
                        $contador++;
                        if ($contador % $lote === 0) {
                            $this->log("Pausa: $pausa seg / $lote copiados");
                            usleep($pausa * 1000000); // pausa en microsegundos
                        }
                    }
                } else {
                    $this->log("Error No copiado: $archivo -X- $destinoFinal");
                }
            } elseif ($recursivo && is_dir($archivo)) {
                $nombreSubdir = basename($archivo);
                $this->copiarArchivosFlexible($archivo, $destino . "/" . $nombreSubdir);
            }
        }
    }
    private function abrirLog() {
        $this->log(null);
    }
    private function cerrarLog() {
        $this->log(false);
    }
    private function log($mensaje=null) {
        static $log = null;
        if ($mensaje===false) { // cerrarLog
            if ($log!==null) {
                fclose($log);
                $log=null;
            }
            return;
        }
        if ($log===null) { // primera vez o abrirLog
            $logPath = $this->config['log'] ?? 'respaldo.log';
            $log = fopen($logPath, 'a');
            if ($log===false) { //!$log) {
                $log=null;
                throw new Exception("No se pudo abrir el archivo de log.");
            }
        }
        if (is_string($mensaje)) $mensaje=trim($mensaje);
        if (!empty($mensaje)) {
            $modo = $this->config['modo'] ?? 'respaldo';
            fwrite($log, date('[Y-m-d H:i:s]') . " [$modo] $mensaje\n");
        }
    }
}
if (empty($argv[1])) throw new Exception("Falta indicar archivo de configuración");
$respaldo = new Respaldo($argv[1]);
$respaldo->ejecutar();
