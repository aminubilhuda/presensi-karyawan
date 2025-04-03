# Panduan CI/CD dari GitHub ke cPanel

Dokumen ini menjelaskan cara mengatur Continuous Integration/Continuous Deployment (CI/CD) dari repositori GitHub ke hosting cPanel untuk proyek Laravel.

## Cara Kerja

Sistem CI/CD ini otomatis men-deploy aplikasi ke cPanel setiap kali ada push ke branch `master`.

### Proses Workflow:

1. GitHub Actions mendeteksi push ke branch `master`
2. Server runner GitHub melakukan:
   - Instalasi dan konfigurasi PHP dan Node.js
   - Instalasi dependensi PHP (Composer) dan JavaScript (npm)
   - Build asset dengan npm
   - Optimasi Laravel
   - Deploy ke cPanel via FTP

## Cara Setup

### 1. Siapkan Secrets di GitHub Repository

Buka repositori GitHub Anda > Settings > Secrets and variables > Actions, lalu tambahkan secrets berikut:

- `FTP_SERVER`: Alamat server FTP cPanel Anda (biasanya `ftp.abdinegara.com`)
- `FTP_USERNAME`: Username FTP cPanel
- `FTP_PASSWORD`: Password FTP cPanel
- `FTP_ROOT_DIR`: Direktori root tempat aplikasi di-deploy (contoh: `public_html/presensi-karyawan`)
- `ENV_FILE`: Seluruh isi file `.env` untuk production

### 2. Buat Direktori .github/workflows

Jika belum ada, buat direktori `.github/workflows` di root repositori Anda.

### 3. Buat File Workflow

Salin konfigurasi workflow ke file `.github/workflows/deploy.yml`:

```yaml
name: Deploy ke cPanel

on:
  push:
    branches: [master]
  workflow_dispatch:

jobs:
  deploy:
    runs-on: ubuntu-latest

    steps:
      # ... (isi sesuai file workflow) ...
```

### 4. Push Perubahan ke GitHub

Commit dan push perubahan ke repositori GitHub Anda:

```bash
git add .github/workflows/deploy.yml
git commit -m "Tambahkan konfigurasi CI/CD GitHub Actions"
git push origin master
```

## Pemecahan Masalah

### Jika Deployment Gagal:

1. Periksa tab "Actions" di repositori GitHub Anda untuk melihat log error
2. Pastikan secrets sudah dikonfigurasi dengan benar
3. Cek izin akses FTP di cPanel
4. Validasi bahwa direktori target di cPanel sudah benar dan memiliki izin tulis

### Tips Keamanan:

1. Jangan pernah menyimpan kredensial FTP atau informasi sensitif lainnya di kode
2. Gunakan secrets GitHub untuk semua informasi sensitif
3. Batasi izin FTP hanya pada direktori yang diperlukan
4. Pertimbangkan untuk menggunakan SSH deployment jika tersedia daripada FTP

## Alternatif: Deploy via Webhook

Proyek ini juga menyediakan opsi deployment melalui webhook GitHub:

1. Upload file `deploy.php` ke server cPanel Anda
2. Konfigurasi `deploy.sh` sesuai kebutuhan
3. Atur webhook di GitHub repository Anda (Settings > Webhooks) dengan URL ke `deploy.php`
4. Sesuaikan "secret" di `deploy.php` dengan "secret" yang Anda tentukan di GitHub webhook 

BERHASIL