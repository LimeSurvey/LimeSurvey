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
        echo "in the followin directories:\n \n";
        echo " * " . realpath(dirname(__FILE__) . "/../views/survey/questions/answer") . "\n";
        echo " * " . realpath(dirname(__FILE__) . '/../../themes/question') . "\n \n";
        echo "And it will generate php files with the strings to be translated \n";
        echo "in " . realpath(dirname(__FILE__) . "/../../tmp") . "\n";
    }

    public function actionGenerateTranslationFiles()
    {
        //Create view files
        $this->generateFiles(dirname(__FILE__) . '/../views/survey/questions/answer', 'view');

        //Create theme files
        $oThemesDir = new DirectoryIterator(dirname(__FILE__) . '/../../themes/question');

        foreach ($oThemesDir as $dirInfo) {
            if ($dirInfo->isDot() || $dirInfo->isFile()) {
                continue;
            }

            $sDirPath = $dirInfo->getRealPath();
            $this->generateFiles($sDirPath . '/survey/questions/answer', 'theme');

        }
    }

    private function generateFiles($sViewsDir, $sFilePrefix)
    {
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
            $fileHandler = fopen($tmpPath . '/' . $fileName . '-' . $sFilePrefix . '-config-xml.php', 'w');
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

                        if (! empty($option->text)) {
                            fwrite($fileHandler, 'gT("' . $option->text . '");' . PHP_EOL);
                        } else {
                            fwrite($fileHandler, 'gT("' . $option . '");' . PHP_EOL);
                        }

                    }
                }
            }

            fclose($fileHandler);
        }
    }
}
