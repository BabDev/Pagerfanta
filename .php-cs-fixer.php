<?php declare(strict_types=1);

return (new PhpCsFixer\Config())
    ->setRules([
        '@Symfony' => true,
        '@Symfony:risky' => true,
        '@PHP81Migration' => true,
        '@PHP80Migration:risky' => true,
        '@PHPUnit100Migration:risky' => true,
        'array_syntax' => ['syntax' => 'short'],
        'blank_line_after_opening_tag' => false,
        'fopen_flags' => false,
        'linebreak_after_opening_tag' => false,
        'no_superfluous_phpdoc_tags' => ['remove_inheritdoc' => true],
        'nullable_type_declaration_for_default_null_value' => [
            'use_nullable_type_declaration' => true,
        ],
    ])
    ->setRiskyAllowed(true)
    ->setFinder(
        (new PhpCsFixer\Finder())
            ->in(__DIR__ . '/lib')
            ->in(__DIR__ . '/tests')
            ->append([
                __DIR__ . '/rector.php',
                __DIR__ . '/.github/patch-packages.php',
            ])
    );
