@echo off
echo ========================================
echo PHP Unit Tests Runner
echo ========================================
echo.

cd /d C:\xampp\htdocs\Capstone

echo Checking PHP installation...
if not exist "C:\xampp\php\php.exe" (
    echo ERROR: PHP not found at C:\xampp\php\php.exe
    echo Please update the path in this script or install XAMPP
    pause
    exit /b 1
)

echo PHP found!
echo.

echo ========================================
echo Test 1: Database Connection
echo ========================================
C:\xampp\php\php.exe test_connection.php
echo.

echo ========================================
echo Test 2: Posts Test
echo ========================================
C:\xampp\php\php.exe test_posts.php
echo.

echo ========================================
echo Test 3: Dues Data Test
echo ========================================
C:\xampp\php\php.exe test_dues_data.php
echo.

echo ========================================
echo Test 4: Dashboard Fetch Test
echo ========================================
C:\xampp\php\php.exe test_dashboard_fetch.php
echo.

echo ========================================
echo All tests completed!
echo ========================================
pause

