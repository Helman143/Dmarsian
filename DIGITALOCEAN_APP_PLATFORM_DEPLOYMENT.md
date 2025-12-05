# DigitalOcean App Platform Deployment Guide
## D'Marsians Taekwondo Management System

**Project Type:** Native PHP Application  
**Database:** MySQL 8  
**PHP Version:** >= 7.4 (Recommended: 8.1+)

---

## Table of Contents

1. [Project Overview](#project-overview)
2. [Prerequisites](#prerequisites)
3. [Pre-Deployment Checklist](#pre-deployment-checklist)
4. [Step-by-Step Deployment](#step-by-step-deployment)
5. [Configuration Files](#configuration-files)
6. [Environment Variables](#environment-variables)
7. [Database Setup](#database-setup)
8. [File Uploads & Storage](#file-uploads--storage)
9. [Routing & Entry Point](#routing--entry-point)
10. [PHP Configuration](#php-configuration)
11. [Security Considerations](#security-considerations)
12. [Troubleshooting](#troubleshooting)
13. [Recommendations & Suggestions](#recommendations--suggestions)

---

## Project Overview

This is a native PHP application for managing a Taekwondo gym, including:
- Student enrollment and management
- Payment tracking
- Dues management
- Post/news management with image uploads
- Admin dashboard
- Trial session registration

**Key Technologies:**
- PHP (Native, no framework)
- MySQL Database
- PHPMailer (via Composer)
- File uploads to `uploads/posts/` directory
- Session-based authentication

---

## Prerequisites

### Required Accounts & Services

1. **DigitalOcean Account**
   - Active account with billing enabled
   - Access to App Platform

2. **GitHub Account**
   - Repository containing your code
   - Access to push commits

3. **SMTP2GO Account** (for email functionality)
   - API key for sending emails
   - Verified sender email

4. **Domain Name** (Optional but recommended)
   - For custom domain setup

### Required Knowledge

- Basic Git operations
- Understanding of environment variables
- Basic PHP/MySQL knowledge

---

## Pre-Deployment Checklist

### ✅ Code Preparation

- [ ] All code is committed to Git repository
- [ ] `.env` file is NOT committed (already in `.gitignore`)
- [ ] `config.php` is NOT committed (already in `.gitignore`)
- [ ] `composer.json` and `composer.lock` are committed
- [ ] `vendor/` directory is NOT committed (already in `.gitignore`)
- [ ] `uploads/` directory is NOT committed (already in `.gitignore`)
- [ ] Test files removed or excluded from production
- [ ] Database dump available (`Database/db.sql`)

### ✅ Configuration Review

- [ ] Review `app.yaml` configuration
- [ ] Verify PHP version compatibility (>= 7.4)
- [ ] Check all required PHP extensions (see `composer.json`)
- [ ] Verify file paths are relative, not absolute

### ✅ Environment Variables Prepared

- [ ] Database credentials ready
- [ ] SMTP2GO API key ready
- [ ] Email configuration ready
- [ ] All sensitive values documented (not in code)

---

## Step-by-Step Deployment

### Step 1: Prepare Your Repository

1. **Ensure your code is pushed to GitHub:**
   ```bash
   git add .
   git commit -m "Prepare for App Platform deployment"
   git push origin main
   ```

2. **Verify these files exist in your repository:**
   - `app.yaml` (App Platform configuration)
   - `public/index.php` (Entry point)
   - `composer.json` (Dependencies)
   - `env-loader.php` (Environment variable loader)

### Step 2: Create App in DigitalOcean App Platform

1. **Log in to DigitalOcean:**
   - Go to https://cloud.digitalocean.com
   - Navigate to **Apps** → **Create App**

2. **Connect GitHub Repository:**
   - Click **GitHub** as source
   - Authorize DigitalOcean if needed
   - Select your repository: `Helman143/Dmarsian`
   - Select branch: `main`
   - Enable **Autodeploy** (optional, recommended)

3. **Configure App:**
   - App Platform will auto-detect PHP
   - Review the detected configuration
   - Click **Edit** to customize if needed

### Step 3: Configure App Spec (app.yaml)

The `app.yaml` file should be in your repository root. Review and update:

```yaml
name: dmarsians-taekwondo
region: nyc  # Change to your preferred region
services:
- name: web
  source_dir: /
  github:
    repo: Helman143/Dmarsian  # Update with your repo
    branch: main
    deploy_on_push: true
  run_command: php -S 0.0.0.0:8080 -t public public/index.php
  environment_slug: php
  instance_count: 1
  instance_size_slug: basic-xxs  # Start small, scale up later
  http_port: 8080
  routes:
  - path: /
  envs:
  - key: APP_ENV
    value: production
    scope: RUN_TIME
  - key: DB_HOST
    value: ${db.HOSTNAME}
    scope: RUN_TIME
  - key: DB_USER
    value: ${db.USERNAME}
    scope: RUN_TIME
  - key: DB_PASS
    value: ${db.PASSWORD}
    scope: RUN_TIME
  - key: DB_NAME
    value: ${db.DATABASE}
    scope: RUN_TIME
  - key: DB_PORT
    value: ${db.PORT}
    scope: RUN_TIME
  - key: SMTP2GO_API_KEY
    value: your_smtp2go_api_key_here  # Set in App Platform dashboard
    scope: RUN_TIME
  - key: SMTP2GO_SENDER_EMAIL
    value: your_email@example.com  # Set in App Platform dashboard
    scope: RUN_TIME
  - key: SMTP2GO_SENDER_NAME
    value: D'Marsians Taekwondo Gym
    scope: RUN_TIME
  - key: ADMIN_BCC_EMAIL
    value: admin@example.com  # Set in App Platform dashboard
    scope: RUN_TIME

databases:
- name: db
  engine: MYSQL
  version: "8"
  production: false  # Set to true for production
```

**⚠️ IMPORTANT:** Do NOT put actual secrets in `app.yaml`. Use App Platform dashboard to set sensitive environment variables.

### Step 4: Set Environment Variables

**In App Platform Dashboard:**

1. Go to your App → **Settings** → **App-Level Environment Variables**
2. Add the following variables:

```
SMTP2GO_API_KEY = your_actual_api_key
SMTP2GO_SENDER_EMAIL = your_actual_email@example.com
SMTP2GO_SENDER_NAME = D'Marsians Taekwondo Gym
ADMIN_BCC_EMAIL = admin@example.com
APP_ENV = production
```

**Note:** Database variables (`DB_HOST`, `DB_USER`, etc.) are automatically set when you add a database component. They use the `${db.*}` syntax in `app.yaml`.

### Step 5: Add Database Component

1. In App Platform dashboard, go to **Components**
2. Click **Add Component** → **Database**
3. Select:
   - **Engine:** MySQL
   - **Version:** 8
   - **Plan:** Basic (start with smallest, scale later)
   - **Name:** `db` (must match name in app.yaml)
4. Click **Add Database**

**⚠️ IMPORTANT:** 
- Database is created automatically
- Connection details are injected as environment variables
- You'll need to import your schema (see Database Setup section)

### Step 6: Configure Build Settings

1. Go to **Settings** → **Build & Deploy**
2. **Build Command:** (Leave empty - App Platform auto-detects PHP)
   - Or use: `composer install --no-dev --optimize-autoloader`
3. **Run Command:** `php -S 0.0.0.0:8080 -t public public/index.php`
4. **HTTP Port:** `8080`

### Step 7: Deploy

1. Click **Deploy** or push to your repository (if autodeploy is enabled)
2. Monitor the build logs
3. Wait for deployment to complete (usually 5-10 minutes)

### Step 8: Import Database

After deployment, you need to import your database schema:

**Option A: Using DigitalOcean Database Console**

1. Go to your database component in App Platform
2. Click **Console** or **Connection Details**
3. Use the provided connection string or connect via MySQL client
4. Import `Database/db.sql`:
   ```sql
   SOURCE /path/to/Database/db.sql;
   ```
   Or copy-paste the SQL content

**Option B: Using MySQL Client (Local)**

1. Get connection details from App Platform database component
2. Connect from your local machine:
   ```bash
   mysql -h [HOSTNAME] -u [USERNAME] -p [DATABASE] < Database/db.sql
   ```
3. Enter password when prompted

**Option C: Using phpMyAdmin or Adminer**

1. Deploy a temporary phpMyAdmin instance or use Adminer
2. Connect using database credentials from App Platform
3. Import `Database/db.sql` via web interface

---

## Configuration Files

### 1. app.yaml

Location: Root directory  
Purpose: App Platform configuration

**Key Settings:**
- `run_command`: PHP built-in server command
- `http_port`: Must be 8080 for App Platform
- `source_dir`: `/` (root of repository)
- `environment_slug`: `php` (auto-detected)

### 2. public/index.php

Location: `public/index.php`  
Purpose: Entry point for all requests

**Current Implementation:**
- Routes requests to appropriate PHP files
- Handles static file serving
- Defaults to `webpage.php` for root requests

**✅ Already exists and configured correctly**

### 3. config.php

Location: Root directory  
Purpose: Database and application configuration

**Key Features:**
- Loads environment variables via `env-loader.php`
- Uses `getenv()` for configuration
- Compatible with App Platform environment variables

**✅ Already configured for App Platform**

### 4. env-loader.php

Location: Root directory  
Purpose: Loads `.env` file (optional, for local development)

**Note:** App Platform uses environment variables directly, so `.env` file is not needed in production. This file gracefully handles missing `.env` files.

### 5. .htaccess

Location: Root directory  
**Status:** ⚠️ **IGNORED by App Platform**

App Platform uses nginx, not Apache, so `.htaccess` files are ignored. You can:
- **Option 1:** Keep it (it will be ignored)
- **Option 2:** Remove it (recommended for App Platform)
- **Option 3:** Create nginx configuration (see recommendations)

---

## Environment Variables

### Required Variables

| Variable | Description | Example | Where to Set |
|----------|-------------|---------|--------------|
| `DB_HOST` | Database hostname | Auto-set by App Platform | Auto (from database component) |
| `DB_USER` | Database username | Auto-set by App Platform | Auto (from database component) |
| `DB_PASS` | Database password | Auto-set by App Platform | Auto (from database component) |
| `DB_NAME` | Database name | `capstone_db` | Auto (from database component) |
| `DB_PORT` | Database port | `25060` (App Platform default) | Auto (from database component) |
| `SMTP2GO_API_KEY` | SMTP2GO API key | `your_api_key_here` | App Platform Dashboard |
| `SMTP2GO_SENDER_EMAIL` | Sender email address | `noreply@example.com` | App Platform Dashboard |
| `SMTP2GO_SENDER_NAME` | Sender display name | `D'Marsians Taekwondo Gym` | App Platform Dashboard |
| `ADMIN_BCC_EMAIL` | Admin BCC email | `admin@example.com` | App Platform Dashboard |
| `APP_ENV` | Application environment | `production` | App Platform Dashboard |
| `HERO_VIDEO_URL` | Hero video URL (DigitalOcean Spaces) | `https://space-name.region.digitaloceanspaces.com/videos/video.mp4` | App Platform Dashboard |
| `SPACES_KEY` | DigitalOcean Spaces access key | `your_spaces_key` | App Platform Dashboard (optional) |
| `SPACES_SECRET` | DigitalOcean Spaces secret key | `your_spaces_secret` | App Platform Dashboard (optional) |
| `SPACES_NAME` | DigitalOcean Spaces name | `dmarsians-media` | App Platform Dashboard (optional) |
| `SPACES_REGION` | DigitalOcean Spaces region | `nyc3` | App Platform Dashboard (optional) |

### Setting Environment Variables

**Method 1: App Platform Dashboard (Recommended for Secrets)**

1. Go to App → **Settings** → **App-Level Environment Variables**
2. Click **Edit**
3. Add each variable:
   - **Key:** Variable name
   - **Value:** Variable value
   - **Scope:** RUN_TIME
4. Click **Save**

**Method 2: app.yaml (Not Recommended for Secrets)**

Only use for non-sensitive values. For secrets, use Method 1.

```yaml
envs:
- key: SMTP2GO_SENDER_NAME
  value: D'Marsians Taekwondo Gym
  scope: RUN_TIME
```

**⚠️ SECURITY WARNING:** Never commit secrets to Git. Always use App Platform dashboard for sensitive values.

---

## Database Setup

### Database Schema Import

1. **Get Database Connection Details:**
   - Go to App Platform → Your App → Database Component
   - Note: Hostname, Username, Password, Database name, Port

2. **Import Schema:**
   - Use `Database/db.sql` file
   - Import via MySQL client, phpMyAdmin, or App Platform console

3. **Verify Import:**
   - Check that all tables are created
   - Verify initial data (if any) is imported

### Database Connection

The application uses `config.php` which:
- Reads environment variables via `getenv()`
- Creates MySQLi connection
- Handles connection errors gracefully

**Connection String Format:**
```
Host: [DB_HOST]
Port: [DB_PORT]
Database: [DB_NAME]
Username: [DB_USER]
Password: [DB_PASS]
```

### Database Tables

Key tables in the system:
- `admin_accounts` - Admin user accounts
- `students` - Student records
- `payments` - Payment records
- `dues` - Dues tracking
- `enrollment_requests` - Enrollment requests
- `posts` - News/blog posts
- `activity_log` - Activity logging
- `trial_sessions` - Trial session registrations

---

## File Uploads & Storage

### Current Implementation

- **Upload Directory:** `uploads/posts/`
- **Upload Handler:** `post_operations.php`
- **File Types:** Images (jpg, png, gif, jfif, etc.)

### ⚠️ CRITICAL ISSUE: Ephemeral Storage

**Problem:** App Platform uses ephemeral file systems. Files uploaded to `uploads/` will be **LOST** when:
- App restarts
- App redeploys
- Container is recreated

### Solutions

#### Option 1: DigitalOcean Spaces (Recommended)

**Setup:**
1. Create a DigitalOcean Space (Object Storage)
2. Install AWS SDK for PHP (S3-compatible)
3. Modify upload code to save to Spaces

**Implementation Steps:**
```bash
composer require aws/aws-sdk-php
```

Modify `post_operations.php`:
```php
use Aws\S3\S3Client;

// Initialize S3 client
$s3Client = new S3Client([
    'version' => 'latest',
    'region'  => 'nyc3', // Your Space region
    'endpoint' => 'https://nyc3.digitaloceanspaces.com',
    'credentials' => [
        'key'    => getenv('SPACES_KEY'),
        'secret' => getenv('SPACES_SECRET'),
    ],
]);

// Upload to Spaces instead of local filesystem
$result = $s3Client->putObject([
    'Bucket' => 'your-space-name',
    'Key'    => 'posts/' . $file_name,
    'Body'   => fopen($_FILES['image']['tmp_name'], 'rb'),
    'ACL'    => 'public-read',
    'ContentType' => mime_content_type($_FILES['image']['tmp_name']),
]);

$image_path = $result['ObjectURL'];
```

**Environment Variables Needed:**
- `SPACES_KEY` - Spaces access key
- `SPACES_SECRET` - Spaces secret key
- `SPACES_NAME` - Space name
- `SPACES_REGION` - Space region (e.g., `nyc3`)

#### Option 2: Database Storage (Not Recommended)

Store images as BLOB in database. **Not recommended** due to:
- Database size bloat
- Performance issues
- Backup complexity

#### Option 3: External CDN/Storage

Use services like:
- Cloudinary
- Imgur API
- AWS S3
- Google Cloud Storage

### Temporary Workaround (Development Only)

For initial testing, you can use local storage, but **expect data loss** on redeployments.

**Create uploads directory on startup:**
Add to `public/index.php` or create a startup script:
```php
if (!is_dir(__DIR__ . '/../uploads/posts')) {
    mkdir(__DIR__ . '/../uploads/posts', 0777, true);
}
```

**⚠️ This is NOT a production solution. Use Spaces or external storage.**

---

## Routing & Entry Point

### Current Setup

**Entry Point:** `public/index.php`

**Routing Logic:**
1. Root request (`/`) → `webpage.php`
2. Direct file requests → Serve file if exists
3. PHP files → Execute PHP
4. Static files → Serve with appropriate MIME type
5. 404 → Return 404 error

### How It Works

```
Request: https://your-app.ondigitalocean.app/
→ public/index.php
→ Routes to webpage.php (root)

Request: https://your-app.ondigitalocean.app/admin_login.php
→ public/index.php
→ Routes to admin_login.php

Request: https://your-app.ondigitalocean.app/api/payments.php
→ public/index.php
→ Routes to api/payments.php

Request: https://your-app.ondigitalocean.app/Styles/style.css
→ public/index.php
→ Serves static file with proper MIME type
```

### ✅ Current Implementation is Correct

The `public/index.php` file handles routing correctly for App Platform.

### Alternative: Nginx Configuration

If you need more control, you can create `.platform/nginx.conf`:

```nginx
server {
    listen 8080;
    server_name _;
    root /app;
    index index.php webpage.php;

    # Security headers
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-XSS-Protection "1; mode=block" always;
    add_header X-Content-Type-Options "nosniff" always;

    # PHP files
    location ~ \.php$ {
        fastcgi_pass 127.0.0.1:9000;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }

    # Static files
    location ~* \.(jpg|jpeg|png|gif|ico|css|js|svg|woff|woff2|ttf|eot)$ {
        expires 1y;
        add_header Cache-Control "public, immutable";
    }

    # Block sensitive files
    location ~ /\. {
        deny all;
    }
    location ~ \.(env|log|ini|conf|sql|bak|backup)$ {
        deny all;
    }

    # Default route
    location / {
        try_files $uri $uri/ /webpage.php?$query_string;
    }
}
```

**Note:** App Platform may not use custom nginx configs. The current `public/index.php` approach is more reliable.

---

## PHP Configuration

### Required PHP Extensions

From `composer.json`:
- `ext-mysqli` - MySQL database
- `ext-mbstring` - Multibyte string handling
- `ext-curl` - HTTP requests (for SMTP2GO)
- `ext-gd` - Image processing
- `ext-zip` - ZIP file handling
- `ext-xml` - XML processing
- `ext-json` - JSON processing

**✅ App Platform PHP buildpack includes these by default**

### PHP Settings

Create `.platform/php.ini` or `.user.ini`:

```ini
; File upload settings
upload_max_filesize = 10M
post_max_size = 10M

; Execution settings
max_execution_time = 300
memory_limit = 256M

; Timezone
date.timezone = "Asia/Manila"

; Error reporting (production)
display_errors = Off
log_errors = On
error_log = /tmp/php_errors.log

; Session settings
session.gc_maxlifetime = 7200
session.cookie_httponly = 1
session.cookie_secure = 1  # Enable for HTTPS only
```

**Location:** Root directory or `.platform/` directory

### PHP Version

**Recommended:** PHP 8.1 or 8.2

Set in `app.yaml`:
```yaml
environment_slug: php-8.2  # or php-8.1
```

Or let App Platform auto-detect (usually latest stable).

---

## Security Considerations

### 1. Environment Variables

✅ **DO:**
- Store secrets in App Platform dashboard
- Use environment variables for all sensitive data
- Never commit `.env` files

❌ **DON'T:**
- Put secrets in `app.yaml`
- Commit `config.php` with hardcoded values
- Expose credentials in error messages

### 2. File Access

✅ **Current Protection:**
- `.gitignore` excludes sensitive files
- `public/index.php` serves files from root (not ideal, but works)

⚠️ **Recommendations:**
- Move all PHP files outside `public/` (requires refactoring)
- Use `.platform/nginx.conf` to block sensitive files
- Implement proper access controls

### 3. Database Security

✅ **App Platform Provides:**
- Encrypted connections
- Isolated database instances
- Automatic backups (if enabled)

⚠️ **Additional Steps:**
- Use strong database passwords
- Limit database access to app only
- Regular backups

### 4. HTTPS/SSL

✅ **App Platform Provides:**
- Automatic HTTPS
- Free SSL certificates
- HTTP to HTTPS redirect (configurable)

### 5. Session Security

**Current Implementation:**
- Uses PHP sessions
- No explicit session security settings

**Recommendations:**
Add to `config.php` or startup:
```php
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_secure', 1);  // HTTPS only
ini_set('session.use_strict_mode', 1);
```

### 6. Input Validation

⚠️ **Review Required:**
- All user inputs should be validated
- SQL injection protection (use prepared statements - ✅ already implemented)
- XSS protection (escape output - review needed)

### 7. File Upload Security

**Current Implementation:**
- Basic file type checking
- Unique filename generation

**Recommendations:**
```php
// Add to post_operations.php
$allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
$max_size = 10 * 1024 * 1024; // 10MB

// Validate MIME type
$finfo = finfo_open(FILEINFO_MIME_TYPE);
$mime_type = finfo_file($finfo, $_FILES['image']['tmp_name']);
if (!in_array($mime_type, $allowed_types)) {
    // Reject
}

// Validate file size
if ($_FILES['image']['size'] > $max_size) {
    // Reject
}

// Scan for malware (if possible)
```

---

## Troubleshooting

### Issue: "500 Internal Server Error"

**Check:**
1. **Runtime Logs:**
   - App Platform → Your App → Runtime Logs
   - Look for PHP errors

2. **Build Logs:**
   - App Platform → Your App → Build Logs
   - Check for build failures

3. **Common Causes:**
   - Missing environment variables
   - Database connection failure
   - PHP syntax errors
   - Missing dependencies

**Fix:**
```bash
# Check environment variables
# Verify database connection
# Review error logs
```

### Issue: "Database Connection Failed"

**Check:**
1. Database component is created and running
2. Environment variables are set correctly
3. Database credentials are correct
4. Database schema is imported

**Fix:**
1. Verify database component status
2. Check environment variables in dashboard
3. Test connection using database console
4. Re-import schema if needed

### Issue: "File Upload Not Working"

**Causes:**
1. Uploads directory doesn't exist
2. Permission issues
3. PHP upload settings too restrictive
4. Ephemeral storage (files lost on restart)

**Fix:**
1. Create directory on startup (temporary)
2. Implement DigitalOcean Spaces (permanent solution)
3. Check PHP upload settings

### Issue: "404 Not Found"

**Check:**
1. `public/index.php` exists
2. Routing logic is correct
3. File paths are correct

**Fix:**
1. Verify `public/index.php` exists
2. Check routing logic
3. Test with direct file access

### Issue: "Composer Dependencies Not Installed"

**Check:**
1. `composer.json` exists
2. Build command includes `composer install`

**Fix:**
Add to build command in App Platform:
```bash
composer install --no-dev --optimize-autoloader
```

### Issue: "Environment Variables Not Loading"

**Check:**
1. Variables set in App Platform dashboard
2. Variable names match code expectations
3. Scope is set to `RUN_TIME`

**Fix:**
1. Verify variables in dashboard
2. Check variable names (case-sensitive)
3. Restart app after adding variables

### Viewing Logs

**Runtime Logs:**
- App Platform → Your App → Runtime Logs
- Real-time application logs
- PHP errors and warnings

**Build Logs:**
- App Platform → Your App → Build Logs
- Build process output
- Dependency installation logs

**Database Logs:**
- App Platform → Database Component → Logs
- Database connection logs
- Query logs (if enabled)

---

## Recommendations & Suggestions

### 🔴 Critical (Must Address)

1. **File Upload Storage**
   - **Current:** Local filesystem (ephemeral)
   - **Problem:** Files lost on redeploy
   - **Solution:** Implement DigitalOcean Spaces
   - **Priority:** HIGH - Required for production

2. **Environment Variables for Secrets**
   - **Current:** Some may be hardcoded
   - **Problem:** Security risk
   - **Solution:** Move all secrets to App Platform dashboard
   - **Priority:** HIGH - Security critical

3. **Database Backups**
   - **Current:** Manual backups
   - **Problem:** Risk of data loss
   - **Solution:** Enable automatic backups in App Platform
   - **Priority:** HIGH - Data protection

### 🟡 Important (Should Address)

4. **Application Structure**
   - **Current:** All files in root, `public/` for entry point
   - **Problem:** Security risk (PHP files accessible)
   - **Solution:** Refactor to proper MVC structure
   - **Priority:** MEDIUM - Long-term improvement

5. **Error Handling**
   - **Current:** Basic error handling
   - **Problem:** May expose sensitive information
   - **Solution:** Implement proper error handling and logging
   - **Priority:** MEDIUM - Security and debugging

6. **Session Security**
   - **Current:** Basic PHP sessions
   - **Problem:** May be vulnerable to session hijacking
   - **Solution:** Implement secure session settings
   - **Priority:** MEDIUM - Security

7. **Input Validation**
   - **Current:** Some validation exists
   - **Problem:** May have gaps
   - **Solution:** Comprehensive input validation
   - **Priority:** MEDIUM - Security

8. **Monitoring & Alerts**
   - **Current:** Manual monitoring
   - **Problem:** Issues may go unnoticed
   - **Solution:** Set up monitoring and alerts
   - **Priority:** MEDIUM - Operations

### 🟢 Nice to Have (Future Improvements)

9. **CDN for Static Assets**
   - **Current:** Served from app
   - **Benefit:** Faster load times
   - **Solution:** Use DigitalOcean CDN or Cloudflare
   - **Priority:** LOW - Performance optimization

10. **Caching**
    - **Current:** No caching
    - **Benefit:** Better performance
    - **Solution:** Implement Redis or file caching
    - **Priority:** LOW - Performance

11. **Database Optimization**
    - **Current:** Basic queries
    - **Benefit:** Faster queries
    - **Solution:** Add indexes, optimize queries
    - **Priority:** LOW - Performance

12. **Code Organization**
    - **Current:** Flat structure
    - **Benefit:** Better maintainability
    - **Solution:** Refactor to MVC or similar pattern
    - **Priority:** LOW - Code quality

13. **Testing**
    - **Current:** Manual testing
    - **Benefit:** Catch bugs early
    - **Solution:** Implement automated tests
    - **Priority:** LOW - Quality assurance

14. **Documentation**
    - **Current:** Basic documentation
    - **Benefit:** Easier maintenance
    - **Solution:** Comprehensive API and code documentation
    - **Priority:** LOW - Maintainability

### 📋 Implementation Priority

**Phase 1 (Before Production):**
1. ✅ File upload storage (Spaces)
2. ✅ Environment variables security
3. ✅ Database backups
4. ✅ Error handling improvements

**Phase 2 (Post-Launch):**
5. Session security
6. Input validation review
7. Monitoring setup

**Phase 3 (Future):**
8. Code refactoring
9. Performance optimizations
10. Testing implementation

### 💡 Quick Wins

1. **Enable Database Backups:**
   - App Platform → Database → Settings → Enable Backups

2. **Set Up Monitoring:**
   - App Platform → Monitoring → Enable

3. **Add Health Check Endpoint:**
   - Create `health.php` for monitoring
   - Returns 200 if app is healthy

4. **Improve Error Messages:**
   - Don't expose sensitive info
   - Log errors properly

---

## Post-Deployment Checklist

After successful deployment:

- [ ] Application is accessible via App Platform URL
- [ ] Database connection working
- [ ] Admin login functional
- [ ] File uploads working (if Spaces implemented)
- [ ] Email sending working (test with trial registration)
- [ ] All environment variables set correctly
- [ ] Database schema imported
- [ ] SSL/HTTPS working (automatic)
- [ ] Error logging enabled
- [ ] Database backups enabled
- [ ] Monitoring enabled (optional)
- [ ] Custom domain configured (if applicable)
- [ ] Test all major features:
  - [ ] Student enrollment
  - [ ] Payment processing
  - [ ] Post creation with image upload
  - [ ] Admin dashboard
  - [ ] Trial session registration

---

## Additional Resources

### DigitalOcean Documentation

- [App Platform Documentation](https://docs.digitalocean.com/products/app-platform/)
- [App Platform PHP Guide](https://docs.digitalocean.com/products/app-platform/how-to/deploy-php-apps/)
- [Spaces Documentation](https://docs.digitalocean.com/products/spaces/)
- [Database Management](https://docs.digitalocean.com/products/databases/)

### PHP Resources

- [PHP Documentation](https://www.php.net/docs.php)
- [PHP Best Practices](https://www.php.net/manual/en/security.php)

### Support

- DigitalOcean Support: https://www.digitalocean.com/support
- Community: https://www.digitalocean.com/community

---

## Summary

This guide covers deploying your native PHP application to DigitalOcean App Platform. Key points:

1. ✅ **App Platform Configuration:** Use `app.yaml` for app spec
2. ✅ **Entry Point:** `public/index.php` handles routing
3. ✅ **Environment Variables:** Set in App Platform dashboard
4. ✅ **Database:** Add as component, import schema
5. ⚠️ **File Uploads:** Implement Spaces for persistent storage
6. ✅ **PHP Configuration:** Use `.platform/php.ini` or `.user.ini`
7. ⚠️ **Security:** Review and implement recommendations

**Next Steps:**
1. Review this guide
2. Address critical recommendations (especially file storage)
3. Test deployment in staging environment
4. Deploy to production
5. Monitor and optimize

---

**Last Updated:** 2025-01-XX  
**Version:** 1.0  
**Maintained By:** Development Team



