# OTP Email Troubleshooting Guide

## Problem Description

OTP emails work correctly on **localhost** but are **not received** when running on **Digital Ocean App Platform**.

- **Sender Email:** `helmandashelle.dacuma@sccpag.edu.ph` (verified in SMTP2GO)
- **Recipient Email:** `helmandacuma5@gmail.com`
- **Status:** Works on localhost ✅ | Fails on Digital Ocean ❌

## Root Cause

The most common cause is that **environment variables are not properly set** in Digital Ocean App Platform. The application uses `getenv()` to read configuration, and if these variables aren't set in the App Platform dashboard, the email sending will fail silently.

## Solution Steps

### Step 1: Verify Environment Variables in Digital Ocean

1. **Go to Digital Ocean App Platform Dashboard**
   - URL: https://cloud.digitalocean.com/apps
   - Select your app: **dmarsians-taekwondo**

2. **Navigate to Environment Variables**
   - Click on **Settings** tab
   - Scroll down to **App-Level Environment Variables**
   - Click **Edit**

3. **Verify/Add These Variables:**

   | Variable Name | Value | Scope |
   |--------------|-------|-------|
   | `SMTP2GO_API_KEY` | `api-DB88D1F1E4B74779BDB77FC2895D8325` | **RUN_TIME** |
   | `SMTP2GO_SENDER_EMAIL` | `helmandashelle.dacuma@sccpag.edu.ph` | **RUN_TIME** |
   | `SMTP2GO_SENDER_NAME` | `D'Marsians Taekwondo Gym` | **RUN_TIME** |
   | `ADMIN_BCC_EMAIL` | `helmandacuma5@gmail.com` (optional) | **RUN_TIME** |

   **⚠️ IMPORTANT:**
   - Make sure **Scope** is set to **RUN_TIME** (not BUILD_TIME)
   - Type variable names **exactly** as shown (case-sensitive)
   - No spaces before or after the variable names

4. **Save and Wait for Redeployment**
   - Click **Save** button
   - Digital Ocean will automatically redeploy your app
   - Wait for deployment to complete (check **Deployments** tab)
   - Deployment usually takes 2-5 minutes

### Step 2: Use Diagnostic Tool

After redeployment, use the diagnostic tool to verify the configuration:

1. **Access the diagnostic page:**
   ```
   https://your-app-url.ondigitalocean.app/diagnose_otp_email.php
   ```

2. **Log in as admin** (if required)

3. **Check the diagnostic results:**
   - Section 1: Environment Variables Check
   - Section 2: System Capabilities Check
   - Section 3: Live API Test

4. **Run the test:**
   - Enter your email: `helmandacuma5@gmail.com`
   - Click "Send Test OTP Email"
   - Check the results

### Step 3: Check Error Logs

If emails still don't work, check the error logs:

1. **In Digital Ocean Dashboard:**
   - Go to your app
   - Click on **Runtime Logs** tab
   - Look for error messages containing:
     - `SMTP2GO_API_KEY is not configured`
     - `SMTP2GO_SENDER_EMAIL is not configured`
     - `cURL Error`
     - `Failed to send OTP email`

2. **Common Error Messages:**

   **Error: "SMTP2GO_API_KEY is not configured"**
   - **Solution:** Add `SMTP2GO_API_KEY` in App Platform environment variables
   - **Solution:** Ensure Scope is set to RUN_TIME
   - **Solution:** Wait for redeployment after adding

   **Error: "SMTP2GO_SENDER_EMAIL is not configured"**
   - **Solution:** Add `SMTP2GO_SENDER_EMAIL` in App Platform environment variables
   - **Solution:** Ensure the email is verified in SMTP2GO dashboard

   **Error: "cURL Error #7: Failed to connect"**
   - **Solution:** Check firewall rules - SMTP2GO API should be accessible
   - **Solution:** Check network connectivity from App Platform

   **Error: "HTTP 401" or "HTTP 403"**
   - **Solution:** API key might be invalid or expired
   - **Solution:** Check SMTP2GO dashboard for API key status
   - **Solution:** Regenerate API key if needed

   **Error: "Sender email not verified"**
   - **Solution:** Log into SMTP2GO dashboard
   - **Solution:** Verify `helmandashelle.dacuma@sccpag.edu.ph` is verified
   - **Solution:** Check sender verification status

## Verification Checklist

After following the steps above, verify:

- [ ] Environment variables are set in Digital Ocean App Platform
- [ ] Scope is set to RUN_TIME (not BUILD_TIME)
- [ ] App has been redeployed after adding variables
- [ ] Diagnostic tool shows all variables as "✓ Found"
- [ ] Test email sends successfully
- [ ] OTP email is received in inbox (check spam folder too)

## Additional Diagnostic Tools

### 1. Check Environment Variables
```
https://your-app-url.ondigitalocean.app/check_env.php
```
This shows all environment variables loaded by the application.

### 2. Check Email Configuration
```
https://your-app-url.ondigitalocean.app/check_email_config.php
```
This shows email-specific configuration and allows sending test emails.

### 3. Test Email Configuration
```
https://your-app-url.ondigitalocean.app/test_email_config.php
```
This is another tool for testing email sending.

## Why It Works on Localhost But Not on Digital Ocean

### Localhost (Working ✅)
- Environment variables are loaded from `.env` file via `env-loader.php`
- Variables are available via `getenv()` and constants
- Configuration is properly loaded

### Digital Ocean (Not Working ❌)
- Environment variables must be set in App Platform Dashboard
- `.env` file is typically not deployed to production
- If variables aren't set in dashboard, `getenv()` returns empty
- Application fails silently (for security, doesn't reveal errors to users)

## Quick Fix Summary

1. **Go to:** Digital Ocean → Apps → dmarsians-taekwondo → Settings
2. **Find:** App-Level Environment Variables → Edit
3. **Add:**
   - `SMTP2GO_API_KEY` = `api-DB88D1F1E4B74779BDB77FC2895D8325` (RUN_TIME)
   - `SMTP2GO_SENDER_EMAIL` = `helmandashelle.dacuma@sccpag.edu.ph` (RUN_TIME)
   - `SMTP2GO_SENDER_NAME` = `D'Marsians Taekwondo Gym` (RUN_TIME)
4. **Click:** Save
5. **Wait:** For redeployment (2-5 minutes)
6. **Test:** Use `diagnose_otp_email.php` to verify

## Still Not Working?

If you've followed all steps and emails still don't work:

1. **Check SMTP2GO Dashboard:**
   - Log into your SMTP2GO account
   - Check email logs for delivery status
   - Verify sender email is still verified
   - Check API key is active

2. **Check Digital Ocean Logs:**
   - Go to Runtime Logs
   - Look for detailed error messages
   - Check for cURL errors or network issues

3. **Verify Network Access:**
   - App Platform should be able to reach `api.smtp2go.com`
   - Check firewall rules if applicable

4. **Contact Support:**
   - If all else fails, check SMTP2GO support
   - Check Digital Ocean support for App Platform issues

## Security Note

⚠️ **After fixing the issue, delete the diagnostic file:**
- `diagnose_otp_email.php` - Contains diagnostic information that should not be publicly accessible

