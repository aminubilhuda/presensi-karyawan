#!/bin/bash

# Tetapkan direktori aplikasi
APP_DIR="/home3/abdinega/absensi.abdinegara.com"

# Log untuk tracking
LOG_FILE="$APP_DIR/deployment-log.txt"
echo "$(date) - Memulai deployment" >> "$LOG_FILE"

# Pindah ke direktori aplikasi
cd "$APP_DIR" || { echo "Gagal pindah ke direktori $APP_DIR"; exit 1; }

# Pull kode terbaru dari GitHub
echo "$(date) - Mengambil kode terbaru dari GitHub..." >> "$LOG_FILE"
git pull origin master >> "$LOG_FILE" 2>&1
if [ $? -ne 0 ]; then
  echo "$(date) - ERROR: Git pull gagal" >> "$LOG_FILE"
  exit 1
fi

# Install dependencies
echo "$(date) - Menginstall dependencies Composer..." >> "$LOG_FILE"
composer install --no-dev --optimize-autoloader >> "$LOG_FILE" 2>&1

# Jalankan migrasi database
echo "$(date) - Menjalankan migrasi database..." >> "$LOG_FILE"
php artisan migrate --force >> "$LOG_FILE" 2>&1

# Optimalkan aplikasi
echo "$(date) - Mengoptimasi aplikasi..." >> "$LOG_FILE"
php artisan cache:clear >> "$LOG_FILE" 2>&1
php artisan config:clear >> "$LOG_FILE" 2>&1
php artisan route:cache >> "$LOG_FILE" 2>&1
php artisan view:cache >> "$LOG_FILE" 2>&1
php artisan optimize >> "$LOG_FILE" 2>&1

echo "$(date) - Deployment berhasil diselesaikan" >> "$LOG_FILE"
echo "Deployment berhasil!"
exit 0
