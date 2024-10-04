<?php

namespace LimeSurvey\Helpers\Update;

use CException;

class Update_624 extends DatabaseUpdateBase
{
    private $defaultOptions = [];

    /**
     * @inheritDoc
     * @throws CException
     */
    public function up()
    {
        $templatesInUpload = $this->getTemplatesInUpload();
        $templatesInCore = $this->getTemplatesInCore();

        // Need to be separeted, array keys may collide
        $this->checkConfigFiles($templatesInUpload);
        $this->checkConfigFiles($templatesInCore);

        $templateConfigurations = $this->db->createCommand()
            ->select('id, options')
            ->from('{{template_configuration}}')
            ->where(['IN', 'options', '@attributes'])
            ->queryAll();

        foreach ($templateConfigurations as $templateConfiguration) {
            $templateName = $templateConfiguration['template_name'];
            $optionsJson = $templateConfiguration['options'];
            $options = json_decode($optionsJson);
            if (isset($this->defaultOptions[$templateName])) {
                $options->cssframework = $this->defaultOptions[$templateName];
                $optionsJson = json_encode($options);
                $this->db->createCommand()->update(
                    '{{template_configuration}}',
                    ['options' => $optionsJson],
                    'id = :id',
                    [':id' => $templateConfiguration['id']]
                );
            } else {
                \Yii::log('No default option was found for ' . $templateName, \CLogger::LEVEL_WARNING, 'application');
            }
        }
    }
    private function getTemplatesInUpload()
    {
        $userTemplateRootDir = App()->getConfig("userthemerootdir");
        return $this->getTemplatesInFolder($userTemplateRootDir);
    }

    private function getTemplatesInCore()
    {
        $standardTemplateRootDir = App()->getConfig("standardthemerootdir");
        return $this->getTemplatesInFolder($standardTemplateRootDir);
    }

    private function getTemplatesInFolder($folder)
    {
        /** @var array<string,string> */
        $templateList = [];

        if ($folder && $handle = opendir($folder)) {
            while (false !== ($fileName = readdir($handle))) {
                if (
                    !is_file("$folder/$fileName") &&
                    $fileName != "." &&
                    $fileName != ".." &&
                    $fileName != ".svn" &&
                    (file_exists("{$folder}/{$fileName}/config.xml"))
                ) {
                    $templateList[$fileName] = $folder . DIRECTORY_SEPARATOR . $fileName;
                }
            }
            closedir($handle);
        }
        return  $templateList;
    }

    private function checkConfigFiles($templateList)
    {
        foreach ($templateList as $templateName => $templatePath) {
            $domDocument = new \DOMDocument();
            $domDocument->load("$templatePath/config.xml");
            if (!$domDocument) {
                \Yii::log('No "config.xml" files were found in ' . $templatePath . ' directory.', \CLogger::LEVEL_WARNING, 'application');
                continue;
            }
            try {
                $newDomDocument = $this->checkDomDocument($domDocument, $templateName);
                if ($newDomDocument) {
                    $newDomDocument->save("$templatePath/config.xml");
                }
            } catch (\Exception $e) {
                \Yii::log('Error: ' . $e->getMessage() . 'found in ' . $templatePath . ' directory.', \CLogger::LEVEL_WARNING, 'application');
            }
        }
    }

    private function getFirstElementByTag($domDocument, $tag)
    {
        if (!$domDocument) {
            return null;
        }
        $elements = $domDocument->getElementsByTagName($tag);
        if ($elements->length > 0) {
            return $elements->item(0);
        }
        return null;
    }

    private function checkDomDocument($domDocument, $templateName)
    {
        $isChangedDomDocument = false;
        // Find first 'cssframework' nodes in the document
        $cssFrameworkNodes = $this->getFirstElementByTag($domDocument, 'cssframework');
        if (!empty($cssFrameworkNodes->length)) {
            $cssFrameworkNode = $cssFrameworkNodes->item(0);
        }
        if ($cssFrameworkNode) {
            $defaultOption = '';
            $dropDownOptionsNode = null;
            foreach ($cssFrameworkNode->childNodes as $child) {
                if ($child->nodeType === XML_TEXT_NODE) {
                    $defaultOption = $child->nodeValue;
                } elseif ($child->nodeName === 'dropdownoptions') {
                    $dropDownOptionsNode = $child;
                }
            }
            if ($dropDownOptionsNode) {
                $optGroupNode = $this->getFirstElementByTag($dropDownOptionsNode, 'optgroup');
                if (!$optGroupNode) {
                    // Create a new 'optgroup' element
                    $optGroupNode = $domDocument->createElement('optgroup');
                    // Loop through all 'option' nodes and move them to 'optgroup'
                    while ($dropDownOptionsNode->childNodes->length > 0) {
                        $optionNode = $dropDownOptionsNode->firstChild;
                        if ($optionNode->nodeName != 'option') {
                            throw new \Exception('Invalid node in the config file.');
                        }
                        $optGroupNode->appendChild($optionNode);
                    }
                    // Append the 'optgroup' with all the 'option' nodes into 'dropdownoptions'
                    $dropDownOptionsNode->appendChild($optGroupNode);
                    $isChangedDomDocument = true;
                }
            } else {
                throw new \Exception('No "dropdownoptions" nodes were found.');
            }
            if ($defaultOption === '') {
                $defaultOption = $optGroupNode->firsChild->nodeValue;
                if (is_string($defaultOption)) {
                    $textNode = $domDocument->createTextNode($defaultOption);
                    $cssFrameworkNode->insertBefore($textNode, $dropDownOptionsNode);
                    $isChangedDomDocument = true;
                    if (!isset($this->defaultOptions[$templateName])) {
                        $this->defaultOptions[$templateName] = $defaultOption;
                    }
                }
            }
        }
        if ($isChangedDomDocument) {
            return $domDocument;
        }
        return null;
    }
}
