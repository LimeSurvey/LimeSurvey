<?php
echo TbHtml::tag('h1', [], $authenticator->name);
$this->widget('zii.widgets.grid.CGridView', [
    'dataProvider' => $authenticator->getUsers(),
    'columns' => [
        'uid',
        'username',
        'name',
        'email'
    ]
]);