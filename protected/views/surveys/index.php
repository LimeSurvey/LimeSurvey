<?php
use ls\models\Survey;
/** @var CActiveDataProvider $surveys */
if ($surveys->totalItemCount == 0) {
        return $this->renderPartial('firstSteps');
    }

//
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

    echo TbHtml::beginForm('', 'post', ['id' => 'surveyForm']);
    $this->widget(WhGridView::class, [
        'template' => "{summary}\n{items}\n$actions\n{pager}\n{extendedSummary}",
        'dataProvider' => $surveys,
        'selectableRows' => 0,
        'filter' => $filter,
        'columns' => [
            [
                'class' => \CCheckBoxColumn::class,
                'checkBoxHtmlOptions' => [
                    'name' => 'ids[]',
                    'form' => 'surveyForm'
                ],
                'selectableRows' => 2
            ],
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
                        'label' => gT("Remove survey"),
                        'options' => [
                            'data-confirm' => gT("Are you sure you want to delete this survey?"),
                            'data-method' => 'delete'
                        ],
                        'url' => function(Survey $survey) {
                            return App()->createUrl('surveys/delete', ['id' => $survey->primaryKey]);
                        },
                        'visible' => function($row, Survey $survey, TbButtonColumn $column) {
                            return !$survey->isActive;
                        }
                    ]
                ]


            ],
            [
                'name' => 'status',
                'htmlOptions' => [
                    'style' => 'width: 150px;',
                ],
                'type' => 'surveyStatus',
                'filter' => TbHtml::dropdownList(\CHtml::modelName($filter) . '[status]', $filter->status, [
                    '' => 'All',
                    Survey::STATUS_ACTIVE => 'Active',
                    Survey::STATUS_INACTIVE => 'Inactive',
                    Survey::STATUS_EXPIRED => 'Expired'
                ]),


            ],
            [
                'name' => 'sid',
                'class' => TbDataColumn::class,
                'type' => 'raw',
                'value' => function(Survey $survey, $row) { return \TbHtml::link($survey->sid, ['surveys/update', 'id' => $survey->sid]); },
                'htmlOptions' => [
                    'style' => 'width: 100px;',
                ],
            ],
            [
                'name' => 'localizedTitle',
                'sortable' => true,
                'class' => TbDataColumn::class,
            ], [
                'class' => TbDataColumn::class,
                'name' => 'bool_usetokens',
                'type' => 'booleanIcon',
                'htmlOptions' => [
                    'style' => 'width: 120px;',
                ],
                'filter' => TbHtml::dropdownList(\CHtml::modelName($filter) . '[bool_usetokens]', $filter->bool_usetokens, [
                    '' => 'All',
                    1 => gT('Yes'),
                    0 => gT('No')
                ])
            ],
            [
                'filter' => false,
                'name' => 'questionCount',
                'htmlOptions' => [
                    'style' => 'width: 100px;',
                ],

            ],

            [
                'filter' => false,
                'name' => 'completedResponseCount',
                'htmlOptions' => [
                    'style' => 'width: 100px;',
                ],

            ],
            [
                'filter' => false,
                'name' =>'partialResponseCount',
                'htmlOptions' => [
                    'style' => 'width: 100px;',
                ],
            ], [
                'filter' => false,
                'name' => 'responseCount',
                'htmlOptions' => [
                    'style' => 'width: 100px;',
                ],
            ], [
                'filter' => false,
                'name' => 'responseRate',
                'type' => 'percentage',
                'htmlOptions' => [
                    'style' => 'width: 100px;',
                ],
            ],

        ]
    ]);
