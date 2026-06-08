<?php

/** @var bool $showUpload */
/** @var string $scanFilesUrl */

if ($showUpload) {
    $this->widget(
        'ext.ButtonWidget.ButtonWidget',
        [
            'name' => 'plugin-install-button',
            'id' => 'plugin-install-button',
            'text' => gT('Upload & install'),
            'icon' => 'ri-download-2-fill',
            'htmlOptions' => [
                'class' => 'btn btn-outline-secondary',
                'data-bs-toggle' => "modal",
                'data-bs-target' => '#installPluginZipModal',
                'title' => gT('Install plugin ZIP file')
            ],
        ]
    );
}

if ($scanFilesUrl !== null) {
    $this->widget(
        'ext.ButtonWidget.ButtonWidget',
        [
            'name' => 'plugin-scanfiles-button',
            'id' => 'plugin-scanfiles-button',
            'text' => gT('Scan files'),
            'icon' => 'ri-search-line',
            'link' => $scanFilesUrl,
            'htmlOptions' => [
                'class' => 'btn btn-outline-secondary',
                'data-bs-toggle' => 'tooltip',
                'title' => gT('Scan files for available plugins')
            ],
        ]
    );
}
