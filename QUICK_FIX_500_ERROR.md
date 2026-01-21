# Quick Fix for 500 Internal Server Error

## Immediate Steps to Fix

### Step 1: Access Diagnostic Page

Visit: `https://your-app.ondigitalocean.app/test.php`

This will show you exactly what's wrong.

### Step 2: Check App Platform Logs

1. Go to DigitalOcean App Platform Dashboard
2. Select your app
3. Go to **Runtime Logs**
4. Look for PHP errors

### Step 3: Common Issues and Fixes

#### Issue 1: Database Connection Failed

**Symptoms:**
- Error in logs: "Access denied for user" or "Connection refused"
- Diagnostic page shows database connection failed

**Fix:**
1. Verify database component is created and running
2. Check environment variables in App Platform dashboard:
   - `DB_HOST` should be auto-set (from `${db.HOSTNAME}`)
   - `DB_USER` should be auto-set
   - `DB_PASS` should be auto-set
   - `DB_NAME` should be auto-set
   - `DB_PORT` should be auto-set
3. Verify database schema is imported
4. Test connection using database console

#### Issue 2: Missing Files

**Symptoms:**
- Error: "require_once(): Failed opening required"
- Diagnostic page shows files missing

**Fix:**
1. Verify all files are committed to Git
2. Check that files exist in repository
3. Redeploy the app

#### Issue 3: Missing PHP Extensions

**Symptoms:**
- Error: "Call to undefined function"
- Diagnostic page shows extensions missing

**Fix:**
1. App Platform should include all required extensions
2. If missing, you may need to use a custom buildpack
3. Check `composer.json` for extension requirements

#### Issue 4: Composer Dependencies Not Installed

**Symptoms:**
- Error: "Class 'PHPMailer\PHPMailer\PHPMailer' not found"
- Diagnostic page shows vendor/autoload.php missing

**Fix:**
1. Add build command to `app.yaml`:
   ```yaml
   build_command: composer install --no-dev --optimize-autoloader
   ```
2. Redeploy the app

#### Issue 5: File Path Issues

**Symptoms:**
- Error: "No such file or directory"
- Files exist but can't be found

**Fix:**
1. Check that `public/index.php` routing is correct
2. Verify file paths are relative, not absolute
3. Check that `chdir()` is working correctly

#### Issue 6: Permission Issues

**Symptoms:**
- Error: "Permission denied"
- Uploads directory not writable

**Fix:**
1. Create uploads directory on startup (temporary fix)
2. Implement DigitalOcean Spaces (permanent solution)

### Step 4: Update app.yaml (If Needed)

If the run command is causing issues, update `app.yaml`:

```yaml
run_command: php -S 0.0.0.0:8080 -t public public/index.php
```

Remove the fallback (`|| php -S 0.0.0.0:8080`) as it can hide errors.

### Step 5: Enable Error Display (Temporary)

The updated `public/index.php` now shows errors in non-production mode.

To see errors:
1. Set `APP_ENV` to something other than `production` in App Platform dashboard
2. Or modify `public/index.php` to always show errors (for debugging only)

### Step 6: Test After Fix

1. Visit: `https://your-app.ondigitalocean.app/`
2. Should load `webpage.php`
3. Check for any errors
4. Visit: `https://your-app.ondigitalocean.app/test.php`
5. All checks should pass

## Quick Commands

### Check Current Configuration

```bash
# View app.yaml
cat app.yaml

# Check if files exist
ls -la public/
ls -la config.php
ls -la webpage.php
```

### Common Fixes

1. **Redeploy:**
   - App Platform → Your App → Deployments → Redeploy

2. **Check Environment Variables:**
   - App Platform → Your App → Settings → Environment Variables

3. **View Logs:**
   - App Platform → Your App → Runtime Logs

4. **Test Database:**
   - App Platform → Database → Console
   - Run: `SELECT 1;`

## After Fixing

1. ✅ Remove diagnostic file: `public/test.php` (or keep for monitoring)
2. ✅ Set `APP_ENV=production` in dashboard
3. ✅ Verify error display is off in production
4. ✅ Test all major features
5. ✅ Monitor logs for any new errors

## Still Having Issues?

1. Check the diagnostic page: `/test.php`
2. Review runtime logs in App Platform
3. Check build logs for deployment issues
4. Verify all environment variables are set
5. Ensure database is running and accessible

---

**Last Updated:** 2025-01-XX



