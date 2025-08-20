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
        <div id="limesurvey-embed-button">
            <span class="text">{$popupTitle}</span>
        </div>
        <script data-ls-interaction>
        (function(){
            const root = window.__LS_SHADOW_ROOT__ || document;
            const container = root.getElementById('limesurvey-parent-container');
            const button = root.getElementById('limesurvey-embed-button');
            const closeBtn = root.getElementById('ls-popup-close');
            let isOpen = false;

            function openPopup() {
                container.classList.add('open');
                isOpen = true;
            }

            function closePopup() {
                container.classList.remove('open');
                isOpen = false;
            }

            button.addEventListener('click', function(){
                isOpen ? closePopup() : openPopup();
            });

            closeBtn.addEventListener('click', function(){
                closePopup();
            });

            const triggerOn = "{$triggerOn}";
            if (triggerOn === "auto") {
                openPopup();
            } 
            if (triggerOn === "scroll") {
                window.addEventListener('scroll', function onScroll() {
                    const rect = button.getBoundingClientRect();
                    if (rect.top < window.innerHeight && rect.bottom >= 0) {
                        openPopup();
                        window.removeEventListener('scroll', onScroll);
                    }
                });
            }
        })();
        </script>
        HTML;
    }
}
