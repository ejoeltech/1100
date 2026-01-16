@echo off
echo Renaming folder from bluedotserp to 1100erp...
echo.
echo Please make sure VS Code and all other programs are closed!
echo.
pause

cd C:\xampp\htdocs
ren bluedotserp 1100erp

if exist "C:\xampp\htdocs\1100erp" (
    echo.
    echo SUCCESS! Folder renamed successfully.
    echo.
    echo New location: C:\xampp\htdocs\1100erp
    echo New URL: http://localhost/1100erp/
    echo.
) else (
    echo.
    echo FAILED! Could not rename folder.
    echo Please close VS Code and try again.
    echo.
)

pause
