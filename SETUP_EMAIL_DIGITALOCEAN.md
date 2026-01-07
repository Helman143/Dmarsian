# 📧 Setup Email Configuration on Digital Ocean App Platform

## Quick Setup Guide

### Step 1: Access Digital Ocean Dashboard

1. Go to: https://cloud.digitalocean.com/apps
2. Click on your app: **dmarsians-taekwondo**
3. Click on **Settings** tab
4. Scroll down to **App-Level Environment Variables**
5. Click **Edit**

### Step 2: Add Email Environment Variables

Add these variables one by one:

| Variable Name | Value | Scope |
|--------------|-------|-------|
| `SMTP2GO_API_KEY` | `api-DB88D1F1E4B74779BDB77FC2895D8325` | RUN_TIME |
| `SMTP2GO_SENDER_EMAIL` | `helmandashelle.dacuma@sccpag.edu.ph` | RUN_TIME |
| `SMTP2GO_SENDER_NAME` | `D'Marsians Taekwondo Gym` | RUN_TIME |
| `ADMIN_BCC_EMAIL` | `helmandacuma5@gmail.com` | RUN_TIME (optional) |

**Important Notes:**
- Click **+ Add Variable** for each one
- Make sure **Scope** is set to **RUN_TIME**
- Type the variable name exactly as shown (case-sensitive)
- Click **Save** when done

### Step 3: Save and Wait for Redeployment

1. Click **Save** button at the bottom
2. Digital Ocean will automatically redeploy your app
3. Wait for deployment to complete (check the **Deployments** tab)
4. Deployment usually takes 2-5 minutes

### Step 4: Verify Configuration

1. After deployment completes, visit:
   - `https://dmarsians-taekwondo-zkcmy.ondigitalocean.app/check_env.php`
2. Log in as admin
3. Check the "Other Environment Variables" section
4. You should see:
   - ✅ `SMTP2GO_API_KEY` - Set
   - ✅ `SMTP2GO_SENDER_EMAIL` - Set (should show your email)
   - ✅ `SMTP2GO_SENDER_NAME` - Set (optional)
   - ✅ `ADMIN_BCC_EMAIL` - Set (optional)

### Step 5: Test Email Sending

1. Go to your admin dashboard
2. Try sending a reminder email
3. Check for detailed error messages (if any)
4. Check your Gmail inbox and spam folder

## Troubleshooting

### If variables don't show up:
- Make sure you clicked **Save** after adding variables
- Wait for deployment to complete
- Refresh the check_env.php page

### If emails still fail:
- Check server logs in Digital Ocean dashboard
- Verify SMTP2GO sender email is verified (it is ✅)
- Check SMTP2GO dashboard for API key status

### Common Error Messages:

**"SMTP2GO API key is not configured"**
- Variable not set in Digital Ocean dashboard
- Variable name is misspelled
- App hasn't redeployed yet

**"HTTP 401" or "HTTP 403"**
- API key is invalid or expired
- Check SMTP2GO dashboard

**"HTTP 400"**
- Sender email not verified (but yours is verified ✅)
- Invalid email format

## Your Current Configuration

✅ **SMTP2GO Sender Email:** `helmandashelle.dacuma@sccpag.edu.ph` (Verified)
✅ **API Key:** `api-DB88D1F1E4B74779BDB77FC2895D8325`
✅ **Sender Name:** `D'Marsians Taekwondo Gym`

Once you set these in Digital Ocean dashboard and redeploy, emails should work!























