@echo off
echo ================================================
echo 1100-ERP GitHub Initialization Script
echo ================================================
echo.

REM Navigate to project directory
cd /d C:\xampp\htdocs\bluedotserp

echo Step 1: Creating README.md...
echo # 1100erp > README.md
echo.

echo Step 2: Initializing Git repository...
git init
echo.

echo Step 3: Adding README.md...
git add README.md
echo.

echo Step 4: Creating first commit...
git commit -m "first commit"
echo.

echo Step 5: Renaming branch to main...
git branch -M main
echo.

echo Step 6: Adding remote origin...
git remote add origin https://github.com/ejoeltech/1100erp.git
echo.

echo Step 7: Pushing to GitHub...
git push -u origin main
echo.

echo ================================================
echo Git initialization complete!
echo ================================================
echo.
echo Repository: https://github.com/ejoeltech/1100erp
echo.
pause
