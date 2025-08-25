<?php

namespace LimeSurvey\Models\Services\embeds;

use LimeSurvey\Models\Services\embeds\BaseEmbed;

class PopupEmbed extends BaseEmbed
{
    /**
     * Gets the HTML wrapper around the main structure
     * @param string $placeholder a text placeholder with a default value which will be replaced with the inner structure
     * @return string
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function getWrapper(string $placeholder = "PLACEHOLDER")
    {
        $popupTitle = $this->embedOptions['windowTitle'] ?? 'Feedback survey';
        $width = $this->embedOptions['width'] ?? '640';
        $height = $this->embedOptions['height'] ?? '520';
        $fullscreenmobileEnabled = ($this->embedOptions['fullscreenmobile'] ?? '') === 'on';
        $triggerOn = $this->embedOptions['triggerOn'] ?? 'auto';
        $cssUrl = $this->getAssetsRootUrl() . '/styles-public/embed/popupEmbed.css';

        $containerClasses = "";
        if ($fullscreenmobileEnabled) {
            $containerClasses .= " fullscreen-mobile";
        }

        return <<<HTML
        <link rel="stylesheet" href="{$cssUrl}">
        <div id="limesurvey-parent-container" class="{$containerClasses}" 
             style="--ls-popup-width: {$width}px; --ls-popup-height: {$height}px;">
             <button id="ls-popup-close" >&times;</button>
             <div class="ls-popup-header">{$popupTitle}</div>
        <div id="limesurvey-container">
            {$placeholder}
        </div>
        </div>
        <div id="limesurvey-embed-button" data-trigger-on="{$triggerOn}">
            <span class="text">{$popupTitle}</span>
        </div>
        HTML;
    }
}
