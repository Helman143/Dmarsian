# DigitalOcean App Platform Deployment Guide

## App Platform vs Droplet Differences

App Platform is a Platform-as-a-Service (PaaS) that handles configuration differently than traditional Apache servers:

- **No direct .htaccess support** - App Platform uses nginx, not Apache
- **Configuration via app.yaml** - Settings are in the app spec file
- **Environment variables** - Use App Platform's environment variable system
- **Buildpacks** - Uses buildpacks to detect and configure PHP

## Disabling .htaccess in App Platform

### Option 1: Remove .htaccess (Recommended)

Since App Platform uses nginx, `.htaccess` files are **ignored**. You can safely remove it:

```bash
# In your local repository
git rm .htaccess
git commit -m "Remove .htaccess for App Platform deployment"
git push
```

### Option 2: Keep .htaccess but Ignore It

App Platform will simply ignore `.htaccess` files, so you can leave it for other deployments.

### Option 3: Use .htaccess for Local, Remove for Production

Add to `.gitignore`:
```
.htaccess
```

Then create `.htaccess.example` as a template.

## App Platform Configuration

### 1. Create app.yaml (App Spec)

Create `app.yaml` in your project root:

```yaml
name: dmarsians-taekwondo
region: nyc
services:
- name: web
  source_dir: /
  github:
    repo: Helman143/Dmarsian
    branch: main
    deploy_on_push: true
  run_command: php -S 0.0.0.0:8080 -t public public/index.php || php -S 0.0.0.0:8080
  environment_slug: php
  instance_count: 1
  instance_size_slug: basic-xxs
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

databases:
- name: db
  engine: MYSQL
  version: "8"
  production: false
```

### 2. Create public/index.php (Entry Point)

App Platform needs a public directory. Create `public/index.php`:

```php
<?php
// public/index.php - Entry point for App Platform

// Get the requested URI
$requestUri = $_SERVER['REQUEST_URI'];
$requestPath = parse_url($requestUri, PHP_URL_PATH);

// Remove leading slash
$requestPath = ltrim($requestPath, '/');

// If root or empty, redirect to webpage.php
if (empty($requestPath) || $requestPath === '/') {
    $requestPath = 'webpage.php';
}

// If file exists in root, serve it
$filePath = __DIR__ . '/../' . $requestPath;
if (file_exists($filePath) && is_file($filePath)) {
    // For PHP files, include them
    if (pathinfo($filePath, PATHINFO_EXTENSION) === 'php') {
        chdir(dirname($filePath));
        require $filePath;
    } else {
        // For static files, serve them
        $mimeTypes = [
            'css' => 'text/css',
            'js' => 'application/javascript',
            'png' => 'image/png',
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'gif' => 'image/gif',
            'svg' => 'image/svg+xml',
            'json' => 'application/json',
        ];
        $ext = pathinfo($filePath, PATHINFO_EXTENSION);
        $mimeType = $mimeTypes[$ext] ?? 'application/octet-stream';
        header('Content-Type: ' . $mimeType);
        readfile($filePath);
    }
    exit;
}

// 404 Not Found
http_response_code(404);
echo "404 - File not found";
```

### 3. Alternative: Use nginx Configuration

Create `.platform/nginx.conf`:

```nginx
server {
    listen 8080;
    server_name _;
    root /app;
    index index.php webpage.php;

    # Security headers (replaces .htaccess headers)
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-XSS-Protection "1; mode=block" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header Referrer-Policy "strict-origin-when-cross-origin" always;

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

    # Default to webpage.php
    location / {
        try_files $uri $uri/ /webpage.php?$query_string;
    }
}
```

## Environment Variables in App Platform

Set these in App Platform dashboard or app.yaml:

1. Go to your App → Settings → App-Level Environment Variables
2. Add:
   - `DB_HOST` - Your database host
   - `DB_USER` - Database username
   - `DB_PASS` - Database password
   - `DB_NAME` - Database name
   - `DB_PORT` - Database port (usually 25060 for App Platform)
   - `SMTP2GO_API_KEY` - Your SMTP key
   - `SMTP2GO_SENDER_EMAIL` - Sender email
   - `SMTP2GO_SENDER_NAME` - Sender name
   - `ADMIN_BCC_EMAIL` - Admin email

## PHP Configuration for App Platform

Create `.platform/php.ini` or use `.user.ini`:

```ini
upload_max_filesize = 10M
post_max_size = 10M
max_execution_time = 300
memory_limit = 256M
date.timezone = "Asia/Manila"
```

## Deployment Steps

### 1. Update app.yaml with your settings

### 2. Commit and Push
```bash
git add app.yaml public/index.php .platform/
git commit -m "Add App Platform configuration"
git push origin main
```

### 3. Deploy via App Platform Dashboard

1. Go to DigitalOcean App Platform
2. Create new app or update existing
3. Connect to your GitHub repository
4. App Platform will auto-detect PHP
5. Set environment variables
6. Deploy

## Key Differences from Droplet

| Feature | Droplet (Apache) | App Platform (nginx) |
|---------|------------------|---------------------|
| .htaccess | ✅ Supported | ❌ Ignored |
| Configuration | .htaccess file | app.yaml + nginx.conf |
| PHP Settings | .htaccess or php.ini | .user.ini or .platform/php.ini |
| Static Files | Apache serves | nginx serves |
| Rewrite Rules | .htaccess | nginx.conf |

## Quick Fix: Remove .htaccess for App Platform

```bash
# Option 1: Remove completely
git rm .htaccess
git commit -m "Remove .htaccess for App Platform"
git push

# Option 2: Rename it
git mv .htaccess .htaccess.droplet
git commit -m "Rename .htaccess for App Platform compatibility"
git push
```

## Testing After Deployment

1. Visit your App Platform URL
2. Test: `https://your-app.ondigitalocean.app/minimal_test.php`
3. Test: `https://your-app.ondigitalocean.app/webpage.php`

## Troubleshooting App Platform

### Check Build Logs
In App Platform dashboard → Runtime Logs

### Check Environment Variables
Settings → App-Level Environment Variables

### Check Database Connection
Ensure database component is created and linked in app.yaml

### PHP Errors
Check Runtime Logs in App Platform dashboard

