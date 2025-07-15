<?php

namespace LimeSurvey\Models\Services;

use LimeSurvey\Models\Services\BaseEmbed;

class StandardEmbed extends BaseEmbed
{
    /**
     * Gets the HTML wrapper around the main structure
     * @param string $placeholder a text placeholder with a default value which will be replaced with the inner structure
     * @return string
     */
    protected function getWrapper(string $placeholder = "PLACEHOLDER")
    {
        $width = ($this->width + 20) . "px";
        $height = ($this->height + 20) . "px";
        return "<div style='width:{$width};height:{$height};border:1px solid red;'>haythem{$placeholder}</div>";
    }
}
