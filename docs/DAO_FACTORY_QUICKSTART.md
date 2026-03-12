# DAOFactory - Quick Start Guide (5 minutes)

Get up and running with the DAOFactory pattern in 5 minutes.

## Installation

### Step 1: Copy the file
```
clases/DAOFactory.php (already provided)
```

### Step 2: Add to bootstrap.php

Open `bootstrap.php` and add:

```php
require_once "clases/DAOFactory.php";
```

That's it! You're ready to use it.

---

## Basic Usage

### Replace This Old Pattern...

```php
global $usrObj;
if (!isset($usrObj)) {
    require_once "clases/Usuarios.php";
    $usrObj = new Usuarios();
}
```

### ...With This Single Line

```php
$usrObj = DAOFactory::get('usr');
```

---

## Common Objects

Most commonly used DAO abbreviations:

```php
$usrObj = DAOFactory::get('usr');  // Usuarios
$perObj = DAOFactory::get('per');  // Perfiles
$prvObj = DAOFactory::get('prv');  // Proveedores
$upObj = DAOFactory::get('up');    // Usuarios_Perfiles
$prcObj = DAOFactory::get('prc');  // Proceso
```

---

## Real Code Examples

### Example 1: Simple Usage

```php
// Get the object
$usrObj = DAOFactory::get('usr');

// Use it
$userData = $usrObj->getData("id=1", 1);
```

### Example 2: Multiple Objects

```php
// Get several objects
$usrObj = DAOFactory::get('usr');
$perObj = DAOFactory::get('per');
$prvObj = DAOFactory::get('prv');

// Use them
$user = $usrObj->getData("id=$userId", 1);
$profile = $perObj->getData("id=$profileId", 1);
$provider = $prvObj->getData("codigo='$code'", 1);
```

### Example 3: Inside Functions

```php
function getUserData($userId) {
    // No global declaration needed!
    $usrObj = DAOFactory::get('usr');
    return $usrObj->getData("id=$userId", 1);
}
```

### Example 4: Chain Calls

```php
// Very clean syntax!
$userData = DAOFactory::get('usr')->getData("id=1", 1);
$profileId = DAOFactory::get('per')->getValue("nombre", "Admin", "id");
```

---

## Available Abbreviations

### Complete Quick Reference

**User Management:**
- `usr` → Usuarios
- `up` → Usuarios_Perfiles
- `per` → Perfiles

**Providers:**
- `prv` → Proveedores
- `pvg` → ProveedorGrupo
- `pvt` → ProveedorTipos

**Process:**
- `prc` → Proceso
- `ev` → Eventos
- `tsk` → Tareas

**Documents:**
- `fac` → Facturas
- `doc` → Doctos
- `art` → Articulos

**Financial:**
- `pag` → Pagos
- `ban` → Bancos
- `cta` → Cuentas

**See `clases/DAOFactory.php` for complete list of 40+ objects**

---

## Error Handling

### What Happens if You Use Wrong Abbreviation?

```php
$obj = DAOFactory::get('xyz');  // Wrong abbreviation!
```

You get a helpful error message:
```
Unknown DAO abbreviation: 'xyz'. 
Available abbreviations: usr, up, per, prv, prc, ...
```

### Safe Usage Pattern

```php
try {
    $obj = DAOFactory::get($abbreviation);
} catch (DAONotFoundException $e) {
    echo "Error: " . $e->getMessage();
}
```

---

## Backward Compatibility (Optional)

If you want to keep using global declarations while migrating:

### Register Common Objects as Globals

In `bootstrap.php`, add:

```php
// Register commonly used DAOs as globals
DAOFactory::registerGlobals(['usr', 'prv', 'per', 'prc']);

// Now these work:
global $usrObj, $prvObj, $perObj, $prcObj;

// But also the new way still works:
$usrObj = DAOFactory::get('usr');  // This also works
```

---

## Before & After: Real Example

### Before (Old Way)

```php
<?php
// In configuracion/login.php

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

// 32 lines of boilerplate code!
```

### After (New Way)

```php
<?php
// In configuracion/login.php

$usrObj = DAOFactory::get('usr');
$upObj = DAOFactory::get('up');
$perObj = DAOFactory::get('per');
$prvObj = DAOFactory::get('prv');

// 4 lines! 87.5% reduction!
```

---

## Testing Your Implementation

### Test 1: Does factory work?

```php
<?php
require_once "clases/DAOFactory.php";

$usrObj = DAOFactory::get('usr');
echo ($usrObj instanceof Usuarios) ? "Success!" : "Failed!";
```

### Test 2: Is caching working?

```php
<?php
require_once "clases/DAOFactory.php";

$obj1 = DAOFactory::get('usr');
$obj2 = DAOFactory::get('usr');
echo ($obj1 === $obj2) ? "Cached correctly!" : "Not cached";
```

### Test 3: Does error handling work?

```php
<?php
require_once "clases/DAOFactory.php";

try {
    DAOFactory::get('unknown');
    echo "ERROR: Should have thrown exception!";
} catch (DAONotFoundException $e) {
    echo "Good! Got expected error: " . $e->getMessage();
}
```

---

## Common Tasks

### Get List of All Available DAOs

```php
$all = DAOFactory::getAvailable();
print_r($all);
// [usr, up, per, prv, prc, ...]
```

### Check if DAO Exists

```php
if (DAOFactory::exists('usr')) {
    $obj = DAOFactory::get('usr');
}
```

### Clear Cache (for testing)

```php
DAOFactory::clear();
$usrObj = DAOFactory::get('usr');  // Fresh instance
```

---

## Tips & Tricks

### Use Shorter Syntax

```php
// Instead of DAOFactory::get()
// You can use the convenience function
$usr = dao('usr');
$prv = dao('prv');
```

### Inline Usage

```php
// Perfect for one-off operations
$userData = dao('usr')->getData("id=1", 1);
```

### Multiple Objects

```php
// Create helper function for frequently used combinations
function getCommonDAOs() {
    return [
        'usr' => DAOFactory::get('usr'),
        'prv' => DAOFactory::get('prv'),
        'per' => DAOFactory::get('per'),
    ];
}

// Use it
['usr' => $usrObj, 'prv' => $prvObj] = getCommonDAOs();
```

---

## Common Pitfalls & Solutions

### ❌ Wrong: Don't instantiate directly anymore
```php
$usrObj = new Usuarios();  // DON'T DO THIS!
```

### ✅ Right: Use the factory
```php
$usrObj = DAOFactory::get('usr');  // DO THIS!
```

---

### ❌ Wrong: Don't pass DAOs as arguments
```php
function processUser($usrObj, $userId) {  // DON'T
    return $usrObj->getData(...);
}
```

### ✅ Right: Get them inside the function
```php
function processUser($userId) {  // DO THIS!
    return DAOFactory::get('usr')->getData(...);
}
```

---

### ❌ Wrong: Don't repeat the pattern
```php
global $usrObj;
if (!isset($usrObj)) { ... }
// STOP doing this!
```

### ✅ Right: Use the factory
```php
$usrObj = DAOFactory::get('usr');  // Just one line!
```

---

## Next Steps

1. ✅ Add DAOFactory.php to clases/
2. ✅ Add require to bootstrap.php
3. ✅ Try it in one file (login.php recommended)
4. ✅ Test thoroughly
5. ✅ Gradually refactor other files
6. ✅ Read detailed docs for advanced usage

---

## Quick Reference Card

Print this and keep handy:

```
DAOFactory Quick Reference
═════════════════════════════════════════

Get Object:
    $obj = DAOFactory::get('abbr');
    
Convenience Function:
    $obj = dao('abbr');

Check Exists:
    if (DAOFactory::exists('abbr')) { ... }

List All:
    $list = DAOFactory::getAvailable();

Get Mapping:
    $map = DAOFactory::getMapping();

Common Abbreviations:
    usr=Usuarios  per=Perfiles  prv=Proveedores
    prc=Proceso   up=Usuarios_Perfiles  ...

Error Handling:
    try {
        $obj = DAOFactory::get('abbr');
    } catch (DAONotFoundException $e) {
        // Handle error
    }
```

---

## Need Help?

- **How to use?** → Read this file (you're reading it!)
- **Detailed examples?** → DAO_FACTORY_IMPLEMENTATION.md
- **Real refactoring?** → DAO_FACTORY_LOGIN_EXAMPLE.md
- **Deep dive?** → DAO_FACTORY_ANALYSIS.md
- **Source code?** → clases/DAOFactory.php

---

**That's it! You now know 95% of what you need. The rest is just practice.** 🎉

**Start with your bootstrap.php and login.php!**
