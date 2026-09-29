<?php

namespace LimeSurvey\Api;

/**
 * ApiConfig
 *
 */
class ApiConfig
{
    /** @var array */
    private $config = [];

    /**
     * ApiConfig
     *
     * @param array $config
     */
    public function __construct(&$config = [])
    {
        $this->config = &$config;
    }

    /**
     * Get entire config array
     *
     * @return array
     */
    public function &getConfig()
    {
        return $this->config;
    }

    /**
     * Set entire config array
     *
     * @param array $config
     * @return void
     */
    public function setConfig(&$config)
    {
        $this->config = &$config;
    }

    /**
     * Set config by path
     *
     * @param string $path
     * @param mixed $value
     * @return void
     */
    public function setPath($path, $value)
    {
        $pathElements = explode('.', $path);
        $field = array_pop($pathElements);
        $parent = &$this->getPath(
            implode('.', $pathElements),
            true
        );
        $parent[$field] = $value;
    }

    /**
     * Get config by path
     *
     * @param string $path
     * @param boolean $createParents
     * @return array|null
     */
    public function &getPath($path, $createParents = false)
    {
        $result = &$this->pathReducer(
            explode('.', $path),
            $this->config,
            $createParents
        );
        return $result;
    }

    /**
     * Path Reducer
     *
     * @param array $pathElements
     * @param array $initData
     * @param boolean $createParents
     * @return array|null
     */
    private function &pathReducer($pathElements, &$initData, $createParents = false)
    {
        $nullRef = null;
        if (empty($pathElements)) {
            return $initData;
        }
        $carry = &$initData;
        foreach ($pathElements as $pathElement) {
            if (!isset($carry[$pathElement])) {
                if ($createParents) {
                    $carry[$pathElement] = [];
                } else {
                    $carry = $nullRef;
                    break;
                }
            }
            $carry = &$carry[$pathElement];
        }
        return $carry;
    }
}
