<div class="row">
<?php
echo TbHtml::openTag('div', ['class' => isset($wrapper) ? $wrapper : 'col-md-12']);

    if (!isset($columns)) {
        $columns = isset($dataProvider->data[0]) ? $dataProvider->data[0]->attributeNames() : [];
    }
    $columns = [
        [
            'class' => \CCheckBoxColumn::class,
            'selectableRows' => 2
        ], [
            'header' => gT("Actions"),
            'class' => TbButtonColumn::class,
            'deleteButtonUrl' => function(Response $model, $row) {
                return App()->createUrl('responses/delete', ['id' => $model->id, 'surveyId' => $model->surveyId]);
            },
            'viewButtonUrl' => function(Response $model, $row) {
                return App()->createUrl('responses/view', ['id' => $model->id, 'surveyId' => $model->surveyId]);
            },
            'updateButtonUrl' => function(Response $model, $row) {
                return App()->createUrl('responses/update', ['id' => $model->id, 'surveyId' => $model->surveyId]);
            }
        ], [
            'header' => gT("Series"),
            'visible' => $survey->use_series,
            'class' => TbButtonColumn::class,
            'template' => "{appendNew}{appendCopy}",
            'buttons' => [
                'appendNew' => [
                    'icon' => TbHtml::ICON_PLUS,
                    'label' => gT("Add empty response to series"),
                    'url' => function(Response $model, $row) {
                        return App()->createUrl('responses/append', ['id' => $model->id, 'surveyId' => $model->surveyId, 'copy' => false]);
                    }
                ],
                'appendCopy' => [
                    'icon' => TbHtml::ICON_PLUS_SIGN,
                    'label' => gT("Add response to series, based on last response"),
                    'url' => function(Response $model, $row) {
                        return App()->createUrl('responses/append', ['id' => $model->id, 'surveyId' => $model->surveyId, 'copy' => true]);
                    }
                ]
            ]
        ],

        'token',
        'submitdate',
        'series_id',

    ];

//    $template = "{summary}\n{items}\n{pager}\n{extendedSummary}";
//    $template = TbHtml::tag('div', [], '') . $template;
    $this->widget(WhGridView::class, [
//        'template' => $template,
        'dataProvider' => $dataProvider,
        'columns' => $columns,
        'responsiveTable' => true,

    ]);
echo TbHtml::closeTag('div');
?></div>