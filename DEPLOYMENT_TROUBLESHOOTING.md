# DigitalOcean Deployment Troubleshooting Guide

## Issue: Changes Not Appearing After Git Push

If you've committed and pushed changes but they're not showing on DigitalOcean App Platform, follow these steps:

### Step 1: Verify Git Push Was Successful

```bash
git log --oneline -1
git remote -v
```

Make sure your latest commit is visible and the remote is pointing to the correct repository.

### Step 2: Check DigitalOcean App Platform Dashboard

1. **Go to DigitalOcean Dashboard:**
   - Navigate to https://cloud.digitalocean.com/apps
   - Select your app: `dmarsians-taekwondo`

2. **Check Activity Tab:**
   - Look for the latest deployment
   - Check if it shows "Succeeded" or "Failed"
   - If failed, click on it to see error logs

3. **Check Deployments Tab:**
   - Verify the latest commit hash matches your local commit
   - Check deployment status

### Step 3: Manually Trigger Deployment

If auto-deploy didn't trigger:

1. In DigitalOcean App Platform dashboard:
   - Go to your app
   - Click on **"Deployments"** tab
   - Click **"Create Deployment"** or **"Redeploy"**
   - Select the latest commit from the dropdown
   - Click **"Deploy"**

2. Wait for deployment to complete (usually 5-10 minutes)

### Step 4: Check Build Logs

If deployment failed:

1. In the Activity tab, click on the failed deployment
2. Review the build logs for errors
3. Common issues:
   - **Composer errors**: Check `composer.json` dependencies
   - **PHP syntax errors**: Run `php -l filename.php` locally
   - **Missing files**: Verify all files are committed
   - **Permission errors**: Check file permissions

### Step 5: Verify App Configuration

Check your `app.yaml` configuration:

```yaml
github:
  repo: Helman143/Dmarsian
  branch: main
  deploy_on_push: true  # Should be true for auto-deploy
```

### Step 6: Clear Browser Cache

Sometimes changes are deployed but browser cache shows old version:

- **Hard Refresh**: `Ctrl+Shift+R` (Windows) or `Cmd+Shift+R` (Mac)
- **Incognito Mode**: Open in private/incognito window
- **Clear Cache**: Clear browser cache and cookies

### Step 7: Verify Files Are Deployed

Check if files exist on the server:

1. Use DigitalOcean App Platform console (if available)
2. Or check via SSH if you have access
3. Verify file timestamps match your latest commit

### Step 8: Check for Build Errors

Common build errors:

1. **Composer Install Fails:**
   - Check `composer.json` for invalid dependencies
   - Verify PHP version compatibility
   - Check `composer.lock` is committed

2. **PHP Syntax Errors:**
   - Run: `php -l admin_login.php`
   - Fix any syntax errors before deploying

3. **Missing Environment Variables:**
   - Check App Platform → Settings → Environment Variables
   - Ensure all required variables are set

### Step 9: Force Redeploy

If all else fails:

1. Make a small change to trigger deployment:
   ```bash
   # Add a comment to any PHP file
   echo "// Deployment trigger $(date)" >> admin_collection.php
   git add admin_collection.php
   git commit -m "Trigger deployment"
   git push
   ```

2. Or manually redeploy from DigitalOcean dashboard

### Step 10: Check App Logs

View runtime logs:

1. In DigitalOcean App Platform:
   - Go to your app
   - Click **"Runtime Logs"** tab
   - Check for PHP errors or warnings

## Quick Checklist

- [ ] Code committed and pushed to GitHub
- [ ] Latest commit visible in DigitalOcean dashboard
- [ ] Deployment status shows "Succeeded"
- [ ] Build logs show no errors
- [ ] Browser cache cleared
- [ ] Files verified in repository
- [ ] Environment variables set correctly
- [ ] App is running (not stopped)

## Still Not Working?

If changes still don't appear:

1. **Check file paths**: Ensure file paths in code match deployed structure
2. **Verify source_dir**: Check `app.yaml` has correct `source_dir: /`
3. **Check run_command**: Verify PHP server command is correct
4. **Contact Support**: Use DigitalOcean support if issue persists

## Issue: Buildpack PHP Download Failure

### Error Message
```
ERROR: Failed to download minimal PHP for bootstrapping!
This is most likely a temporary internal error.
```

### Cause
This error occurs when the Heroku PHP buildpack (used by DigitalOcean App Platform) fails to download PHP during the bootstrapping phase. This is typically a **temporary network issue** with the buildpack's download servers.

### Solutions

#### Solution 1: Retry Deployment (Recommended)
This is usually a temporary issue. Simply retry the deployment:

1. Go to DigitalOcean App Platform dashboard
2. Navigate to your app → **Deployments** tab
3. Click **"Redeploy"** or **"Create Deployment"**
4. Select the same commit and deploy again
5. Wait 5-10 minutes for the build to complete

**Most deployments succeed on retry** since this is a transient network issue.

#### Solution 2: Wait and Retry
If the first retry fails:
- Wait 15-30 minutes before retrying
- The buildpack's download servers may be experiencing temporary issues
- Try again after a short wait

#### Solution 3: Verify PHP Version Configuration
Ensure your PHP version is correctly specified:

1. Check `.php-version` file contains: `8.2`
2. Verify `app.yaml` has: `environment_slug: php-8.2`
3. Check `composer.json` requires: `"php": ">=8.2.0 <8.3.0"`

#### Solution 4: Check Buildpack Status
If multiple retries fail:
- Check DigitalOcean status page: https://status.digitalocean.com/
- Check Heroku status page: https://status.heroku.com/ (since App Platform uses Heroku buildpacks)
- There may be a known outage

#### Solution 5: Contact Support
If the issue persists after multiple retries:
1. Document the error with timestamps
2. Note how many times you've retried
3. Contact DigitalOcean support with:
   - App name: `dmarsians-taekwondo`
   - Error message
   - Build logs
   - Number of retry attempts

### Prevention Tips

1. **Always check deployment status** after pushing
2. **Test locally first** before deploying
3. **Use feature branches** for testing
4. **Monitor build logs** for warnings
5. **Keep deployment documentation updated**
6. **Retry failed builds** - most buildpack errors are temporary









