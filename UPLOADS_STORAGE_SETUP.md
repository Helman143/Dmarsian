# Uploads Storage Setup Guide
## DigitalOcean Spaces for Post Images

**Current Status:** Existing images (5.02 MB) are committed to Git and will deploy.  
**⚠️ CRITICAL:** New uploads will be **LOST** on redeploy because DigitalOcean App Platform uses ephemeral storage.

---

## The Problem

DigitalOcean App Platform uses **ephemeral storage**:
- Files uploaded to `uploads/posts/` will be **LOST** when:
  - App restarts
  - App redeploys  
  - Container is recreated
- Even if you commit uploads to Git, **new uploads** after deployment will disappear

---

## Solution: DigitalOcean Spaces (Recommended)

Use the same DigitalOcean Space you created for the video to store post images.

### Step 1: Install AWS SDK

```bash
composer require aws/aws-sdk-php
```

### Step 2: Update post_operations.php

The code will be updated to:
- Upload new images to DigitalOcean Spaces
- Store Spaces URLs in database
- Fall back to local storage for development

### Step 3: Migrate Existing Images

Upload existing images from `uploads/posts/` to Spaces:
- Use the same Space as your video
- Upload to `posts/` folder
- Update database records with Spaces URLs

### Step 4: Set Environment Variables

Already set for video:
- `SPACES_KEY` ✅
- `SPACES_SECRET` ✅  
- `SPACES_NAME` ✅
- `SPACES_REGION` ✅

---

## Quick Fix (Current)

**What's Working:**
- ✅ Existing 33 images (5.02 MB) are committed to Git
- ✅ Images will deploy with your code
- ✅ Images will display on production

**What's NOT Working:**
- ❌ New uploads after deployment will be lost on redeploy
- ❌ This is a temporary solution

---

## Next Steps

1. **For Now:** Existing images will work ✅
2. **For Production:** Implement DigitalOcean Spaces (see implementation guide)
3. **Migration:** Upload existing images to Spaces and update database

---

## Implementation Status

- [x] Existing images committed to Git
- [ ] AWS SDK installed
- [ ] post_operations.php updated for Spaces
- [ ] Existing images migrated to Spaces
- [ ] Database updated with Spaces URLs

---

**Note:** Keep `uploads/` in `.gitignore` after implementing Spaces - only commit initial images for deployment.





