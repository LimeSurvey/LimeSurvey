<?php
    if (empty($surveys)) {
        return $this->renderPartial('firstSteps');
    }
    $this->widget(TbGridView::class, [
        'dataProvider' => $surveys,
        'filter' => $filter,
        'columns' => [
            'actions' => [
                'htmlOptions' => [
                    'style' => 'width: 100px;',
                ],
                'class' => TbButtonColumn::class,
                'template' => '{remove}{update}',
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
                'header' => 'Survey ID',
                'name' => 'sid',
                'type' => 'raw',
                'value' => function(Survey $survey, $row) { return \TbHtml::link($survey->sid, ['surveys/update', 'id' => $survey->sid]); },
                'htmlOptions' => [
                    'style' => 'width: 100px;',
                ],
            ],
            [
                'name' => 'localizedTitle',
            ], [
                'class' => \CDataColumn::class,
                'name' => 'bool_usetokens',
                'type' => 'booleanIcon',
                'htmlOptions' => [
                    'style' => 'width: 100px;',
                ],
                'filter' => TbHtml::dropdownList(\CHtml::modelName($filter) . '[bool_usetokens]', $filter->bool_usetokens, [
                    '' => 'All',
                    1 => gT('Yes'),
                    0 => gT('No')
                ])
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
?>