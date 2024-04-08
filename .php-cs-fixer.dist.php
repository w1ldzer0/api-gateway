<?php

$finder = (new PhpCsFixer\Finder())
    ->in(__DIR__)
    ->exclude(['var'])
;

return (new PhpCsFixer\Config())
    ->setRules([
        '@PSR12' => true,
        '@Symfony' => true,
        'concat_space' => false,
        'yoda_style' => false,
        'single_line_throw' => false,
        'php_unit_fqcn_annotation' => false
    ])
    ->setFinder($finder)
    ;
