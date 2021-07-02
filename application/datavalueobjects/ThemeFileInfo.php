<?php

namespace LimeSurvey\Datavalueobjects;

/**
 * Class ThemeFileInfo
 *
 * This class represents a theme file. It includes the real path, the virtual path, and the category.
 *
 * @package LimeSurvey\DataValueObject
 */
class ThemeFileInfo
{

    /** @var string the real path to the file (eg. '/var/www/html/limesurvey/themes/survey/vanilla/files/logo.png') */
    public $realPath;

    /** @var string the virtual path to the file (eg. 'image::theme::logo.png') */
    public $virtualPath;

    /** @var ThemeFileCategory */
    public $category;

    /**
     * @param string $realPath
     * @param string $virtualPath
     * @param ThemeFileCategory $category
     */
    public function __construct($realPath, $virtualPath, $category)
    {
        $this->realPath = $realPath;
        $this->virtualPath = $virtualPath;
        $this->category = $category;
    }
}