<?php

namespace LimeSurvey\Models\Services\embeds;

use LimeSurvey\Models\Services\embeds\BaseEmbed;

class StandardEmbed extends BaseEmbed
{
    /**
     * Gets the HTML wrapper around the main structure
     * @param string $placeholder a text placeholder with a default value which will be replaced with the inner structure
     * @return string
     */
    protected function getWrapper(string $placeholder = "PLACEHOLDER")
    {
        $width = ($this->embedOptions['width'] ?? $this->fullWidth);
        $height = ($this->embedOptions['height'] ?? $this->fullHeight);
        $style = "width: {$width}; height: {$height}; border: 1px solid #6E748C; border-radius: 4px;";

        return "<div style='{$style}'>{$placeholder}</div>";
    }
}
