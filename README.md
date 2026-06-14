# MAC Billing Deploy

Repository ini berisi source code aplikasi **MAC Billing** berbasis Laravel. Paket ini sudah dibersihkan dari file sensitif dan siap disimpan di GitHub private.

Domain production yang biasa dipakai:

```text
https://bill.macdevi.cloud
```

## Status Paket Bersih

Yang sudah tidak disertakan di paket ini:

```text
.env
.env.* selain .env.example
database/*.sqlite
database/*.db
storage/app/backups
storage/backups
storage/logs/*.log
public/uploads
public/storage
file *.bak, *.backup, *.old, *.log, *.zip
```

Logo brand tetap disimpan di:

```text
storage/app/public/settings/macnet-logo.png
```

Agar logo terbaca di server, jalankan `php artisan storage:link` setelah deploy.

## Persyaratan Server

Minimal server:

```text
Ubuntu 22.04 / 24.04
Nginx
PHP 8.2 atau PHP 8.3
PHP-FPM
Composer
Git
SQLite
Certbot untuk SSL
```

Extension PHP yang dibutuhkan:

```bash
php-cli
php-fpm
php-mbstring
php-xml
php-curl
php-zip
php-sqlite3
php-bcmath
php-tokenizer
php-fileinfo
```

## Install Paket Server

Contoh untuk PHP 8.3:

```bash
sudo apt update
sudo apt install -y nginx git unzip curl composer \
php8.3-fpm php8.3-cli php8.3-mbstring php8.3-xml php8.3-curl \
php8.3-zip php8.3-sqlite3 php8.3-bcmath
```

## Deploy Fresh dari GitHub

Backup folder lama jika ada:

```bash
sudo mv /var/www/macbilling /var/www/macbilling_backup_$(date +%Y%m%d_%H%M)
```

Clone repository:

```bash
cd /var/www
sudo git clone https://github.com/macdevi/macbilling-deploy.git macbilling
cd /var/www/macbilling
```

Jika repository private, gunakan GitHub token saat clone.

Install dependency Laravel:

```bash
sudo composer install --no-dev --optimize-autoloader
```

Buat file environment:

```bash
sudo cp .env.example .env
sudo nano .env
```

Isi konfigurasi penting di `.env`:

```env
APP_NAME="MAC Billing"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://bill.macdevi.cloud

DB_CONNECTION=sqlite
DB_DATABASE=/var/www/macbilling/database/database.sqlite
```

Buat database SQLite kosong:

```bash
sudo touch /var/www/macbilling/database/database.sqlite
```

Generate key Laravel:

```bash
sudo php artisan key:generate
```

Jalankan migrasi dan seeder fresh:

```bash
sudo php artisan migrate --seed --force
```

Buat symbolic link storage agar logo dan upload publik terbaca:

```bash
sudo php artisan storage:link
```

Atur permission:

```bash
sudo chown -R www-data:www-data /var/www/macbilling
sudo chmod -R 775 /var/www/macbilling/storage
sudo chmod -R 775 /var/www/macbilling/bootstrap/cache
sudo chmod 664 /var/www/macbilling/database/database.sqlite
```

Clear dan cache Laravel:

```bash
sudo php artisan optimize:clear
sudo php artisan config:cache
sudo php artisan route:cache
sudo php artisan view:cache
```

## Konfigurasi Nginx

Buat file konfigurasi:

```bash
sudo nano /etc/nginx/sites-available/macbilling
```

Isi:

```nginx
server {
    listen 80;
    server_name bill.macdevi.cloud;

    root /var/www/macbilling/public;
    index index.php index.html;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/run/php/php8.3-fpm.sock;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

Aktifkan site:

```bash
sudo ln -s /etc/nginx/sites-available/macbilling /etc/nginx/sites-enabled/macbilling
sudo nginx -t
sudo systemctl restart nginx
sudo systemctl restart php8.3-fpm
```

Jika PHP yang dipakai bukan 8.3, cek versi dengan:

```bash
php -v
ls /etc/php
```

Lalu sesuaikan `php8.3-fpm` menjadi versi yang aktif, misalnya `php8.2-fpm`.

## SSL HTTPS

Jika belum ada SSL:

```bash
sudo apt install -y certbot python3-certbot-nginx
sudo certbot --nginx -d bill.macdevi.cloud
```

## Login Awal Setelah Fresh Deploy

Seeder membuat user awal:

```text
admin / password
kasir / password
teknisi / password
```

Setelah login pertama, langsung ubah semua password user dari menu pengaturan user.

## Update Aplikasi dari GitHub tanpa Hapus Database

```bash
cd /var/www/macbilling
sudo git pull
sudo composer install --no-dev --optimize-autoloader
sudo php artisan migrate --force
sudo php artisan storage:link
sudo php artisan optimize:clear
sudo php artisan config:cache
sudo php artisan route:cache
sudo php artisan view:cache
sudo systemctl restart nginx
sudo systemctl restart php8.3-fpm
```

## Deploy Ulang Fresh Database

Gunakan hanya kalau ingin database kosong dari awal:

```bash
cd /var/www/macbilling
sudo rm -f database/database.sqlite
sudo touch database/database.sqlite
sudo php artisan migrate:fresh --seed --force
sudo chown www-data:www-data database/database.sqlite
```

Peringatan: `migrate:fresh` menghapus semua tabel dan data lama.

## Catatan Keamanan

Jangan upload file berikut ke GitHub:

```text
.env
.env.* selain .env.example
database/*.sqlite
database/*.db
storage/app/backups
storage/backups
public/uploads
public/storage
vendor/
node_modules/
file *.zip, *.bak, *.backup, *.old, *.log
```
