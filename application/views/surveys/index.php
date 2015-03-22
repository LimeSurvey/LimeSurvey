<?php
    if (empty($surveys)) {
        return $this->renderPartial('firstSteps');
    }
    
    $this->widget('TbGridView', [
        'dataProvider' => $surveys,
        'filter' => new Survey(),
        'columns' => [
            [
                'name' => 'status',
                'type' => 'surveyStatus',
                'filter' => TbHtml::dropdownList('Survey[status]', '', [
                    '' => 'All',
                    'active' => 'Active',
                    'inactive' => 'Inactive',
                    'expired' => 'Expired'
                ])
            ],
            [
                'header' => 'Survey ID',
                'class' => 'CLinkColumn',
                'labelExpression' => function(Survey $survey, $row) { return $survey->sid; },
                'urlExpression' => function(Survey $survey, $row) { return ['surveys/view', 'id' => $survey->sid]; }
            ],
            'localizedTitle',
            'completedResponseCount',
            'partialResponseCount',
            'responseCount',
            [
                'name' => 'responseRate',
                'type' => 'percentage'
            ]
        ]
    ]);
?>