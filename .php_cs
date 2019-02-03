<?php

if (!file_exists(__DIR__.'/src')) {
    exit(0);
}

return PhpCsFixer\Config::create()
    ->setRules([
        '@Symfony' => true,
        '@Symfony:risky' => true,
        '@PHPUnit48Migration:risky' => true,
        'php_unit_no_expectation_annotation' => false, // part of `PHPUnitXYMigration:risky` ruleset, to be enabled when PHPUnit 4.x support will be dropped, as we don't want to rewrite exceptions handling twice
        'array_syntax' => ['syntax' => 'short'],
        'fopen_flags' => false,
        'ordered_imports' => true,
        'protected_to_private' => false,
        // Part of @Symfony:risky in PHP-CS-Fixer 2.13.0. To be removed from the config file once upgrading
        'native_function_invocation' => ['include' => ['@compiler_optimized'], 'scope' => 'namespaced'],
        // Part of future @Symfony ruleset in PHP-CS-Fixer To be removed from the config file once upgrading
        'phpdoc_types_order' => ['null_adjustment' => 'always_last', 'sort_algorithm' => 'none'],
    ])
    ->setRiskyAllowed(true)
    ->setFinder(
        PhpCsFixer\Finder::create()
            ->in(__DIR__.'/src')
            ->append([__FILE__])
            ->exclude([
                'Symfony/Bridge/ProxyManager/Tests/LazyProxy/PhpDumper/Fixtures',
                // directories containing files with content that is autogenerated by `var_export`, which breaks CS in output code
                'Symfony/Component/DependencyInjection/Tests/Fixtures',
                'Symfony/Component/Routing/Tests/Fixtures/dumper',
                // fixture templates
                'Symfony/Component/Templating/Tests/Fixtures/templates',
                'Symfony/Bundle/FrameworkBundle/Tests/Fixtures/TemplatePathsCache',
                'Symfony/Bundle/FrameworkBundle/Tests/Templating/Helper/Resources/Custom',
                // generated fixtures
                'Symfony/Component/VarDumper/Tests/Fixtures',
                'Symfony/Component/VarExporter/Tests/Fixtures',
                // resource templates
                'Symfony/Bundle/FrameworkBundle/Resources/views/Form',
                // explicit trigger_error tests
                'Symfony/Bridge/PhpUnit/Tests/DeprecationErrorHandler/',
            ])
            // Support for older PHPunit version
            ->notPath('Symfony/Bridge/PhpUnit/SymfonyTestsListener.php')
            // file content autogenerated by `var_export`
            ->notPath('Symfony/Component/Translation/Tests/fixtures/resources.php')
            // test template
            ->notPath('Symfony/Bundle/FrameworkBundle/Tests/Templating/Helper/Resources/Custom/_name_entry_label.html.php')
            // explicit heredoc test
            ->notPath('Symfony/Bundle/FrameworkBundle/Tests/Fixtures/Resources/views/translation.html.php')
            // explicit trigger_error tests
            ->notPath('Symfony/Component/Debug/Tests/DebugClassLoaderTest.php')
            // invalid annotations on purpose
            ->notPath('Symfony/Component/DependencyInjection/Tests/Fixtures/includes/autowiring_classes.php')
    )
;
