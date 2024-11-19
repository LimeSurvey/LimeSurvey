<?php

return [
    'name' => 'WORLD',
    'components' => [
        'viewRenderer' => [
            'class' => 'root.vendor.vintagesucks.twig-renderer.ETwigViewRenderer',
            'twigPathAlias' => 'root.vendor.twig.twig.lib.Twig',
            'fileExtension' => '.twig',
            'lexerOptions' => [
                'tag_comment' => ['{#', '#}'],
            ],
            'functions' => [
                'rot13' => 'str_rot13',
            ],
        ],
    ]
];
