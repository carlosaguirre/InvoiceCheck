<?php
/**
 * DAOFactory - Centralized Data Access Object Management
 * 
 * This class provides centralized, singleton-based management of all DAO (Data Access Object)
 * instances. It replaces the repetitive pattern of:
 * 
 *     global $usrObj;
 *     if (!isset($usrObj)) {
 *         require_once "clases/Usuarios.php";
 *         $usrObj = new Usuarios();
 *     }
 * 
 * With a simple, clean interface:
 * 
 *     $usrObj = DAOFactory::get('usr');
 * 
 * Features:
 * - Lazy instantiation (objects created on first use)
 * - Singleton caching (one instance per table)
 * - Centralized mapping (all tables in one place)
 * - Automatic file loading
 * - Error handling with helpful messages
 * - Backward compatible with existing code
 * - Optional global registration for compatibility
 * 
 * Naming Convention:
 * - Abbreviations are 2-3 letters, typically first letters of class name
 * - Format: abbreviation → class name
 * - Example: 'usr' → 'Usuarios', 'prv' → 'Proveedores'
 * 
 * @package InvoiceCheck
 * @version 1.0
 * @author Data Architecture Optimization
 */

// ============================================================================
// EXCEPTION CLASSES
// ============================================================================

/**
 * Exception thrown when DAO operation fails
 */
class DAOException extends Exception {}

/**
 * Exception thrown when DAO abbreviation is unknown
 */
class DAONotFoundException extends DAOException {}

// ============================================================================
// DAO FACTORY CLASS
// ============================================================================

class DAOFactory {
    
    /**
     * Singleton instance of DAOFactory
     * @var DAOFactory|null
     */
    private static $instance = null;
    
    /**
     * Cache of instantiated DAO objects
     * Format: ['abbreviation' => object_instance, ...]
     * @var array
     */
    private $objects = array();
    
    /**
     * Mapping of abbreviations to class names and file paths
     * Format: [
     *     'abbreviation' => [
     *         'class' => 'ClassName',
     *         'file' => 'clases/ClassName.php' (optional, auto-derived)
     *     ],
     *     ...
     * ]
     * @var array
     */
    private $mapping = array();
    
    /**
     * Constructor - Initialize with default table mappings
     * 
     * Maps are stored as:
     * - Key: abbreviation (2-3 letters, lowercase)
     * - Value: class name (exact case as defined)
     * 
     * Files are auto-derived from class name: clases/ClassName.php
     * If different, override by setting 'file' key
     * 
     * @access private Use getDAOInstance() instead
     */
    private function __construct() {
        $this->initializeMapping();
    }
    
    /**
     * Initialize the default DAO mapping table
     * 
     * Add your table mappings here:
     * 'abbreviation' => 'ClassName' (file auto-derived)
     * or use array format for custom file paths
     * 
     * @return void
     */
    private function initializeMapping() {
/*
acc Cuentas
act Acciones
art Articulos
bnk Bancos
cal Calendar
cat catalogoSAT
cec ComExtCatalogo
ced ComExtDocumentos
cee ComExtExpediente
clb CatLista69B
cmf CargaMF
cpt Conceptos
cpy CPagos
ctf Contrafacturas
ctr Contrarrecibos
doc Doctos
dpy DPagos
emp Empleados
evt Eventos
fir Firmas
gpo Grupo
hst Historial
inf InfoLocal
inv Facturas
log Logs
mdp MetodosDePago
mnu Menu
mrc Mercancias
nom Nomina
obq OpcionesBloqueo
ord OrdenesCompra
per Perfiles
prc Proceso
prm Permisos
prt ProveedorTipos
prv Proveedores
ptc ProveedorTipoCuentas
psu PagoSoloUuid
pvg ProveedorGrupo
py Pagos
rar ReposicionArchivos
rcc ReposicionCajaChica
rlb RegLista69B
rvc RepViaConceptos
rvi ReposicionViaticos
sol SolicitudPago
srv Servicios
tmp Temporales
tok Tokens
trc Trace
usr Usuarios
ug Usuarios_Grupo
up Usuarios_Perfiles
*/
        // ====================================================================
        // USER & PERMISSION MANAGEMENT
        // ====================================================================
        $this->addMapping('act', 'Acciones'); // OK. Meant for managing user actions which are used for permission management.
        $this->addMapping('usr', 'Usuarios'); // OK. Table to store user information. This is the main table for user management, and it is used across multiple modules for various purposes, including authentication, authorization, notifications, and more. This table is meant to store the data related to users of the system, including their credentials, contact information, and other relevant details.
        $this->addMapping('up', 'Usuarios_Perfiles'); // Table with correlation between users and profiles. This is meant to manage the relationship between users and profiles, allowing a user to have multiple profiles and a profile to be assigned to multiple users. This table is used for permission management, as permissions are assigned to profiles, and users inherit those permissions through their assigned profiles.
        $this->addMapping('ug', 'Usuarios_Grupo'); // Table with correlation between users, profiles and groups. This is meant to manage the relationship between users and profiles in specific groups, allowing the system to restrict users access to some profiles only for specific companies in the corporate.
        $this->addMapping('per', 'Perfiles'); // OK. Meant for managing user profiles which are linked to a group of 'Acciones' for permission management.
        $this->addMapping('prm', 'Permisos'); // OK. Meant for managing permissions, linking 'Perfiles' and 'Acciones' and defining read and write flags.
        // $this->addMapping('emp', 'Empleados'); // Module not implemented yet, but this is the intended table for employee data, which can be used for user management and other purposes.
        // $this->addMapping('nom','Nomina'); // Functionality for this table is not implemented. The project to implement this functionality is on hold, so this table is not currently used. This is meant to be used for payroll management, but it can be used for other purposes related to employee management as well.
        
        // ====================================================================
        // PROVIDER/VENDOR MANAGEMENT
        // ====================================================================
        $this->addMapping('prv', 'Proveedores'); // OK. Stores provider information, including their RFC, name, and other relevant details. This is the main table for provider management, and it is used across multiple modules for various purposes, including invoice management, payment management, and more. Currently providers are also stored in 'Usuarios' table to allow them access the portal. Provider and User data are kept separately avoiding ambiguous situations. Field 'codigoProveedor' in 'Proveedores' table must be used in 'Usuarios' table as 'nombre' field to associate provider data with user data.
        // $this->addMapping('pvg', 'ProveedorGrupo'); // Table meant for storing specific data between a specific provider and a specific company in the corporate group. This table is not currently used, as the project to implement this functionality is on hold. Currently we are proposing unified providers management for all companies in the corporate group, so this table might not be necessary, but it can be useful for specific cases, so it is better to have it defined and decide later if it is kept or removed.
        // $this->addMapping('clb','CatLista69B'); // Functionality for this table is managed completely with its function 'estaMarcado($rfc)', for validating if a provider is marked with the SAT's list 69B. No need to create a full DAO class for it, as it is not used for any other purpose.
        // $this->addMapping('rlb','RegLista69B'); // Functionality for this table is managed completely with its function 'updateDocuments()', for updating SAT's list 69B. No need to create a full DAO class for it, as it is not used for any other purpose.
        $this->addMapping('prt', 'ProveedorTipos'); // Table for accounting identification of providers. This table belongs to a project on hold, there is only some configuration for administration roles. This is meant to be used for classifying providers according to SAT's accounting types, which can be useful for financial and tax purposes, but it can also be used for other purposes related to provider management.
        $this->addMapping('ptc', 'ProveedorTipoCuentas'); // Table meant for storing accounting information related to provider types. This table is not currently used, as the project to implement this functionality is on hold. This table would help Accounting Department to obtain tax rates and other relevant financial information related to provider types.

        // ====================================================================
        // CLIENT/CUSTOMER MANAGEMENT - Companies in the corporate group. This portal works with external providers, we are corporate members, and the clients.
        // ====================================================================
        $this->addMapping('gpo', 'Grupo'); // OK. List of companies in the corporate group, meant to be CFDI Receptor Companies in the Invoice documents.
        
        // ====================================================================
        // CONFIGURATION, NAVIGATION, TRACKING AND LOGGING TABLES
        // ====================================================================
        $this->addMapping('mnu', 'Menu'); // OK. List of menu items for navigation. This allows dynamic management of the navigation structure, with permissions and specific rules.
        $this->addMapping('inf', 'InfoLocal'); // OK. Meant for storing general information about the local environment and configuration data that can be used across modules.
        $this->addMapping('fir', 'Firmas'); // OK. List of actions that require user reference (signature). Currently mostly used in payment requests for displaying reference between users and process actions.
        $this->addMapping('prc', 'Proceso'); // OK. Meant for tracking invoice statuses and other flow processes with status control. Currently best usage for this table is for tracking user actions in the system, so it can be used for auditing and process control, but it can be used for other purposes as well.
        $this->addMapping('evt', 'Eventos'); // OK. Meant for tracking events for automatic and scheduled tasks.
        $this->addMapping('log', 'Logs'); // OK. Stores logs for javascript function logService, which is used for tracking and debugging client-side events, but can store also server-side events. This is meant to be a general-purpose log service, so it can be used across modules for various purposes.
        $this->addMapping('trc', 'Trace'); // OK. Meant for tracking detailed traces of system operations for debugging and auditing purposes. This is meant to be a general-purpose trace service, so it can be used across modules for various purposes.
        // $this->addMapping('obq', 'OpcionesBloqueo'); // Table meant for managing blocking reasons for providers. Purchasing department requested a way to configure reasons for blocking providers but module project has not been approved yet. At some time in the future a depuration process will be required to determine if this table is kept or removed, along with others in the same situation.
        // $this->addMapping('hst', 'Historial'); // Meant to be used for tracking changes in important tables, but this has not been implemented yet. This is planned to be used for general-purpose tables 'historialchar' and 'historialint' for tracking changes in any other table, so it does not have a specific prefix related to a single module.
        
        // ====================================================================
        // INVOICES AND RELATED DOCUMENTS MANAGEMENT
        // ====================================================================
        $this->addMapping('inv', 'Facturas'); // OK. Data related to CFDI documents and the respective cycle management. Although 'Facturas' name is used, this table stores information about all types of CFDI documents, including invoices, payment receipts, credit notes, etc. This is the main table for managing CFDI documents in the system, and it is used across multiple modules for various purposes, including invoice management, payment management, and more. This table is meant to store the data extracted from CFDI documents uploaded to the system, and it is linked to other tables for managing related information such as concepts, payments, etc.
        $this->addMapping('cpt', 'Conceptos'); // OK. Data extracted from invoice concepts/articles. A single invoice or credit note document can have multiple concepts, so this is meant to be a separate table for managing that data in a more efficient way.
        $this->addMapping('ctf', 'Contrafacturas'); // OK. Invoice reference for payment receipt documents saved in table Contrarrecibos. Each Contrarrecibo can have multiple invoice references, so this is meant to be a separate table for managing that data in a more efficient way.
        $this->addMapping('ctr', 'Contrarrecibos'); // OK. Control documents in Purchasing Department to prepare the payment of invoices' process. Each Contrarrecibo can have multiple invoice references, so the data related to the invoice references is stored in a separate table 'Contrafacturas'. Finance and CEO departments can decide to pay as they wish, so bank information about payments might not correlate directly with the Contrarrecibos data. Contrarrecibos and contrafacturas are meant for Purchasing Department's internal pre-payment management.
        $this->addMapping('cpy', 'CPagos'); // OK. SAT requires a Payment Receipt document (CFDI de Recepción de Pago) for each payment received by providers, so the provider creates this documents and uploads them to our system. Having multiple tables related to payment control allows us to evaluate the payment process and prove inconsistencies in it, so we can propose alternatives and improvements to the process. CPagos is meant to store the data extracted from the payment receipt documents uploaded by providers, and it is one of the valid sources to set PAID status to an invoice reference.
        $this->addMapping('dpy', 'DPagos'); // OK. Payment Receipt document can have multiple invoice references, so this is meant to be a separate table for managing the data related to the invoice references in a more efficient way. Payment Receipt Documents allow partial payments so DPagos help us track and manage payments and determine when an invoice is fully paid.
        $this->addMapping('mrc', 'Mercancias'); // OK. Mercancias is meant for CFDI invoices from freight providers, there is a section to specify transported goods (mercancias), so this table is meant to store the data related to that section of the invoice documents. This is not used for all CFDI invoices, but only for those related to freight services, so it is better to have a separate table for it.
        $this->addMapping('srv', 'Servicios'); // OK. Table meant for data analysis and management of service-related invoices. Field 'codigoArticulo' is captured manually by Purchasing Department users, each company in the corporate group has its own code for each service. This table is meant to analyze this information and propose a unified code for each service, so we can have a better management and analysis of service-related invoices. There are interests from Purchasing and Finances Departments to improve the management of certain service-related invoices, particularly those related to freight services.
        
        // ====================================================================
        // ACCOUNTING AND FINANCIAL MANAGEMENT (ABOVE OR ASIDE INVOICE LAYER)
        // ====================================================================
        $this->addMapping('py', 'Pagos'); // OK. Table for recording documented expenditures in the portal. In the Load Payments module, text files are received with payment lists containing date, amount, total and related expenditure number. This information is stored in this table to be used as reference and confirmation of received payments. The payment information must match because both transaction documents and expenditure records are generated along with the bank transaction externally to the portal by finance, treasury or accounting departments, so this table is a reference point to validate received payment information and detect possible inconsistencies in the process. Payment information can be used to generate expenditure reports, account statements and other financial reports.
        $this->addMapping('sol', 'SolicitudPago'); // OK. Table for managing urgent payment requests, however some companies also use this process for non-urgent payment requests. This is a specific process in Purchasing Department, so this table is meant to manage the information related to this process, which is partially related to invoice management. Currently payment requests are related to one invoice, or to a purchasing order without an invoice, also there is a special payment request related to an internal pre-payment receipt (Contrarrecibos), also other payment alternatives are planned and this table is meant to be used for those payment-related processes. Each record in this table represents a payment request, which can be linked to one invoice, or to one purchasing order, or to a pre-payment receipt documents, but it also has its own specific information related to the payment request process, so it is better to have a separate table for it.
        $this->addMapping('ord', 'OrdenesCompra'); // OK. Table for storing specific purchasing order information related to related urgent payment requests. This is a specific process in Purchasing Department, so this table is meant to manage the information related to this process, which is partially related to invoice management. Purchasing orders are usually generated and sent to providers, it is common that providers request payment before an invoice document is generated and sent to the company, and this tables allows Purchasing Department to register the information related to this process. The payment request process allows users to append an invoice document after this process status have changed to PAID.
        $this->addMapping('tok', 'Tokens'); // OK. Table meant for storing tokens for user authentication without using passwords. Critical payment requests' steps includes an email notification, in those emails actions can be performed by users through links with embedded tokens for authentication, so this table is meant to store those tokens and manage their status and expiration for security purposes. This is meant to be used for payment request process, but it can be used for other processes as well if needed.
        $this->addMapping('mdp', 'MetodosDePago'); // OK. Old table to validate SAT's catalogue data for 'FormaDePago'. Name is wrong, but this wasn't fixed because almost all SAT's catalogues are currently validated with catalogoSAT class. This table is still used because not all 'FormaDePago' values in SAT's catalogue are accepted by our corporate companies.
        $this->addMapping('acc', 'Cuentas'); // OK. Not fully implemented yet.
        $this->addMapping('bnk', 'Bancos'); // OK. Not fully implemented yet
        $this->addMapping('rar', 'ReposicionArchivos'); // OK. Table meant for Petty Cash Reimbursement Records' Documents, which is a specific process in Purchasing Department. Each record in this table stores one document meant as expenses proof, and is linked to a specific reimbursement process.
        $this->addMapping('rcc', 'ReposicionCajaChica'); // OK. Table meant for Petty Cash Reimbursement Records, which is a specific process in Purchasing Department. Each record in this table stores one reimbursement process. Each reimbursement process can have multiple documents linked to it, which are stored in the 'ReposicionArchivos' table.
        $this->addMapping('rvi', 'ReposicionViaticos'); // OK. Table meant for Travel Reimbursement Records, which is a specific process in Purchasing Department. Each record in this table stores one reimbursement process. Each reimbursement process can have multiple documents linked to it, which are stored in the 'RepViaConceptos' table.
        $this->addMapping('rvc', 'RepViaConceptos'); // OK. Table meant for Travel Reimbursement Records' Documents, which is a specific process in Purchasing Department. Each record in this table stores one document meant as expenses proof, and is linked to a specific reimbursement process.
        $this->addMapping('tmp', 'Temporales'); // OK. Table meant for storing temporarily uploaded petty cash documents before petty cash records are generated, which is a specific process in Purchasing Department. Each record in this table stores one temporarily uploaded documents.
        // $this->addMapping('cec', 'ComExtCatalogo'); // For module 'Comercio Exterior', this module implementation disabled and replaced with an external code. All related code kept as reference and will be removed eventually.
        // $this->addMapping('ced', 'ComExtDocumentos'); // For module 'Comercio Exterior', this module implementation disabled and replaced with an external code. All related code kept as reference and will be removed eventually.
        // $this->addMapping('cee', 'ComExtExpediente'); // For module 'Comercio Exterior', this module implementation disabled and replaced with an external code. All related code kept as reference and will be removed eventually.
        // $this->addMapping('psu', 'PagoSoloUuid'); // Table meant for payments related to specific Invoice Document with UUID. Module planning was cancelled. At some time in the future a depuration process will be required to determine if this table is kept or removed, along with others in the same situation.
        
        // ====================================================================
        // OTHER TABLES
        // ====================================================================
        $this->addMapping('art', 'Articulos'); // OK. Not used in codebase yet
        $this->addMapping('cal', 'Calendar'); // OK. Special class for module 'Citas' (Deals with three tables), not released yet
        $this->addMapping('cmf', 'CargaMF'); // OK. Not fully implemented yet for module 'Carga Masiva de Facturas'
        $this->addMapping('doc', 'Doctos'); // OK. I need to verify if this is still being used, if this still have some potential, or if it can be removed. This is related to directory 'tareas', and php files in that directory are meant to be automatic and customized tasks, but this has not been fully implemented.
        // $this->addMapping('cat', 'catalogoSAT'); // Special Class, do not inherit from DBObject. Manipulates all tables starting with 'cat' belonging to the SAT catalog.
    }
    
    /**
     * Add a single mapping entry
     * 
     * @param string $abbreviation Short code for the DAO (e.g., 'usr')
     * @param string|array $classOrConfig Class name or config array
     * @return void
     * 
     * @example
     *     addMapping('usr', 'Usuarios');
     *     addMapping('usr', ['class' => 'Usuarios', 'file' => 'path/to/Usuarios.php']);
     */
    private function addMapping($abbreviation, $classOrConfig) {
        // Get the base path - use document root if available, otherwise use parent directory of this file
        $basePath = $_SERVER['DOCUMENT_ROOT'] ?? dirname(__DIR__);
        
        if (is_string($classOrConfig)) {
            $this->mapping[$abbreviation] = [
                'class' => $classOrConfig,
                'file' => $basePath . DIRECTORY_SEPARATOR . "clases" . DIRECTORY_SEPARATOR . "$classOrConfig.php"
            ];
        } else if (is_array($classOrConfig)) {
            // Validate required keys
            if (!isset($classOrConfig['class'])) {
                throw new DAOException("Mapping for '$abbreviation' missing 'class' key");
            }
            // Auto-derive file if not provided
            if (!isset($classOrConfig['file'])) {
                $classOrConfig['file'] = $basePath . DIRECTORY_SEPARATOR . "clases" . DIRECTORY_SEPARATOR . "{$classOrConfig['class']}.php";
            }
            $this->mapping[$abbreviation] = $classOrConfig;
        } else {
            throw new DAOException("Invalid mapping config for abbreviation: $abbreviation");
        }
    }
    
    // ========================================================================
    // PUBLIC STATIC API
    // ========================================================================
    
    /**
     * Get the singleton instance of DAOFactory
     * 
     * @return DAOFactory The singleton instance
     */
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new DAOFactory();
        }
        return self::$instance;
    }
    
    /**
     * Get a DAO object by abbreviation
     * 
     * Returns cached instance if exists, otherwise instantiates new object.
     * Automatically includes the required class file.
     * 
     * @param string $abbreviation The DAO abbreviation (e.g., 'usr', 'prv')
     * @return object The DAO object instance
     * 
     * @throws DAONotFoundException If abbreviation is unknown
     * @throws DAOException If class cannot be instantiated
     * 
     * @example
     *     $usrObj = DAOFactory::get('usr');
     *     $userData = $usrObj->getData("id=1", 1);
     */
    public static function get($abbreviation) {
        return self::getInstance()->getDAOInstance($abbreviation);
    }
    
    /**
     * Check if a DAO abbreviation is registered
     * 
     * @param string $abbreviation The DAO abbreviation to check
     * @return bool True if registered, false otherwise
     * 
     * @example
     *     if (DAOFactory::exists('usr')) {
     *         $usrObj = DAOFactory::get('usr');
     *     }
     */
    public static function exists($abbreviation) {
        return isset(self::getInstance()->mapping[$abbreviation]);
    }
    
    /**
     * Get list of all registered DAO abbreviations
     * 
     * @return array List of abbreviations
     * 
     * @example
     *     $available = DAOFactory::getAvailable();
     *     // ['usr', 'prv', 'per', ...]
     */
    public static function getAvailable() {
        return array_keys(self::getInstance()->mapping);
    }
    
    /**
     * Get the mapping table (for debugging/documentation)
     * 
     * @return array The complete mapping configuration
     * 
     * @example
     *     $mapping = DAOFactory::getMapping();
     *     foreach ($mapping as $abbr => $config) {
     *         echo "$abbr => {$config['class']}";
     *     }
     */
    public static function getMapping() {
        return self::getInstance()->mapping;
    }
    
    /**
     * Register all or specific DAOs as global variables (for compatibility)
     * 
     * Optional method for backward compatibility. Registers DAO objects as globals
     * so they can be accessed without DAOFactory::get() in existing code.
     * 
     * Names follow pattern: {abbreviation}Obj
     * Example: 'usr' becomes $usrObj, 'prv' becomes $prvObj
     * 
     * @param array|null $abbreviations Specific abbreviations to register, or null for all
     * @return void
     * 
     * @example
     *     // Register specific DAOs
     *     DAOFactory::registerGlobals(['usr', 'prv', 'per']);
     *     global $usrObj, $prvObj, $perObj;
     *     // Now $usrObj, $prvObj, $perObj are available
     *     
     *     // Register all DAOs
     *     DAOFactory::registerGlobals();
     *     // All {abbr}Obj variables now available
     */
    public static function registerGlobals($abbreviations = null) {
        $factory = self::getInstance();
        $toRegister = $abbreviations ?? array_keys($factory->mapping);
        
        foreach ($toRegister as $abbreviation) {
            if (!$factory->exists($abbreviation)) {
                throw new DAONotFoundException("Cannot register unknown DAO: $abbreviation");
            }
            
            // Create global variable name: {abbreviation}Obj
            $globalName = $abbreviation . 'Obj';
            
            // Get the DAO instance and register as global
            $GLOBALS[$globalName] = $factory->getDAOInstance($abbreviation);
        }
    }
    
    /**
     * Clear all cached DAO objects
     * 
     * Use with caution - clears the entire cache. Useful for testing.
     * Next call to get() will create fresh instances.
     * 
     * @return void
     * 
     * @example
     *     DAOFactory::clear();
     *     $usrObj = DAOFactory::get('usr'); // Fresh instance
     */
    public static function clear() {
        self::getInstance()->objects = array();
    }
    
    // ========================================================================
    // PRIVATE INSTANCE METHODS
    // ========================================================================
    
    /**
     * Get or create a DAO instance (internal method)
     * 
     * @param string $abbreviation The DAO abbreviation
     * @return object The DAO object instance
     * 
     * @throws DAONotFoundException If abbreviation unknown
     * @throws DAOException If instantiation fails
     * @access private
     */
    private function getDAOInstance($abbreviation) {
        // Return from cache if exists
        if (isset($this->objects[$abbreviation])) {
            return $this->objects[$abbreviation];
        }
        
        // Validate abbreviation is registered
        if (!isset($this->mapping[$abbreviation])) {
            $available = implode(', ', array_keys($this->mapping));
            throw new DAONotFoundException(
                "Unknown DAO abbreviation: '$abbreviation'. "
                . "Available abbreviations: $available"
            );
        }
        
        $config = $this->mapping[$abbreviation];
        $className = $config['class'];
        $filePath = $config['file'];
        
        // Load the class file
        if (!class_exists($className, false)) {
            if (!file_exists($filePath)) {
                throw new DAOException(
                    "DAO class file not found: $filePath (class: $className from '".__DIR__."')"
                );
            }
            require_once $filePath;
        }
        
        // Verify class exists after loading
        if (!class_exists($className)) {
            throw new DAOException(
                "DAO class not found: $className (file: $filePath from '".__DIR__."')"
            );
        }
        
        // Instantiate the class
        try {
            $instance = new $className();
        } catch (Exception $e) {
            throw new DAOException(
                "Failed to instantiate DAO class '$className' from '".__DIR__."': " . $e->getMessage()
            );
        }
        
        // Cache the instance
        $this->objects[$abbreviation] = $instance;
        
        return $instance;
    }
}

// ============================================================================
// CONVENIENCE FUNCTION
// ============================================================================

/**
 * Convenience function for getting DAO objects
 * 
 * Alternative to DAOFactory::get() for shorter syntax
 * 
 * @param string $abbreviation The DAO abbreviation
 * @return object The DAO object instance
 * 
 * @example
 *     $empObj = dao("emp", ["rows_per_page"=>$_POST["regPerPage"]??100, "pageno"=>$_POST["pageSwitch"]??1, "orderlist"=>["alias"=>"asc"]]);
 *     $gpoObj = dao("gpo");
 *     $gpoObj = dao("gpo", ["rows_per_page"=>0, "orderlist"=>["alias"=>"asc"], "fullmap"=>[]]);
 *     $logObj = dao('log');
 *     $prvObj = dao('prv');
 *     $prvObj = dao('prv', ["rows_per_page"=>0, "orderlist"=>["alias"=>"asc"]]);
 *     $prcObj = dao('prc');
 *     $solObj = dao('sol', ["rows_per_page"=>0]);
 *     $ugObj = dao('ug');
 *     $upObj = dao('up');
 *     $usrObj = dao('usr');
 */
function dao($abbreviation, $initValues = null) {
    $dao = DAOFactory::get($abbreviation);
    if (is_array($initValues)) {
        $dao->backupValues(array_keys($initValues));
        foreach ($initValues as $key => $value) {
            if (property_exists($dao, $key)) $dao->$key = $value;
        }
    }
    return $dao;
}

// \$...?Obj|dao\([^()]+\)
// ============================================================================
// OPTIONAL: Auto-register common DAOs at bootstrap
// ============================================================================

/**
 * Optional: Call this in your bootstrap/configuration to auto-register
 * the most commonly used DAOs as global variables for backward compatibility
 * 
 * Uncomment in bootstrap.php if desired:
 *     DAOFactory::registerGlobals(['usr', 'up', 'per', 'prv', 'prc']);
 * 
 * Then these are available globally:
 *     global $usrObj, $upObj, $perObj, $prvObj, $prcObj;
 */

// === END DAOFactory ===
