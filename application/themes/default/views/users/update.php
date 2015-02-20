<div class="col-sm-12 col-md-6 col-md-offset-3">
<?php
    /* @var \ls\pluginmanager\iUser $user */
    $this->widget('SettingsWidget', [
        'settings' => $user->getSettings(),
        'buttons' => [
            'Save user' => [
                'type' => 'submit',
                'color' => 'primary'
            ],
            'Back to list' => [
                'type' => 'link',
                'href' => ['users/index']
            ]
        ]
    ]);
?>
</div>