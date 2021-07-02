<?php

namespace LimeSurvey\Datavalueobjects;

/**
 * Class ThemeFileCategory
 *
 * This class represents a theme file category (eg. Global files, Theme files, Survey files)
 *
 * @package LimeSurvey\DataValueObject
 */
class ThemeFileCategory
{

    /** @var string the category name */
    public $name;

    /** @var string the display title for the category */
    public $title;

    /** @var string the base path for the category */
    public $path;

    /** @var string the "virtual" path prefix (eg. 'image::generalfiles::') */
    public $pathPrefix;

    /**
     * @param string $name
     * @param string $title
     * @param string $path
     * @param string $pathPrefix
     */
    public function __construct($name, $title, $path, $pathPrefix)
    {
        $this->name = $name;
        $this->title = $title;
        $this->path = $path;
        $this->pathPrefix = $pathPrefix;
    }
}