<div class="col-sm-12 col-md-6 col-md-offset-3">
<?php
echo TbHtml::tag('h1', [], $authenticator->name);
$this->widget('WhGridView', [
    'selectableRows' => false,
    'dataProvider' => is_array($users = $authenticator->getUsers()) ? new CArrayDataProvider($users) : $users,
    'columns' => [
        'uid',
        'username',
        'name',
        'email',
        'actions' => [
            'class' => 'TbButtonColumn',
            'template' => '{view}{update}',
            'buttons' => [
                'update' => [
                    'url' => function(\ls\pluginmanager\iUser $user) use ($authenticator) { return ['users/update', 'id' => $user->id, 'plugin' => $user->getAuthenticator()->id]; },
                    'visible' => function($row, \ls\pluginmanager\iUser $user) { return $user->getAuthenticator()->writable() && !empty($user->getSettings()); }
                ],
                'view' => [
                    'url' => function(\ls\pluginmanager\iUser $user) use ($authenticator) { return ['users/read', 'id' => $user->id, 'plugin' => $user->getAuthenticator()->id]; },
                ]
            ]
            
        ]
    ]
]);
?>
</div>