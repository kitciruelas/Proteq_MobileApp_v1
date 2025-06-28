@echo off
echo ========================================
echo Proteq Backend Setup Guide
echo ========================================
echo.
echo 1. Install XAMPP from: https://www.apachefriends.org/
echo 2. Start Apache and MySQL in XAMPP Control Panel
echo 3. Open phpMyAdmin: http://localhost/phpmyadmin
echo 4. Create database: proteq_db
echo 5. Import proteq_db.sql file
echo 6. Copy this backend folder to: C:\xampp\htdocs\proteq-backend\
echo 7. Test API: http://localhost/proteq-backend/api/test.php
echo.
echo API Base URL: http://localhost/proteq-backend/api/
echo.
echo Press any key to open XAMPP website...
pause
start https://www.apachefriends.org/ 