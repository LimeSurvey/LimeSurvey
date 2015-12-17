<?php
/** @var CActiveDataProvider $dataProvider */

$actions = TbHtml::tag('div', [
    'style' => 'padding-left: 10px; margin-top: -21px;'
], implode(" ", [
    '└─── ',
    gT("With selected") . ':',
    TbHtml::openTag('div', ['class' => 'btn-group']),
    TbHtml::submitButton('', [
        'color' => 'primary',
        'icon' => 'trash',
        'title' => gT('Delete surveys'),
        'formaction' => App()->createUrl('surveys/deleteMultiple'),
        'data-method' => 'delete',
        'data-confirm' => gT("This will delete all selected surveys, are you sure?")
    ]),
    TbHtml::submitButton('', [
        'icon' => 'play',
        'title' => gT("Activate selected surveys"),
        'formaction' => App()->createUrl('surveys/activateMultiple'),
        'data-confirm' => gT("This will activate all selected surveys, are you sure?")
    ]),
    TbHtml::submitButton('', [
        'icon' => 'stop',
        'color' => 'danger',
        'title' => gT("Stop selected surveys"),
        'formaction' => App()->createUrl('surveys/deactivateMultiple'),
        'data-confirm' => gT("This will deactivate all selected surveys, are you sure?")
    ]),
    TbHtml::endForm()
]));

echo TbHtml::beginForm('', 'post');
$this->widget(WhGridView::class, [
    'template' => "{summary}\n{items}\n{pager}\n{extendedSummary}",
    'dataProvider' => $dataProvider,
    'selectableRows' => 0,
//    'filter' => $filter,
    'columns' => [
        [
            'htmlOptions' => [
                'style' => 'width: 100px;',
            ],
            'class' => TbButtonColumn::class,
            'template' => '{remove} {update}',
            'header' => gT("Actions"),
            'buttons' => [
                'remove' => [
                    'icon' => 'trash',
                    'label' => gT("Remove labelset"),
                    'options' => [
                        'data-confirm' => gT("Are you sure you want to delete this survey?"),
                        'data-method' => 'delete'
                    ],
                    'url' => function(\ls\models\LabelSet $model) {
                        return App()->createUrl('labels/delete', ['id' => $model->primaryKey]);
                    },
                ]
            ]


        ],
        'label_name',
        'languages'

    ]
]);
