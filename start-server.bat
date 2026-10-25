@echo off
title LAN Speedtest & Intranet Assessment Pro
echo =============================================================
echo   Menjalankan LAN Speedtest Assessment Server...
echo =============================================================
echo Memeriksa instalasi Node.js...
where node >nul 2>nul
if %ERRORLEVEL% EQU 0 (
    echo Node.js ditemukan! Menjalankan high-performance server.js...
    node server.js
) else (
    echo Node.js tidak ditemukan, memeriksa PHP CLI...
    where php >nul 2>nul
    if %ERRORLEVEL% EQU 0 (
        echo PHP ditemukan! Menjalankan PHP Built-in Server pada port 3000...
        echo Akses di browser: http://localhost:3000/
        php -S 0.0.0.0:3000
    ) else (
        echo [ERROR] Tidak ditemukan Node.js maupun PHP pada PATH.
        echo Harap jalankan via XAMPP Apache atau pasang Node.js/PHP.
        pause
    )
)
pause
