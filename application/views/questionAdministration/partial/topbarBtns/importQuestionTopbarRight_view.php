<!-- Import -->
<?php
$this->widget(
    'ext.ButtonWidget.ButtonWidget',
    [
        'id' => 'save-button',
        'name' => 'save-button',
        'text' => gT('Import'),
        'icon' => 'ri-download-fill',
        'htmlOptions' => [
                'class' => 'btn btn-primary',
        ],
    ]
);
?>
