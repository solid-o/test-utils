<?php

declare(strict_types=1);

use Illuminate\Filesystem\FilesystemServiceProvider;
use Illuminate\Translation\TranslationServiceProvider;
use Illuminate\View\ViewServiceProvider;

return [
    'name' => 'Laravel',
    'env' => 'testing',

    'providers' => [
        FilesystemServiceProvider::class,
        TranslationServiceProvider::class,
        ViewServiceProvider::class,
    ],
];
