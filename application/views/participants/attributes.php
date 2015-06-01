<div class="row"><div class="col-md-8 col-md-offset-2"
<?php
$this->widget(WhGridView::class, [
    'id' => 'ParticipantAttributes',
    'dataProvider' => $dataProvider,
    'columns' => [
        'attribute_id',
        'name.defaultname',
        'value',

    ]
]);
?></div></div>