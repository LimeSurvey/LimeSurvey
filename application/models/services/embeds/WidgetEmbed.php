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
        $fullscreenmobileEnabled = $this->embedOptions['fullscreenmobile'] == 'on' ?? false;

        $arrow = $side === "right" ? "<" : ">";
        $cssUrl = $this->getAssetsRootUrl() . '/styles-public/embed/widgetEmbed.css';

        $sideClasses = "side-{$side}";
        $containerClasses = $sideClasses;
        if ($fullscreenmobileEnabled) {
            $containerClasses .= " fullscreen-mobile";
        }

        return <<<HTML
        <link rel="stylesheet" href="{$cssUrl}">
        <div id="limesurvey-container" class="{$containerClasses}">
            {$placeholder}
        </div>
        <div id="limesurvey-embed-button" class="{$sideClasses}">
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
                const containerWidth = container.getBoundingClientRect().width;
                const viewportWidth = window.innerWidth || document.documentElement.clientWidth;
                let buttonSlideDifference = 1;
                if (viewportWidth <= 768) {
                    buttonSlideDifference =  button.getBoundingClientRect().width;
                }
                if (isOpen) {
                    container.style.{$side} = -containerWidth + 'px';
                    button.style.{$side} = '0';
                    icon.innerHTML = '{$arrow}';
                } else {
                    container.style.{$side} = '0';
                    button.style.{$side} = (containerWidth - buttonSlideDifference) + 'px';
                    icon.innerHTML = 'x';
                }
                isOpen = !isOpen;
            });
        })();
        </script>
        HTML;
    }
}
