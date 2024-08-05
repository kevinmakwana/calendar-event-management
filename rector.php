<?php

declare(strict_types=1);

use Rector\Caching\ValueObject\Storage\FileCacheStorage;
use Rector\Config\RectorConfig;
use Rector\Set\ValueObject\LevelSetList;
use Rector\Set\ValueObject\SetList;
use RectorLaravel\Set\LaravelSetList;

return RectorConfig::configure()
    ->withCache(
        cacheDirectory: '.rector-cache',
        cacheClass: FileCacheStorage::class,
    )
    ->withPaths([
        __DIR__.'/app',
        // __DIR__.'/bootstrap',
        // __DIR__.'/config',
        // __DIR__.'/lang',
        // __DIR__.'/public',
        // __DIR__.'/resources',
        // __DIR__.'/routes',
        // __DIR__.'/tests',
    ])
    ->withPhpSets(php83: true)
    ->withSets([
        SetList::DEAD_CODE,
        LevelSetList::UP_TO_PHP_83,
        LaravelSetList::LARAVEL_110,
    ])
    ->withRules([
    ]);
