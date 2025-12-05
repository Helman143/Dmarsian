# Admin Login Issue - Diagnosis & Solutions

## Issues Found

After scanning your codebase, I've identified several potential reasons why admin login might be failing:

### 1. **Silent Error Handling**
   - The original `login_process.php` didn't provide detailed error logging
   - Login failures were redirected with generic error messages
   - No way to see what was actually happening during login attempts

### 2. **Possible Root Causes**

   **a) Empty admin_accounts Table**
   - The `admin_accounts` table might be empty in your database
   - Even though `Database/db.sql` shows sample data, it may not have been imported
   - The SQL file shows one admin account:
     - Username: `Mr.Mars`
     - Email: `mars@gmail.com`
     - Password: Hashed (bcrypt)

   **b) Password Verification Issues**
   - The code uses `password_verify()` for hashed passwords
   - If the password in the database is not properly hashed, verification will fail
   - The code now supports both hashed and plain text passwords

   **c) Database Connection Issues**
   - Connection might be failing silently
   - Check your `config.php` and `db_connect.php` settings

   **d) Username/Email Mismatch**
   - The query searches for exact matches (case-sensitive)
   - Ensure you're using the exact username or email from the database

## What I've Fixed

### 1. Enhanced Error Logging in `login_process.php`
   - Added detailed logging for each login attempt
   - Logs when account is found/not found
   - Logs password verification results
   - Logs whether password is hashed or plain text
   - Better exception handling with stack traces

### 2. Improved Error Messages
   - Updated `admin_login.php` to show more specific error messages
   - Different messages for different error types

### 3. Created Diagnostic Tool
   - New file: `diagnose_admin_login.php`
   - Run this in your browser to check:
     - Database connection status
     - Whether `admin_accounts` table exists
     - How many admin accounts are in the database
     - Test login queries with specific usernames
     - PHP password function availability
     - Session configuration
     - File permissions

## How to Diagnose Your Issue

### Step 1: Run the Diagnostic Tool
1. Open your browser and navigate to: `http://localhost/Dmarsian/diagnose_admin_login.php`
2. Review all test results
3. Pay special attention to:
   - **Test 3**: Check if you have any admin accounts
   - **Test 4**: Test your login query with your username/email

### Step 2: Check PHP Error Logs
1. Check your PHP error log (usually in `C:\xampp\php\logs\php_error_log` or Apache error log)
2. Look for entries starting with "Admin login attempt" or "Admin login error"
3. These will tell you exactly what's happening during login

### Step 3: Verify Admin Account Exists
Run this SQL query in phpMyAdmin or MySQL:
```sql
SELECT id, username, email, 
    CASE 
        WHEN password LIKE '$2y$%' OR password LIKE '$2a$%' OR password LIKE '$2b$%' THEN 'Hashed'
        ELSE 'Plain Text'
    END as password_type
FROM admin_accounts;
```

### Step 4: Create Admin Account (if needed)
If the `admin_accounts` table is empty, you can create an account:

**Option A: Using admin_profile.php**
- Navigate to `admin_profile.php` (if accessible)
- Create a new admin account through the interface

**Option B: Using SQL**
```sql
INSERT INTO admin_accounts (email, username, password) 
VALUES ('admin@example.com', 'admin', '$2y$10$YourHashedPasswordHere');
```

To generate a hashed password, you can use this PHP code:
```php
<?php
echo password_hash('your_password_here', PASSWORD_DEFAULT);
?>
```

## Common Solutions

### Solution 1: Database Not Imported
If `admin_accounts` table is empty:
- Import `Database/db.sql` into your database
- Or manually create an admin account using the SQL above

### Solution 2: Wrong Credentials
Based on the SQL file, try:
- **Username**: `Mr.Mars`
- **Email**: `mars@gmail.com`
- **Password**: You'll need to know the original password that was hashed, or reset it

### Solution 3: Password Hash Mismatch
If the password in the database is not properly hashed:
- The code now supports both hashed and plain text
- But for security, you should hash passwords using `password_hash()`

### Solution 4: Case Sensitivity
- MySQL usernames/emails are case-sensitive on some systems
- Ensure you're typing the username/email exactly as stored

## Testing the Fix

1. **Check Error Logs**: After attempting to log in, check your PHP error log for detailed information
2. **Use Diagnostic Tool**: Run `diagnose_admin_login.php` to verify everything is set up correctly
3. **Test Login**: Try logging in with the credentials from your database

## Next Steps

1. Run `diagnose_admin_login.php` to identify the specific issue
2. Check your PHP error logs for detailed login attempt information
3. Verify you have at least one account in `admin_accounts` table
4. If needed, create a new admin account
5. Try logging in again and check the error logs for detailed feedback

## Security Note

The enhanced logging includes sensitive information. For production:
- Remove or reduce detailed logging
- Don't log passwords (even hashed ones)
- Consider using a proper logging framework
- Ensure error logs are not publicly accessible

