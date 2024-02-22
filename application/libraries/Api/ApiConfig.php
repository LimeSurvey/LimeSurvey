<?php

namespace LimeSurvey\Api;

/**
 * RestConfig
 *
 */
class ApiConfig
{
    private $config = [];

    public function __construct(&$config = [])
    {
        $this->config = &$config;
    }

    /**
     * Get entire config array
     */
    public function &getConfig()
    {
        return $this->config;
    }

    /**
     * Set entire config array
     */
    public function setConfig(&$config)
    {
        $this->config = &$config;
    }

    /**
     * Set config by path
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
     */
    private function &pathReducer($pathElements, &$initData, $createParents = false)
    {
        $nullRef = null;
        if (empty($pathElements)) {
            return $initData;
        }
        $carry = &$initData;
        if (is_array($pathElements) && !empty($pathElements)) {
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
        }
        return $carry;
    }
}
