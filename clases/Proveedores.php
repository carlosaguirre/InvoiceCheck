<?php
require_once dirname(__DIR__)."/bootstrap.php";
require_once "clases/DBObject.php";
class Proveedores extends DBObject {
    const PROVEEDORES_ERRCODE_NOEXIST=1;
    const PROVEEDORES_ERRCODE_NOTFOUND=2;
    const PROVEEDORES_ERRCODE_EMPTY=3;
    const PROVEEDORES_ERRCODE_DELETED=4;
    CONST PROVEEDORES_ERRCODE_BADNATION=5;
    const EXTRANJERO=0;
    const NACIONAL=1;
    const TIPO_COMERCIAL=1;
    const TIPO_FLETE=2;
    const TIPO_ADUANA=4;
    const TIPO_LOGISTICA=8;
    const TIPO_MAX=15;
    const TIPO_EXTRANJERO=16; // no incluido en tabla, se suma cuando NACIONAL=0
    public static $warning="";
    function __construct() {
        $this->tablename      = "proveedores";
        $this->rows_per_page  = 10;
        $this->fieldlist      = array("id", "codigo", "razonSocial", "rfc", "taxId", "nacional", "idTipo", "tipo", "zona", "idBanco", "banco", "rfcbanco", "cuenta", "edocta", "numpagEdoCta", "verificado", "opinion", "numpagOpinion", "cumplido", "generaopinion", "venceopinion", "credito", "codigoFormaPago", "status", "inicioVigencia", "finVigencia", "calle", "colonia", "ciudad", "estado", "pais", "codigoPostal", "telefono", "referencia1", "referencia2", "referencia3", "referencia4", "comentarios", "esServicio", "conCodgEnDesc", "reqObjImp", "reqPayTaxChk", "reqDefCvPrdSrv", "esAgenteAduanal", "modifiedTime");
        $this->fieldlist['id'] = array('pkey' => 'y', 'auto' => 'y');
        $this->fieldlist['modifiedTime'] = array('auto' => 'y');
        $this->fieldlist['codigo'] = array('skey' => 'y');
        $this->fieldlist['rfc'] = array('skey' => 'y');
        $this->log = "\n// xxxxxxxxxxxxxx Proveedores xxxxxxxxxxxxxx //\n";
    }
    function getNextCode($codebase) {
        $codl = strtoupper(substr($codebase, 0, 1));
        $tmpcod = $this->getValue("codigo",$codl."%","max(codigo)");
        $numcod = intval(substr($tmpcod,2));
        $numcod++;
        // $cod = $codl."-".$this->getValue("codigo",$codl."%","count(1)+1");
        $cod = $codl."-".str_pad("$numcod", 3, "0", STR_PAD_LEFT);
        if ($codebase==$cod) {
            $numcod++;
            $cod = $codl."-".str_pad("$numcod", 3, "0", STR_PAD_LEFT);
        }
        return $cod;
    }
    function getRegistryArrData($codeOrId) {
        $this->log .= "// INI function getRegistryArrData ( $codeOrId ) //\n";
        sessionInit();
        //$esSistemas=validaPerfil(["Administrador","Sistemas"]);
        //$bloqueaProv = validaPerfil("BloquearPrv")||$esSistemas;
        $where="id='$codeOrId' OR codigo='$codeOrId'";
        //if (!$esSistemas) $where="($where) and status<>'eliminado'";
        $arrData = $this->getData($where);
        global $query;
        $this->log .= "// QRY: $query //\n";
        if (empty($arrData)) {
            $this->log .= "// RESULT: NO EXISTS //\n";
            if (isset($_SESSION["$_SERVER[SERVER_NAME]_invoice_check_provider_cache"]) && isset($_SESSION["$_SERVER[SERVER_NAME]_invoice_check_provider_cache"][$codeOrId])) {
                $mightBeRealCode=$_SESSION["$_SERVER[SERVER_NAME]_invoice_check_provider_cache"][$codeOrId];
                if (is_string($mightBeRealCode) && isset($_SESSION["$_SERVER[SERVER_NAME]_invoice_check_provider_cache"][$mightBeRealCode]))
                    unset($_SESSION["$_SERVER[SERVER_NAME]_invoice_check_provider_cache"][$mightBeRealCode]);
                else if (is_array($mightBeRealCode) && isset($mightBeRealCode["id"]) && isset($_SESSION["$_SERVER[SERVER_NAME]_invoice_check_provider_cache"][$mightBeRealCode["id"]]))
                    unset($_SESSION["$_SERVER[SERVER_NAME]_invoice_check_provider_cache"][$mightBeRealCode["id"]]);
                unset($_SESSION["$_SERVER[SERVER_NAME]_invoice_check_provider_cache"][$codeOrId]);
            }
            return ["error"=>"Resultado vacío","errno"=>self::PROVEEDORES_ERRCODE_NOEXIST, "log"=>$this->log];
        }
        if (isset($arrData[0])) $arrData=$arrData[0];
        if (empty($arrData)) {
            $this->log .= "// RESULT: EMPTY //\n";
            return ["error"=>"Elemento de registro vacío","errno"=>self::PROVEEDORES_ERRCODE_EMPTY, "log"=>$this->log];
        }
        $id=$arrData["id"];
        $code=$arrData["codigo"];
        $status=$arrData["status"];
        if ($status==="eliminado" && !validaPerfil(["Administrador","Sistemas"])) {
            //$this->log .= "// RESULT: DELETED //\n";
            return ["error"=>"Proveedor eliminado del sistema","errno"=>self::PROVEEDORES_ERRCODE_DELETED, "log"=>$this->log];
        }
        foreach ($arrData as $key => $value) {
            if ($value===null) {
                $arrData[$key]="";
            } else if (($key==="venceopinion"||$key==="generaopinion")&&!empty($value)) {
                $tmpDate=DateTime::createFromFormat('Y-m-d',$value);
                if (isset($tmpDate)) {
                    $arrData[$key]=$tmpDate->format('d/m/Y');
                }
            }
        }

        if (!empty($arrData["id"])) {
            require_once "clases/Usuarios.php";
            $usrObj = new Usuarios();
            $arrValues = $usrObj->getValue("nombre", $code, "id,email");
            if (!empty($arrValues)) {
                list($arrData["userId"],$arrData["email"]) = explode("|",$arrValues);
            }
        }
        if (!isset($_SESSION["$_SERVER[SERVER_NAME]_invoice_check_provider_cache"])) $_SESSION["$_SERVER[SERVER_NAME]_invoice_check_provider_cache"]=[];
        $_SESSION["$_SERVER[SERVER_NAME]_invoice_check_provider_cache"]["$id"]=$code;
        $_SESSION["$_SERVER[SERVER_NAME]_invoice_check_provider_cache"][$code]=$arrData;
        return $arrData;
    }
    static function esCorporativo($codigo) {
        static $groupCodes = ["A-010","A-046","B-036","B-097","B-098","C-075","C-502","D-015","D-027","D-999","E-054","E-087","I-162","L-007","L-022","L-043","M-040","M-122","M-262","P-011","P-049","P-101","R-069","S-050","S-164","T-028"];
        return in_array($codigo, $groupCodes);
    }
    static function describeBankStatus($status,$verificado,$cumplido,$descType=0) { // [activo,inactivo,actualizar,bloqueado],[-1,0,1],[-2,-1,0,1],[0,1]
        static::$warning="describeBankStatus: $status, $verificado, $cumplido, $descType";
        $dt0=($descType==0);
        $dt1=($descType==1);
        if ($status==="actualizar") {
            $status=$dt0?"Actualizar datos":($dt1?"el proveedor deba actualizar datos":"El proveedor debe actualizar sus datos bancarios");
        } else if ($verificado==0) {
            $pl=["",""]; // 0 = plural s suffix, 1 = plural n suffix
            $status=$dt0?"Cuenta":($dt1?"el estado de cuenta":"El estado de cuenta");
            $isMsgA=true;
            if ($cumplido<=0) {
                $status.=" y ";
                $pl=["s","n"];
                $status.=$dt0?"Opinión":"el documento del SAT de Opinión de Cumplimiento";
                if ($cumplido<0) $isMsgA=false;
            }
            if ($isMsgA)
                $status.=$dt0?" Pendiente".$pl[0]:($dt1?" está".$pl[1]." PENDIENTE".strtoupper($pl[0])." por aprobar":" está".$pl[1]." en espera de ser autorizado".$pl[0]);
            else
                $status.=$dt0?" no Verificados":" no están verificados para pago";
        } else if ($verificado<0 || $cumplido<-1) {
            $pl=["",""];
            $status="";
            if ($verificado<0) {
                $status=$dt0?"Cuenta":($dt1?"el estado de cuenta":"El estado de cuenta");
                if ($cumplido<-1) {
                    $status.=" y ";
                    $pl=["s","n"];
                }
            }
            if ($cumplido<-1) {
                $status.=$dt0?"Opinión":(($dt1||$verificado<0)?"el documento del SAT de Opinión de Cumplimiento":"El documento del SAT de Opinión de Cumplimiento");
            }
            $status.=$dt0?" Rechazada".$pl[0]:" está".$pl[1]." RECHAZADO".strtoupper($pl[0]);
            if ($cumplido==0) // verificado es menor a cero
                $status.=$dt0?"":" y la Opinión no verificada";
        } else if ($cumplido<0) {
            $status=$dt0?"Opinión Vencida":($dt1?"el documento del SAT de Opinión de Cumplimiento VENCIDO":"El documento del SAT de Opinión de Cumplimiento está VENCIDO");
        } else if ($cumplido==0) {
            $status=$dt0?"Opinión Pendiente":($dt1?"el documento del SAT de Opinión de Cumplimiento está PENDIENTE por aprobar":"El documento del SAT de Opinión de Cumplimiento no está verificado");
        } else {
            if ($dt0) static::$warning.=" (dt0)";
            else if ($status==="activo") static::$warning.=" (activo)";
            else if ($dt1) static::$warning.=" (dt1)";
            else static::$warning.=" (dt2)";
            $status=$dt0?strtoupper($status):(($status==="activo")?"":($dt1?"el status del proveedor está en ".$status:"El proveedor está '".$status."'"));
        }
        static::$warning.=" > $status";
        return $status;
    }
    function setIdMap($force=false) {
        if (!hasSession()) return false;
        global $_esAdministrador, $_esSistemasX;
        require_once "configuracion/loggedInCheck.php";
        if ($force||empty($_SESSION["prvMap"])) {
            $oldRPP=$this->rows_per_page;
            $this->rows_per_page=0;
            $this->clearOrder();
            $this->addOrder("codigo");
            $prvData=$this->getData(($_esAdministrador||$_esSistemasX)?false:"status not in (\"inactivo\",\"eliminado\")",0,"id, codigo, razonSocial, banco, cuenta, verificado, cumplido, status");
            $prvMap=[];
            foreach ($prvData as $row) {
                $id=$row["id"];
                unset($row["id"]);
                $prvMap[$id]=$row;
            }
            $_SESSION['prvMap'] = $prvMap;
            return true;
        }
        return false;
    }
    function setIdOptSessions($idTipo=null,$value="codigo", $force=false) {
        // Que indica cada valor de idTipo:
        // null : nacional=null, tipo=null (todos los proveedores)
        // negativo : nacional=null, no importa si es nacional o foraneo
        // menor a 1000 : nacional=1
        // 1000 o mayor : foraneo (nacional=0)
        //  1 : Comercial. Vende material y/o servicios diversos
        //  2 : Fletero. Realiza traslado de material, aereo, marítimo o terrestre
        //  4 : Agente Aduanal. Realiza operaciones de importacion y exportacion
        //  8 : Logística. Realiza servicio de logística para importacion o exportacion
        // 16 : Servicio. Proveedor de servicios como agua, luz, gas
        if (!hasSession()) return false;
        if ($value!=="razon" && $value!=="rfc") $value="codigo";
        $sortBy=($value==="razon"?"razonSocial":($value==="rfc"?"rfc":"codigo"));

        $prvOptIdTipo=$_SESSION["prvOptIdTipo"]??null;

        if ($prvOptIdTipo!=$idTipo) $force=true;
        $_SESSION["prvOptIdTipo"]=$idTipo;
        $valNacional = (!isset($idTipo)||$idTipo<0)?null:($idTipo<1000?1:0);
        $valTipo = isset($idTipo)?(abs($idTipo)%1000):null;
        $prvOptDefVal=$_SESSION["prvOptDefVal"]??"codigo";
        if ($prvOptDefVal!==$value) $force=true;
        //if ($esSAdmin)
        $_SESSION["prvOptDefVal"]=$value;
        if ($force||empty($_SESSION["prvIdOpt"])) {
            $oldRPP=$this->rows_per_page;
            $this->rows_per_page=0;
            $this->clearOrder();
            $this->addOrder($sortBy);
            $prvData=$this->getData("status not in (\"inactivo\",\"eliminado\")".(is_null($valNacional)?"":" and nacional=$valNacional").(is_null($valTipo)||$valTipo==0?"":" and tipo&$valTipo>0"),0,"id,rfc,razonSocial,codigo,nacional,tipo".(!isset($valNacional)||$valNacional!=1?",taxId":"").",idBanco,cuenta,calle,ciudad,estado,pais,codigoPostal");
            $prvIdOpt=[]; $prvRazSoc2Id=[]; $prvCodigo2Id=[]; $prvRFC2Id=[];
            foreach ($prvData as $idx => $prv) {
                $prvId=$prv["id"]; $prvRfc=$prv["rfc"]; $prvRzS=$prv["razonSocial"]; $prvCod=$prv["codigo"];
                $pos=strpos($prvRzS,"&");
                if ($pos!==false && substr($prvRzS,$pos,5)!=="&amp;") $prvRzS=str_replace("&", "&amp;", $prvRzS);
                $prvIdOpt[$prvId]=["rfc"=>$prvRfc,"razon"=>$prvRzS,"codigo"=>$prvCod];
                if (isset($prvIdOpt[$prvId][$value]))
                    $prvIdOpt[$prvId]["value"]=$prvIdOpt[$prvId][$value];
                $prvRazSoc2Id[$prvRzS]=$prvId;
                $prvCodigo2Id[$prvCod]=$prvId;
                $prvRFC2Id[$prvRfc]=$prvId;
            }
            $_SESSION['prvIdOpt']=$prvIdOpt;
            $_SESSION['prvRazSoc2Id']=$prvRazSoc2Id;
            $_SESSION['prvCodigo2Id']=$prvCodigo2Id;
            $_SESSION['prvRFC2Id']=$prvRFC2Id;
            $this->rows_per_page=$oldRPP;
            return true;
        }
        return false;
    }
    function setXIdOptSessions($force=false) {

    }
    function setOptSessions($force=false) {
        if (!$force&&!empty($_SESSION["prvCodigoOpt"])&&!empty($_SESSION["prvRazSocOpt"])&&!empty($_SESSION["prvRFCOpt"])) return false;
        $prvFullMapWhere="status not in (\"inactivo\",\"eliminado\")";
        if (getUser()->nombre==="admin") $prvFullMapWhere=false;
        else if (validaPerfil("Administrador")) $prvFullMapWhere="status!=\"eliminado\"";
        else if (validaPerfil("Proveedor")&&!validaPerfil("Sistemas")) $prvFullMapWhere="codigo='".getUser()->nombre."'";
        $oldRPP=$this->rows_per_page;
        $this->rows_per_page = 0;
        if (empty($_SESSION["prvCodigoOpt"])) {
            $this->clearFullMap();
            $this->clearOrder();
            $this->addOrder("codigo");
            $_SESSION['prvCodigoOpt'] = $this->getFullMap("codigo","codigo",$prvFullMapWhere);
        }
        if (empty($_SESSION["prvRazSocOpt"])) {
            $this->clearFullMap();
            $this->clearOrder();
            $this->addOrder("razonSocial");
            $prvRazSocOpt=$this->getFullMap("codigo","razonSocial",$prvFullMapWhere);
            foreach ($prvRazSocOpt as $key=>$value) {
                $pos=strpos($value,"&");
                if ($pos!==false) $prvRazSocOpt[$key] = str_replace("&", "&amp;",$value);
            }
            $_SESSION['prvRazSocOpt'] = $prvRazSocOpt;
        }
        if (empty($_SESSION["prvRFCOpt"])) {
            $this->clearFullMap();
            $this->clearOrder();
            $this->addOrder("rfc");
            $prvRFCOpt = $this->getFullMap("codigo","rfc",$prvFullMapWhere);
            foreach ($prvRFCOpt as $key=>$value) {
                $pos=strpos($value,"&");
                if ($pos!==false) $prvRFCOpt[$key] = str_replace("&", "&amp;",$value);
            }
            $_SESSION['prvRFCOpt'] = $prvRFCOpt;
        }
        return $prvFullMapWhere;
    }
    function getXXId() {
        global $bd_servidor, $bd_base, $bd_usuario, $bd_clave;
        $ip = (empty($_SERVER['HTTP_CLIENT_IP'])?(empty($_SERVER['HTTP_X_FORWARDED_FOR'])?($_SERVER['REMOTE_ADDR']):$_SERVER['HTTP_X_FORWARDED_FOR']):$_SERVER['HTTP_CLIENT_IP']);
        try {
            $dsn="mysql:host=$bd_servidor;dbname=$bd_base;charset=utf8";
            $pdo = new PDO($dsn, $bd_usuario, $bd_clave);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
            // toDo: generate rfc XEXX consecutive
            if ($pdo->beginTransaction()) {
                
                $stmt=$pdo->query("select concat('XEXX',lpad(right(valor,9)+1,9,'0')) from infolocal where nombre='lastXEXX' FOR UPDATE");
                $newRfc=$stmt->fetchColumn();
                if (isset($newRfc[0])) {
                    $fixQry="UPDATE infolocal set valor=? where nombre='lastXEXX'";
                } else {
                    $stmt=$pdo->query("select concat('XEXX',lpad(right(rfc,9)+1,9,'0')) nrfc from proveedores where rfc like 'XEXX%' order by rfc desc limit 1 FOR UPDATE");
                    $newRfc=$stmt->fetchColumn();
                    if (!isset($newRfc[0])) $newRfc="XEXX110101001";
                    $fixQry="INSERT INTO infolocal (nombre,valor) VALUES ('lastXEXX',?)";
                }
                $stmt=$pdo->prepare($fixQry);
                $stmt->execute([$newRfc]);
                $pdo->commit();
                return $newRfc;
            }
        } catch (Exception $ex) {
            if(isset($pdo)) {
                $pdo->rollback();
                $pdo=null;
            }
            throw $ex;
        }
        throw new Exception("Transacción fallida, consulte al administrador");
    }
}
