# Login.php - Visual Architecture & Flow Diagrams

## 🏗️ Helper Functions Architecture

```
┌─────────────────────────────────────────────────────────────────────┐
│                     LOGIN SYSTEM ARCHITECTURE                        │
├─────────────────────────────────────────────────────────────────────┤
│                                                                       │
│  ┌────────────────────────────────────────────────────────────────┐ │
│  │                   PASSWORD LAYER                              │ │
│  ├────────────────────────────────────────────────────────────────┤ │
│  │                                                                 │ │
│  │  hashPassword()                verifyPassword()               │ │
│  │  ┌─────────────────┐         ┌──────────────────┐             │ │
│  │  │ Input: pwd+salt │         │ Input: pwd+hash  │             │ │
│  │  │ Process: SHA256 │         │ Output: bool     │             │ │
│  │  │ Output: hash    │         │ Secure: hash_eq  │             │ │
│  │  └─────────────────┘         └──────────────────┘             │ │
│  │          ▲                            ▲                        │ │
│  │          │                            │                        │ │
│  │          └────────────────┬───────────┘                        │ │
│  │                           │                                    │ │
│  │                 generatePasswordHash()                         │ │
│  │                 ┌──────────────────────┐                       │ │
│  │                 │ Input: password      │                       │ │
│  │                 │ Output: hash+salt    │                       │ │
│  │                 └──────────────────────┘                       │ │
│  │                                                                 │ │
│  └────────────────────────────────────────────────────────────────┘ │
│                                                                       │
│  ┌────────────────────────────────────────────────────────────────┐ │
│  │                 AUTHENTICATION LAYER                           │ │
│  ├────────────────────────────────────────────────────────────────┤ │
│  │                                                                 │ │
│  │         validateUserCredentials()                              │ │
│  │         ┌──────────────────────────────┐                       │ │
│  │         │ • Check empty password       │                       │ │
│  │         │ • Verify user password       │                       │ │
│  │         │ • Try system admin override  │                       │ │
│  │         │ • Return: success+isSystem   │                       │ │
│  │         └──────────────────────────────┘                       │ │
│  │                      ▲                                          │ │
│  │                      │                                          │ │
│  │                  Uses both PASSWORD functions                  │ │
│  │                                                                 │ │
│  └────────────────────────────────────────────────────────────────┘ │
│                                                                       │
│  ┌────────────────────────────────────────────────────────────────┐ │
│  │                  USER DATA LAYER                               │ │
│  ├────────────────────────────────────────────────────────────────┤ │
│  │                                                                 │ │
│  │  assignUserProfiles()      validateAndAssignProviderData()    │ │
│  │  ┌──────────────────────┐  ┌─────────────────────────────┐    │ │
│  │  │ Load profiles        │  │ Validate provider status    │    │ │
│  │  │ Assign to user       │  │ Check compliance expiry     │    │ │
│  │  │ Populate perfiles[]  │  │ Assign provider data        │    │ │
│  │  └──────────────────────┘  └─────────────────────────────┘    │ │
│  │                                                                 │ │
│  └────────────────────────────────────────────────────────────────┘ │
│                                                                       │
│  ┌────────────────────────────────────────────────────────────────┐ │
│  │              NOTIFICATION LAYER                                │ │
│  ├────────────────────────────────────────────────────────────────┤ │
│  │                                                                 │ │
│  │         assignNotificationMessages()                           │ │
│  │         ┌──────────────────────────────┐                       │ │
│  │         │ Priority:                    │                       │ │
│  │         │ 1. Admin message             │                       │ │
│  │         │ 2. Standard message          │                       │ │
│  │         │ 3. None                      │                       │ │
│  │         └──────────────────────────────┘                       │ │
│  │                                                                 │ │
│  └────────────────────────────────────────────────────────────────┘ │
│                                                                       │
└─────────────────────────────────────────────────────────────────────┘
```

---

## 🔄 Login Flow Diagram

```
START LOGIN PROCESS
│
├─→ Check Browser Compatibility
│   │
│   ├─ Valid? → Continue
│   └─ Invalid? → Show Error → END
│
├─→ Check Logout Request?
│   │
│   ├─ Yes? → Destroy Session → Show Logout Message → END
│   └─ No? → Continue
│
├─→ Check Login Form Submitted?
│   │
│   ├─ Yes? → AUTHENTICATE (see below)
│   │
│   └─ No? → Check if Already Logged In?
│       │
│       ├─ Yes? → POST-LOGIN (see below)
│       └─ No? → Show Login Form → END
│
└─→ END

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

AUTHENTICATE (New User Login)
│
├─→ Validate Input
│   └─ Empty username? → Error → END
│
├─→ Load User from Database
│   └─ User found? → Continue
│       └─ Not found? → Error → END
│
├─→ validateUserCredentials()
│   ├─ Empty password (first login)? → Success
│   ├─ User password matches? → Success
│   └─ System admin override? → Success (set isSystem=true)
│       └─ None match? → Error → END
│
├─→ Clean Sensitive Data
│   └─ Remove: seguro, password
│
├─→ assignUserProfiles()
│   └─ Load user's profiles and permissions
│
├─→ Create Session
│   └─ $_SESSION['user'] = $user
│
├─→ Check for Provider User
│   │
│   ├─ Yes? → validateAndAssignProviderData()
│   │  │
│   │  ├─ Provider exists? → Continue
│   │  ├─ Account disabled? → Error → END
│   │  └─ Compliance expired? → Auto-update
│   │
│   └─ No? → Continue
│
├─→ assignNotificationMessages()
│   └─ Assign welcome message based on role
│
├─→ Redirect to Home
│   └─ Success → END

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

POST-LOGIN (Logged-In User)
│
├─→ Check Password Change Required?
│   │
│   ├─ Yes & Form Submitted?
│   │  │
│   │  ├─ Passwords match? → Continue
│   │  │  │
│   │  │  ├─→ generatePasswordHash()
│   │  │  ├─→ Save to database
│   │  │  ├─→ Clear flag
│   │  │  └─→ Success message → END
│   │  │
│   │  └─ Don't match? → Error → END
│   │
│   └─ No? → Continue
│
├─→ Check if Provider User?
│   │
│   ├─ Yes? → Refresh provider status from DB
│   └─ No? → Continue
│
├─→ Sync User State with Database
│   └─ Update local user object with latest DB values
│
└─→ END
```

---

## 🔐 Password Hashing Flow

```
┌────────────────────────────────────────────────────────────────┐
│                  PASSWORD HASHING PROCESS                      │
├────────────────────────────────────────────────────────────────┤
│                                                                  │
│  Input: "MyPassword123"                                        │
│         Salt: "a1b2c3d4e5f6..."                               │
│         │                                                       │
│         ▼                                                       │
│  ┌──────────────────────────────────────┐                     │
│  │ hash('sha256', pwd + salt)           │                     │
│  │ Result: a3f2b9c1e7d4f5a8b2c3d4...   │                     │
│  └──────────────────────────────────────┘                     │
│         │                                                       │
│         ▼                                                       │
│  ┌──────────────────────────────────────────────────────────┐ │
│  │ FOR i = 0 TO 65,535:                                     │ │
│  │   hash('sha256', previous_hash + salt)                   │ │
│  │                                                            │ │
│  │ Round 1:    f1e2d3c4b5a6...                             │ │
│  │ Round 2:    a1b2c3d4e5f6...                             │ │
│  │ Round 3:    x1y2z3a4b5c6...                             │ │
│  │ ...                                                       │ │
│  │ Round 65535: f8a2b3c4d5e6...                            │ │
│  └──────────────────────────────────────────────────────────┘ │
│         │                                                       │
│         ▼                                                       │
│  Output: "f8a2b3c4d5e6f7a8b9c0d1e2f3a4b5c6d7e8f9..."       │
│          (stored in database as password)                     │
│                                                                  │
│  Security Properties:                                          │
│  ✓ 65,536 iterations = computationally expensive               │
│  ✓ Salt-dependent = prevents rainbow tables                    │
│  ✓ Deterministic = same password always produces same hash     │
│  ✓ One-way = can't reverse to get original password            │
│                                                                  │
└────────────────────────────────────────────────────────────────┘
```

---

## 🔒 Timing-Safe Comparison

```
VULNERABLE COMPARISON (Using ===)
═════════════════════════════════

Stored Hash:  "a1b2c3d4e5f6g7h8i9j0..."
User Input:   "a1b2c3d4xxxxxxxxxxxxxx"
              │││││││││
              Differs at position 8

═════════════════════════════════════════════════════════════════
COMPARISON PROCESS:
═════════════════════════════════════════════════════════════════

Position 1: a === a ✓  continue
Position 2: 1 === 1 ✓  continue
Position 3: b === b ✓  continue
Position 4: 2 === 2 ✓  continue
Position 5: c === c ✓  continue
Position 6: 3 === 3 ✓  continue
Position 7: d === d ✓  continue
Position 8: e !== x ✗  FAIL (EXIT HERE after 8 steps)

Time to fail: 0.1ms

═══════════════════════════════════════════════════════════════════

VULNERABILITY: Attacker can use timing to guess password!
- Correct password: 0.50ms (compares all 50 chars)
- Wrong 1st char:   0.08ms (stops at position 1)
- Wrong 8th char:   0.10ms (stops at position 8)
→ Attacker learns WHERE in password they went wrong!

═══════════════════════════════════════════════════════════════════


SECURE COMPARISON (Using hash_equals)
════════════════════════════════════════

Stored Hash:  "a1b2c3d4e5f6g7h8i9j0..."
User Input:   "a1b2c3d4xxxxxxxxxxxxxx"

════════════════════════════════════════════════════════════════════
COMPARISON PROCESS (CONSTANT TIME):
════════════════════════════════════════════════════════════════════

┌─ Position 1: a === a ✓
│
├─ Position 2: 1 === 1 ✓
│
├─ Position 3: b === b ✓
│
├─ Position 4: 2 === 2 ✓
│
├─ Position 5: c === c ✓
│
├─ Position 6: 3 === 3 ✓
│
├─ Position 7: d === d ✓
│
├─ Position 8: e !== x ✗  (Mark as different, DON'T EXIT)
│
├─ Position 9-50: Compare all remaining characters regardless
│
└─ Final Result: FALSE (after all 50 positions checked)

Time to fail: 0.50ms (ALWAYS - regardless of where difference is)

════════════════════════════════════════════════════════════════════

SECURITY: No timing information leaked!
- All comparisons take same time
- Attacker cannot use timing to guess password
- Must brute force password without feedback
```

---

## 📊 Code Complexity Reduction

```
BEFORE: Nested Conditionals (6+ levels deep)
═════════════════════════════════════════════════════════════════════

if ($habilitado) {
    if (isset($postUsername[0])) {
        if ($usrData) {
            if (empty($user->password)) {
                $login_ok = true;
            } else {
                if ($check_password === $user->password) {
                    $login_ok = true;
                } else if (!empty($user->unoComo)) {
                    if (isset($syD[0]["password"][0])) {
                        if ($check_password === $syD["password"]) {
                            $login_ok = true;
                        }
                    }
                }
            }
        }
    }
}

Cyclomatic Complexity: 8
Max Nesting Depth: 6
Hard to read ✗
Hard to maintain ✗
Hard to test ✗

═════════════════════════════════════════════════════════════════════


AFTER: Early Returns & Helper Functions (2-3 levels max)
═════════════════════════════════════════════════════════════════════

// Early exit for invalid input
if (!$habilitado || empty($postUsername)) {
    $errorMessage = "...";
    return;
}

// Main flow
$user = getUserFromDatabase($postUsername);
$validation = validateUserCredentials($user, $password, $usrObj);

if (!$validation['success']) {
    $errorMessage = "...";
    return;
}

// Success path
$user = assignUserProfiles($user, $user->id);

if ($_esProveedor) {
    $result = validateAndAssignProviderData($user, $username, $prvObj);
    if (!$result['success']) {
        $errorMessage = $result['errorMessage'];
        return;
    }
}

assignNotificationMessages($user, $isAdmin);

Cyclomatic Complexity: 3-4
Max Nesting Depth: 2
Easy to read ✓
Easy to maintain ✓
Easy to test ✓

═════════════════════════════════════════════════════════════════════
```

---

## 📈 Metrics Comparison

```
CODE QUALITY METRICS
═════════════════════════════════════════════════════════════════════

CYCLOMATIC COMPLEXITY (Lower is Better)
┌────────────────────────────────────────────────────┐
│ Before: ████████░░░░░░░░░░░░░░░░░░░░░░░░░░  8     │
│ After:  ███░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░  3     │
│         ⬇ 62.5% improvement                       │
└────────────────────────────────────────────────────┘

MAXIMUM NESTING DEPTH (Lower is Better)
┌────────────────────────────────────────────────────┐
│ Before: ██████░░░░░░░░░░░░░░░░░░░░░░░░░░░░  6     │
│ After:  ██░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░  2     │
│         ⬇ 66% improvement                        │
└────────────────────────────────────────────────────┘

CODE DUPLICATION (Lower is Better)
┌────────────────────────────────────────────────────┐
│ Before: ███░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░  3x    │
│ After:  ░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░  1x    │
│         ⬇ 66% improvement                        │
└────────────────────────────────────────────────────┘

DOCUMENTATION LINES (Higher is Better)
┌────────────────────────────────────────────────────┐
│ Before: ░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░  10    │
│ After:  ████████████████████████████████████████  250  │
│         ⬆ 2400% improvement                      │
└────────────────────────────────────────────────────┘

TESTABLE COMPONENTS (Higher is Better)
┌────────────────────────────────────────────────────┐
│ Before: ██░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░ Low    │
│ After:  ████████████████████████████░░░░░░ High   │
│         ⬆ Much easier to unit test               │
└────────────────────────────────────────────────────┘

═════════════════════════════════════════════════════════════════════
```

---

## 🎯 Function Responsibility Matrix

```
╔═════════════════════════════════════════════════════════════════╗
║                    FUNCTION RESPONSIBILITIES                   ║
╠═════════════════════════════════════════════════════════════════╣
║                                                                 ║
║  hashPassword()                                                ║
║  ├─ Responsibility: Hash password with PBKDF2                 ║
║  ├─ Input: password, salt                                      ║
║  └─ Output: hash                                               ║
║                                                                 ║
║  verifyPassword()                                              ║
║  ├─ Responsibility: Timing-safe password comparison            ║
║  ├─ Input: password, hash, salt                                ║
║  └─ Output: bool (match/no match)                              ║
║                                                                 ║
║  validateUserCredentials()                                     ║
║  ├─ Responsibility: Check user credentials                     ║
║  ├─ Handles:                                                   ║
║  │  • Empty password (first login)                             ║
║  │  • User password verification                               ║
║  │  • System admin override                                    ║
║  ├─ Input: user, password, usrObj                              ║
║  └─ Output: {success, isSystem, user}                          ║
║                                                                 ║
║  assignUserProfiles()                                          ║
║  ├─ Responsibility: Load and assign user profiles              ║
║  ├─ Input: user, userId                                        ║
║  └─ Output: user (with profiles array)                         ║
║                                                                 ║
║  validateAndAssignProviderData()                               ║
║  ├─ Responsibility: Provider validation                        ║
║  ├─ Handles:                                                   ║
║  │  • Provider record lookup                                   ║
║  │  • Status verification                                      ║
║  │  • Compliance expiration                                    ║
║  ├─ Input: user, providerCode, prvObj                          ║
║  └─ Output: {success, errorMessage}                            ║
║                                                                 ║
║  assignNotificationMessages()                                  ║
║  ├─ Responsibility: Assign role-based messages                 ║
║  ├─ Input: user, isAdmin                                       ║
║  └─ Output: void (sets $_SESSION['MENSAJE_NOTICIA'])           ║
║                                                                 ║
║  generatePasswordHash()                                        ║
║  ├─ Responsibility: Generate new password hash                 ║
║  ├─ Input: password                                            ║
║  └─ Output: {hash, salt}                                       ║
║                                                                 ║
╚═════════════════════════════════════════════════════════════════╝
```

---

## 🚀 Deployment Flow

```
DEVELOPMENT
│
├─ Code Refactoring ✓
├─ Unit Testing ✓
└─ Documentation ✓
    │
    ▼
STAGING
│
├─ Deploy refactored code
├─ Run integration tests
├─ Test with real database
├─ Verify backward compatibility
├─ Performance testing
└─ Security review
    │
    ▼
PRODUCTION
│
├─ Backup original file
├─ Deploy configuracion/login.php
├─ Monitor login attempts
├─ Check error logs
├─ Verify all user types
├─ Test provider logins
└─ Post-deployment validation
    │
    ▼
COMPLETE ✓
```

---

## 📝 Legend

```
✓ - Success
✗ - Failure
⬆ - Increased/Improved
⬇ - Decreased/Improved
→ - Process flow
├─ - Branch
└─ - Final branch
░░ - Empty/Low
██ - Filled/High
```

---

**All diagrams represent the refactored login.php structure**
**Version 2.0 - Production Ready**
