<?php

namespace LimeSurvey\Models\Services;

abstract class BaseEmbed
{
    protected $width;
    protected $height;
    protected $src;
    protected $structure;
    public const EMBED_STRUCTURE_STANDARD = "Standard";
    protected static $supportedEmbeds = null;

    /**
     * Instantiates an embed class or throws error if it's not supported
     * @param string $key
     * @throws \Exception
     * @return object
     */
    public static function instantiate(string $key)
    {
        if (!self::$supportedEmbeds) {
            self::$supportedEmbeds = [
                self::EMBED_STRUCTURE_STANDARD
            ];
        }
        $embed = "LimeSurvey\\Models\\Services\\{$key}Embed";
        if (in_array($key, self::$supportedEmbeds)) {
            return new $embed();
        } else {
            throw new \Exception(sprintf(gT('The embed %s is not supported'), $key));
        }
    }

    /**
     * Sets the width of the inner content and returns the instance
     * @param int $width
     * @return static
     */
    public function setWidth(int $width)
    {
        $this->width = $width;
        return $this;
    }

    /**
     * Sets the height of the inner content and returns the instance
     * @param int $height
     * @return static
     */
    public function setHeight(int $height)
    {
        $this->height = $height;
        return $this;
    }

    /**
     * Sets the src of the inner content and returns the instance
     * @param string $src
     * @return static
     */
    public function setSrc(string $src)
    {
        $this->src = $src;
        $this->structure = null;
        return $this;
    }

    public function setStructure(string $structure)
    {
        $this->structure = $structure;
        $this->src = null;
        return $this;
    }

    /**
     * Gets the HTML wrapper around the main structure
     * @param string $placeholder a text placeholder with a default value which will be replaced with the inner structure
     * @return string
     */
    abstract protected function getWrapper(string $placeholder = "PLACEHOLDER");

    /**
     * Returns the inner structure
     * @return string
     */
    protected function getStructure()
    {
        return $this->structure ?
            $this->structure :
            "<iframe style='width:{$this->width}px;height:{$this->height}px;' src=\"{$this->src}\">"
        ;
    }

    /**
     * Renders the structure with the wrapper wrapped around it
     * @param string $placeholder a text placeholder with a default value which will be replaced with the inner structure
     * @return array|string
     */
    public function render(string $placeholder = "PLACEHOLDER")
    {
        return str_replace($placeholder, $this->getStructure(), $this->getWrapper($placeholder));
    }
}
