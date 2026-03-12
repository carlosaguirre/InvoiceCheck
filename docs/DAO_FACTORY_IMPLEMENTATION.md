# DAOFactory - Implementation & Usage Guide

## Quick Start

### 1. Include the Factory

Add to your bootstrap.php or main configuration file:

```php
require_once "clases/DAOFactory.php";
```

### 2. Use It Everywhere

Replace this:
```php
global $usrObj;
if (!isset($usrObj)) {
    require_once "clases/Usuarios.php";
    $usrObj = new Usuarios();
}
```

With this:
```php
$usrObj = DAOFactory::get('usr');
```

### 3. That's It!

The factory handles:
- ✅ Creating the instance
- ✅ Caching for reuse
- ✅ Loading the file
- ✅ Error handling

---

## Usage Examples

### Basic Single Object

```php
// Get a DAO object
$usrObj = DAOFactory::get('usr');

// Use it
$userData = $usrObj->getData("id=1", 1);
$user = (object)$userData[0];
```

### Multiple Objects

```php
// Get several objects - very clean!
$usrObj = DAOFactory::get('usr');
$perObj = DAOFactory::get('per');
$prvObj = DAOFactory::get('prv');

// Use them
$userData = $usrObj->getData(...);
$profiles = $perObj->getData(...);
$providers = $prvObj->getData(...);
```

### Using the Convenience Function

```php
// Even shorter!
$usrObj = dao('usr');
$perObj = dao('per');
$prvObj = dao('prv');
```

### Inside Functions

**Before:**
```php
function getUserData($userId) {
    global $usrObj;
    if (!isset($usrObj)) {
        require_once "clases/Usuarios.php";
        $usrObj = new Usuarios();
    }
    return $usrObj->getData("id=" . $userId, 1);
}
```

**After:**
```php
function getUserData($userId) {
    return DAOFactory::get('usr')->getData("id=" . $userId, 1);
}
```

### Checking if DAO Exists

```php
if (DAOFactory::exists('usr')) {
    $usrObj = DAOFactory::get('usr');
}
```

### Getting All Available DAOs

```php
$available = DAOFactory::getAvailable();
// Returns: ['usr', 'up', 'per', 'prv', 'prc', ...]

foreach ($available as $abbr) {
    echo "Available: $abbr\n";
}
```

### Getting the Mapping Table

```php
$mapping = DAOFactory::getMapping();
// Returns: ['usr' => ['class' => 'Usuarios', 'file' => '...'], ...]

foreach ($mapping as $abbr => $config) {
    echo "$abbr => {$config['class']}\n";
}
```

---

## Backward Compatibility

### Option 1: Gradual Migration (Recommended)

Keep using globals in bootstrap, but simplify initialization:

```php
// In bootstrap.php
DAOFactory::registerGlobals(['usr', 'up', 'per', 'prv', 'prc']);

// Then in your code, you can use either:
// OLD WAY (still works)
global $usrObj;
$userData = $usrObj->getData(...);

// NEW WAY (cleaner)
$usrObj = DAOFactory::get('usr');
$userData = $usrObj->getData(...);

// Or even shorter
$userData = dao('usr')->getData(...);
```

### Option 2: Full Replacement

Replace all instances of the old pattern with `DAOFactory::get()` calls.

Recommended approach:
1. Add DAOFactory to bootstrap
2. Start using `DAOFactory::get()` in new code
3. Gradually refactor old code
4. Once complete, remove global registration

---

## Error Handling

### Unknown Abbreviation

```php
try {
    $obj = DAOFactory::get('xyz');
} catch (DAONotFoundException $e) {
    // Catch specific not-found error
    echo "Error: " . $e->getMessage();
    // Output: "Unknown DAO abbreviation: 'xyz'. Available abbreviations: usr, up, per, ..."
}
```

### File Not Found

```php
try {
    $obj = DAOFactory::get('usr');
} catch (DAOException $e) {
    // Catch any DAO-related error
    echo "Error: " . $e->getMessage();
    // Output: "DAO class file not found: clases/Usuarios.php (class: Usuarios)"
}
```

### Class Instantiation Error

```php
try {
    $obj = DAOFactory::get('usr');
} catch (DAOException $e) {
    // Catch instantiation errors
    echo "Error: " . $e->getMessage();
    // Output: "Failed to instantiate DAO class 'Usuarios': [details]"
}
```

---

## Advanced Usage

### Custom File Paths

If a class doesn't follow the standard naming convention:

```php
// In DAOFactory::initializeMapping()
$this->addMapping('custom', [
    'class' => 'SpecialClass',
    'file' => 'vendor/special/path/SpecialClass.php'
]);
```

### Adding New Mappings

At runtime (if needed):

```php
// Direct access to the factory
$factory = DAOFactory::getInstance();

// Add custom mapping
$factory->addMapping('myobj', 'MyCustomClass');

// Now you can use it
$obj = DAOFactory::get('myobj');
```

### Clearing Cache (for Testing)

```php
// Clear all cached instances
DAOFactory::clear();

// Next call to get() creates fresh instances
$usrObj1 = DAOFactory::get('usr');
$usrObj2 = DAOFactory::get('usr');
// $usrObj1 and $usrObj2 are the same instance (cached)

// After clear:
DAOFactory::clear();
$usrObj3 = DAOFactory::get('usr');
// $usrObj3 is a NEW instance
```

### Getting Multiple Objects at Once

```php
// Get a batch of objects
$abbreviations = ['usr', 'per', 'prv'];
$objects = [];

foreach ($abbreviations as $abbr) {
    $objects[$abbr] = DAOFactory::get($abbr);
}

// Or create a helper function:
function getDAOs(...$abbreviations) {
    $objects = [];
    foreach ($abbreviations as $abbr) {
        $objects[$abbr] = DAOFactory::get($abbr);
    }
    return $objects;
}

// Use it:
['usr' => $usrObj, 'per' => $perObj] = getDAOs('usr', 'per');
```

---

## Migration Examples

### Example 1: login.php Refactoring

**Current (30+ lines of initialization):**
```php
global $usrObj;
if (!isset($usrObj)) {
    require_once "clases/Usuarios.php";
    $usrObj = new Usuarios();
}

global $upObj;
if (!isset($upObj)) {
    require_once "clases/Usuarios_Perfiles.php";
    $upObj = new Usuarios_Perfiles();
}

global $perObj;
if (!isset($perObj)) {
    require_once "clases/Perfiles.php";
    $perObj = new Perfiles();
}

global $prvObj;
if (!isset($prvObj)) {
    require_once "clases/Proveedores.php";
    $prvObj = new Proveedores();
}

global $prcObj;
if (!isset($prcObj)) {
    require_once "clases/Proceso.php";
    $prcObj = new Proceso();
}
```

**Refactored (5 lines):**
```php
$usrObj = DAOFactory::get('usr');
$upObj = DAOFactory::get('up');
$perObj = DAOFactory::get('per');
$prvObj = DAOFactory::get('prv');
$prcObj = DAOFactory::get('prc');
```

**Or even shorter in functions:**
```php
function validateUserCredentials($user, $password) {
    $usrObj = DAOFactory::get('usr');
    // ... use $usrObj
}
```

### Example 2: Complex Usage Inside Methods

**Before:**
```php
class Usuarios extends DBObject {
    function getPerfiles($usuario = false) {
        require_once "clases/Usuarios_Perfiles.php";
        $upObj = new Usuarios_Perfiles();
        
        require_once "clases/Perfiles.php";
        $prfObj = new Perfiles();
        
        // ... rest of method
    }
}
```

**After:**
```php
class Usuarios extends DBObject {
    function getPerfiles($usuario = false) {
        $upObj = DAOFactory::get('up');
        $prfObj = DAOFactory::get('per');
        
        // ... rest of method (same)
    }
}
```

---

## Available DAO Abbreviations

### User & Permissions
- `usr` → Usuarios
- `up` → Usuarios_Perfiles
- `per` → Perfiles

### Providers
- `prv` → Proveedores
- `pvg` → ProveedorGrupo
- `pvt` → ProveedorTipos
- `pvtc` → ProveedorTipoCuentas

### Process & Tracking
- `prc` → Proceso
- `ev` → Eventos
- `tsk` → Tareas
- `log` → Logs

### Documents
- `fac` → Facturas
- `doc` → Doctos
- `art` → Articulos
- `con` → Conceptos

### Financial
- `pag` → Pagos
- `dpag` → DPagos
- `cpag` → CPagos
- `sp` → SolicitudPago
- `mep` → MetodosDePago
- `ban` → Bancos
- `cta` → Cuentas

### Configuration
- `cfg` → Config
- `cat` → catalogoSAT
- `grp` → Grupo
- `emp` → Empleados
- `srv` → Servicios

### Specialized
- `nom` → Nomina
- `oc` → OrdenesCompra
- `cf` → Contrafacturas
- `cr` → Contrarrecibos
- `cfdi` → CFDI
- `rap` → ReposicionArchivos
- `rcaja` → ReposicionCajaChica
- `rviat` → ReposicionViaticos

### Data Handlers
- `pdf` → PDF
- `pdfcr` → PDFCR
- `q` → QueryService
- `fr` → Firmas
- `ftp` → FTP
- `tok` → Tokens
- `hist` → Historial

---

## Best Practices

### ✅ DO

```php
// Good: Use DAOFactory::get() consistently
$usrObj = DAOFactory::get('usr');
$data = $usrObj->getData(...);

// Good: Use convenience function in short chains
$data = dao('usr')->getData(...);

// Good: Check existence if uncertain
if (DAOFactory::exists('custom')) {
    $obj = DAOFactory::get('custom');
}

// Good: Use in functions without global
function processUser($id) {
    return DAOFactory::get('usr')->getData("id=$id", 1);
}
```

### ❌ DON'T

```php
// Bad: Don't use old pattern anymore
global $usrObj;
if (!isset($usrObj)) {
    require_once "clases/Usuarios.php";
    $usrObj = new Usuarios();
}

// Bad: Don't instantiate directly (bypass factory)
$usrObj = new Usuarios();  // Wrong!

// Bad: Don't pass DAO objects as function arguments
function processUser($usrObj, $id) {  // Wrong!
    return $usrObj->getData("id=$id", 1);
}
// Instead: function processUser($id) { DAOFactory::get('usr')... }

// Bad: Don't assume object is always available
// (but with factory, you know it will be if abbr exists)
$usrObj->getData(...);  // Might not have $usrObj!
// Better: $usrObj = DAOFactory::get('usr');
```

---

## Testing & Debugging

### Debug: See All Available DAOs

```php
// In a debug script
$mapping = DAOFactory::getMapping();
echo "<pre>";
print_r($mapping);
echo "</pre>";
```

### Debug: Check Singleton

```php
// Verify you're getting the same instance
$usr1 = DAOFactory::get('usr');
$usr2 = DAOFactory::get('usr');
echo ($usr1 === $usr2) ? "Same instance (cached)" : "Different instances";
```

### Debug: Clear for Testing

```php
// If testing and need fresh instances
DAOFactory::clear();
$usrObj = DAOFactory::get('usr');  // Fresh instance
```

### Debug: Log Available Objects

```php
// See what's currently cached
$factory = DAOFactory::getInstance();
echo "Cached objects: ";
echo implode(", ", array_keys($factory->objects ?? []));
```

---

## FAQ

**Q: Do I still need `global` declarations?**
A: No! With DAOFactory, you don't need globals for DAO objects.

**Q: What about passing DAOs to functions?**
A: Don't! Functions should call `DAOFactory::get()` internally instead.

**Q: Can I add new DAOs at runtime?**
A: Yes, but add them to `initializeMapping()` for consistency.

**Q: Is it thread-safe?**
A: PHP is single-threaded per request, so yes for normal use.

**Q: What if I need a fresh instance?**
A: Call `DAOFactory::clear()` and then `DAOFactory::get()` again.

**Q: Can I override the mapping?**
A: Yes, extend DAOFactory and override `initializeMapping()`.

**Q: Performance impact?**
A: Minimal - factory overhead is negligible compared to database queries.

**Q: What about old code using globals?**
A: Use `DAOFactory::registerGlobals()` for backward compatibility during migration.

---

## Implementation Checklist

- [ ] Copy DAOFactory.php to clases/ directory
- [ ] Add require_once to bootstrap.php
- [ ] Test DAOFactory::get() with one abbreviation
- [ ] Test error handling (unknown abbreviation)
- [ ] Optional: Add registerGlobals() call to bootstrap
- [ ] Start using in new code
- [ ] Gradually refactor existing code
- [ ] Remove old pattern completely
- [ ] Update team coding standards
- [ ] Document in project wiki/guidelines

---

**You now have a clean, professional DAO factory pattern! 🎉**
