<?php

declare(strict_types=1);

$baseConfig = NoCompromises\PhpCsFixer\Config\Factory::create(__DIR__);

return $baseConfig
    ->setRules(
        array_merge(
            $baseConfig->getRules(),
            [
                'php_unit_method_casing' => ['case' => 'snake_case'],
            ]
        )
    );


