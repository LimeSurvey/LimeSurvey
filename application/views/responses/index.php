<?php

$columns = isset($dataProvider->data[0]) ? $dataProvider->data[0]->attributeNames() : [];

$columns = [
    [
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
        'visible' => $this->survey->use_series,
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
    ]
] + $columns;
$this->widget(WhGridView::class, [
    'dataProvider' => $dataProvider,
    'columns' => $columns,
    'responsiveTable' => true,

]);