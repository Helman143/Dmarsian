# Video Compression Script for Windows PowerShell
# Compresses MP4 video files using FFmpeg
# 
# Usage: .\compress_video.ps1

$inputVideo = "Video\quality_restoration_20251105174029661.mp4"
$outputVideo = "Video\quality_restoration_20251105174029661_compressed.mp4"

# Check if input file exists
if (-not (Test-Path $inputVideo)) {
    Write-Host "ERROR: Video file not found: $inputVideo" -ForegroundColor Red
    exit 1
}

# Get original file size
$originalSize = (Get-Item $inputVideo).Length / 1MB
Write-Host "Original file size: $([math]::Round($originalSize, 2)) MB" -ForegroundColor Yellow

# Check if FFmpeg is installed
$ffmpegPath = "ffmpeg"
try {
    $null = Get-Command ffmpeg -ErrorAction Stop
    Write-Host "FFmpeg found!" -ForegroundColor Green
} catch {
    Write-Host "ERROR: FFmpeg is not installed or not in PATH" -ForegroundColor Red
    Write-Host ""
    Write-Host "Please install FFmpeg:" -ForegroundColor Yellow
    Write-Host "1. Download from: https://ffmpeg.org/download.html" -ForegroundColor Cyan
    Write-Host "2. Or use Chocolatey: choco install ffmpeg" -ForegroundColor Cyan
    Write-Host "3. Or use winget: winget install ffmpeg" -ForegroundColor Cyan
    Write-Host ""
    Write-Host "After installation, add FFmpeg to your PATH or restart PowerShell"
    exit 1
}

Write-Host ""
Write-Host "Starting compression..." -ForegroundColor Yellow
Write-Host "This may take several minutes depending on your CPU..." -ForegroundColor Yellow
Write-Host ""

# Compression settings:
# - crf 28: Quality factor (lower = better quality, higher = smaller file)
#   Range: 18-28 (18 = high quality, 28 = good compression)
# - preset medium: Encoding speed (slower = better compression)
# - vcodec libx264: H.264 codec (widely supported)
# - acodec aac: Audio codec
# - movflags +faststart: Enables web streaming (video can start playing before fully downloaded)

$ffmpegArgs = @(
    "-i", $inputVideo,
    "-c:v", "libx264",
    "-crf", "28",
    "-preset", "medium",
    "-c:a", "aac",
    "-b:a", "128k",
    "-movflags", "+faststart",
    "-y",
    $outputVideo
)

try {
    & $ffmpegPath $ffmpegArgs 2>&1 | Out-Null
    
    if (Test-Path $outputVideo) {
        $newSize = (Get-Item $outputVideo).Length / 1MB
        $savings = $originalSize - $newSize
        $percentReduction = ($savings / $originalSize) * 100
        
        Write-Host ""
        Write-Host "✅ Compression completed!" -ForegroundColor Green
        Write-Host "Original size: $([math]::Round($originalSize, 2)) MB" -ForegroundColor White
        Write-Host "Compressed size: $([math]::Round($newSize, 2)) MB" -ForegroundColor White
        Write-Host "Space saved: $([math]::Round($savings, 2)) MB ($([math]::Round($percentReduction, 1))%)" -ForegroundColor Green
        Write-Host ""
        Write-Host "Compressed file: $outputVideo" -ForegroundColor Cyan
        Write-Host ""
        Write-Host "Next steps:" -ForegroundColor Yellow
        Write-Host "1. Review the compressed video quality" -ForegroundColor White
        Write-Host "2. If satisfied, replace the original:" -ForegroundColor White
        Write-Host "   Move-Item -Force '$outputVideo' '$inputVideo'" -ForegroundColor Cyan
        Write-Host "3. Or use the compressed version and update webpage.php" -ForegroundColor White
    } else {
        Write-Host "ERROR: Compression failed - output file not created" -ForegroundColor Red
        exit 1
    }
} catch {
    Write-Host "ERROR: Compression failed: $_" -ForegroundColor Red
    exit 1
}
























