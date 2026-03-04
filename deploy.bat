@echo off
REM Prepare project for Hostinger deployment
REM Run this locally before uploading files

echo [1/4] Installing production dependencies...
call composer install --no-dev --optimize-autoloader
if errorlevel 1 goto error

echo [2/4] Building frontend assets...
call npm run build
if errorlevel 1 goto error

echo [3/4] Removing public/hot (if exists)...
if exist public\hot del public\hot

echo [4/4] Caching config, routes, views...
call php artisan config:cache
call php artisan route:cache
call php artisan view:cache
call php artisan event:cache

echo.
echo Done. You can now upload to Hostinger.
echo See DEPLOYMENT_CHECKLIST.md for server setup.
goto end

:error
echo Deployment preparation failed.
exit /b 1

:end
