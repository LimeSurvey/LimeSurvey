<div class="row"><div class="col-md-12">
<?php
use ls\models\Token;

echo TbHtml::buttonGroup([
    [
        'icon' => 'plus',
        'title' => gT("Create new token"),
        'url' => ['tokens/create', 'surveyId' => $survey->sid]
    ],
    [
        'icon' => 'import',
        'title' => gT("Import from CSV"),
        'url' => ['tokens/import', 'surveyId' => $survey->sid]
    ],
    [
        'icon' => 'cog',
        'title' => gT("Generate tokens"),
        'data-method' => 'post',
        'data-confirm' => gT("Clicking 'Yes' will generate tokens for all those in this token list that have not been issued one. Continue?"),
        'url' => ['tokens/generate', 'surveyId' => $survey->sid]
    ],
]);
$columns = isset($dataProvider->data[0]) ? $dataProvider->data[0]->attributeNames() : [];

$columns = [
    [
        'header' => gT("Actions"),
        'class' => TbButtonColumn::class,
        'deleteButtonUrl' => function(Token $model, $row) {
            return App()->createUrl('tokens/delete', ['id' => $model->primaryKey, 'surveyId' => $model->surveyId]);
        },
        'viewButtonUrl' => function(Token $model, $row) {
            return App()->createUrl('tokens/view', ['id' => $model->primaryKey, 'surveyId' => $model->surveyId]);
        },
        'updateButtonUrl' => function(Token $model, $row) {
            return App()->createUrl('tokens/update', ['id' => $model->primaryKey, 'surveyId' => $model->surveyId]);
        },
        'template' => '{createResponse} {view} {update} {remove}',
        'buttons' => [
            'remove' => [
                'icon' => 'trash',
                'label' => gT("Remove token"),
                'options' => [
                    'data-confirm' => gT("Are you sure you want to delete this token?"),
                    'data-method' => 'delete'
                ],
                'url' => function(Token $token) {
                    return App()->createUrl('tokens/delete', ['id' => $token->primaryKey, 'surveyId' => $token->surveyId]);
                },
            ],
            'createResponse' => [
                'icon' => TbHtml::ICON_CERTIFICATE,
                'title' => gT("Execute survey with this token."),
                'visible' => function($row, Token $model) {
                    return $model->survey->isActive && $model->usesleft > 0 && !empty($model->token);
                },
                'url' => function(Token $model, $row) {
                    return App()->createUrl('surveys/start', ['token' => $model->token, 'id' => $model->surveyId]);
                },
                'options' => [
                    'target' => '_blank'
                ]
            ]
        ]
    ], [
        'class' => WhRelationalColumn::class,
        'name' => 'Responses',
        'url' => App()->createUrl('tokens/responses', ['surveyId' => $survey->sid]),
        'value' => function(\ls\models\Token $model) {
            return $model->responseCount;
        },
//        'afterAjaxUpdate' => 'js:function(tr,rowid,data){
//        bootbox.alert("I have afterAjax events too!<br/>This will only happen once for row with id: "+rowid);
//    }'
    ], [
        'header' => gT("Series"),
        'visible' => $survey->use_series,
        'class' => TbButtonColumn::class,
        'template' => "{appendNew}{appendCopy}",
        'buttons' => [
            'appendNew' => [
                'icon' => TbHtml::ICON_PLUS,
                'label' => gT("Add empty response to series"),
                'visible' => function($row, Token $model) {
                    return $model->completed != 'N' && $model->usesleft > 1;
                },
                'url' => function(Token $model, $row) {
                    return App()->createUrl('responses/append', ['id' => $model->primaryKey, 'surveyId' => $model->surveyId, 'copy' => false]);
                }
            ],
            'appendCopy' => [
                'icon' => TbHtml::ICON_PLUS_SIGN,
                'label' => gT("Add response to series, based on last response"),
                'visible' => function($row, Token $model) {
                    return $model->completed != 'N' && $model->usesleft > 1;
                },
                'url' => function(Token $model, $row) {
                    return App()->createUrl('responses/append', ['id' => $model->primaryKey, 'surveyId' => $model->surveyId, 'copy' => true]);
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
?></div></div>