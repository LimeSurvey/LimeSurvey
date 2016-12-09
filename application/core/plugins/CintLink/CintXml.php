<?php

require_once(__DIR__ . "/CintLinkAPI.php");

/**
 * Helper class to parse links in the
 * Cint XML.
 *
 * @since 2016-08-09
 * @author Olle Härstedt
 */
final class CintXml
{
    /**
     * Raw XML
     * @var string
     */
    private $raw;

    /**
     * @var SimpleXmlElement
     */
    private $xml;

    /**
     * @param string $apiKey
     */
    public function __construct($apiKey)
    {
        $url = 'https://' . $apiKey . '.cds.cintworks.net/';
        $curl = new Curl();
        $this->raw = $curl->get($url);

        if (!is_string($this->raw->body))
        {
            // TODO: Better error message
            // TODO: Ignore silently?
            throw new Exception('Could not get Xml file using Curl - is you server configured properly?');
        }

        $this->xml = new SimpleXmlElement($this->raw->body);
    }

    /**
     * Get global variables
     * @return SimpleXmlElement|null
     */
    public function getGlobalVariables()
    {
        $url = $this->getHrefFromRel('global-variables');
        if ($url !== false)
        {
            $curl = new Curl();
            $curlResponse = $curl->get($url);
            return $curlResponse->body;
        }
        else
        {
            throw new Exception('Found no href from rel');
        }

    }

    /**
     * Get link from rel name
     * @param string $relName Like 'global-variables' or 'quote'
     * @return string|false
     */
    private function getHrefFromRel($relName)
    {
        $rel = 'http://cds.cint.com/rel/' . $relName;
        foreach ($this->xml->children() as $child)
        {
            if ($child['rel'] == $rel)
            {
                return $child['href'];
            }
        }

        return false;
    }
}
