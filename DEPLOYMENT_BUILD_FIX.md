# Deployment Build Fix - PHP Download Timeout

## Problem
The DigitalOcean App Platform build is failing with two issues:

1. **Network Timeout**: Downloading PHP 8.4.8 times out after 5 minutes
   - Error: `curl error 28: Operation timed out after 300002 milliseconds`
   - Only downloaded 9.8MB out of 16.9MB

2. **Composer.lock Out of Sync**: The lock file doesn't match composer.json

## Root Cause
- The buildpack is selecting PHP 8.4.8 (latest) instead of 8.3.x
- PHP 8.4.8 download is timing out (likely network/CDN issue)
- Composer.lock needs to be regenerated

## Solution Applied

### 1. Pinned PHP Version
Updated `.php-version` to specify PHP 8.3.22 (latest stable 8.3.x):
```
8.3.22
```

This ensures the buildpack uses a stable 8.3.x version instead of 8.4.8.

### 2. Update Composer.lock (Required)

**You must run this locally before deploying:**

```bash
# Navigate to project directory
cd /path/to/Dmarsian

# Update composer.lock to match composer.json
composer update --lock

# Or if you want to update dependencies too:
composer update

# Commit the changes
git add composer.lock .php-version
git commit -m "Fix: Pin PHP to 8.3.22 and update composer.lock"
git push
```

## Why PHP 8.3.22?
- PHP 8.3.22 is the latest stable 8.3.x release
- More stable and tested than 8.4.8
- Smaller download size (less likely to timeout)
- Better compatibility with existing dependencies

## Alternative: If Timeout Persists

If the timeout issue continues, you can:

1. **Use PHP 8.2.x** (even more stable):
   - Update `.php-version` to `8.2.28`
   - Update `composer.json` to `"php": "^8.2"`

2. **Check DigitalOcean Status**:
   - The timeout might be a temporary CDN/network issue
   - Try deploying again after a few minutes

3. **Contact DigitalOcean Support**:
   - If timeouts persist, it may be an infrastructure issue

## Verification

After updating, verify:
- `.php-version` contains `8.3.22`
- `composer.lock` is committed and up to date
- Build succeeds without timeout errors

## Next Steps

1. Run `composer update --lock` locally
2. Commit `composer.lock` and `.php-version`
3. Push to repository
4. Trigger new deployment on DigitalOcean

