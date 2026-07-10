git clone → cd gacha-api
composer install
cp .env.example .env → edit bagian DB_* sesuai database MySQL
php artisan key:generate + php artisan jwt:secret
Buat database MySQL (CREATE DATABASE gacha)
php artisan migrate -seed (auto buat tabel + admin default)/php artisan migrate:fresh --seed
php artisan serve
Di dalamnya juga ada:

Curl examples untuk login admin/user, list events, register, pull
Ringkasan semua endpoint (+ admin & user)
Akun default: admin@gacha.test / password123
