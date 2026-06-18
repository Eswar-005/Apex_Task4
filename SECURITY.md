# DevBlog Security Audit & Mitigation Report

This document serves as a professional-grade security audit and validation log for the **DevBlog** CRUD application. It outlines the security vulnerabilities assessed, the technical controls implemented, and the verification test cases proving the application's resilience against common OWASP Top 10 vulnerabilities.

---

## 1. Executive Summary

A comprehensive security audit of the DevBlog CRUD application was conducted to establish a baseline of defense-in-depth security. Major patches were introduced across three core domains:
1. **SQL Injection (SQLi) Prevention**: Enforcing parameterized prepared statements across all database-facing code.
2. **Input Validation & Data Integrity**: Implementing multi-tier (client-side and server-side) verification to guarantee input validity and clean data flow.
3. **Role-Based Access Control (RBAC)**: Restricting database operations and routing based on user permissions (`admin`, `editor`, `user`).
4. **Cross-Site Request Forgery (CSRF) & Session Security**: Protecting destructive forms using stateful token verification.

---

## 2. Vulnerability Assessment & Patches (Before vs. After)

### 2.1 SQL Injection (SQLi)
*   **Vulnerability Description**: Unsanitized user inputs from form fields and query variables were previously interpolated directly into raw SQL query strings, allowing malicious users to execute arbitrary SQL commands.
*   **Before (Vulnerable Example)**:
    ```php
    $pdo->query("SELECT * FROM posts WHERE id = " . $_GET['id']);
    // Vulnerable to direct variable interpolation via URL parameters
    ```
*   **After (Secured & Patched)**:
    - Disabled prepared statement emulation globally in database configurations (`PDO::ATTR_EMULATE_PREPARES => false`) to enforce server-side query parsing.
    - Converted all database execution calls across `index.php`, `login.php`, `register.php`, `create.php`, `edit.php`, and `delete.php` to parameterized PDO prepared statements.
    ```php
    $stmt = $pdo->prepare("SELECT * FROM posts WHERE id = ?");
    $stmt->execute([$post_id]);
    $post = $stmt->fetch();
    ```

### 2.2 Unvalidated Form Inputs
*   **Vulnerability Description**: Missing client and server-side checks permitted empty, excessively long, or invalid data types to enter the system, leading to database truncation or UI styling breakages.
*   **Before**: Inputs were accepted blindly without pattern constraints or length validation.
*   **After**:
    - **Client-Side**: Added strict HTML5 attributes (`required`, `minlength`, `maxlength`, and `pattern` regex validation) to fields.
    - **Real-Time Visual Indicators**: Implemented `.touched` CSS styles inside `style.css` so inputs glow green (valid) or red (invalid) dynamically once interacted with.
    - **Interactive Requirements Checklist**: Integrated a dynamic checklist in `register.php` that guides the user on username formats and strong password rules (minimum 8 characters, uppercase, lowercase, numbers, and special characters), hiding once validation is fully met.
    - **Server-Side Validation**: Created strict validation steps checking input formats, lengths, and complexity (regex checks on username/password) before processing any transaction.

### 2.3 Role-Based Access Control (RBAC) Bypass
*   **Vulnerability Description**: Any registered user could access post creation, modification, and deletion features by guessing routes or sending direct HTTP requests to `create.php`, `edit.php`, or `delete.php`.
*   **Before**: No role check existed, and all authenticated accounts shared a single flat set of permissions.
*   **After**:
    - Added a `role` ENUM column (`admin`, `editor`, `user`) to the database.
    - Implemented session role tracking on login: `$_SESSION['role']`.
    - **UI Conditional Rendering**: The "New Post", "Edit", and "Delete" buttons are only rendered when allowed by the user's role.
    - **Strict Backend Redirection**: Added route-guard interceptions at the head of every administrative file:
        ```php
        // Restrict edit access to admin or editor roles
        if (($_SESSION['role'] ?? 'user') === 'user') {
            set_flash_message('error', 'You do not have permission to edit posts.');
            header('Location: index.php');
            exit;
        }
        ```

---

## 3. Security Test Cases & Verification Logs

To prove the security of the implemented measures, a series of automated and manual penetration tests were executed on the system.

### Test Case 1: SQL Injection Attempt on Login Route
*   **Methodology**: Attempted bypass of the login authentication mechanism by inputting SQL injection payloads.
*   **Input Username**: `' OR '1'='1`
*   **Input Password**: `' OR 1=1 --`
*   **Expected Behavior**: The application must treat the injection payload as a raw literal string, search for a literal user named `\' OR \'1\'=\'1`, fail to find it, and securely reject the attempt with a generic error message, without exposing database errors.
*   **Observed Behavior**: The authentication system rejected the login securely. The webpage reloaded displaying the error: "Invalid username or password." No database syntax exceptions or crashes occurred.
*   **Proof (Attempt Failure Screenshot)**:
    ![SQL Injection Attempt Failure](C:/Users/Lalitha/.gemini/antigravity-ide/brain/1c724bb7-e461-46ce-92e9-df75208cd10a/sqli_attempt_failure_1781717388529.png)

---

### Test Case 2: Password Complexity Enforcement (Register Page)
*   **Methodology**: Tested the real-time registration verification checking by submitting a weak password and checking formatting helpers.
*   **Input Username**: `test` (Too short, length 4 required)
*   **Input Password**: `123` (Weak, minimum 8 characters, uppercase, lowercase, numbers, and special characters required)
*   **Expected Behavior**: Client-side Javascript must intercept inputs, show the warning checklists, and indicate which constraints are violated.
*   **Observed Behavior**: Both the username and password checklists expanded showing red indicator marks next to missing requirements.
*   **Proof (Requirements Warning Screenshot)**:
    ![Register Format Checklist Warnings](C:/Users/Lalitha/.gemini/antigravity-ide/brain/1c724bb7-e461-46ce-92e9-df75208cd10a/register_invalid_details_1781717142275.png)

---

### Test Case 3: Role-Based Access Control Interception
*   **Methodology**: Logged in as a standard user (`test_user`) and attempted to directly navigate to administrative pages (`create.php`, `edit.php`, `delete.php`) via the address bar.
*   **Expected Behavior**: The application backend must intercept the session, block access, store a warning flash message, and redirect the browser back to `index.php`.
*   **Observed Behavior**: The user was successfully redirected to `index.php` with a warning alert stating "You do not have permission to create/edit posts."
*   **Proof (RBAC Interception Alert Screenshot)**:
    ![Permission Denied Redirect](C:/Users/Lalitha/.gemini/antigravity-ide/brain/1c724bb7-e461-46ce-92e9-df75208cd10a/permission_denied_message_1781714958689.png)

---

## 4. Runnable SQLi Verification Script

To provide a programmatical proof of prepared statements security, a dedicated test script was built at [tests/sqli_proof.php](file:///c:/Users/Lalitha/apexPlanet_task4/tests/sqli_proof.php). It runs an SQL injection payload (`' OR '1'='1`) against both a vulnerable concatenated query simulation and a secure prepared query.

### Verified Console Run Output:
```text
=================================================================
           DEVBLOG SQL INJECTION (SQLi) DEMONSTRATION            
=================================================================

Simulated Attack Input Payload: ' OR '1'='1

--- DEMO 1: Vulnerable Direct Concatenation ---
Query: SELECT id, username, role FROM users WHERE username = '' OR '1'='1'
Status: 🔴 BREACHED!
Records Leaked from Database:
  - [ID: 1] Username: developer | Role: user
  - [ID: 2] Username: admin_user | Role: admin
  - [ID: 3] Username: editor_user | Role: editor
  - [ID: 4] Username: test_user | Role: user
  - [ID: 5] Username: user_123 | Role: user
  - [ID: 6] Username: strong_user | Role: user

-----------------------------------------------------------------

--- DEMO 2: Secured Prepared Statement (PDO) ---
Query: SELECT id, username, role FROM users WHERE username = ? (Param bound as string)
Status: 🟢 SECURE! No records matched. The database safely treated the payload as a literal string.

=================================================================
```

