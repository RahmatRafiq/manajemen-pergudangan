@echo off
echo Starting Laravel Stock Alert Services...
echo.

echo 1. Starting Queue Worker...
start "Queue Worker" powershell -Command "cd '%cd%'; php artisan queue:work --verbose"

echo 2. Starting Reverb WebSocket Server...
start "Reverb Server" powershell -Command "cd '%cd%'; php artisan reverb:start"

echo 3. Starting Vite Development Server...
start "Vite Dev Server" powershell -Command "cd '%cd%'; npm run dev"

echo.
echo ✅ All services are starting in separate windows...
echo.
echo Services started:
echo   - Queue Worker (processes notifications)
echo   - Reverb Server (WebSocket broadcasting)  
echo   - Vite Dev Server (frontend development)
echo.
echo ⚠️  Keep all windows open for real-time alerts to work!
echo.
echo Now you can:
echo   1. Visit http://localhost:8000/stock-alerts to see alerts
echo   2. Make stock transactions to trigger real-time alerts
echo   3. Check browser console for WebSocket connection status
echo.
pause
