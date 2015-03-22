<div class="row">
<div class="col-lg-4 col-lg-offset-4">
<?php
    $this->widget('SettingsWidget', [
        'prefix' => $prefix,
        'settings' => $settings,
        'buttons' => [
            'Update preferences' => [
                'type' => 'submit',
                'color' => 'primary'
            ]
        ]
    ]);
  
    
?>
</div>
</div>