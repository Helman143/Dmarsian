# Fix Database Firewall - Step by Step

## Current Status

✅ **Environment variables are correct**  
✅ **Error handling is working**  
❌ **Database firewall is blocking App Platform**

**Error:** Connection timed out (Error Code 2002)  
**Database:** `db-mysql-sgp1-91028-do-user-29758924-0.m.db.ondigitalocean.com:25060`

## ✅ SOLUTION - Enable App Platform Access

### Step 1: Go to Your Database

1. Log in to **Digital Ocean Dashboard**
2. Click on **Databases** in the left sidebar
3. Find and click on your MySQL database (the one with hostname starting with `db-mysql-sgp1-91028`)

### Step 2: Open Settings

1. Click on the **Settings** tab (or look for **"Trusted Sources"** or **"Firewall"**)

### Step 3: Enable App Platform Access

You have **two options**:

#### Option A: Quick Toggle (If Available)

1. Look for a toggle or checkbox labeled:
   - **"Allow connections from App Platform"**
   - **"Enable App Platform access"**
   - **"App Platform connections"**
2. **Enable/Turn ON** this option
3. Click **Save** or **Apply**

#### Option B: Add to Trusted Sources

If the toggle doesn't exist:

1. Find the **"Trusted Sources"** section
2. Click **"Add Trusted Source"** or **"Add IP Address"**
3. Look for an option to add **"App Platform"** (it might be a dropdown)
4. Select **"App Platform"** from the list
5. Click **Add** or **Save**

### Step 4: Verify Changes

1. After saving, wait **1-2 minutes** for changes to propagate
2. The trusted sources list should show **"App Platform"** or similar

### Step 5: Test Connection

1. Visit: `https://dmarsians-taekwondo-zkcmy.ondigitalocean.app/test_db_connection.php`
2. Or try logging in to your app
3. Check Runtime Logs - should see "Database connection successful"

## Visual Guide

### Where to Find Settings:

```
Digital Ocean Dashboard
  └── Databases
      └── [Your Database Name]
          └── Settings Tab
              └── Trusted Sources / Firewall Section
                  └── [Enable App Platform Access]
```

### What to Look For:

- **Toggle/Switch:** "Allow connections from App Platform"
- **Button:** "Add Trusted Source" → Select "App Platform"
- **Checkbox:** "Enable App Platform connections"

## Alternative: Manual IP Address (If Needed)

If you can't find the App Platform option, you may need to:

1. Go to your **App Platform** app settings
2. Find your app's **outbound IP addresses** (if listed)
3. Add those IPs to your database's trusted sources

**However**, the "App Platform" option is much easier and recommended.

## Troubleshooting

### Issue: Can't Find "Trusted Sources" Section

**Solution:**
- Look for **"Firewall"**, **"Network"**, or **"Access Control"** tabs
- Some databases have it under **"Settings"** → **"Network"**

### Issue: Changes Not Taking Effect

**Solution:**
- Wait 2-3 minutes for changes to propagate
- Try restarting your app (not usually necessary)
- Verify the setting was saved correctly

### Issue: Still Getting Timeout After Enabling

**Solutions:**
1. Double-check the setting is enabled
2. Verify database region matches app region (if possible)
3. Check database is running and healthy
4. Verify environment variables are correct
5. Try the connection test page again

## Quick Checklist

- [ ] Opened Digital Ocean Dashboard
- [ ] Went to Databases → Your Database
- [ ] Opened Settings → Trusted Sources
- [ ] Enabled "Allow connections from App Platform"
- [ ] Saved changes
- [ ] Waited 2-3 minutes
- [ ] Tested connection using `test_db_connection.php`
- [ ] Verified connection success in logs
- [ ] Tested login functionality

## Expected Result

After enabling App Platform access:

✅ **Connection succeeds**  
✅ **No more timeout errors**  
✅ **Login functionality works**  
✅ **Logs show "Database connection successful"**

## Still Need Help?

1. **Check the connection test page:** `test_db_connection.php`
2. **Review runtime logs** for specific error messages
3. **Verify database status** is "Running" and healthy
4. **Contact Digital Ocean support** if issue persists

---

**Most Important:** Enable "Allow connections from App Platform" in your database firewall settings!

