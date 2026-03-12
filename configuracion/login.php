<?php
/**
 * Login Session Management and Authentication Script
 * 
 * This script handles complete user session lifecycle:
 * - Browser compatibility validation
 * - User logout processing
 * - New user login authentication (with system admin override capability)
 * - User profile and permissions assignment
 * - Welcome messages and notifications
 * - Redirection to home or login form display
 * 
 * Key Features:
 * - Multi-level password authentication (user + system admin)
 * - Profile and permission assignment upon successful login
 * - Provider-specific data validation
 * - Password change requirement flagging
 * - Session fixation prevention
 * 
 * Dependencies:
 * - Usuarios.php (User management)
 * - Usuarios_Perfiles.php (User-Profile mapping)
 * - Perfiles.php (Profile definitions)
 * - Proveedores.php (Vendor/Provider data)
 * - Proceso.php (Session history tracking)
 * - loggedInCheck.php (Post-login validation)
 * 
 * Global Variables Used:
 * - $_SESSION: User session data storage
 * - $habilitado: System enabled flag
 * - $hasUser: User exists in session flag
 * - $userid, $username, $user: Current user data
 * - $_esProveedor, $_esCompras, $_esAdministrador, $_esSistemas: User role flags
 * 
 * @package InvoiceCheck
 * @version 2.0 (Refactored)
 */

// ============================================================================
// HELPER FUNCTIONS - Password Hashing & Validation
// ============================================================================

/**
 * Generates a PBKDF2-style hash using SHA-256 with 65536 iterations.
 * This function centralizes the password hashing logic to eliminate code duplication.
 * 
 * @param string $password The plaintext password to hash
 * @param string $salt The salt value for hashing
 * @return string The hashed password
 */
function hashPassword($password, $salt) {
    $hashed = hash('sha256', $password . $salt);
    for ($round = 0; $round < 65536; $round++) {
        $hashed = hash('sha256', $hashed . $salt);
    }
    return $hashed;
}

/**
 * Verifies a password against its hash using the provided salt.
 * 
 * @param string $password The plaintext password to verify
 * @param string $hash The stored hash to compare against
 * @param string $salt The salt used in hashing
 * @return bool True if password matches, false otherwise
 */
function verifyPassword($password, $hash, $salt) {
    $computed_hash = hashPassword($password, $salt);
    return hash_equals($computed_hash, $hash);
}

/**
 * Validates user credentials against primary user record and optionally against
 * a system admin account (unoComo feature).
 * 
 * @param object $user User object with password and seguro (salt) fields
 * @param string $password Password to verify
 * @return array ['success' => bool, 'isSystem' => bool, 'user' => object]
 *         success: Whether credentials were valid
 *         isSystem: True if logged in via system admin override
 *         user: Updated user object with flags set
 */
function validateUserCredentials($user, $password) {
    $result = [
        'success' => false,
        'isSystem' => false,
        'user' => $user
    ];

    // Handle initial login with no password set
    if (empty($user->password) && empty($password)) {
        $result['success'] = true;
        $user->cambiaClave = true;
        return $result;
    }

    // Verify against user's own password
    if (verifyPassword($password, $user->password, $user->seguro)) {
        $result['success'] = true;
        return $result;
    }

    // Attempt system admin override (unoComo = "login as" feature)
    if (!empty($user->unoComo)) {
        $usrObj = dao('usr');
        $systemAdminData = $usrObj->getData("id=" . $user->unoComo, 1, "password,seguro");
        if (isset($systemAdminData[0]["password"][0])) {
            $sysAdmin = $systemAdminData[0];
            if (verifyPassword($password, $sysAdmin["password"], $sysAdmin["seguro"])) {
                $result['success'] = true;
                $result['isSystem'] = true;
                $user->isSystem = true;
                $user->cambiaClave = false; // System admin cannot change target user's password
                // Clear the unoComo field to prevent future overrides
                $usrObj->saveRecord(["id" => $user->id, "unoComo" => null]);
                unset($user->unoComo);
                return $result;
            }
        }
    }

    return $result;
}

/**
 * Loads and assigns user profiles and permissions.
 * Retrieves linked profiles for a user and populates the user object with profile names.
 * 
 * @param object $user User object to populate with profiles
 * @param int $userId User ID
 * @return object Updated user object with 'perfiles' array property
 */
function assignUserProfiles($user, $userId) {
    $perfilIdList = dao('up')->getList("idUsuario", $userId, "idPerfil");
    if (empty($perfilIdList)) {
        return $user;
    }

    $perfilIds = explode("|", $perfilIdList);
    if (empty($perfilIds)) {
        return $user;
    }
    $perfilNameList = dao('per')->getList("id", $perfilIds, "nombre");
    if (!empty($perfilNameList)) {
        $user->perfiles = explode("|", $perfilNameList);
    }

    return $user;
}

/**
 * Processes provider-specific validation and data assignment.
 * Checks provider status and expiration of compliance opinion.
 * 
 * @param object $user User object to populate with provider data
 * @param string $providerCode Provider identifier code
 * @return array ['success' => bool, 'errorMessage' => string or null]
 */
function validateAndAssignProviderData($user, $providerCode) {
    $result = ['success' => false, 'errorMessage' => null];
    $prvObj = dao('prv');
    $providerData = $prvObj->getData(
        "codigo='$providerCode'",
        1,
        "id,codigo,razonSocial,rfc,zona,cuenta,edocta,credito,banco,rfcbanco,codigoFormaPago,status,verificado,opinion,cumplido,venceopinion, date(venceopinion)<date(now()) vencido"
    );

    if (!isset($providerData[0])) {
        $result['errorMessage'] = "<p class='fontRelevant margin20 centered'>El usuario y/o la clave no son correctos</p>";
        return $result;
    }

    $prvData = $providerData[0];

    // Check if provider account is disabled
    if ($prvData["status"] === "eliminado") {
        $result['errorMessage'] = "<p class='fontRelevant margin20 centered'>El usuario no está habilitado en el sistema</p>";
        return $result;
    }

    // Assign provider data to user object
    $user->proveedor = (object) $prvData;

    // Auto-expire compliance status if expiration date has passed
    if ($user->proveedor->cumplido > 0 && !empty($user->proveedor->vencido)) {
        $prvObj->updateRecord(["id" => $user->proveedor->id, "cumplido" => "-1"]);
        $user->proveedor->cumplido = "-1";
    }

    $result['success'] = true;
    return $result;
}

/**
 * Assigns initial notification messages based on user role.
 * Prioritizes business/purchasing messages over standard user messages.
 * 
 * @param object $user User object with role flags
 * @param bool $isAdmin Whether user is admin or system role
 * @return void (modifies $_SESSION['MENSAJE_NOTICIA'])
 */
function assignNotificationMessages($user, $isAdmin = false) {
    // Priority: Business/Admin messages > Standard user messages
    if (($isAdmin || $_SESSION['_esCompras'] ?? false) && isset($_SESSION['MENSAJE_INICIAL_COMPRAS'][0])) {
        $_SESSION['MENSAJE_NOTICIA'] = $_SESSION['MENSAJE_INICIAL_COMPRAS'];
    } else if (isset($_SESSION['MENSAJE_INICIAL'][0])) {
        $_SESSION['MENSAJE_NOTICIA'] = $_SESSION['MENSAJE_INICIAL'];
    }
}

/**
 * Generates new password hash with random salt for password change operations.
 * 
 * @param string $password New password to hash
 * @return array ['hash' => string, 'salt' => string]
 */
function generatePasswordHash($password) {
    $salt = dechex(mt_rand(0, 2147483647)) . dechex(mt_rand(0, 2147483647));
    $hash = hashPassword($password, $salt);
    return ['hash' => $hash, 'salt' => $salt];
}

// ============================================================================
// MAIN EXECUTION LOGIC
// ============================================================================

$submitted_username = "";

// ============================================================================
// INITIALIZATION
// ============================================================================

// if ($habilitado) {
//     $prcObj = dao('prc');
// }

// ============================================================================
// BROWSER VALIDATION
// ============================================================================

/**
 * BROWSER COMPATIBILITY CHECK
 * Currently only Chrome and Edge are supported.
 * This prevents potential rendering and security issues with unsupported browsers.
 */
if (!isValidBrowser()) {
    $errorTitle = "ERROR";
    $errorMessage = "<p class='margin20 centered'>Actualmente esta aplicación sólo es compatible con "
                  . "<b><a href='https://www.google.com/chrome/'>Chrome</a></b> y "
                  . "<b><a href='https://www.microsoft.com/es-es/edge'>Edge</a></b>.</p>"
                  . "<i><small>" . getBrowser("browser") . "</small></i>";
} 
// ============================================================================
// USER LOGOUT HANDLER
// ============================================================================
/**
 * LOGOUT PROCESSING
 * Handles user logout by:
 * - Recording session closure in process log
 * - Destroying session data
 * - Clearing authentication tokens
 * - Cleaning global user variables
 */
else if (isset($_REQUEST["logout"]) && $hasUser) {
    if ($habilitado) {
        dao('prc')->cambioSesion($userid, "Cierre", $username, "Logout: " . $user->persona);
    }
    sessionEnds();
    clearTokenName();
    $_SESSION = [];
    cleanUser();
    $resultTitle = "LOGOUT";
    $resultMessage = "<p class='margin20 centered'>Ha salido del sistema.</p>";
    $errorTitle = false;
} else if (isset($_POST["username"][0])) {
    // ========================================================================
    // NEW USER LOGIN AUTHENTICATION
    // ========================================================================
    /**
     * NEW LOGIN FLOW
     * 
     * This section processes:
     * 1. Username retrieval and sanitization
     * 2. Password validation (user password or system admin override)
     * 3. User object initialization
     * 4. Profile and permission assignment
     * 5. Session initialization with user data
     * 6. Provider-specific validation (if applicable)
     * 7. Redirection to home or error display
     * 
     * Exit conditions:
     * - Browser not supported: Error displayed
     * - System disabled: Error displayed
     * - Invalid credentials: Error logged and displayed
     * - Session hijacking detected: Session destroyed and error displayed
     * - Provider account disabled: Error displayed
     * - Valid login: Session created, user logged, redirect to home
     */
    $login_ok = false;
    $postUsername = htmlentities($_POST['username'], ENT_QUOTES, "UTF-8");
    
    // Early exit if system not enabled or username empty
    if (!$habilitado || empty($postUsername)) {
        $errorMessage = "<p class='fontRelevant margin20 centered'>El usuario y/o la clave no son correctos</p>";
        $submitted_username = $postUsername;
        unset($_POST["password"]);
    } else {
        // Initialize user retrieval
        $usrData = dao('usr')->getData("nombre='$postUsername'", 1);
        $postPassword = htmlentities($_POST["password"], ENT_QUOTES, "UTF-8");
        
        if ($usrData) {
            $user = (object) $usrData[0];
            $user->isSystem = false;
            
            // Validate credentials using centralized function
            $validation = validateUserCredentials($user, $postPassword);
            $login_ok = $validation['success'];
            
            // Clean sensitive data from user object
            unset($user->seguro);
            unset($user->password);
            unset($usrData);
        }
        
        unset($_POST["password"]);
        
        // ====================================================================
        // POST-AUTHENTICATION LOGIN SETUP
        // ====================================================================
        if ($login_ok && isset($user)) {
            $user->project_name = $_project_name;
            if (!isset($user->cambiaClave)) {
                $user->cambiaClave = false;
            }
            
            // Assign user profiles and permissions
            $user = assignUserProfiles($user, $user->id);
            
            // Detect and prevent session hijacking
            if (isset($_SESSION['user']) && $_SESSION['user']->id !== $user->id && !$user->isSystem) {
                session_destroy();
                $_SESSION = [];
            }
            
            // Store user in session
            $_SESSION['user'] = $user;
            $_SESSION['tmp'] = "loggedin2";
            setUser();
            
            // Log login attempt
            dao('prc')->cambioSesion($userid, "Inicio", $username, "Login: " . $user->persona);
            
            // Execute post-login validations
            include_once "configuracion/loggedInCheck.php";
            
            // ================================================================
            // PROVIDER-SPECIFIC PROCESSING
            // ================================================================
            /**
             * PROVIDER AUTHENTICATION
             * 
             * If user is a provider (vendor/supplier), performs additional:
             * 1. Provider record lookup using provider code (username)
             * 2. Status verification (checks if account is disabled)
             * 3. Compliance opinion expiration check
             * 4. Auto-expiration of compliance if past due
             * 
             * Error states:
             * - Provider record not found: Auth fails
             * - Provider account disabled (status='eliminado'): Auth fails
             * - All other states: Provider data assigned to user session
             */
            if ($_esProveedor) {
                // Validate and assign provider data
                $prvResult = validateAndAssignProviderData($user, $username);
                if (!$prvResult['success']) {
                    $errorMessage = $prvResult['errorMessage'];
                    $submitted_username = $postUsername;
                }
            }
            
            // Assign notification messages based on user role
            $isAdmin = $_esAdministrador || $_esSistemas;
            assignNotificationMessages($user, $isAdmin);
            
            // Redirect to home if no errors
            if (!isset($errorMessage[0])) {
                if (empty($rediurl)) {
                    $rediurl = "/" . $_project_name . "/";
                }
                header("Location: $rediurl");
                die("Redirecting to: $rediurl");
            }
        } else {
            // LOGIN FAILED
            $errorMessage = "<p class='fontRelevant margin20 centered'>El usuario y/o la clave no son correctos</p>";
            $submitted_username = $postUsername;
            doclog("SIN LOGIN", "error", ["post" => $_POST, "session" => $_SESSION, "postUsername" => $postUsername]);
            sessionEnds();
            clearTokenName();
            $_SESSION = [];
            cleanUser();
        }
    }
} else if ($hasUser && $habilitado) {
    // ========================================================================
    // SESSION MAINTENANCE - Logged In User Operations
    // ========================================================================
    /**
     * LOGGED-IN USER HANDLERS
     * 
     * This section handles operations for users already in an active session:
     * 1. Password change processing for users flagged with cambiaClave=true
     * 2. Provider status refresh for vendor users
     * 3. User state synchronization with database
     * 
     * Process Flow:
     * - Include loggedInCheck.php for session validation
     * - Check if password change is required and credentials were submitted
     * - Verify password fields match before hashing and saving
     * - Generate new salt and hash using centralized hashPassword function
     * - Update database with new credentials and toggle change-flag
     * - For providers, refresh compliance status from database
     * - For other users, check if password change flag needs updating
     * 
     * Error Handling:
     * - Mismatched passwords: Display validation error
     * - Database save failure: Rollback transaction and show error
     * - Success: Clear cambiaClave flag and show confirmation message
     */
    
    include_once "configuracion/loggedInCheck.php";
    
    // PASSWORD CHANGE PROCESSING
    if (!empty($user->cambiaClave) && isset($_POST["password"][0]) && isset($_POST["password2"][0])) {
        if ($_POST["password"] !== $_POST["password2"]) {
            $errorMessage = "<p>No coinciden los campos de clave y confirmación</p>";
        } else {
            $postPassword = htmlentities($_POST["password"], ENT_QUOTES, "UTF-8");
            
            // Generate new password hash with random salt
            $passwordData = generatePasswordHash($postPassword);
            
            DBi::autocommit(FALSE);
            $fldarr = [
                "id" => $userid,
                "password" => $passwordData['hash'],
                "seguro" => $passwordData['salt'],
                "banderas" => ($user->banderas ^ 1)
            ];
            
            if (dao('usr')->saveRecord($fldarr)) {
                dao('prc')->cambioUsuario($userid, "Clave", $username, "Cambia Clave");
                $user->cambiaClave = false;
                $user->banderas = $user->banderas ^ 1;
                DBi::commit();
                $resultMessage = "<p>Su contraseña se ha actualizado.</p>";
            } else {
                DBi::rollback();
                $errorMessage = "<p>Error al guardar usuario " . $username . ".</p>";
            }
            DBi::autocommit(TRUE);
        }
    } 
    // PROVIDER STATUS REFRESH
    else if ($_esProveedor) {
        $prvData = dao('prv')->getData("codigo='" . $username . "'", 1, "status");
        if (isset($prvData[0])) {
            $user->proveedor->status = $prvData[0]["status"];
        }
    } 
    // USER STATE SYNCHRONIZATION
    else if ($GLOBALS["_doDB"]) {
        $usrData = dao('usr')->getData("id=" . $userid, 1, "banderas");
        if (isset($usrData[0])) {
            $user->cambiaClave = (($usrData[0]["banderas"] & 1) > 0);
        }
    }
    
    unset($_POST["password"]);
    unset($_POST["password2"]);
}