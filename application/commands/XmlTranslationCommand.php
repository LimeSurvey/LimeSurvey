<?php

/**
 * To execute this command :
 * php application/commands/console.php XmlTranslation index
 * php application/commands/console.php XmlTranslation generateTranslationFiles
 */

class XmlTranslationCommand extends CConsoleCommand
{
    public function actionIndex()
    {
        echo "This command will take all config.xml files \n";
        echo "in " . realpath(dirname(__FILE__) . "/../views/survey/questions/answer") . "\n";
        echo "and it will generate php files with the strings to be translated \n";
        echo "in " . realpath(dirname(__FILE__) . "/../../tmp \n");
    }

    public function actionGenerateTranslationFiles()
    {
        $sViewsDir = dirname(__FILE__) . '/../views/survey/questions/answer';
        $oViewsDir = new DirectoryIterator($sViewsDir);

        foreach ($oViewsDir as $fileInfo) {
            if ($fileInfo->isDot() || $fileInfo->isFile()) {
                continue;
            }

            $sFilePath = $fileInfo->getRealPath() . '/config.xml';

            if (! is_file($sFilePath)) {
                continue;
            }

            $xmlContents = file_get_contents($sFilePath);
            $config = new SimpleXMLElement($xmlContents);

            $attributes = $config->attributes;

            $fileName = $fileInfo->getFileName();
            $tmpPath = dirname(__FILE__) . '/../../tmp';
            $fileHandler = fopen($tmpPath . '/' . $fileName . '-config-xml.php', 'w');
            $date = date('F d, Y');

            fwrite($fileHandler, '<?php' . PHP_EOL);
            fwrite($fileHandler, '//Entries from file ' . $sFilePath . PHP_EOL);
            fwrite($fileHandler, '//File generated on ' . $date . PHP_EOL);
            fwrite($fileHandler, PHP_EOL);

            $currentAttributeCategory = '';

            foreach ($attributes->attribute as $attribute) {

                if ($currentAttributeCategory != (string)$attribute->category) {

                    $currentAttributeCategory = (string)$attribute->category;
                    fwrite($fileHandler, PHP_EOL . '//' . $currentAttributeCategory . ' attributes.' . PHP_EOL);

                }

                if (! empty($attribute->help)) {
                    fwrite($fileHandler, 'gT("' . $attribute->help . '");' . PHP_EOL);
                }

                if (! empty($attribute->caption)) {
                    fwrite($fileHandler, 'gT("' . $attribute->caption . '");' . PHP_EOL);
                }

                if (! empty($attribute->options)) {

                    foreach ($attribute->options->children() as $option) {

                        fwrite($fileHandler, 'gT("' . $option->text . '");' . PHP_EOL);

                    }
                }
            }

            fclose($fileHandler);
        }
    }
}
