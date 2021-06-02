<?php

namespace LimeSurvey\Models\Services;

use Exception;

/**
 * Service class for handling config XML files.
 */
class XmlConfigHandler
{
    /** @var boolean contains the original entity loader state after disabling it */
    private $oldEntityLoaderState;

    /** @var boolean indicates if XML handling is started. This is used to avoid disabling the entity loader twice (and thus loosing the original state) */
    private $handling = false;

    /** @var string path to the XML file */
    private $xmlFilePath;

    /** @var \SimpleXMLElement|false the loaded XML config */
    private $xmlConfig;

    /**
     * @param string $xmlFilePath   the path to the XML file
     */
    public function __construct($xmlFilePath)
    {
        $this->xmlFilePath = $xmlFilePath;
        $this->xmlConfig = $this->load();
    }

    /**
     * Load and return the XML config
     *
     * @return \SimpleXMLElement|false
     */
    private function load()
    {
        $this->startXmlHandling();
        $xmlConfig = simplexml_load_file($this->xmlFilePath);
        $this->endXmlHandling();
        return $xmlConfig;
    }

    /**
     * Disables the entity loader
     */
    private function startXmlHandling()
    {
        if ($this->handling) {
            return;
        }
        if (\PHP_VERSION_ID < 80000) {
            $this->oldEntityLoaderState = libxml_disable_entity_loader(true); // @see: http://phpsecurity.readthedocs.io/en/latest/Injection-Attacks.html#xml-external-entity-injection
        }
        $this->handling = true;
    }

    /**
     * Restores the entity loader state
     */
    private function endXmlHandling()
    {
        if (!$this->handling) {
            return;
        }
        if (\PHP_VERSION_ID < 80000) {
            libxml_disable_entity_loader($this->oldEntityLoaderState); // Put back entity loader to its original state, to avoid contagion to other applications on the server
        }
        $this->handling = false;
    }

    /**
     * Returns the $nodeName XML node as an array
     *
     * @param string $nodeName the name of the node to retrieve
     * @return array<mixed> the node contents as an array
     */
    public function getNodeAsArray($nodeName)
    {
        if (empty($this->xmlConfig)) {
            throw new Exception(gT("No XML config loaded"));
        }
        $node = json_decode(json_encode((array)$this->xmlConfig->$nodeName), true);
        return $node;
    }

    /**
     * Returns the loaded XML config
     */
    public function getConfig()
    {
        return $this->xmlConfig;
    }
}
