# Fix Database Connection - Step by Step Guide

## Current Issue

The template variables `${db.HOSTNAME}` are not being resolved, which means the database component is not properly linked to your app.

## Solution: Link Database Component OR Set Variables Manually

You have two options:

---

## Option 1: Fix Database Component Linking (Recommended)

### Step 1: Check if Database Component Exists

1. Go to **Digital Ocean App Platform Dashboard**
2. Select your app: **dmarsians-taekwondo**
3. Look at the **Components** section
4. Check if you see a **Database** component named `db`

### Step 2A: If Database Component EXISTS

If the database component exists but template variables aren't resolving:

1. Go to your app's **Settings** tab
2. Click on **Components** or **App Spec**
3. Verify the database component is listed
4. If it's not linked, you may need to:
   - **Redeploy the app** - Sometimes App Platform needs a redeploy to link components
   - **Edit app.yaml** - Ensure the database component name matches exactly (`db`)

### Step 2B: If Database Component DOES NOT EXIST

You need to create it:

1. In App Platform dashboard, go to your app
2. Click **Settings** → **Components**
3. Click **Add Component** → **Database**
4. Configure:
   - **Name:** `db` (must match `app.yaml`)
   - **Engine:** MySQL
   - **Version:** 8
   - **Plan:** Basic (smallest plan to start)
5. Click **Add Database**
6. Wait for database to be created (5-10 minutes)
7. **Redeploy your app** to link the database

### Step 3: Verify Template Variables Are Resolved

After linking/creating the database:

1. Go to **Settings** → **App-Level Environment Variables**
2. Check if these variables are automatically populated:
   - `DB_HOST` (should show actual hostname, not `${db.HOSTNAME}`)
   - `DB_USER` (should show actual username)
   - `DB_PASS` (should show actual password)
   - `DB_NAME` (should show actual database name)
   - `DB_PORT` (should show actual port number)

If they're still showing `${db.*}`, the component isn't linked properly.

---

## Option 2: Set Environment Variables Manually (Quick Fix)

If the database component linking isn't working, you can set the variables manually:

### Step 1: Get Database Connection Details

**If you have a database component:**
1. Go to your database component in App Platform
2. Click on it to view **Connection Details**
3. Note down:
   - **Hostname** (e.g., `db-mysql-nyc1-12345.db.ondigitalocean.com`)
   - **Username** (e.g., `doadmin`)
   - **Password** (click to reveal)
   - **Database name** (e.g., `defaultdb`)
   - **Port** (usually `25060` for App Platform)

**If you don't have a database component:**
You need to create one first (see Option 1, Step 2B).

### Step 2: Set Environment Variables in Dashboard

1. Go to **App Platform** → Your App → **Settings** → **App-Level Environment Variables**
2. Click **Edit**
3. **Remove** the existing database variables (if they show `${db.*}`)
4. **Add** these variables with actual values:

   | Key | Value | Scope |
   |-----|-------|-------|
   | `DB_HOST` | `your-actual-hostname.db.ondigitalocean.com` | RUN_TIME |
   | `DB_USER` | `your-actual-username` | RUN_TIME |
   | `DB_PASS` | `your-actual-password` | RUN_TIME |
   | `DB_NAME` | `your-actual-database-name` | RUN_TIME |
   | `DB_PORT` | `25060` (or your actual port) | RUN_TIME |

5. Click **Save**

### Step 3: Update app.yaml (Optional)

If you're setting variables manually, you can simplify `app.yaml` by removing the database template variables:

```yaml
envs:
- key: APP_ENV
  value: production
  scope: RUN_TIME
# Remove DB_* variables - they'll be set from dashboard
```

Or keep them but they'll be overridden by dashboard values.

### Step 4: Redeploy

1. After setting variables, trigger a new deployment:
   - Push a commit to your repository, OR
   - Go to **Deployments** tab and click **Create Deployment**

---

## Option 3: Use External Database (If You Have One)

If you have a database outside of App Platform:

1. Go to **Settings** → **App-Level Environment Variables**
2. Set the variables with your external database details:
   - `DB_HOST` = your external database hostname
   - `DB_USER` = your external database username
   - `DB_PASS` = your external database password
   - `DB_NAME` = your database name
   - `DB_PORT` = your database port (usually `3306`)

---

## Verification Steps

After fixing the configuration:

1. **Check Logs:**
   - Go to **Runtime Logs** in App Platform
   - Look for database connection messages
   - Should NOT see `${db.HOSTNAME}` anymore
   - Should see actual hostname or connection success/failure

2. **Test Connection:**
   - Try logging in to your app
   - Check if you get past the database connection error
   - If connection succeeds, you should see login working

3. **Verify Variables:**
   - Go to **Settings** → **App-Level Environment Variables**
   - Variables should show actual values, not template variables

---

## Common Issues

### Issue: Database Component Exists But Variables Not Resolved

**Solution:**
- Redeploy the app after creating/linking database
- Check that database component name in `app.yaml` matches exactly (`db`)
- Verify database component is in "Running" state

### Issue: Can't See Database Component

**Solution:**
- Database component might be in a different app
- Check all your apps in App Platform
- Create a new database component if needed

### Issue: Variables Still Show `${db.*}` After Setting Manually

**Solution:**
- Make sure you're editing **App-Level** environment variables, not component-level
- Remove the old `${db.*}` entries first, then add new ones
- Redeploy after changing variables

---

## Next Steps After Fixing

1. **Import Database Schema:**
   - Connect to your database using the connection details
   - Import `Database/db.sql` file
   - Verify tables are created

2. **Test Application:**
   - Try logging in
   - Check all database-dependent features
   - Monitor logs for any connection issues

3. **Monitor:**
   - Keep an eye on runtime logs
   - Check database component health
   - Verify connection stability

---

## Quick Reference

**Where to find database connection details:**
- App Platform → Your App → Database Component → Connection Details

**Where to set environment variables:**
- App Platform → Your App → Settings → App-Level Environment Variables

**How to trigger redeploy:**
- Push to your Git repository (if auto-deploy enabled)
- OR: Deployments tab → Create Deployment

