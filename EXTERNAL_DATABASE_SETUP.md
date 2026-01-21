# External Database Setup Guide

## Overview

You're using a **separate/managed database** in Digital Ocean (not an App Platform component). This guide shows you how to connect your App Platform web service to your external database.

## Step 1: Get Your Database Connection Details

1. Go to **Digital Ocean Dashboard** → **Databases**
2. Select your MySQL database
3. Click on **Connection Details** or **Connection Parameters**
4. Note down the following information:

   - **Host** (e.g., `db-mysql-nyc1-12345.db.ondigitalocean.com`)
   - **Port** (usually `25060` for managed databases)
   - **Username** (e.g., `doadmin`)
   - **Password** (click to reveal)
   - **Database** (your database name, e.g., `capstone_db`)

## Step 2: Set Environment Variables in App Platform

1. Go to **Digital Ocean App Platform Dashboard**
2. Select your app: **dmarsians-taekwondo**
3. Go to **Settings** → **App-Level Environment Variables**
4. Click **Edit**

5. **Remove** any existing database variables that show `${db.*}` (template variables)

6. **Add** these environment variables with your actual database values:

   | Key | Value | Scope | Notes |
   |-----|-------|-------|-------|
   | `DB_HOST` | Your database hostname | RUN_TIME | From connection details |
   | `DB_USER` | Your database username | RUN_TIME | From connection details |
   | `DB_PASS` | Your database password | RUN_TIME | From connection details (click to reveal) |
   | `DB_NAME` | Your database name | RUN_TIME | Usually `capstone_db` or similar |
   | `DB_PORT` | Your database port | RUN_TIME | Usually `25060` for managed databases |

7. Click **Save**

## Step 3: Verify Database Access from App Platform

Your App Platform app needs to be able to connect to your external database. Check:

### Firewall/Trusted Sources

1. In your database settings, go to **Trusted Sources** or **Firewall**
2. Ensure that **App Platform** is allowed, OR
3. Add the App Platform IP ranges (if using IP-based firewall)

**Note:** Digital Ocean managed databases usually allow connections from App Platform automatically, but verify this.

### Connection String Format

Your database connection should work with:
- **Host:** `your-db-host.db.ondigitalocean.com`
- **Port:** `25060` (standard for managed databases)
- **SSL:** Required (your `config.php` already handles this)

## Step 4: Test the Connection

After setting the environment variables:

1. **Redeploy your app** (push a commit or trigger deployment)
2. Check the **Runtime Logs** in App Platform
3. Look for:
   - ✅ **Success:** Database connection messages with actual hostname
   - ❌ **Error:** Any connection errors (will show the actual hostname now, not `${db.HOSTNAME}`)

4. Try logging in to your app
5. If connection works, you should be able to log in successfully

## Step 5: Import Database Schema (If Not Done)

If you haven't imported your database schema yet:

1. Connect to your database using the connection details
2. Use MySQL client, phpMyAdmin, or Digital Ocean's database console
3. Import `Database/db.sql` file

**Using MySQL client:**
```bash
mysql -h [YOUR_HOST] -P 25060 -u [YOUR_USER] -p [YOUR_DATABASE] < Database/db.sql
```

**Using Digital Ocean Console:**
1. Go to your database → **Console** tab
2. Copy and paste the contents of `Database/db.sql`
3. Execute

## Troubleshooting

### Issue: Still seeing `${db.HOSTNAME}` in logs

**Solution:**
- Make sure you removed the template variables from `app.yaml` (already done)
- Verify environment variables in App Platform dashboard show actual values, not `${db.*}`
- Redeploy after setting variables

### Issue: Connection timeout or refused

**Solutions:**
1. **Check Trusted Sources:**
   - Go to your database → **Settings** → **Trusted Sources**
   - Ensure App Platform is allowed
   - Add App Platform IP ranges if needed

2. **Verify Connection Details:**
   - Double-check hostname, port, username, password
   - Ensure database name is correct

3. **Check SSL:**
   - Your `config.php` already handles SSL
   - Managed databases require SSL connections

### Issue: Access denied error

**Solutions:**
1. Verify username and password are correct
2. Check database user has proper permissions
3. Ensure you're connecting to the correct database

### Issue: Database not found

**Solution:**
- Verify `DB_NAME` matches your actual database name
- Create the database if it doesn't exist
- Import schema after creating database

## Security Best Practices

1. **Never commit secrets to Git:**
   - Database credentials are set in App Platform dashboard only
   - `app.yaml` doesn't contain actual credentials

2. **Use strong passwords:**
   - Your managed database should have a strong password
   - Rotate passwords periodically

3. **Limit access:**
   - Only allow App Platform to connect
   - Use firewall/trusted sources settings

## Verification Checklist

- [ ] Removed database component from `app.yaml` ✅
- [ ] Removed template variables from `app.yaml` ✅
- [ ] Set `DB_HOST` in App Platform dashboard
- [ ] Set `DB_USER` in App Platform dashboard
- [ ] Set `DB_PASS` in App Platform dashboard
- [ ] Set `DB_NAME` in App Platform dashboard
- [ ] Set `DB_PORT` in App Platform dashboard
- [ ] Verified database trusted sources allow App Platform
- [ ] Redeployed app after setting variables
- [ ] Checked logs for connection success
- [ ] Tested login functionality
- [ ] Verified database schema is imported

## Next Steps

After completing the setup:

1. Monitor runtime logs for any connection issues
2. Test all database-dependent features
3. Set up database backups (if not already configured)
4. Monitor database performance

## Quick Reference

**Where to find database connection details:**
- Digital Ocean Dashboard → Databases → Your Database → Connection Details

**Where to set environment variables:**
- App Platform → Your App → Settings → App-Level Environment Variables

**How to trigger redeploy:**
- Push to your Git repository (if auto-deploy enabled)
- OR: App Platform → Deployments → Create Deployment

