<?php

namespace LimeSurvey\Datavalueobjects;

/**
 * Class ThemeFileInfo
 *
 * This class represents a theme file. It includes the real path, the virtual path, and the category.
 *
 * Virtual paths are a special notation for relative paths, including a prefix to give context.
 * Eg.: the path "image::theme::files/logo.png" is relative to the theme folder, while 
 *      "image::generalfiles::" is relative to the general files folder.
 * If $path is not valid, returns null.
 * Paths can be
 * - related to a global theme option and hence the file be located on the generalfiles directory.
 * - related to a survey theme option and hence the file be located relative to a survey upload directory.
 * - related to a theme and hence the file be located on the theme directory (eg. when uploaded from theme editor)
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