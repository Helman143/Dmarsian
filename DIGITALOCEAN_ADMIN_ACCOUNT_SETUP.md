# Setting Up Admin Account on Digital Ocean

## Step 1: Find Your Digital Ocean App URL

1. **Log in to Digital Ocean Dashboard**
   - Go to: https://cloud.digitalocean.com/apps

2. **Find Your App**
   - Click on your app: `dmarsians-taekwondo`

3. **Get Your App URL**
   - Your app URL will look like: `https://dmarsians-taekwondo-xxxxx.ondigitalocean.app`
   - You can find it in the app overview page or settings

## Step 2: Set a Security Key

**IMPORTANT:** Before accessing the script, you need to set a security key to prevent unauthorized access.

1. **Open `create_admin_account.php`** in your code editor
2. **Find this line:**
   ```php
   $SECRET_KEY = 'CHANGE_THIS_TO_A_RANDOM_STRING_12345';
   ```
3. **Change it to a random string**, for example:
   ```php
   $SECRET_KEY = 'MySecretKey2024!@#$%';
   ```
4. **Save and commit the change:**
   ```bash
   git add create_admin_account.php
   git commit -m "Set security key for admin account creation"
   git push
   ```
5. **Wait for Digital Ocean to redeploy** (usually takes 1-2 minutes)

## Step 3: Access the Script

1. **Open your browser** and go to:
   ```
   https://your-app-url.ondigitalocean.app/create_admin_account.php?key=YOUR_SECRET_KEY
   ```

   **Example:**
   ```
   https://dmarsians-taekwondo-xxxxx.ondigitalocean.app/create_admin_account.php?key=MySecretKey2024!@#$%
   ```

2. **The script will:**
   - Check if the account already exists
   - Create the admin account with:
     - Email: `helmandacuma5@gmail.com`
     - Username: `helmandacuma5`
     - Password: `YAMY@M143` (hashed automatically)

3. **After successful creation:**
   - You'll see a success message with account details
   - **IMMEDIATELY DELETE THE SCRIPT** for security

## Step 4: Delete the Script (CRITICAL!)

**After creating the account, you MUST delete the script for security:**

### Option A: Delete via Git (Recommended)
```bash
git rm create_admin_account.php
git commit -m "Remove admin account creation script after use"
git push
```

### Option B: Delete via Digital Ocean Console
1. Go to your app in Digital Ocean Dashboard
2. Use the console/terminal to delete the file
3. Or redeploy without the file

## Step 5: Log In

1. **Go to admin login page:**
   ```
   https://your-app-url.ondigitalocean.app/admin_login.php
   ```

2. **Use these credentials:**
   - **Email or Username:** `helmandacuma5@gmail.com` or `helmandacuma5`
   - **Password:** `YAMY@M143`

## Troubleshooting

### Script shows "Access Denied"
- Make sure you included the `?key=YOUR_SECRET_KEY` parameter in the URL
- Verify the key matches exactly what you set in the file
- Check that you've pushed and deployed the updated file

### Script shows "Account Already Exists"
- The account was already created
- You can log in directly at `admin_login.php`
- Or delete the existing account from the database first

### Script shows "Database Connection Failed"
- Check your database environment variables in Digital Ocean
- Go to: App Platform → Your App → Settings → App-Level Environment Variables
- Verify: `DB_HOST`, `DB_USER`, `DB_PASS`, `DB_NAME`, `DB_PORT` are set correctly

### Can't find the script after deployment
- The script should be accessible via the routing system
- Try: `https://your-app-url.ondigitalocean.app/create_admin_account.php?key=YOUR_KEY`
- Check the deployment logs in Digital Ocean dashboard

## Security Notes

⚠️ **IMPORTANT SECURITY WARNINGS:**

1. **Delete the script immediately after use** - It can create admin accounts!
2. **Never commit the secret key to Git** - Change it before pushing (or use environment variables)
3. **Use a strong, random secret key** - Don't use simple passwords
4. **Consider IP whitelisting** - Add additional security checks if needed
5. **Monitor your app logs** - Check for unauthorized access attempts

## Alternative: Use SQL Directly

If you prefer to create the account via SQL:

1. **Generate password hash:**
   - Visit: `https://your-app-url.ondigitalocean.app/generate_password_hash.php`
   - Copy the generated hash

2. **Connect to your database** (via Digital Ocean database console or MySQL client)

3. **Run this SQL:**
   ```sql
   INSERT INTO admin_accounts (email, username, password) 
   VALUES (
       'helmandacuma5@gmail.com', 
       'helmandacuma5', 
       'PASTE_THE_HASH_HERE'
   );
   ```

## Need Help?

- Check Digital Ocean App Platform logs: App → Runtime Logs
- Check database connection: App → Database Component → Connection Details
- Review error logs in the app dashboard





