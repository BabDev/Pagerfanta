<?php declare(strict_types=1);

return (new PhpCsFixer\Config())
    ->setRules([
        '@Symfony' => true,
        '@Symfony:risky' => true,
        '@PHP74Migration' => true,
        '@PHP74Migration:risky' => true,
        '@PHPUnit84Migration:risky' => true,
        'array_syntax' => ['syntax' => 'short'],
        'blank_line_after_opening_tag' => false,
        'declare_strict_types' => false,
        'fopen_flags' => false,
        'linebreak_after_opening_tag' => false,
        'no_superfluous_phpdoc_tags' => ['remove_inheritdoc' => true],
        'php_unit_test_case_static_method_calls' => ['call_type' => 'self'],
    ])
    ->setRiskyAllowed(true)
    ->setFinder(
        (new PhpCsFixer\Finder())
            ->in(__DIR__.'/lib')
            ->in(__DIR__.'/tests')
            ->append([
                __DIR__.'/rector.php',
                __DIR__.'/.github/patch-packages.php',
            ])
    );
