<?php
require_once dirname(__DIR__)."/bootstrap.php";
require_once "clases/DBObject.php";
class Contrarrecibos extends DBObject {
    function __construct() {
        $this->tablename      = "contrarrecibos";
        $this->rows_per_page  = 10;
        $this->fieldlist      = array("id", "folio", "codigoProveedor", "razonProveedor", "rfcGrupo", "razonGrupo", "aliasGrupo", "fechaRevision", "credito", "fechaPago", "total", "esCopia", "estaImpreso", "selloImpreso", "numAutorizadas", "numContraRegs", "comprobantePago", "crExterno", "modifiedTime");
        $this->fieldlist['id'] = array('pkey' => 'y', 'auto' => 'y');
        $this->fieldlist['modifiedTime'] = array('auto' => 'y');
        $this->log = "\n// xxxxxxxxxxxxxx Contrarrecibos xxxxxxxxxxxxxx //\n";
    }
    function getNextFolio($aliasGrupo) {
        require_once "clases/InfoLocal.php";
        $info = new InfoLocal();
        // TODO: Alternativa cuando no hay APCU.
        //       getValue de InfoLocal _CR_$aliasGrupo
        //        - Si lo encuentra incrementar valor en InfoLocal
        //        - Si no lo encuentra obtener max(folio) de Contrarrecibos
        //          - Incrementar en uno y guardar en InfoLocal
        if ($info->available()) {
            $ultimoFolio = $info->obtener("_CR_$aliasGrupo");
            $retVal = $this->getValue("aliasGrupo",$aliasGrupo,"max(folio)+1", false, "group by aliasGrupo");
            
            if (empty($ultimoFolio)) $ultimoFolio=1;
            else $ultimoFolio = +$ultimoFolio+1;
            
            if (empty($retVal) || $retVal==="NULL" || $ultimoFolio>$retVal) $retVal = $ultimoFolio;
            $info->definir("_CR_$aliasGrupo",$retVal);
            return $retVal;
        } else {
            global $query;
            //DBi::freeResult();
            //DBi::nextResult();
            $aliasGrupo=strtoupper($aliasGrupo);
            $query="CALL NEXTCRID('$aliasGrupo')";
            $start=hrtime(true);
            $result = DBi::query($query);
            $duration=(hrtime(true)-$start)/1000000000.0;
            doclog("NEXTCRID","call",["query"=>$query,"clases"=>"Contrarrecibos","duration"=>$duration]);
            if ($result) {
                $row = $result->fetch_assoc();
                $retVal=$row["nextId"]??false;
                $this->log .= "// getNextFolio ( $aliasGrupo ) = $retVal\n";
                $duration=(hrtime(true)-$start)/1000000000.0;
                doclog("NEXTCRID","call",["alias"=>$aliasGrupo,"row"=>$row,"value"=>$retVal,"duration"=>$duration]);
                $result->free();
                //DBi::freeResult();
                //$result->close();
                DBi::nextResult();
                return $retVal;
            }
            //DBi::freeResult();
            //DBi::nextResult();
            return false;
        }
    }
    static function getFechaVencimiento($fechaRevision, $diasCredito=0, $dif=0) {
        if ($diasCredito<=0) return substr($fechaRevision, 0, 10);
        if ($dif>0) {
            $revTS=strtotime($fechaRevision);
            $wkDt=+date("w",$revTS);
        } else $wkDt=+date("w");
        $wkd=($wkDt+$diasCredito)%7;
        $dayOffset=3-$dif;
        $baseDayInc=9-$dif;
        if ($wkd<$dayOffset) $wkd+=7; // Domingo y Lunes aumenta 7 para contrarrestar los 8 que se agregan
        $creditoFix=$diasCredito+$baseDayInc-$wkd; // De Domingo a Lunes, 8-wkd = 1, 0, 6, 5, 4, 3, 2. Son los dias que se agregan a credito para asegurar que la fecha caiga en Lunes.
        $venceTS=strtotime($fechaRevision."+ $creditoFix days");
        return date("Y-m-d",$venceTS);
    }
}
