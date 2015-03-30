<?php

$columns = isset($dataProvider->data[0]) ? $dataProvider->data[0]->attributeNames() : [];

array_unshift($columns, [
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
]);
$this->widget(WhGridView::class, [
    'dataProvider' => $dataProvider,
    'columns' => $columns,
    'responsiveTable' => true,

]);