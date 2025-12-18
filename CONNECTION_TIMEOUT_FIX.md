# Database Connection Timeout Fix

## Current Error

```
Fatal error: Uncaught mysqli_sql_exception: Connection timed out in /workspace/config.php:69
```

## What This Means

✅ **Good News:** Your environment variables are now set correctly (no more `${db.HOSTNAME}` errors)

❌ **Problem:** The database connection is timing out, which means:
- App Platform cannot reach your database
- Database firewall is blocking App Platform IPs
- Network connectivity issue

## Root Cause

The most common cause is **database firewall/trusted sources** not allowing connections from App Platform.

## ✅ SOLUTION

### Step 1: Check Database Firewall Settings

1. Go to **Digital Ocean Dashboard** → **Databases**
2. Select your MySQL database
3. Go to **Settings** → **Trusted Sources** (or **Firewall**)
4. Look for **"Allow connections from App Platform"** option
5. **Enable it** if available

### Step 2: Add App Platform to Trusted Sources

If the option above doesn't exist:

1. In **Trusted Sources** section
2. Click **Add Trusted Source**
3. Select **"App Platform"** from the dropdown
4. OR add the App Platform IP ranges manually

**Alternative:** Some databases have a toggle for "Allow App Platform connections" - enable it.

### Step 3: Verify Connection Details

Make sure your environment variables are correct:

1. Go to **App Platform** → Your App → **Settings** → **App-Level Environment Variables**
2. Verify:
   - `DB_HOST` = Correct database hostname
   - `DB_PORT` = Correct port (usually `25060` for managed databases)
   - `DB_USER` = Correct username
   - `DB_PASS` = Correct password
   - `DB_NAME` = Correct database name

### Step 4: Test Connection

1. Visit: `https://dmarsians-taekwondo-zkcmy.ondigitalocean.app/test_db_connection.php`
2. This will show detailed connection diagnostics
3. Check the error message for specific guidance

### Step 5: Redeploy After Changes

After updating firewall settings:

1. Wait a few minutes for changes to propagate
2. Test connection again
3. If still failing, check database logs

## Common Issues and Solutions

### Issue 1: "Connection timed out" Error

**Cause:** Database firewall blocking App Platform

**Solution:**
- Enable "Allow connections from App Platform" in database settings
- Add App Platform to trusted sources
- Check database is in same region as app (if possible)

### Issue 2: Wrong Port

**Symptoms:** Connection timeout with correct hostname

**Solution:**
- Managed databases usually use port `25060` (not `3306`)
- Check your database connection details for correct port
- Update `DB_PORT` environment variable

### Issue 3: Database Not Running

**Symptoms:** Connection timeout, database appears offline

**Solution:**
- Check database status in Digital Ocean dashboard
- Ensure database is running and healthy
- Restart database if needed

### Issue 4: Network Connectivity

**Symptoms:** Intermittent timeouts, connection works sometimes

**Solution:**
- Check if database and app are in same region
- Verify network settings
- Check for any network restrictions

## Diagnostic Tools

### 1. Connection Test Page

Visit: `https://dmarsians-taekwondo-zkcmy.ondigitalocean.app/test_db_connection.php`

This page will:
- Show current environment variables
- Test database connection
- Provide specific error messages
- Give troubleshooting guidance

### 2. Environment Variables Check

Visit: `https://dmarsians-taekwondo-zkcmy.ondigitalocean.app/check_env.php`

This page shows:
- Which environment variables are set
- If template variables are still present
- Current configuration status

### 3. Runtime Logs

Check App Platform Runtime Logs for:
- Connection attempts
- Error messages
- Timeout details

## Step-by-Step Fix Checklist

- [ ] Go to Digital Ocean → Databases → Your Database
- [ ] Navigate to Settings → Trusted Sources
- [ ] Enable "Allow connections from App Platform" OR add App Platform to trusted sources
- [ ] Verify environment variables are correct in App Platform dashboard
- [ ] Wait 2-3 minutes for firewall changes to propagate
- [ ] Test connection using `test_db_connection.php`
- [ ] Check runtime logs for connection success
- [ ] Test login functionality

## Expected Result

After fixing firewall settings:

✅ Connection should succeed  
✅ No more timeout errors  
✅ Login functionality should work  
✅ Database queries should execute successfully

## Files Updated

- ✅ `config.php` - Added try-catch for connection errors, improved error logging
- ✅ `test_db_connection.php` - Created diagnostic tool for connection testing

## Still Having Issues?

1. **Check database logs** in Digital Ocean dashboard
2. **Verify database is running** and healthy
3. **Test connection** using `test_db_connection.php`
4. **Review error messages** in runtime logs for specific guidance
5. **Contact Digital Ocean support** if issue persists

---

**Most Important:** Enable "Allow connections from App Platform" in your database firewall settings!

