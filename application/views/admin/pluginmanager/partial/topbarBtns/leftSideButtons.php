<?php

/** @var bool $showUpload */
/** @var string $scanFilesUrl */
/** @var \LimeSurvey\Menu\Menu[] $extraMenus */

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

foreach ($extraMenus as $menuIndex => $menu) {
    $htmlOptions = ['class' => 'btn btn-outline-secondary'];
    if ($menu->getOnClick()) {
        $htmlOptions['onclick'] = $menu->getOnClick();
    }
    if ($menu->getTooltip()) {
        $htmlOptions['data-bs-toggle'] = 'tooltip';
        $htmlOptions['title'] = $menu->getTooltip();
    }
    $this->widget(
        'ext.ButtonWidget.ButtonWidget',
        [
            'name' => 'plugin-extra-menu-button-' . $menuIndex,
            'text' => $menu->getLabel(),
            'icon' => $menu->getIconClass(),
            'link' => $menu->getHref(),
            'htmlOptions' => $htmlOptions,
        ]
    );
}
