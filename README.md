## MAC Billing

# Demo aplikasi tersedia di:

http://202.10.41.241:8090
admin/password
kasir/password
teknisi/password

MAC Billing adalah aplikasi billing berbasis Laravel untuk manajemen pelanggan, paket layanan, invoice, pembayaran, kolektor, teknisi, ODP, dan laporan operasional.

Repository ini disiapkan agar bisa dideploy ulang di VPS/server baru tanpa membawa data sensitif seperti `.env`, database aktif, file backup, log, atau upload pribadi.

## Fitur Utama

- Manajemen pelanggan
- Manajemen paket layanan
- Generate invoice/tagihan
- Pembayaran invoice
- Dashboard admin
- Dashboard kasir/kolektor
- Dashboard teknisi
- Manajemen ODP
- Laporan dan rekap data
- Support SQLite untuk deploy sederhana

## Persyaratan Server

Minimal server yang direkomendasikan:

- Ubuntu 22.04 / 24.04
- Nginx
- PHP 8.2 atau PHP 8.3
- PHP-FPM
- Composer
- Git
- SQLite
- Unzip
- Certbot jika memakai HTTPS/domain

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

Cek versi PHP:

```bash
php -v
```

## Clone Repository

Masuk ke folder web server:

```bash
cd /var/www
```

Clone repository:

```bash
sudo git clone https://github.com/USERNAME/NAMA-REPOSITORY.git macbilling
cd /var/www/macbilling
```

Ganti `USERNAME/NAMA-REPOSITORY` sesuai repository masing-masing.

## Install Dependency Laravel

```bash
sudo composer install --no-dev --optimize-autoloader
```

Jika server tidak mengizinkan composer dengan sudo, gunakan:

```bash
composer install --no-dev --optimize-autoloader
```

## Konfigurasi Environment

Copy file contoh environment:

```bash
sudo cp .env.example .env
```

Edit file `.env`:

```bash
sudo nano .env
```

Contoh konfigurasi dasar:

```env
APP_NAME="MAC Billing"
APP_ENV=production
APP_KEY=
APP_DEBUG=false
APP_URL=http://IP-VPS/HOST

LOG_CHANNEL=stack
LOG_LEVEL=error

DB_CONNECTION=sqlite
DB_DATABASE=/var/www/macbilling/database/database.sqlite

CACHE_DRIVER=file
QUEUE_CONNECTION=sync
SESSION_DRIVER=file
SESSION_LIFETIME=120
```

Ganti `IP-VPS/HOST` dengan IP VPS atau domain masing-masing, misalnya:

```text
http://123.123.123.123
```

atau:

```text
https://domain-anda.com
```

Simpan file nano dengan:

```text
CTRL + O, Enter, CTRL + X
```

## Generate APP_KEY

```bash
sudo php artisan key:generate
```

## Setup Database SQLite

Buat file database kosong:

```bash
sudo touch /var/www/macbilling/database/database.sqlite
```

Atur permission database:

```bash
sudo chown www-data:www-data /var/www/macbilling/database/database.sqlite
sudo chmod 664 /var/www/macbilling/database/database.sqlite
```

Jalankan migrasi dan seeder:

```bash
sudo php artisan migrate --seed --force
```

## Permission Folder Laravel

```bash
sudo chown -R www-data:www-data /var/www/macbilling
sudo chmod -R 775 /var/www/macbilling/storage
sudo chmod -R 775 /var/www/macbilling/bootstrap/cache
sudo chmod 664 /var/www/macbilling/database/database.sqlite
```

## Storage Link

Jalankan perintah berikut agar file public storage seperti logo bisa terbaca:

```bash
sudo php artisan storage:link
```

## Optimasi Laravel

```bash
sudo php artisan optimize:clear
sudo php artisan config:cache
sudo php artisan route:cache
sudo php artisan view:cache
```

## Konfigurasi Nginx

Buat file konfigurasi Nginx:

```bash
sudo nano /etc/nginx/sites-available/macbilling
```

Isi konfigurasi:

```nginx
server {
    listen 80;
    server_name IP-VPS/HOST;

    root /var/www/macbilling/public;
    index index.php index.html;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/run/php/php8.3-fpm.sock;
    }

    location ~ /\. {
        deny all;
    }
}
```

Ganti `IP-VPS/HOST` dengan IP VPS atau domain masing-masing.

Jika memakai PHP 8.2, ganti:

```nginx
fastcgi_pass unix:/run/php/php8.3-fpm.sock;
```

menjadi:

```nginx
fastcgi_pass unix:/run/php/php8.2-fpm.sock;
```

Aktifkan site:

```bash
sudo ln -s /etc/nginx/sites-available/macbilling /etc/nginx/sites-enabled/macbilling
sudo nginx -t
sudo systemctl restart nginx
sudo systemctl restart php8.3-fpm
```

Jika memakai PHP 8.2:

```bash
sudo systemctl restart php8.2-fpm
```

## Akses Aplikasi

Buka browser:

```text
http://IP-VPS/HOST
```

atau jika memakai domain dan SSL:

```text
https://DOMAIN-ANDA.COM
```

## Login Default

Setelah deploy fresh, akun awal biasanya:

```text
admin / password
kasir / password
teknisi / password
```

Segera ganti semua password default setelah login pertama.

## Setup SSL HTTPS

Jika memakai domain, install Certbot:

```bash
sudo apt install -y certbot python3-certbot-nginx
```

Aktifkan SSL:

```bash
sudo certbot --nginx -d DOMAIN-ANDA.COM
```

Ganti `DOMAIN-ANDA.COM` dengan domain masing-masing.

## Update Aplikasi dari GitHub

Untuk update source tanpa menghapus database:

```bash
cd /var/www/macbilling
sudo git pull
sudo composer install --no-dev --optimize-autoloader
sudo php artisan migrate --force
sudo php artisan optimize:clear
sudo php artisan config:cache
sudo php artisan route:cache
sudo php artisan view:cache
sudo systemctl restart nginx
sudo systemctl restart php8.3-fpm
```

Jika memakai PHP 8.2:

```bash
sudo systemctl restart php8.2-fpm
```

## Deploy Ulang Fresh

Gunakan ini hanya kalau ingin database kosong dari awal.

```bash
cd /var/www/macbilling
sudo rm -f database/database.sqlite
sudo touch database/database.sqlite
sudo chown www-data:www-data database/database.sqlite
sudo chmod 664 database/database.sqlite
sudo php artisan migrate:fresh --seed --force
```

Peringatan: `migrate:fresh` akan menghapus semua tabel dan data lama.

## File yang Tidak Boleh Masuk Repository

Pastikan file berikut tidak diupload ke GitHub:

```text
.env
.env.*
database/*.sqlite
database/*.db
vendor/
node_modules/
storage/app/backups/
storage/backups/
storage/logs/
storage/framework/cache/
storage/framework/sessions/
storage/framework/views/
public/uploads/
public/storage/
*.bak
*.backup
*.old
*.log
*.zip
```

Yang boleh ada:

```text
.env.example
storage/**/.gitkeep
bootstrap/cache/.gitkeep
```

## Troubleshooting

Jika muncul error permission:

```bash
sudo chown -R www-data:www-data /var/www/macbilling
sudo chmod -R 775 /var/www/macbilling/storage
sudo chmod -R 775 /var/www/macbilling/bootstrap/cache
```

Jika halaman kosong atau error 500:

```bash
cd /var/www/macbilling
sudo php artisan optimize:clear
sudo tail -n 100 storage/logs/laravel.log
```

Jika database SQLite tidak bisa ditulis:

```bash
sudo chown www-data:www-data database/database.sqlite
sudo chmod 664 database/database.sqlite
sudo chown www-data:www-data database
sudo chmod 775 database
```

Jika route tidak terbaca:

```bash
sudo php artisan route:clear
sudo php artisan config:clear
sudo php artisan cache:clear
sudo php artisan view:clear
```

## Catatan Keamanan

- Jangan commit `.env` ke GitHub.
- Jangan commit database aktif.
- Jangan commit file backup pelanggan.
- Jangan commit foto/profile pribadi.
- Jangan commit log production.
- Ganti password default setelah deploy.
- Gunakan repository private jika aplikasi berisi kode internal.
