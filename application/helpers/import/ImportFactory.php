<?php
namespace ls\import;
use \DOMDocument;
/**
 * Class ImportFactory
 * A factory for import objects.
 */
class ImportFactory
{
    public static function getForLss($file) {
        $dom = new DOMDocument();
        $dom->load($file);
        $nodeList = $dom->getElementsByTagName('DBVersion');
        if ($nodeList->length > 0 ) {
            $dbVersion = intval($nodeList->item(0)->textContent);
            $result = static::getForDbVersion($dbVersion);
            $result->setSource($dom);
            return $result;
        }
    }

    /**
     * @param int $version
     * @throws \Exception
     * @return BaseImport
     */
    public static function getForDbVersion($version) {
        // Check if we have an importer for that version.
        $class = "ls\\import\\importers\\Import$version";
        if (class_exists($class)) {
            /** @var BaseImport $result */
            return new $class();
        } else {
            throw new \Exception("No importer for database version $version ($class)");
        }
    }
}