# 🔍 How to Verify OTP Email Issue

## Current Situation

Based on your SMTP2GO Activity dashboard:

✅ **Localhost:** OTP emails are being sent and delivered successfully
- Shows in SMTP2GO Activity as "Delivered"
- Recipient: `helmandacuma5@gmail.com`
- Subject: "Your Admin OTP Code"

❌ **Digital Ocean:** OTP emails are NOT being sent
- No entries in SMTP2GO Activity when testing from Digital Ocean
- This means the email never reaches SMTP2GO API

## Root Cause

**The environment variables are NOT set in Digital Ocean App Platform.**

When you test on Digital Ocean:
1. Code tries to read `SMTP2GO_API_KEY` → Returns empty
2. Code tries to read `SMTP2GO_SENDER_EMAIL` → Returns empty
3. Code exits early with error log: "SMTP2GO_API_KEY is not configured"
4. Email is never sent to SMTP2GO
5. No entry appears in SMTP2GO Activity

## How to Verify the Issue

### Method 1: Check SMTP2GO Activity (Easiest)

1. **Go to SMTP2GO Dashboard:**
   - https://app.smtp2go.com/
   - Navigate to: **Reports** → **Activity**

2. **Test OTP from Digital Ocean:**
   - Go to your Digital Ocean app forgot password page
   - Request an OTP
   - Wait 1-2 minutes

3. **Check Activity:**
   - If you see a NEW entry with "Your Admin OTP Code" → Email was sent ✅
   - If you see NO new entry → Email was NOT sent ❌ (Environment variables not set)

### Method 2: Check Digital Ocean Runtime Logs

1. **Go to Digital Ocean Dashboard:**
   - https://cloud.digitalocean.com/apps
   - Select: **dmarsians-taekwondo**
   - Click: **Runtime Logs** tab

2. **Look for these error messages:**
   ```
   ERROR: SMTP2GO_API_KEY is not configured
   ERROR: SMTP2GO_SENDER_EMAIL is not configured
   OTP Email Debug - Checking configuration
   ```

3. **If you see these errors:**
   - ✅ Confirms environment variables are NOT set
   - ✅ Need to add them in App Platform dashboard

### Method 3: Use Quick Test Tool

1. **Access the test tool:**
   ```
   https://your-app-url.ondigitalocean.app/quick_otp_test.php?key=OTP_TEST_2024_SECRET_KEY_CHANGE_THIS
   ```
   (Change the secret key in the file first!)

2. **Check Section 1:**
   - If it shows "✗ NOT SET" → Environment variables are missing
   - If it shows "✓ SET" → Variables are loaded, check other issues

## Solution: Set Environment Variables

### Step-by-Step Instructions

1. **Go to Digital Ocean App Platform:**
   - URL: https://cloud.digitalocean.com/apps
   - Click on: **dmarsians-taekwondo**

2. **Navigate to Environment Variables:**
   - Click **Settings** tab
   - Scroll to **App-Level Environment Variables**
   - Click **Edit**

3. **Add Variable 1:**
   - **Key:** `SMTP2GO_API_KEY`
   - **Value:** `api-DB88D1F1E4B74779BDB77FC2895D8325`
   - **Scope:** `RUN_TIME` ⚠️ MUST BE RUN_TIME
   - Click **+ Add Variable**

4. **Add Variable 2:**
   - **Key:** `SMTP2GO_SENDER_EMAIL`
   - **Value:** `helmandashelle.dacuma@sccpag.edu.ph`
   - **Scope:** `RUN_TIME` ⚠️ MUST BE RUN_TIME
   - Click **+ Add Variable**

5. **Add Variable 3 (Optional):**
   - **Key:** `SMTP2GO_SENDER_NAME`
   - **Value:** `D'Marsians Taekwondo Gym`
   - **Scope:** `RUN_TIME`
   - Click **+ Add Variable**

6. **Save:**
   - Click **Save** button
   - Wait for redeployment (2-5 minutes)
   - Check **Deployments** tab for progress

## Verify the Fix

After redeployment:

1. **Test OTP from Digital Ocean:**
   - Go to forgot password page
   - Request an OTP

2. **Check SMTP2GO Activity:**
   - Go to SMTP2GO Dashboard → Reports → Activity
   - You should see a NEW entry with:
     - Event: "Delivered" (or "Queued")
     - Recipient: `helmandacuma5@gmail.com`
     - Subject: "Your Admin OTP Code"
     - Date: Current time

3. **Check Your Gmail:**
   - Check inbox (and spam folder)
   - You should receive the OTP email

## Why This Happens

### Localhost ✅
- Uses `.env` file
- Variables loaded automatically via `env-loader.php`
- `getenv('SMTP2GO_API_KEY')` returns the value
- Email is sent successfully

### Digital Ocean ❌
- `.env` file is NOT deployed to production
- Must set variables in App Platform Dashboard
- If not set, `getenv('SMTP2GO_API_KEY')` returns empty
- Code exits early with error
- Email never reaches SMTP2GO API
- No entry in SMTP2GO Activity

## Quick Checklist

- [ ] Environment variables set in Digital Ocean dashboard
- [ ] Scope set to RUN_TIME (not BUILD_TIME)
- [ ] App redeployed after adding variables
- [ ] Tested OTP request from Digital Ocean
- [ ] Checked SMTP2GO Activity for new entry
- [ ] Received OTP email in Gmail

## Still Not Working?

If you've set the variables and still don't see entries in SMTP2GO Activity:

1. **Double-check variable names:**
   - Must be exactly: `SMTP2GO_API_KEY` (case-sensitive)
   - Must be exactly: `SMTP2GO_SENDER_EMAIL` (case-sensitive)

2. **Check deployment status:**
   - Go to Deployments tab
   - Wait for latest deployment to show "Active"

3. **Check Digital Ocean logs:**
   - Runtime Logs tab
   - Look for "OTP Email Debug" messages
   - Check if variables are being loaded

4. **Verify API key:**
   - Check SMTP2GO dashboard
   - Verify API key is active
   - Check if there are any account limits

## Summary

**The issue:** Environment variables not set in Digital Ocean → Email never sent → No entry in SMTP2GO Activity

**The fix:** Set environment variables in Digital Ocean App Platform → Redeploy → Test → Check SMTP2GO Activity for new entry

