<?php
    if (empty($surveys)) {
        return $this->renderPartial('firstSteps');
    }
    
    $this->widget(TbGridView::class, [
        'dataProvider' => $surveys,
        'filter' => new Survey(),
        'columns' => [
            'actions' => [
                'htmlOptions' => [
                    'style' => 'width: 100px;',
                ],
                'class' => TbButtonColumn::class,
                'template' => '{remove}',
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
                'filter' => TbHtml::dropdownList('Survey[status]', '', [
                    '' => 'All',
                    'active' => 'Active',
                    'inactive' => 'Inactive',
                    'expired' => 'Expired'
                ]),


            ],
            [
                'header' => 'Survey ID',
                'class' => 'CLinkColumn',
                'labelExpression' => function(Survey $survey, $row) { return $survey->sid; },
                'urlExpression' => function(Survey $survey, $row) { return ['surveys/update', 'id' => $survey->sid]; },
                'htmlOptions' => [
                    'style' => 'width: 100px;',
                ],
            ],
            [
                'name' => 'localizedTitle',
            ],
            [
                'name' => 'completedResponseCount',
                'htmlOptions' => [
                    'style' => 'width: 100px;',
                ],

            ],
            [
                'name' =>'partialResponseCount',
                'htmlOptions' => [
                    'style' => 'width: 100px;',
                ],
            ], [
                'name' => 'responseCount',
                'htmlOptions' => [
                    'style' => 'width: 100px;',
                ],
            ], [
                'name' => 'responseRate',
                'type' => 'percentage',
                'htmlOptions' => [
                    'style' => 'width: 100px;',
                ],
            ],

        ]
    ]);
?>