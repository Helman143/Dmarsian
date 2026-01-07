# 🔧 Fix SMTP2GO API Key Issue

## Problem
The error message shows:
```
"error": "An API User matching the passed 'api_key' was not found"
```

This means the API key in your `.env` file is **incorrect or expired**.

## Solution: Get Your Correct SMTP2GO API Key

### Step 1: Log into SMTP2GO
1. Go to: https://app.smtp2go.com/
2. Log in with your SMTP2GO account credentials

### Step 2: Get Your API Key
1. Once logged in, go to: **Settings** → **API Keys**
   - Or navigate to: https://app.smtp2go.com/settings/api_keys
2. You'll see a list of API keys
3. **If you see an existing key:**
   - Copy the API key (it should start with `api-`)
   - **DO NOT** regenerate it unless necessary
4. **If you don't see any keys or need a new one:**
   - Click **"Create API Key"** or **"Add API Key"**
   - Give it a name (e.g., "D'Marsians OTP Emails")
   - Copy the key immediately (you won't be able to see it again)

### Step 3: Update Your .env File

1. Open your `.env` file in the project root:
   ```
   C:\xampp\htdocs\Dmarsian\.env
   ```

2. Find this line:
   ```
   SMTP2GO_API_KEY=api-DB88D1F1E4B74779BDB77FC2895D8325
   ```

3. Replace it with your **actual API key** from SMTP2GO:
   ```
   SMTP2GO_API_KEY=api-YOUR_ACTUAL_API_KEY_HERE
   ```

4. Save the file

### Step 4: Test Again

1. Go back to: `http://localhost/Dmarsian/test_otp_email.php`
2. Click "Send Test OTP Email" again
3. You should see a success message this time

## Important Notes

- **API keys are sensitive:** Never share them publicly or commit them to version control
- **The .env file is already in .gitignore** - this is correct, it won't be committed
- **If you're using Digital Ocean:** You'll also need to update the environment variable there:
  - Go to: Digital Ocean → Your App → Settings → App-Level Environment Variables
  - Update `SMTP2GO_API_KEY` with the correct value
  - Make sure Scope is set to **RUN_TIME**

## Still Having Issues?

If you can't find your API key in SMTP2GO:
1. Check if you're logged into the correct SMTP2GO account
2. Verify your SMTP2GO account is active (not suspended)
3. Contact SMTP2GO support if needed






