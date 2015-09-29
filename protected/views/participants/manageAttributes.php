<div class="row">
    <div class="col-md-12">
<?php

use ls\models\ParticipantAttributeName;

$this->widget(WhGridView::class, [
        'dataProvider' => $dataProvider,
        'columns' => [
            [
                'header' => gT('Actions'),
                'template' => '{update}{delete}',
                'class' => TbButtonColumn::class,
                'deleteButtonUrl' => function(ParticipantAttributeName $model) { return ['participants/removeAttribute', 'id' => $model->id]; },
                'updateButtonUrl' => function(ParticipantAttributeName $model) { return ['participants/updateAttribute', 'id' => $model->id]; }

            ],
            'id',
            'name',
            'type',
            [
                'name' => 'visible',
                'type' => 'boolean'
            ],
        ],
        'responsiveTable' => true,

    ]);

?></div>
</div>