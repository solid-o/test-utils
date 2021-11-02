<?php

declare(strict_types=1);

return [
    'name' => 'Laravel',
    'env' => 'testing',

    'providers' => [
        Illuminate\Filesystem\FilesystemServiceProvider::class,
        Illuminate\Translation\TranslationServiceProvider::class,
        Illuminate\View\ViewServiceProvider::class,
    ],
];
