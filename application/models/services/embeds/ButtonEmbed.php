<?php

namespace LimeSurvey\Models\Services\embeds;

use LimeSurvey\Models\Services\embeds\BaseEmbed;

class ButtonEmbed extends BaseEmbed
{
    /**
     * Gets the HTML wrapper around the main structure
     * @param string $placeholder a text placeholder with a default value which will be replaced with the inner structure
     * @return string
     */
    protected function getWrapper(string $placeholder = "PLACEHOLDER")
    {
        return "";
    }

     /**
     * overrides the render method to render the button on itself ( without survey structure )
     * @return array|string
     */
    public function render(string $placeholder = "PLACEHOLDER")
    {
        $options = $this->embedOptions ?? [];
        $buttonText = $options['buttonText'] ?? 'Start survey';
        $fontSize = $options['fontSize'] ?? 16;
        $borderRadius = $options['borderRadius'] ?? 4;
        $buttonColor = $options['buttonColor'] ?? '#14AE5C';
        $customIcon = $options['customIcon'] ?? null;
        $fullscreen = $options['fullscreenmobile'] ?? 'off';
        $openIn = $options['openIn'] ?? 'newwindow';
        $surveyId = $options['surveyId'] ?? null;

        $cssUrl = $this->getAssetsRootUrl() . '/styles-public/embed/buttonEmbed.css';

        $surveyUrl = '#';
        if ($surveyId) {
            $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
            $host = $_SERVER['HTTP_HOST'];
            $surveyUrl = "{$protocol}://{$host}/index.php/{$surveyId}";
        }

        $style = sprintf(
            "font-size:%dpx; border-radius:%dpx; background:%s;",
            (int) $fontSize,
            (int) $borderRadius,
            $buttonColor
        );

        $classes = $fullscreen === 'on' ? 'fullscreen-mobile' : '';

        $iconHtml = '';
        if (!empty($customIcon)) {
            $iconHtml = '<img src="' . $customIcon . '"class="ls-button-icon" />';
        }

        $onclick = $openIn === 'newwindow'
            ? "window.open('" . $surveyUrl . "', '_blank');"
            : "window.location.href='" . $surveyUrl . "';";

        return <<<HTML
        <link rel="stylesheet" href="{$cssUrl}">
        <button 
            id="limesurvey-embed-button" 
            class="{$classes}"
            style="{$style}"
            onclick="{$onclick}"
        >
            {$iconHtml}
            <span class="ls-button-text">{$buttonText}</span>
        </button>
        HTML;
    }
}
