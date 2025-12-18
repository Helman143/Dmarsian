# Template Variable Resolution Fix

## Problem

The error showed that Digital Ocean App Platform template variables like `${db.HOSTNAME}` were not being resolved and were being used literally, causing:

```
Warning: mysqli::real_connect(): php_network_getaddresses: getaddrinfo for ${db.HOSTNAME} failed
```

## Root Cause

The template variables in `app.yaml` (like `${db.HOSTNAME}`) should be automatically resolved by Digital Ocean App Platform when the database component is linked to your app. If they're not resolved, it means:

1. **Database component is not linked** to your app service
2. **Database component doesn't exist** or isn't running
3. **app.yaml configuration issue** - the database reference might be incorrect

## Fix Applied

Added validation in `config.php` to:
1. **Detect unresolved template variables** - Check if environment variables start with `${`
2. **Prevent connection attempts** - Don't try to connect if variables are unresolved
3. **Log clear error messages** - Help identify the configuration issue
4. **Set safe defaults** - Prevent undefined constant errors

## What You Need to Do in Digital Ocean

### Step 1: Verify Database Component Exists

1. Go to Digital Ocean App Platform Dashboard
2. Select your app: `dmarsians-taekwondo`
3. Check if you see a **Database** component in the Components list
4. If it doesn't exist, you need to create it

### Step 2: Link Database Component to Your App

The database component must be **linked** to your web service for template variables to resolve.

**Option A: If database component already exists but isn't linked:**

1. Go to your app's **Settings** → **Components**
2. Find your database component
3. Ensure it's linked to your web service
4. If not linked, you may need to recreate the app with the database component

**Option B: If database component doesn't exist:**

You need to add it to your `app.yaml`. The current `app.yaml` references a database component named `db`, but it might not exist.

### Step 3: Verify app.yaml Configuration

Your `app.yaml` should have:

```yaml
databases:
- name: db
  engine: MYSQL
  version: "8"
  production: false
```

And the environment variables should reference it:

```yaml
envs:
- key: DB_HOST
  value: ${db.HOSTNAME}
  scope: RUN_TIME
```

### Step 4: Alternative Solution - Use Direct Environment Variables

If the template variable approach isn't working, you can set environment variables directly in the App Platform dashboard:

1. Go to App Platform → Your App → **Settings** → **App-Level Environment Variables**
2. Click **Edit**
3. Add these variables manually:
   - `DB_HOST` = (get from database component connection details)
   - `DB_USER` = (get from database component connection details)
   - `DB_PASS` = (get from database component connection details)
   - `DB_NAME` = (get from database component connection details)
   - `DB_PORT` = (get from database component connection details)

**To get database connection details:**
1. Go to your database component in App Platform
2. Click on it to view connection details
3. Copy the hostname, username, password, database name, and port

### Step 5: Update app.yaml (If Using Direct Variables)

If you set variables directly in the dashboard, you can simplify `app.yaml`:

```yaml
envs:
- key: DB_HOST
  value: ""  # Will be set from dashboard
  scope: RUN_TIME
- key: DB_USER
  value: ""
  scope: RUN_TIME
# etc...
```

Or remove the database references from envs and let them be set only from the dashboard.

## Expected Behavior After Fix

### Before Fix:
- Fatal error when trying to connect
- Script crashes with unresolved template variable error

### After Fix:
- Script detects unresolved template variables
- Logs clear error messages explaining the issue
- Prevents connection attempt (no fatal error)
- Returns gracefully with `$conn = false`
- Other parts of the app can check `$conn` and handle the error

## Verification

After deploying the fix:

1. **Check the logs** in App Platform Runtime Logs
2. Look for messages like:
   - `"ERROR: DB_HOST contains unresolved template variable: ${db.HOSTNAME}"`
   - `"CRITICAL: Database environment variables not resolved"`
3. If you see these, follow the steps above to fix the database component linking
4. If variables are resolved, you should see normal connection attempts (success or failure with actual hostname)

## Files Modified

- `config.php` - Added template variable detection and validation

## Next Steps

1. **Commit and push** the changes
2. **Check Digital Ocean App Platform** to verify database component is linked
3. **Review logs** after deployment to see if variables are resolved
4. **Fix database component linking** if variables are still unresolved
5. **Test login** functionality after database connection is established

