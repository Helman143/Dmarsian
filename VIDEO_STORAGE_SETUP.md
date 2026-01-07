# Video Storage Setup Guide
## Using DigitalOcean Spaces for Large Video Files

This guide explains how to store your large video file (489MB) on DigitalOcean Spaces instead of committing it to Git.

---

## Why DigitalOcean Spaces?

1. **Git Limitations**: Large files (>100MB) cause issues with Git repositories
2. **Ephemeral Storage**: DigitalOcean App Platform uses ephemeral storage - files are lost on redeploy
3. **Performance**: Spaces provides CDN-like performance with global edge locations
4. **Cost-Effective**: Much cheaper than storing large files in Git or database

---

## Step 1: Create a DigitalOcean Space

1. **Log in to DigitalOcean Dashboard**
   - Go to https://cloud.digitalocean.com
   - Navigate to **Spaces** → **Create a Space**

2. **Configure Your Space**
   - **Name**: `dmarsians-media` (or your preferred name)
   - **Region**: Choose closest to your app (e.g., `nyc3` for New York)
   - **File Listing**: **Private** (recommended) or **Public**
   - Click **Create a Space**

3. **Get Your Access Keys**
   - Go to **API** → **Spaces Keys**
   - Click **Generate New Key**
   - **Name**: `dmarsians-spaces-key`
   - **Access**: **Read and Write**
   - **Save the Access Key and Secret Key** (you won't see the secret again!)

---

## Step 2: Upload Video to Spaces

### Option A: Using DigitalOcean Dashboard (Easiest)

1. Go to your Space in DigitalOcean Dashboard
2. Click **Upload**
3. Create a folder: `videos/`
4. Upload `quality_restoration_20251105174029661.mp4` to `videos/`
5. Set file to **Public** (if you want direct access)
6. Copy the **Public URL** (will look like: `https://dmarsians-media.nyc3.digitaloceanspaces.com/videos/quality_restoration_20251105174029661.mp4`)

### Option B: Using AWS CLI (Recommended for Automation)

1. **Install AWS CLI** (if not installed):
   ```bash
   # Windows (using Chocolatey)
   choco install awscli
   
   # Or download from: https://awscli.amazonaws.com/AWSCLIV2.msi
   ```

2. **Configure AWS CLI for DigitalOcean Spaces**:
   ```bash
   aws configure
   ```
   - **AWS Access Key ID**: Your Spaces Access Key
   - **AWS Secret Access Key**: Your Spaces Secret Key
   - **Default region**: `nyc3` (or your Space region)
   - **Default output format**: `json`

3. **Upload the video**:
   ```bash
   aws s3 cp "Video/quality_restoration_20251105174029661.mp4" \
     s3://dmarsians-media/videos/quality_restoration_20251105174029661.mp4 \
     --endpoint-url=https://nyc3.digitaloceanspaces.com \
     --acl public-read
   ```

### Option C: Using PHP Script (Included)

Use the provided `upload_video_to_spaces.php` script:

1. **Install AWS SDK** (required for the script):
   ```bash
   composer require aws/aws-sdk-php
   ```

2. **Set environment variables** in your `.env` file (see Step 3)

3. **Run the script**:
   ```bash
   php upload_video_to_spaces.php
   ```

**Note**: This script is optional. You can upload manually via the dashboard (Option A) and skip this step.

---

## Step 3: Set Environment Variables in App Platform

1. **Go to App Platform Dashboard**
   - Navigate to your app → **Settings** → **App-Level Environment Variables**

2. **Add These Variables**:

   | Variable | Value | Description |
   |----------|-------|-------------|
   | `HERO_VIDEO_URL` | `https://dmarsians-media.nyc3.digitaloceanspaces.com/videos/quality_restoration_20251105174029661.mp4` | Full URL to your video in Spaces |
   | `SPACES_KEY` | `your_spaces_access_key` | Spaces access key (for future uploads) |
   | `SPACES_SECRET` | `your_spaces_secret_key` | Spaces secret key (for future uploads) |
   | `SPACES_NAME` | `dmarsians-media` | Your Space name |
   | `SPACES_REGION` | `nyc3` | Your Space region |

3. **Click Save**

---

## Step 4: Update Your Code

The code has been updated to:
- Use `HERO_VIDEO_URL` environment variable if available
- Fall back to local `Video/` folder for local development
- Automatically handle both scenarios

**File Updated**: `webpage.php`

---

## Step 5: Verify Setup

1. **Commit and Push Changes**:
   ```bash
   git add .
   git commit -m "Add Spaces support for video storage"
   git push origin main
   ```

2. **Wait for App Platform to Deploy**

3. **Test Your Site**:
   - Visit your app URL
   - Check that the hero video loads from Spaces
   - Open browser DevTools → Network tab
   - Verify video is loading from `digitaloceanspaces.com`

---

## Troubleshooting

### Video Not Loading

1. **Check Environment Variable**:
   - Visit: `https://your-app.ondigitalocean.app/check_env.php`
   - Verify `HERO_VIDEO_URL` is set correctly

2. **Check Spaces File Permissions**:
   - Ensure file is set to **Public** in Spaces dashboard
   - Or verify CORS settings allow your domain

3. **Check Video URL**:
   - Copy the URL from Spaces dashboard
   - Test in browser directly
   - Should download/play the video

### CORS Issues

If you see CORS errors:

1. Go to Spaces → **Settings** → **CORS Configurations**
2. Add CORS rule:
   ```json
   {
     "AllowedOrigins": ["*"],
     "AllowedMethods": ["GET", "HEAD"],
     "AllowedHeaders": ["*"],
     "ExposeHeaders": ["ETag"],
     "MaxAgeSeconds": 3000
   }
   ```

### Local Development

For local development, the code will automatically use the local `Video/` folder if `HERO_VIDEO_URL` is not set. This allows you to develop without Spaces configured locally.

---

## Cost Estimate

**DigitalOcean Spaces Pricing** (as of 2024):
- **Storage**: $5/month for 250GB
- **Bandwidth**: $0.01/GB (first 1TB free per month)
- **Your video**: ~489MB = ~0.5GB

**Estimated Monthly Cost**: ~$5-6 (mostly the base storage cost)

---

## Next Steps

1. ✅ Upload video to Spaces
2. ✅ Set environment variables
3. ✅ Deploy updated code
4. ✅ Test video loading
5. ✅ Remove video from local `Video/` folder (optional, it's already in `.gitignore`)

---

## Additional Resources

- [DigitalOcean Spaces Documentation](https://docs.digitalocean.com/products/spaces/)
- [AWS CLI S3-Compatible Commands](https://docs.digitalocean.com/products/spaces/how-to/upload-files/)
- [Spaces Pricing](https://www.digitalocean.com/pricing/spaces-object-storage)

