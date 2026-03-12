# Data Object Control - Analysis & Optimization Strategy

## Current Strategy Analysis

### How It Works Now

Your current approach uses:

```php
// Pattern repeated everywhere
global $usrObj;
if (!isset($usrObj)) {
    require_once "clases/Usuarios.php";
    $usrObj = new Usuarios();
}
```

**Key Characteristics:**
- ✅ Lazy initialization (objects created when needed)
- ✅ Global scope for reusability
- ✅ Single instance per table
- ✅ Naming convention: `{abbreviation}Obj` where abbreviation is 2-3 letters
- ❌ Repetitive code (20-30+ duplications across codebase)
- ❌ No centralized control
- ❌ Manual file inclusion required
- ❌ Error-prone (typos in requires, class names)
- ❌ Difficult to track all available objects

### Example Map
```
usrObj   → clases/Usuarios.php      → Usuarios
upObj    → clases/Usuarios_Perfiles.php → Usuarios_Perfiles
perObj   → clases/Perfiles.php      → Perfiles
prvObj   → clases/Proveedores.php   → Proveedores
prcObj   → clases/Proceso.php       → Proceso
```

### Goals to Maintain
1. ✅ Keep single link per table
2. ✅ Create only objects needed during interaction
3. ✅ Avoid passing objects as function arguments
4. ✅ Table configuration only set once
5. ✅ Global accessibility

---

## Proposed Solution: DAO Factory Pattern

### Architecture Overview

```
┌─────────────────────────────────────────────────────┐
│                  DAOFactory                         │
│  (Singleton - manages all data object instances)    │
├─────────────────────────────────────────────────────┤
│                                                     │
│  CONFIGURATION TABLE (MAP):                        │
│  ['usr' => 'Usuarios',                             │
│   'up'  => 'Usuarios_Perfiles',                    │
│   'per' => 'Perfiles',                             │
│   'prv' => 'Proveedores',                          │
│   'prc' => 'Proceso', ...]                         │
│                                                     │
│  INTERNAL CACHE:                                   │
│  [$abbreviation => $object_instance, ...]          │
│                                                     │
│  PUBLIC METHODS:                                   │
│  - get($abbreviation) → gets or creates instance  │
│  - getGlobal() → registers all instances globally  │
│                                                     │
└─────────────────────────────────────────────────────┘
```

### Pseudocode Flow

```
DAOFactory::get('usr')
    │
    ├─ Is 'usr' in cache? 
    │  ├─ YES → Return cached instance
    │  └─ NO → Continue
    │
    ├─ Look up 'usr' in mapping table
    │  ├─ Found 'Usuarios'? → Continue
    │  └─ NOT found? → Throw error
    │
    ├─ Instantiate Usuarios class
    │
    ├─ Store in cache['usr']
    │
    └─ Return instance
```

---

## Benefits of This Approach

### Code Reduction
**Before:**
```php
// Repeated 30+ times across codebase
global $usrObj;
if (!isset($usrObj)) {
    require_once "clases/Usuarios.php";
    $usrObj = new Usuarios();
}
// Code: 4 lines
```

**After:**
```php
$usrObj = DAOFactory::get('usr');
// Code: 1 line
// 75% reduction!
```

### Centralized Control
- All table mappings in one place
- Easy to add new objects
- Easy to see what objects exist
- No scattered file includes

### Error Prevention
- Typo in abbreviation → clear error
- Missing class file → automatic error
- Incorrect file path → automatic resolution

### Testability
- Easy to mock in tests
- Easy to swap implementations
- Centralized initialization logic

### Maintainability
- Single point of truth
- Easy to add class initialization logic
- Easy to add logging/debugging
- Easy to add metrics/monitoring

---

## Implementation Details

### 1. DAOFactory Class Structure

```php
class DAOFactory {
    // Singleton instance
    private static $instance = null;
    
    // Instance cache
    private $objects = array();
    
    // Table mapping
    private $mapping = array(
        'usr' => 'Usuarios',
        'up' => 'Usuarios_Perfiles',
        'per' => 'Perfiles',
        'prv' => 'Proveedores',
        'prc' => 'Proceso',
        // ... more mappings
    );
    
    // Get singleton instance
    public static function getInstance()
    
    // Get DAO object
    public function get($abbreviation)
    
    // Register as globals
    public function registerGlobals()
    
    // Check if abbreviation exists
    public function exists($abbreviation)
    
    // Get all available abbreviations
    public function getAvailable()
    
    // Clear all cached objects
    public function clear()
    
    // Get the mapping table
    public function getMapping()
}
```

### 2. Usage Pattern

**Simple Usage:**
```php
// Get a single object
$usrObj = DAOFactory::get('usr');
$usrObj->getData(...)

// Get multiple objects
$prvObj = DAOFactory::get('prv');
$perObj = DAOFactory::get('per');

// All using single statement pattern
```

**Initialization (if desired):**
```php
// Register all commonly used objects as globals at startup
// (Optional - for backward compatibility if needed)
DAOFactory::registerGlobals(['usr', 'per', 'prv']);
```

### 3. Error Handling

```php
// Graceful error messages
try {
    $unknownObj = DAOFactory::get('xyz');
} catch (DAOException $e) {
    // Error: "Unknown DAO abbreviation: 'xyz'. Available: usr, up, per, prv, prc..."
}
```

---

## Migration Path

### Phase 1: Add DAO Factory (No Breaking Changes)
1. Create DAOFactory class
2. Keep old pattern working
3. Gradually adopt new pattern

### Phase 2: Refactor High-Use Files
1. `configuracion/login.php` → 30+ line reduction
2. `clases/Usuarios.php` → Use DAO in internal methods
3. Other high-use files

### Phase 3: Full Adoption
1. Refactor all files
2. Remove old pattern entirely
3. Documentation update

### Phase 4: Advanced Features (Optional)
1. Add lazy loading
2. Add auto-configuration
3. Add logging/metrics
4. Add configuration file support

---

## Advantages Over Current Approach

| Aspect | Current | DAO Factory |
|--------|---------|-----------|
| Code repetition | 30+ duplicates | Single pattern |
| Lines per object | 4 lines | 1 line |
| Initialization | Manual | Automatic |
| Centralized control | ❌ No | ✅ Yes |
| Error handling | Poor | Excellent |
| Documentation | None | Built-in mapping |
| Testability | Difficult | Easy |
| Backward compatible | ✅ (current) | ✅ (can coexist) |
| Extensibility | Low | High |

---

## Comparison Examples

### Example 1: Basic Usage

**Current:**
```php
global $usrObj;
if (!isset($usrObj)) {
    require_once "clases/Usuarios.php";
    $usrObj = new Usuarios();
}
$userData = $usrObj->getData("id=1", 1);
```

**New:**
```php
$usrObj = DAOFactory::get('usr');
$userData = $usrObj->getData("id=1", 1);
```

**Reduction:** 3 lines → 0 lines of initialization

---

### Example 2: Multiple Objects

**Current:**
```php
global $usrObj, $perObj, $prvObj;

if (!isset($usrObj)) {
    require_once "clases/Usuarios.php";
    $usrObj = new Usuarios();
}

if (!isset($perObj)) {
    require_once "clases/Perfiles.php";
    $perObj = new Perfiles();
}

if (!isset($prvObj)) {
    require_once "clases/Proveedores.php";
    $prvObj = new Proveedores();
}

$user = $usrObj->getData(...);
$perfil = $perObj->getData(...);
$proveedor = $prvObj->getData(...);
```

**New:**
```php
$usrObj = DAOFactory::get('usr');
$perObj = DAOFactory::get('per');
$prvObj = DAOFactory::get('prv');

$user = $usrObj->getData(...);
$perfil = $perObj->getData(...);
$proveedor = $prvObj->getData(...);
```

**Reduction:** 18 lines → 3 lines

---

### Example 3: Inside Functions

**Current:**
```php
function processUser($userId) {
    global $usrObj;
    if (!isset($usrObj)) {
        require_once "clases/Usuarios.php";
        $usrObj = new Usuarios();
    }
    return $usrObj->getData("id=" . $userId, 1);
}
```

**New:**
```php
function processUser($userId) {
    $usrObj = DAOFactory::get('usr');
    return $usrObj->getData("id=" . $userId, 1);
}
```

**Reduction:** 7 lines → 3 lines

---

## Summary

This DAO Factory pattern:
- ✅ Maintains all your current goals
- ✅ Reduces code duplication 75%
- ✅ Centralizes control
- ✅ Improves error handling
- ✅ Easier to maintain and extend
- ✅ Fully backward compatible
- ✅ Can be adopted gradually
- ✅ Makes code more professional

**Next step:** Review the implementation files provided in the docs folder.
