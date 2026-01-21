# Fix Authentication Error (Error 1045)

## Current Status

✅ **Firewall is working** - Connection reaches database  
✅ **Network connectivity is working**  
❌ **Authentication failed** - Wrong username/password

**Error:** Access denied for user 'doadmin'@'146.190.104.12' (using password: YES)  
**Error Code:** 1045

## What This Means

The connection is reaching your database, but the username/password combination is incorrect or the user doesn't have permission.

## ✅ SOLUTION

### Step 1: Verify Database Credentials

1. Go to **Digital Ocean Dashboard** → **Databases**
2. Select your MySQL database
3. Go to the **"Users"** tab (or **"Connection Details"**)
4. Find the user you're trying to connect with (likely `doadmin`)
5. **Verify:**
   - Username is correct
   - Password is correct (click to reveal if hidden)

### Step 2: Reset Password (If Needed)

If you're not sure about the password:

1. In the **Users** tab, find your user (`doadmin`)
2. Click **"Reset Password"** or **"Change Password"**
3. Set a new password
4. **Copy the new password** - you'll need it for Step 3

### Step 3: Update Environment Variables in App Platform

1. Go to **App Platform** → Your App → **Settings** → **App-Level Environment Variables**
2. Click **Edit**
3. **Update these variables:**

   | Variable | Action | Notes |
   |----------|--------|-------|
   | `DB_USER` | Update if wrong | Should match database username exactly |
   | `DB_PASS` | Update with correct password | Copy from database connection details |

4. **Important:** 
   - Make sure there are **no extra spaces** before/after the password
   - If password has special characters, make sure they're not being escaped incorrectly
   - Copy-paste the password directly from database settings

5. Click **Save**

### Step 4: Verify User Permissions

1. In your database **Users** tab
2. Check that the user has proper permissions
3. For App Platform connections, the user typically needs:
   - **ALL PRIVILEGES** on the database, OR
   - At minimum: SELECT, INSERT, UPDATE, DELETE, CREATE, DROP permissions

### Step 5: Test Connection

1. Wait 1-2 minutes after updating environment variables
2. Visit: `https://dmarsians-taekwondo-zkcmy.ondigitalocean.app/test_db_connection.php`
3. Check if connection succeeds
4. Review Runtime Logs for success message

## Common Issues

### Issue 1: Password Has Special Characters

**Symptoms:** Password looks correct but still fails

**Solution:**
- Some special characters might need escaping
- Try resetting password to one without special characters (temporarily for testing)
- Or ensure password is copied exactly without any modifications

### Issue 2: Extra Spaces in Password

**Symptoms:** Password copied with leading/trailing spaces

**Solution:**
- Copy password directly from database settings
- Don't add any spaces before or after
- Check environment variable value in App Platform dashboard

### Issue 3: Wrong Username

**Symptoms:** Using default username but database has different user

**Solution:**
- Check database Users tab for actual username
- Update `DB_USER` environment variable to match exactly
- Case-sensitive: `doadmin` vs `DOADMIN` matters

### Issue 4: User Doesn't Have Permissions

**Symptoms:** Authentication succeeds but queries fail

**Solution:**
- Check user permissions in database Users tab
- Grant necessary permissions:
  ```sql
  GRANT ALL PRIVILEGES ON your_database.* TO 'your_user'@'%';
  FLUSH PRIVILEGES;
  ```

## Quick Checklist

- [ ] Opened Digital Ocean → Databases → Your Database
- [ ] Checked Users tab for correct username
- [ ] Verified or reset password
- [ ] Copied username and password exactly
- [ ] Updated `DB_USER` in App Platform environment variables
- [ ] Updated `DB_PASS` in App Platform environment variables
- [ ] Saved environment variables
- [ ] Waited 1-2 minutes
- [ ] Tested connection using `test_db_connection.php`
- [ ] Verified connection success

## Expected Result

After fixing credentials:

✅ **Connection succeeds**  
✅ **Authentication successful**  
✅ **Login functionality works**  
✅ **Logs show "Database connection successful"**

## Still Having Issues?

1. **Double-check credentials** - Copy directly from database connection details
2. **Try resetting password** - Set a simple password temporarily to test
3. **Verify user exists** - Check database Users tab
4. **Check user permissions** - Ensure user has access to the database
5. **Review connection test page** - `test_db_connection.php` for detailed errors

---

**Most Important:** Ensure `DB_USER` and `DB_PASS` environment variables match exactly what's in your database Users settings!

