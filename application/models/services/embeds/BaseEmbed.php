<?php

namespace LimeSurvey\Models\Services\embeds;

abstract class BaseEmbed
{
    protected $fullWidth = '100%';
    protected $fullHeight = '100%';

    protected $embedOptions;
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
        $embed = "LimeSurvey\\Models\\Services\\embeds\\{$key}Embed";
        if (in_array($key, self::$supportedEmbeds)) {
            return new $embed();
        } else {
            throw new \Exception(sprintf(gT('The embed %s is not supported'), $key));
        }
    }

    /**
     * Sets embed options (for wrapper) and returns the instance
     * @param array $options
     * @return static
     */
    public function setEmbedOptions(array $options)
    {
        $this->embedOptions = $options;
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
            "<iframe style='width:{$this->fullWidth};height:{$this->fullHeight};' src=\"{$this->src}\">"
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
