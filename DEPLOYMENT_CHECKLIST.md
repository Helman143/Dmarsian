# DigitalOcean App Platform Deployment Checklist

Use this checklist to ensure a smooth deployment process.

---

## Pre-Deployment Preparation

### Code & Repository
- [ ] All code committed to Git
- [ ] `.env` file NOT committed (in `.gitignore`)
- [ ] `config.php` NOT committed (in `.gitignore`)
- [ ] `composer.json` and `composer.lock` committed
- [ ] `vendor/` directory NOT committed
- [ ] `uploads/` directory NOT committed
- [ ] Test files removed or excluded
- [ ] Code pushed to GitHub repository

### Configuration Files
- [ ] `app.yaml` exists and configured
- [ ] `public/index.php` exists (entry point)
- [ ] `env-loader.php` exists
- [ ] `composer.json` has all dependencies
- [ ] `.gitignore` properly configured

### Database
- [ ] Database dump available (`Database/db.sql`)
- [ ] Database schema reviewed
- [ ] Initial data prepared (if needed)
- [ ] Database credentials documented (not in code)

### Environment Variables
- [ ] List of all required environment variables prepared
- [ ] SMTP2GO API key obtained
- [ ] Email configuration ready
- [ ] All sensitive values documented

---

## DigitalOcean App Platform Setup

### Account & Access
- [ ] DigitalOcean account created
- [ ] Billing information added
- [ ] GitHub account connected
- [ ] Repository access granted

### App Creation
- [ ] App created in App Platform
- [ ] GitHub repository connected
- [ ] Branch selected (main)
- [ ] Autodeploy enabled (optional)

### App Configuration
- [ ] `app.yaml` reviewed and updated
- [ ] PHP version selected (8.1 or 8.2 recommended)
- [ ] Instance size selected (basic-xxs to start)
- [ ] Build command configured
- [ ] Run command configured
- [ ] HTTP port set to 8080

### Database Component
- [ ] Database component added
- [ ] MySQL 8 selected
- [ ] Database name matches `app.yaml` (`db`)
- [ ] Database plan selected
- [ ] Automatic backups enabled
- [ ] Backup retention period set (7-30 days)

### Environment Variables
- [ ] `APP_ENV` set to `production`
- [ ] `SMTP2GO_API_KEY` set (in dashboard)
- [ ] `SMTP2GO_SENDER_EMAIL` set (in dashboard)
- [ ] `SMTP2GO_SENDER_NAME` set
- [ ] `ADMIN_BCC_EMAIL` set (in dashboard)
- [ ] Database variables verified (auto-set)

### File Upload Storage (CRITICAL)
- [ ] DigitalOcean Space created (if using Spaces)
- [ ] Spaces credentials obtained
- [ ] `SPACES_KEY` set (in dashboard)
- [ ] `SPACES_SECRET` set (in dashboard)
- [ ] `SPACES_NAME` set (in dashboard)
- [ ] `SPACES_REGION` set (in dashboard)
- [ ] Upload code modified to use Spaces
- [ ] OR: External storage service configured

---

## Deployment

### Initial Deployment
- [ ] App deployed successfully
- [ ] Build logs reviewed (no errors)
- [ ] Runtime logs reviewed (no errors)
- [ ] App URL accessible

### Database Setup
- [ ] Database connection details obtained
- [ ] Database schema imported (`Database/db.sql`)
- [ ] Database tables verified
- [ ] Initial data imported (if needed)
- [ ] Database connection tested

### Configuration Verification
- [ ] Environment variables verified
- [ ] Database connection working
- [ ] PHP configuration applied (if using `.platform/php.ini`)
- [ ] Health check endpoint working (`/health.php`)

---

## Post-Deployment Testing

### Basic Functionality
- [ ] Homepage loads (`/` or `/webpage.php`)
- [ ] Admin login page loads (`/admin_login.php`)
- [ ] Admin login works
- [ ] Admin dashboard loads
- [ ] No PHP errors in logs

### Core Features
- [ ] Student enrollment form works
- [ ] Student enrollment submission works
- [ ] Payment processing works
- [ ] Dues calculation works
- [ ] Post creation works
- [ ] File upload works (if Spaces implemented)
- [ ] Email sending works (test with trial registration)

### Security Checks
- [ ] HTTPS working (automatic)
- [ ] Sensitive files not accessible (`.env`, `config.php`)
- [ ] Error messages don't expose sensitive info
- [ ] Session security working
- [ ] Input validation working

### Performance Checks
- [ ] Page load times acceptable
- [ ] Database queries optimized
- [ ] Static assets loading correctly
- [ ] No memory leaks (monitor over time)

---

## Monitoring & Maintenance

### Monitoring Setup
- [ ] App Platform monitoring enabled
- [ ] Alerts configured for:
  - [ ] High error rates
  - [ ] Database connection failures
  - [ ] High memory/CPU usage
  - [ ] Deployment failures
- [ ] Health check endpoint monitored

### Backup & Recovery
- [ ] Database backups enabled
- [ ] Backup restoration tested
- [ ] Backup schedule verified
- [ ] Backup retention period set
- [ ] Recovery procedure documented

### Documentation
- [ ] Deployment procedure documented
- [ ] Environment variables documented
- [ ] Database credentials documented (secure location)
- [ ] Troubleshooting guide created
- [ ] Team access configured

---

## Critical Issues to Address

### Before Production Launch
- [ ] **File Upload Storage** - Spaces or external storage implemented
- [ ] **Environment Variables** - All secrets in dashboard, not code
- [ ] **Database Backups** - Automatic backups enabled
- [ ] **Error Handling** - Proper error handling implemented
- [ ] **Session Security** - Secure session settings applied

### Post-Launch (First Month)
- [ ] **Input Validation** - Comprehensive review completed
- [ ] **File Upload Security** - Enhanced validation added
- [ ] **Monitoring** - Full monitoring setup
- [ ] **Performance** - Initial optimization completed

---

## Quick Reference

### App Platform URLs
- **Dashboard:** https://cloud.digitalocean.com/apps
- **Your App:** https://[your-app-name].ondigitalocean.app
- **Health Check:** https://[your-app-name].ondigitalocean.app/health.php

### Important Files
- **App Config:** `app.yaml`
- **Entry Point:** `public/index.php`
- **Database Config:** `config.php`
- **PHP Config:** `.platform/php.ini` or `.user.ini`
- **Health Check:** `health.php`

### Environment Variables (Set in Dashboard)
- `SMTP2GO_API_KEY`
- `SMTP2GO_SENDER_EMAIL`
- `ADMIN_BCC_EMAIL`
- `SPACES_KEY` (if using Spaces)
- `SPACES_SECRET` (if using Spaces)
- `SPACES_NAME` (if using Spaces)
- `SPACES_REGION` (if using Spaces)

### Database Variables (Auto-Set)
- `DB_HOST`
- `DB_USER`
- `DB_PASS`
- `DB_NAME`
- `DB_PORT`

---

## Troubleshooting Quick Reference

### App Won't Start
1. Check build logs
2. Check runtime logs
3. Verify `run_command` in `app.yaml`
4. Verify `public/index.php` exists

### Database Connection Failed
1. Verify database component is running
2. Check environment variables
3. Test connection using database console
4. Verify database schema is imported

### File Uploads Not Working
1. Check if Spaces is configured (if using)
2. Verify upload directory exists and is writable
3. Check PHP upload settings
4. Review upload code

### 404 Errors
1. Verify `public/index.php` exists
2. Check routing logic
3. Verify file paths are correct
4. Check App Platform routes configuration

---

## Post-Deployment Sign-Off

- [ ] All checklist items completed
- [ ] All critical issues addressed
- [ ] Application tested and working
- [ ] Team trained on deployment process
- [ ] Documentation complete
- [ ] Monitoring and alerts configured
- [ ] Backups verified
- [ ] Ready for production use

**Deployed By:** _________________  
**Date:** _________________  
**App URL:** _________________  
**Database:** _________________  

---

**Last Updated:** 2025-01-XX
