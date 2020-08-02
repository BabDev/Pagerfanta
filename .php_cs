<?php declare(strict_types=1);

return PhpCsFixer\Config::create()
    ->setRules([
        '@Symfony' => true,
        '@Symfony:risky' => true,
        '@PHPUnit75Migration:risky' => true,
        'array_syntax' => ['syntax' => 'short'],
        'blank_line_after_opening_tag' => false,
        'declare_strict_types' => true,
        'fopen_flags' => false,
        'no_superfluous_phpdoc_tags' => ['remove_inheritdoc' => true],
        'ordered_imports' => true,
        'protected_to_private' => true,
        'void_return' => true,
    ])
    ->setRiskyAllowed(true)
    ->setFinder(
        PhpCsFixer\Finder::create()
            ->in(__DIR__.'/lib')
            ->in(__DIR__.'/tests')
    )
;
