# Video Compression Guide
## Reduce Video File Size for Web Deployment

Your current video file is **488.75 MB**, which is too large for Git. This guide shows you how to compress it to a more manageable size.

---

## Quick Options

### Option 1: FFmpeg (Recommended - Best Quality)
**Best for:** Maximum control and quality  
**Compression:** Can reduce to 50-100 MB with good quality

### Option 2: HandBrake (Easiest - GUI)
**Best for:** User-friendly interface  
**Compression:** Can reduce to 50-150 MB

### Option 3: Online Tools (Fastest)
**Best for:** Quick compression without installing software  
**Compression:** Varies by tool

---

## Method 1: Using FFmpeg (PowerShell Script)

### Step 1: Install FFmpeg

**Option A: Using Chocolatey (Recommended)**
```powershell
choco install ffmpeg
```

**Option B: Using winget**
```powershell
winget install ffmpeg
```

**Option C: Manual Installation**
1. Download from: https://ffmpeg.org/download.html
2. Extract to a folder (e.g., `C:\ffmpeg`)
3. Add to PATH: Add `C:\ffmpeg\bin` to your system PATH

### Step 2: Run Compression Script

```powershell
.\compress_video.ps1
```

This will create: `Video\quality_restoration_20251105174029661_compressed.mp4`

### Step 3: Review and Replace

1. **Check the compressed video quality**
2. **If satisfied, replace the original:**
   ```powershell
   Move-Item -Force "Video\quality_restoration_20251105174029661_compressed.mp4" "Video\quality_restoration_20251105174029661.mp4"
   ```

### Manual FFmpeg Commands

If you prefer manual control:

**High Quality (Larger file ~100-150 MB):**
```powershell
ffmpeg -i Video\quality_restoration_20251105174029661.mp4 -c:v libx264 -crf 23 -preset medium -c:a aac -b:a 128k -movflags +faststart Video\quality_restoration_20251105174029661_compressed.mp4
```

**Balanced (Medium file ~50-100 MB):**
```powershell
ffmpeg -i Video\quality_restoration_20251105174029661.mp4 -c:v libx264 -crf 28 -preset medium -c:a aac -b:a 128k -movflags +faststart Video\quality_restoration_20251105174029661_compressed.mp4
```

**Maximum Compression (Smaller file ~20-50 MB):**
```powershell
ffmpeg -i Video\quality_restoration_20251105174029661.mp4 -c:v libx264 -crf 32 -preset slow -vf "scale=1920:-1" -c:a aac -b:a 96k -movflags +faststart Video\quality_restoration_20251105174029661_compressed.mp4
```

**Settings Explained:**
- `-crf 28`: Quality factor (18-28, lower = better quality)
- `-preset medium`: Speed vs compression (slow = better compression)
- `-vf "scale=1920:-1"`: Resize to 1920px width (optional, for max compression)
- `-b:a 128k`: Audio bitrate
- `-movflags +faststart`: Enables web streaming

---

## Method 2: Using HandBrake (GUI)

### Step 1: Download HandBrake
- Download from: https://handbrake.fr/
- Install the application

### Step 2: Compress Video

1. **Open HandBrake**
2. **Click "Open Source"** → Select your video file
3. **Choose Preset:** "Fast 1080p30" or "Web/Google 1080p30"
4. **Adjust Settings (Optional):**
   - **Video Codec:** H.264 (x264)
   - **Quality:** RF 28 (or adjust slider)
   - **Framerate:** Same as source
   - **Resolution:** 1920x1080 (or original)
5. **Set Destination:** Choose output location
6. **Click "Start Encode"**

### Step 3: Review and Replace
- Check quality
- Replace original file if satisfied

---

## Method 3: Online Tools

### Recommended Online Compressors:

1. **TinyVideo** (https://tinyvid.io/)
   - Free, no account needed
   - Processes in browser (privacy-friendly)
   - Good compression ratios

2. **Zight Video Compressor** (https://zight.com/tools/video-compressor/)
   - Free, no account needed
   - Simple drag-and-drop interface

3. **CloudConvert** (https://cloudconvert.com/)
   - Free tier available
   - Multiple format support

**Note:** For 488 MB file, upload/download may take time depending on your internet speed.

---

## Target File Sizes

| Quality Level | Target Size | Use Case |
|--------------|-------------|----------|
| **High Quality** | 100-150 MB | Best for hero videos, high-end displays |
| **Balanced** | 50-100 MB | Recommended for web (good quality/size ratio) |
| **Compressed** | 20-50 MB | Good for slower connections, mobile |
| **Maximum** | 10-30 MB | Minimal quality, fastest loading |

**Recommendation:** Aim for **50-100 MB** for a hero video. This provides good quality while being manageable for Git.

---

## After Compression

### Option A: Commit to Git (If < 100 MB)

If compressed file is under 100 MB:

1. **Remove Video/ from .gitignore temporarily:**
   ```powershell
   # Edit .gitignore, comment out or remove: Video/
   ```

2. **Add and commit:**
   ```powershell
   git add Video/
   git commit -m "Add compressed hero video"
   git push origin main
   ```

3. **Update webpage.php** (if using different filename):
   ```php
   $heroVideoUrl = 'Video/quality_restoration_20251105174029661_compressed.mp4';
   ```

### Option B: Use DigitalOcean Spaces (Recommended)

Even after compression, using Spaces is recommended:

1. Upload compressed video to Spaces
2. Set `HERO_VIDEO_URL` environment variable
3. Keep Video/ in `.gitignore`

**Benefits:**
- Faster loading (CDN)
- No Git repository bloat
- Better for future updates

---

## Troubleshooting

### FFmpeg Not Found
- Ensure FFmpeg is installed and in PATH
- Restart PowerShell after installation
- Verify: `ffmpeg -version`

### Compression Too Slow
- Use `-preset fast` instead of `medium` or `slow`
- Lower quality: increase `-crf` value (e.g., 30-32)

### File Still Too Large
- Reduce resolution: `-vf "scale=1280:-1"` (720p)
- Lower quality: `-crf 32` or higher
- Reduce audio bitrate: `-b:a 96k`

### Quality Too Low
- Increase quality: `-crf 23` or lower
- Keep original resolution
- Use `-preset slow` for better compression

---

## Comparison: Compression vs Spaces

| Method | Pros | Cons |
|--------|------|------|
| **Compress + Git** | Simple, no external service | Still large, slows Git operations |
| **Compress + Spaces** | Fast loading, CDN, scalable | Requires Spaces setup |
| **Original + Spaces** | Best quality | Large upload, higher bandwidth costs |

**Recommendation:** Compress to 50-100 MB, then upload to Spaces for best balance.

---

## Quick Reference

**Compress with FFmpeg (Balanced):**
```powershell
ffmpeg -i Video\quality_restoration_20251105174029661.mp4 -c:v libx264 -crf 28 -preset medium -c:a aac -b:a 128k -movflags +faststart Video\quality_restoration_20251105174029661_compressed.mp4
```

**Check file size:**
```powershell
(Get-Item Video\quality_restoration_20251105174029661_compressed.mp4).Length / 1MB
```

**Replace original:**
```powershell
Move-Item -Force Video\quality_restoration_20251105174029661_compressed.mp4 Video\quality_restoration_20251105174029661.mp4
```

