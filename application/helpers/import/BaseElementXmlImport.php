<?php
namespace ls\import;

/**
 * Class BaseElementXmlImport
 * Base class for XML files that use elements only. (Current LSS format)
 * @package ls\import
 */
abstract class BaseElementXmlImport extends BaseXmlImport
{

    public $parsedDocument;
    public function setSource($file)
    {
        parent::setSource($file);
        $this->parsedDocument = $this->recurse($this->document->firstChild);
    }


    protected function recurse(\DOMNode $node) {
        if ($node->hasChildNodes()) {
            $result = [];
            if ($node->childNodes->length == 1) {
                return $node->firstChild->data;
            }
            foreach ($node->childNodes as $childNode) {
                if ($childNode instanceof \DOMElement) {
                    $recurse = $this->recurse($childNode);
                    if (!isset($result[$childNode->tagName])) {
                        $result[$childNode->tagName] = $recurse;
                    } elseif(is_array($result[$childNode->tagName]) && isset($result[$childNode->tagName][0])) {
                        $result[$childNode->tagName][] = $recurse;
                    } else {
                        $result[$childNode->tagName] = [$result[$childNode->tagName], $recurse];
                    }

                }
            }

            return $result;
        }
    }
}