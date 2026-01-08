# Quick Start: Video Storage on DigitalOcean

## The Problem
Your video file (489MB) is too large to commit to Git and DigitalOcean App Platform uses ephemeral storage.

## The Solution
Store the video on DigitalOcean Spaces and reference it via URL.

---

## Quick Steps (5 minutes)

### 1. Create DigitalOcean Space
- Go to: https://cloud.digitalocean.com/spaces
- Click **Create a Space**
- Name: `dmarsians-media`
- Region: `nyc3` (or closest to your app)
- Click **Create**

### 2. Upload Video
- Open your Space in dashboard
- Click **Upload**
- Create folder: `videos/`
- Upload: `Video/quality_restoration_20251105174029661.mp4`
- Set file to **Public**
- Copy the **Public URL**

### 3. Set Environment Variable
- Go to: App Platform → Your App → Settings → Environment Variables
- Add variable:
  - **Key**: `HERO_VIDEO_URL`
  - **Value**: (paste the URL you copied)
  - **Scope**: `RUN_TIME`
- Click **Save**

### 4. Deploy
The code is already updated! Just commit and push:
```bash
git add .
git commit -m "Add Spaces support for video"
git push origin main
```

### 5. Test
- Wait for deployment to complete
- Visit your site
- Video should load from Spaces!

---

## That's It! ✅

The video will now:
- ✅ Load from DigitalOcean Spaces (CDN)
- ✅ Not be committed to Git (already in `.gitignore`)
- ✅ Persist across deployments
- ✅ Work in both production and local development

---

## Need More Details?

See `VIDEO_STORAGE_SETUP.md` for:
- Detailed instructions
- Troubleshooting
- Cost estimates
- Alternative upload methods
























