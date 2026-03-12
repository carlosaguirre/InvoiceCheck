# Login.php - Code Examples & Usage Guide

## Helper Function Usage Examples

### 1. Password Hashing (hashPassword)

**Basic usage:**
```php
$salt = dechex(mt_rand(0, 2147483647)) . dechex(mt_rand(0, 2147483647));
$hashedPassword = hashPassword($userPassword, $salt);

// Store in database:
$userObj->saveRecord([
    'id' => $userId,
    'password' => $hashedPassword,
    'seguro' => $salt
]);
```

**Why it's better:**
- Before: Password hashing logic repeated 3 times
- After: Single, testable function
- Ensures consistent algorithm everywhere

---

### 2. Password Verification (verifyPassword)

**Basic usage:**
```php
// During login
$enteredPassword = htmlentities($_POST['password'], ENT_QUOTES, "UTF-8");
if (verifyPassword($enteredPassword, $storedHash, $storedSalt)) {
    // Password is correct
} else {
    // Password is incorrect
}
```

**Security benefit:**
- Uses `hash_equals()` for timing-safe comparison
- Prevents attackers from using timing measurements to guess passwords
- Example: Correct password might take 0.5ms to reject, 
  incorrect password only 0.1ms → attacker knows they're close

---

### 3. Validate User Credentials (validateUserCredentials)

**Example 1: Normal login**
```php
$user = (object) $userData[0];
$validation = validateUserCredentials($user, $enteredPassword, $usrObj);

if ($validation['success']) {
    $_SESSION['user'] = $user;
    $_SESSION['user']->isSystem = $validation['isSystem'];
}
```

**Example 2: System admin override**
```php
// User record has unoComo field pointing to admin user
// If admin password is entered, user can login as that user
$validation = validateUserCredentials($user, $enteredPassword, $usrObj);

if ($validation['success'] && $validation['isSystem']) {
    // Logged in with admin credentials
    // unoComo field was cleared to prevent reuse
}
```

**What it handles:**
- Empty password (first-time login)
- User's own password
- System admin override
- Proper flag setting
- Secure cleanup of sensitive data

---

### 4. Assign User Profiles (assignUserProfiles)

**Usage:**
```php
$user = (object) $userData[0];
$user = assignUserProfiles($user, $user->id);

// Now $user->perfiles contains array of profile names
// ['Administrador', 'Gestor de Facturas']
foreach ($user->perfiles as $profileName) {
    echo "User has profile: $profileName";
}
```

**What it does:**
1. Queries `Usuarios_Perfiles` for user's profiles
2. Retrieves profile names from `Perfiles` table
3. Populates user object with `perfiles` array

---

### 5. Validate Provider Data (validateAndAssignProviderData)

**Usage:**
```php
require_once "clases/Proveedores.php";
$prvObj = new Proveedores();

$result = validateAndAssignProviderData($user, $username, $prvObj);

if ($result['success']) {
    // User is valid provider
    // $user->proveedor now contains all provider data
    // Compliance status has been auto-expired if needed
} else {
    // Provider validation failed
    echo $result['errorMessage']; // Already formatted error
}
```

**What it checks:**
- Provider exists in database
- Provider status is not 'eliminado' (deleted)
- Compliance opinion expiration
- Auto-updates compliance if expired
- Retrieves all provider details (RFC, bank, account, etc.)

**Return structure:**
```php
[
    'success' => true,  // false if validation failed
    'errorMessage' => null  // Error message if failed, null if success
]
```

---

### 6. Assign Notification Messages (assignNotificationMessages)

**Usage:**
```php
$isAdmin = $_esAdministrador || $_esSistemas;
assignNotificationMessages($user, $isAdmin);

// Now $_SESSION['MENSAJE_NOTICIA'] is set with appropriate message
```

**Message priority:**
```
IF (is admin/purchasing user) AND has purchasing message:
    Use MENSAJE_INICIAL_COMPRAS
ELSE IF has standard message:
    Use MENSAJE_INICIAL
ELSE:
    Nothing assigned
```

**When to use:**
- After successful login
- After any role/permission change
- When displaying home page

---

### 7. Generate Password Hash (generatePasswordHash)

**Usage:**
```php
// When user changes password
$newPassword = htmlentities($_POST['password'], ENT_QUOTES, "UTF-8");
$passwordData = generatePasswordHash($newPassword);

// Use both hash and salt
$usrObj->saveRecord([
    'id' => $userId,
    'password' => $passwordData['hash'],
    'seguro' => $passwordData['salt']
]);
```

**Return value:**
```php
[
    'hash' => 'a3f2b9c1e7d4...',  // SHA-256 hash
    'salt' => 'f4a2c8d1b9e7...'   // Random salt
]
```

**Note:** 
- Generates new random salt each time
- Uses same 65,536-iteration PBKDF2 algorithm
- Don't reuse salt from old password

---

## Complete Login Flow Example

```php
// 1. Validate browser
if (!isValidBrowser()) {
    showError("Browser not supported");
    exit;
}

// 2. Validate username is provided
$postUsername = htmlentities($_POST['username'], ENT_QUOTES, "UTF-8");
if (empty($postUsername)) {
    showError("Username required");
    exit;
}

// 3. Load user from database
$usrObj = new Usuarios();
$usrData = $usrObj->getData("nombre='$postUsername'", 1);
if (!$usrData) {
    showError("Invalid credentials");
    exit;
}

// 4. Validate credentials
$user = (object) $usrData[0];
$user->isSystem = false;
$postPassword = htmlentities($_POST['password'], ENT_QUOTES, "UTF-8");

$validation = validateUserCredentials($user, $postPassword, $usrObj);
if (!$validation['success']) {
    showError("Invalid credentials");
    exit;
}

// 5. Clean sensitive data
unset($user->seguro);
unset($user->password);

// 6. Assign profiles
$user = assignUserProfiles($user, $user->id);

// 7. Create session
$_SESSION['user'] = $user;
setUser(); // Sets global $userid, $username, etc.

// 8. Provider validation (if applicable)
if (isProvider($user)) {
    $prvObj = new Proveedores();
    $result = validateAndAssignProviderData($user, $username, $prvObj);
    if (!$result['success']) {
        showError($result['errorMessage']);
        exit;
    }
}

// 9. Assign notifications
$isAdmin = isAdmin($user);
assignNotificationMessages($user, $isAdmin);

// 10. Redirect to home
header("Location: /home");
exit;
```

---

## Password Change Workflow

```php
// 1. User submits password change form
if (!empty($user->cambiaClave) && isset($_POST['password'][0])) {
    
    // 2. Validate passwords match
    if ($_POST['password'] !== $_POST['password2']) {
        showError("Passwords don't match");
        exit;
    }
    
    // 3. Sanitize new password
    $newPassword = htmlentities($_POST['password'], ENT_QUOTES, "UTF-8");
    
    // 4. Generate new hash
    $passwordData = generatePasswordHash($newPassword);
    
    // 5. Update database in transaction
    DBi::autocommit(FALSE);
    try {
        $usrObj->saveRecord([
            'id' => $userid,
            'password' => $passwordData['hash'],
            'seguro' => $passwordData['salt'],
            'banderas' => ($user->banderas ^ 1)  // Toggle flag
        ]);
        
        // 6. Update session
        $user->cambiaClave = false;
        $user->banderas = $user->banderas ^ 1;
        
        DBi::commit();
        showSuccess("Password updated");
    } catch (Exception $e) {
        DBi::rollback();
        showError("Failed to update password");
    }
    DBi::autocommit(TRUE);
}
```

---

## Error Handling Examples

**HTML error message format:**
```php
// This format is used throughout login.php
$errorMessage = "<p class='fontRelevant margin20 centered'>Message text here</p>";

// Display in template with safe echo
echo $errorMessage;
```

**Structured error returns:**
```php
// Provider validation returns structured errors
$result = validateAndAssignProviderData($user, $username, $prvObj);
if (!$result['success']) {
    // Error message is pre-formatted HTML
    echo $result['errorMessage'];
}
```

---

## Security Best Practices Applied

### ✅ Input Validation
```php
$postUsername = htmlentities($_POST['username'], ENT_QUOTES, "UTF-8");
$postPassword = htmlentities($_POST['password'], ENT_QUOTES, "UTF-8");
```

### ✅ Timing-Safe Comparison
```php
// Uses hash_equals() internally
if (verifyPassword($password, $hash, $salt)) { }
```

### ✅ Sensitive Data Cleanup
```php
unset($user->seguro);  // Remove salt
unset($user->password);  // Remove hash
```

### ✅ Session Fixation Prevention
```php
if (isset($_SESSION['user']) && $_SESSION['user']->id !== $user->id) {
    session_destroy();  // Prevent hijacking
}
```

### ✅ Transaction Management
```php
DBi::autocommit(FALSE);
try {
    // Multiple operations
    DBi::commit();
} catch (Exception $e) {
    DBi::rollback();
}
DBi::autocommit(TRUE);
```

---

## Testing These Functions

### Unit Test Example
```php
// Test hashPassword consistency
$salt = "test_salt_123";
$hash1 = hashPassword("myPassword", $salt);
$hash2 = hashPassword("myPassword", $salt);
assert($hash1 === $hash2, "Same password should produce same hash");

// Test verifyPassword
$isValid = verifyPassword("myPassword", $hash1, $salt);
assert($isValid === true, "Valid password should verify");

$isInvalid = verifyPassword("wrongPassword", $hash1, $salt);
assert($isInvalid === false, "Wrong password should not verify");
```

### Integration Test Example
```php
// Test complete login flow
$user = createTestUser("testuser", "password123");
$validation = validateUserCredentials($user, "password123", $usrObj);
assert($validation['success'] === true, "Valid credentials should succeed");

$user2 = assignUserProfiles($user, $user->id);
assert(isset($user2->perfiles), "User should have profiles assigned");
```

---

## Common Pitfalls to Avoid

❌ **Don't:** Try to compare hashes with `===` directly
```php
if ($hash1 === $hash2) { }  // Vulnerable to timing attacks
```

✅ **Do:** Use verifyPassword() function
```php
if (verifyPassword($password, $hash, $salt)) { }
```

---

❌ **Don't:** Leave salt and hash in user object after login
```php
$_SESSION['user']->password = $hash;  // Security risk
```

✅ **Do:** Unset sensitive data
```php
unset($user->password);
unset($user->seguro);
```

---

❌ **Don't:** Reuse salt for different passwords
```php
$newHash = hashPassword($newPassword, $oldSalt);  // Wrong!
```

✅ **Do:** Generate new salt each time
```php
$passwordData = generatePasswordHash($newPassword);
// Uses fresh random salt
```
