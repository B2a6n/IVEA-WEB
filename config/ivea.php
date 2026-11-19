<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | Seed Administrator
    |--------------------------------------------------------------------------
    |
    | Credentials for the administrator created by AdminSeeder. When no
    | password is configured, the seeder generates a random one and prints it.
    |
    */

    'seed_admin' => [
        'name' => env('IVEA_ADMIN_NAME', 'Administración IVEA'),
        'email' => env('IVEA_ADMIN_EMAIL', 'admin@ivea.test'),
        'password' => env('IVEA_ADMIN_PASSWORD'),
    ],

];
