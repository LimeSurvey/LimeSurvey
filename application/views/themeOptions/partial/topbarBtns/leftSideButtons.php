<?php

/** @var bool $canImport */
/** @var string $importErrorMessage*/

if($canImport) {
    $this->widget(
        'ext.ButtonWidget.ButtonWidget',
        [
            'name' => 'uploadandinstall',
            'id' => 'uploadandinstall', //this one is important to trigger the click for submit button
            'text' => gT('Upload & install'),
            'icon' => 'ri-download-2-fill',
            'htmlOptions' => [
                'class' => 'btn btn-outline-secondary',
                'data-bs-target' => '#importSurveyModal',
                'data-bs-toggle' => 'modal',
                'role' => 'button',
            ],
        ]
    );
} else { ?>
<span class="btntooltip" data-bs-toggle="tooltip" data-bs-placement="bottom"
      title="<?php echo $importErrorMessage; ?>"
      style="display: inline-block">
<?php
    $this->widget(
        'ext.ButtonWidget.ButtonWidget',
        [
            'name' => 'uploadandinstall',
            'id' => 'uploadandinstall', //this one is important to trigger the click for submit button
            'text' => gT('Upload & install'),
            'icon' => 'icon-download-2-fill',
            'htmlOptions' => [
                'class' => 'btn btn-outline-secondary',
                'role' => 'button',
                'disabled' => "disabled"
            ],
        ]
    );
}
