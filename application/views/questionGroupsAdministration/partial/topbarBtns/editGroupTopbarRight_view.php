<!-- Close -->
<?php
if (!empty($closeBtnUrl)) {
    $this->widget(
        'ext.ButtonWidget.ButtonWidget',
        [
            'name' => 'close-button',
            'id' => 'close-button',
            'text' => gT('Close'),
            'icon' => 'ri-close-fill',
            'link' => $closeBtnUrl,
            'htmlOptions' => [
                'class' => 'btn btn-danger',
            ],
        ]
    );
}
?>

<!-- White Close button -->
<?php
if (!empty($showWhiteCloseButton)) {
    $this->widget(
        'ext.ButtonWidget.ButtonWidget',
        [
            'name' => 'close-button',
            'id' => 'close-button',
            'text' => gT('Close'),
            'icon' => 'ri-close-fill',
            'link' => $closeUrl,
            'htmlOptions' => [
                'class' => 'btn btn-outline-secondary',
            ],
        ]
    );
}
?>

<!-- Save and close -->
<?php
$this->widget(
    'ext.ButtonWidget.ButtonWidget',
    [
        'name' => 'save-and-close-button',
        'id' => 'save-and-close-button',
        'text' => gT('Save and close'),
        'icon' => 'ri-checkbox-fill',
        'htmlOptions' => [
            'class' => 'btn btn-outline-secondary',
        ],
    ]
);
?>

<!-- Save -->
<?php
$this->widget(
    'ext.ButtonWidget.ButtonWidget',
    [
        'name' => 'save-button',
        'id' => 'save-button',
        'text' => gT('Save'),
        'icon' => 'ri-check-fill',
        'htmlOptions' => [
            'class' => 'btn btn-primary',
            'role' => 'button',
        ],
    ]
);
?>
