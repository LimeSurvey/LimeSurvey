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
            'sid',
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