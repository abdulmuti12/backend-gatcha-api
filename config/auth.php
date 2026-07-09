<?php

// NOTE: file ini adalah versi TERMODIFIKASI dari config/auth.php bawaan Laravel.
// Timpa file config/auth.php di project Laravel kamu dengan isi ini,
// atau merge bagian 'guards' & 'providers' saja jika sudah ada kustomisasi lain.

return [

    'defaults' => [
        'guard' => 'api',
        'passwords' => 'users',
    ],

    'guards' => [
        // Guard untuk end-user (pemain gacha), memakai driver jwt dari tymon/jwt-auth
        'api' => [
            'driver' => 'jwt',
            'provider' => 'users',
        ],

        // Guard terpisah untuk admin, provider & tabel berbeda dari user
        'admin-api' => [
            'driver' => 'jwt',
            'provider' => 'admins',
        ],
    ],

    'providers' => [
        'users' => [
            'driver' => 'eloquent',
            'model' => App\Models\User::class,
        ],

        'admins' => [
            'driver' => 'eloquent',
            'model' => App\Models\Admin::class,
        ],
    ],

    'passwords' => [
        'users' => [
            'provider' => 'users',
            'table' => 'password_reset_tokens',
            'expire' => 60,
            'throttle' => 60,
        ],
    ],

    'password_timeout' => 10800,

];
