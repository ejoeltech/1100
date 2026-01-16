# Script to rename bluedotserp to 1100erp
# This script will:
# 1. Replace all /bluedotserp/ paths with /1100erp/
# 2. Rename the folder from bluedotserp to 1100erp

Write-Host "=== 1100-ERP Path Update Script ===" -ForegroundColor Cyan
Write-Host ""

# Set paths
$oldPath = "C:\xampp\htdocs\bluedotserp"
$newPath = "C:\xampp\htdocs\1100erp"

# Check if old folder exists
if (-not (Test-Path $oldPath)) {
    Write-Host "ERROR: Source folder not found: $oldPath" -ForegroundColor Red
    exit 1
}

# Check if new folder already exists
if (Test-Path $newPath) {
    Write-Host "ERROR: Destination folder already exists: $newPath" -ForegroundColor Red
    Write-Host "Please remove or rename the existing folder first." -ForegroundColor Yellow
    exit 1
}

Write-Host "Step 1: Replacing all /bluedotserp/ paths with /1100erp/..." -ForegroundColor Green

# Get all PHP, HTML, and JS files
$files = Get-ChildItem -Path $oldPath -Include *.php,*.html,*.js -Recurse -File

$fileCount = 0
$replaceCount = 0

foreach ($file in $files) {
    try {
        $content = Get-Content $file.FullName -Raw -ErrorAction Stop
        
        if ($content -match '/bluedotserp/') {
            $newContent = $content -replace '/bluedotserp/', '/1100erp/'
            Set-Content -Path $file.FullName -Value $newContent -NoNewline
            $fileCount++
            
            # Count replacements
            $matches = ([regex]::Matches($content, '/bluedotserp/')).Count
            $replaceCount += $matches
            
            Write-Host "  Updated: $($file.Name) ($matches replacements)" -ForegroundColor Gray
        }
    } catch {
        Write-Host "  WARNING: Could not process $($file.Name): $_" -ForegroundColor Yellow
    }
}

Write-Host ""
Write-Host "Summary: Updated $fileCount files with $replaceCount path replacements" -ForegroundColor Green
Write-Host ""

Write-Host "Step 2: Renaming folder from bluedotserp to 1100erp..." -ForegroundColor Green

try {
    Rename-Item -Path $oldPath -NewName "1100erp" -ErrorAction Stop
    Write-Host "SUCCESS: Folder renamed successfully!" -ForegroundColor Green
} catch {
    Write-Host "ERROR: Failed to rename folder: $_" -ForegroundColor Red
    Write-Host "You may need to close any programs accessing the folder (VS Code, browsers, etc.)" -ForegroundColor Yellow
    exit 1
}

Write-Host ""
Write-Host "=== COMPLETE ===" -ForegroundColor Cyan
Write-Host "Project has been successfully renamed to 1100-ERP" -ForegroundColor Green
Write-Host ""
Write-Host "New project location: $newPath" -ForegroundColor White
Write-Host "New URL: http://localhost/1100erp/" -ForegroundColor White
Write-Host ""
Write-Host "Next steps:" -ForegroundColor Yellow
Write-Host "1. Update your browser bookmarks" -ForegroundColor White
Write-Host "2. Clear browser cache" -ForegroundColor White
Write-Host "3. Access the new URL: http://localhost/1100erp/" -ForegroundColor White
Write-Host ""
