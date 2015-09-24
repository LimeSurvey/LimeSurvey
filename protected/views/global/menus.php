<?php
$items = require __DIR__ . '/../global/menu.php';
$this->widget(\TbNavbar::class, [
    'brandUrl' => ['surveys/index'],
    'display' => TbHtml::NAVBAR_DISPLAY_FIXEDTOP,
    'fluid' => true,
    'items' => [
        [
            'class' => TbNav::class,
            'items' => $items[0],
        ],
        [
            'class' => TbNav::class,
            'htmlOptions' => ['class' => 'navbar-right'],
            'items' => $items[1],
        ]
    ]
]);
foreach (array_reverse($this->menus) as $name => $data) {
    $file = __DIR__ . "/../global/{$name}Menu.php";
    if (file_exists($file)) {
        if (!is_array($data)) {
         $data = ['model' => $data];
        }
        $menu = require_config($file, $data, $this);
        $this->widget(\TbNavBar::class, [
            'brandLabel' => isset($menu['brandLabel']) ? $menu['brandLabel'] :  false,
            'display' => null,
            'fluid' => true,
            'items' => [
                [
                    'class' => 'TbNav',
                    'items' => $menu[0]
                ],
                [
                    'class' => 'TbNav',
                    'htmlOptions' => [
                    'class' => 'navbar-right'
                    ],
                    'items' => $menu[1]
                ]
            ]
        ]);

    }
}