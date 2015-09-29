<div class="row">
<?php
echo TbHtml::openTag('div', ['class' => isset($wrapper) ? $wrapper : 'col-md-12']);
    $this->widget(WhGridView::class, [
        'dataProvider' => $dataProvider,
        'columns' => [
            [
                'header' => gT('Actions'),
                'class' => TbButtonColumn::class
            ],
            [
                'class' => WhRelationalColumn::class,
                'name' => 'attributeCount',
//                'value' => function(ls\models\Participant $model) { return $model->; },
                'url' => App()->createUrl('participants/attributes')
            ],
            'firstname',
            'lastname',
            'email',
            'blacklisted' => [
                'name' => 'blacklisted',
                'type' => 'boolean',
            ],
            'surveyCount',
            'language',
            'owner.name'
        ],
        'responsiveTable' => true,

    ]);
echo TbHtml::closeTag('div');
?></div>