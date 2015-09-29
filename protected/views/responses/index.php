<div class="row">
<?php
use ls\models\Response;

echo TbHtml::openTag('div', ['class' => isset($wrapper) ? $wrapper : 'col-md-12']);

    if (!isset($columns)) {
        $columns = isset($dataProvider->data[0]) ? $dataProvider->data[0]->attributeNames() : [];
    }

    $actions = TbHtml::tag('div', [
        'style' => 'padding-left: 10px; margin-top: -21px;'
    ], implode(" ", [
        '└─── ',
        gT("With selected") . ':',
        TbHtml::openTag('div', ['class' => 'btn-group']),
        TbHtml::submitButton('', [
            'color' => 'danger',
            'icon' => 'trash',
            'title' => gT('Delete responses'),
            'formaction' => App()->createUrl('responses/deleteMultiple', ['surveyId' => $survey->primaryKey]),
            'data-method' => 'delete',
            'data-confirm' => gT("This will delete all selected responses, are you sure?")
        ]),
//        TbHtml::submitButton('', [
//            'icon' => 'play',
//            'title' => gT("Activate selected surveys"),
//            'formaction' => App()->createUrl('surveys/activateMultiple'),
//            'data-confirm' => gT("This will activate all selected surveys, are you sure?")
//        ]),
//        TbHtml::submitButton('', [
//            'icon' => 'stop',
//            'color' => 'danger',
//            'title' => gT("Stop selected surveys"),
//            'formaction' => App()->createUrl('surveys/deactivateMultiple'),
//            'data-confirm' => gT("This will activate all selected surveys, are you sure?")
//        ]),
        TbHtml::endForm()
    ]));

    $columns = array_merge([
        [
            'class' => \CCheckBoxColumn::class,
            'selectableRows' => 2,
            'checkBoxHtmlOptions' => [
                'name' => 'ids[]',
                'form' => 'responseForm'
            ],
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
        ]
    ], $columns);


    echo TbHtml::beginForm('', 'post', ['id' => 'responseForm']);
    $this->widget(WhGridView::class, [
        'template' => "{summary}\n{items}\n$actions\n{pager}\n{extendedSummary}",
        'dataProvider' => $dataProvider,
        'columns' => $columns,
        'responsiveTable' => true,

    ]);
echo TbHtml::closeTag('div');
?></div>