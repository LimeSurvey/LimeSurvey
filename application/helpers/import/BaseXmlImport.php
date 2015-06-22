<?php
namespace ls\import;
abstract class BaseXmlImport extends BaseImport{
    /**
     * @var \DOMDocument
     */
    protected $document;
    public function __construct() {}

    public function setSource($file) {
        if (is_string($file)) {
            $this->document = new \DOMDocument();
            $this->document->load($file);
        } elseif ($file instanceof \DOMDocument) {
            $this->document = $file;
        }

    }



}