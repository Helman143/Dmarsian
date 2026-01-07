# 🚨 URGENT: OTP Email Not Working on Digital Ocean

## Immediate Action Required

Your OTP emails are not being received because **environment variables are not set** in Digital Ocean App Platform.

## Quick Fix (5 Minutes)

### Step 1: Set Environment Variables

1. **Open Digital Ocean Dashboard:**
   - Go to: https://cloud.digitalocean.com/apps
   - Click on: **dmarsians-taekwondo**

2. **Navigate to Environment Variables:**
   - Click **Settings** tab
   - Scroll to **App-Level Environment Variables**
   - Click **Edit**

3. **Add These Variables (ONE BY ONE):**

   **Variable 1:**
   - **Key:** `SMTP2GO_API_KEY`
   - **Value:** `api-DB88D1F1E4B74779BDB77FC2895D8325`
   - **Scope:** `RUN_TIME` ⚠️ MUST BE RUN_TIME
   - Click **+ Add Variable**

   **Variable 2:**
   - **Key:** `SMTP2GO_SENDER_EMAIL`
   - **Value:** `helmandashelle.dacuma@sccpag.edu.ph`
   - **Scope:** `RUN_TIME` ⚠️ MUST BE RUN_TIME
   - Click **+ Add Variable**

   **Variable 3 (Optional):**
   - **Key:** `SMTP2GO_SENDER_NAME`
   - **Value:** `D'Marsians Taekwondo Gym`
   - **Scope:** `RUN_TIME`
   - Click **+ Add Variable**

4. **Save:**
   - Click **Save** button at the bottom
   - Wait for automatic redeployment (2-5 minutes)
   - Check **Deployments** tab to see progress

### Step 2: Test Immediately

After redeployment completes:

1. **Use Quick Test Tool:**
   ```
   https://your-app-url.ondigitalocean.app/quick_otp_test.php?key=OTP_TEST_2024_SECRET_KEY_CHANGE_THIS
   ```
   ⚠️ **Change the secret key in the file first!**

2. **Or Use Diagnostic Tool:**
   ```
   https://your-app-url.ondigitalocean.app/diagnose_otp_email.php
   ```
   (Requires admin login)

3. **Or Try Forgot Password:**
   - Go to forgot password page
   - Enter your username/email
   - Check your Gmail inbox (and spam folder)

## Verify Environment Variables Are Set

### Method 1: Check via Quick Test Tool
- Visit `quick_otp_test.php` (with secret key)
- Section 1 will show if variables are loaded

### Method 2: Check via check_env.php
- Visit `check_env.php` (requires admin login)
- Look for SMTP2GO variables in the list

### Method 3: Check Digital Ocean Dashboard
- Go to Settings → App-Level Environment Variables
- Verify all three variables are listed
- Verify Scope is **RUN_TIME** (not BUILD_TIME)

## Check Error Logs

If emails still don't work after setting variables:

1. **Go to Digital Ocean Dashboard:**
   - Your app → **Runtime Logs** tab

2. **Look for these error messages:**
   - `SMTP2GO_API_KEY is not configured`
   - `SMTP2GO_SENDER_EMAIL is not configured`
   - `OTP Email Debug - Checking configuration`
   - `cURL Error`
   - `Failed to send OTP email`

3. **Common Errors:**

   **"SMTP2GO_API_KEY is not configured"**
   - ✅ Solution: Variable not set or Scope is wrong
   - ✅ Fix: Add variable with Scope = RUN_TIME
   - ✅ Wait for redeployment

   **"cURL Error #7: Failed to connect"**
   - ✅ Solution: Network/firewall issue
   - ✅ Fix: Check if SMTP2GO API is accessible

   **"HTTP 401" or "HTTP 403"**
   - ✅ Solution: API key invalid
   - ✅ Fix: Verify API key in SMTP2GO dashboard

   **"Sender email not verified"**
   - ✅ Solution: Email not verified in SMTP2GO
   - ✅ Fix: Log into SMTP2GO and verify sender email

## Why It Works on Localhost But Not Digital Ocean

### Localhost ✅
- Uses `.env` file via `env-loader.php`
- Variables loaded automatically
- Everything works

### Digital Ocean ❌
- `.env` file is NOT deployed
- Must set variables in App Platform Dashboard
- If not set, `getenv()` returns empty
- Email sending fails silently

## Most Common Issue

**99% of the time, the problem is:**
- Environment variables are NOT set in Digital Ocean dashboard
- OR Scope is set to BUILD_TIME instead of RUN_TIME
- OR App hasn't been redeployed after adding variables

## Verification Checklist

After following steps above:

- [ ] Variables added in Digital Ocean dashboard
- [ ] Scope set to RUN_TIME (not BUILD_TIME)
- [ ] App redeployed (check Deployments tab)
- [ ] Quick test tool shows variables as "✓ SET"
- [ ] Test email sends successfully
- [ ] OTP email received in inbox

## Still Not Working?

1. **Double-check variables in dashboard:**
   - Names are case-sensitive: `SMTP2GO_API_KEY` (not `smtp2go_api_key`)
   - No extra spaces
   - Scope is RUN_TIME

2. **Check deployment status:**
   - Go to Deployments tab
   - Wait for latest deployment to show "Active"

3. **Check SMTP2GO dashboard:**
   - Log into SMTP2GO account
   - Verify sender email is still verified
   - Check API key is active
   - Check email logs for delivery status

4. **Check Digital Ocean logs:**
   - Runtime Logs tab
   - Look for detailed error messages
   - Copy error messages for troubleshooting

## Files to Delete After Fixing

⚠️ **Security:** Delete these diagnostic files after fixing:
- `quick_otp_test.php`
- `diagnose_otp_email.php`

## Need More Help?

1. Check `OTP_EMAIL_TROUBLESHOOTING.md` for detailed guide
2. Check Digital Ocean Runtime Logs for specific errors
3. Check SMTP2GO dashboard for email delivery status

