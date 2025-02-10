<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\CodeQuality\Rector as CodeQuality;
use Rector\Php53\Rector as Php53;
use Rector\Php54\Rector as Php54;
use Rector\Php55\Rector as Php55;
use Rector\Php56\Rector as Php56;
use Rector\Php70\Rector as Php70;
use Rector\Php71\Rector as Php71;
use Rector\Set\ValueObject\LevelSetList;
use Rector\ValueObject\PhpVersion;

return RectorConfig::configure()
    ->withPaths([
        __DIR__ . '/library',
        __DIR__ . '/tests',
    ])
    ->withRules([
        CodeQuality\Class_\CompleteDynamicPropertiesRector::class
    ])
    ->withSkip([
        Php53\FuncCall\DirNameFileConstantToDirConstantRector::class,
        Php53\Ternary\TernaryToElvisRector::class,
        Php54\Array_\LongArrayToShortArrayRector::class,
        Php55\Class_\ClassConstantToSelfClassRector::class,
        Php55\String_\StringClassNameToClassConstantRector::class,
        Php56\FuncCall\PowToExpRector::class,
        Php70\FuncCall\MultiDirnameRector::class,
        Php70\FuncCall\RandomFunctionRector::class,
        Php70\StmtsAwareInterface\IfIssetToCoalescingRector::class,
        Php70\Ternary\TernaryToNullCoalescingRector::class,
        Php70\Variable\WrapVariableVariableNameInCurlyBracesRector::class,
        Php71\FuncCall\RemoveExtraParametersRector::class,
        Php71\List_\ListToArrayDestructRector::class,
        __DIR__ . '/tests/Zend/Loader/_files/ParseError.php',
    ])
    ->withSets([
        LevelSetList::UP_TO_PHP_82
    ])
    ->withPhpVersion(PhpVersion::PHP_71);
