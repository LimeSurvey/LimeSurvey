<?php

namespace LimeSurvey\Models\Services\embeds;

use LimeSurvey\Models\Services\embeds\BaseEmbed;

class WidgetEmbed extends BaseEmbed
{
    /**
     * Gets the HTML wrapper around the main structure
     * @param string $placeholder a text placeholder with a default value which will be replaced with the inner structure
     * @return string
     */
    protected function getWrapper(string $placeholder = "PLACEHOLDER")
    {
        $side = $this->embedOptions['widgetPosition'] ?? 'right';
        $buttonText = $this->embedOptions['widgetTitle'] ?? 'Feedback survey';
        $fullscreenmobileEnabled = $this->embedOptions['fullscreenmobile'] === 'on' ?? false;
        $arrow = $side === "right" ? "<" : ">";
        $cssUrl = $this->getAssetsRootUrl() . '/styles-public/embed/widgetEmbed.css';

        $classes = "side-{$side}";
        if ($fullscreenmobileEnabled) {
            $classes .= " fullscreen-mobile";
        }

        return <<<HTML
        <link rel="stylesheet" href="{$cssUrl}">
        <div id="limesurvey-container" class="{$classes}">
            {$placeholder}
        </div>
        <div id="limesurvey-embed-button" class="{$classes}">
            <span class="text">{$buttonText}</span>
            <span class="icon">{$arrow}</span>
        </div>
        <script data-ls-interaction>
        (function(){
            const root = window.__LS_SHADOW_ROOT__ || document;
            var container = root.getElementById('limesurvey-container');
            var button = root.getElementById('limesurvey-embed-button');
            var icon = button.querySelector('.icon');
            var isOpen = false;
        
            button.addEventListener('click', function(){
                if (isOpen) {
                    button.classList.remove('open');
                    container.classList.remove('open');
                    icon.innerHTML = '{$arrow}';
                } else {
                    button.classList.add('open');
                    container.classList.add('open');
                    icon.innerHTML = 'x';
                }
                isOpen = !isOpen;
            });
        })();
        </script>
        HTML;
    }
}
