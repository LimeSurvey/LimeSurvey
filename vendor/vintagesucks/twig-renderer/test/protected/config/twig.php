<?php

return [
    'name' => 'WORLD',
    'components' => [
        'viewRenderer' => [
            'class' => 'root.vendor.vintagesucks.twig-renderer.ETwigViewRenderer',
            'twigPathAlias' => 'root.vendor.twig.twig.lib.Twig',
            'fileExtension' => '.twig',
        ],
    ]
];
