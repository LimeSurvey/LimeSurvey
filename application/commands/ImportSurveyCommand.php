<?php

Yii::import('application.helpers.replacements_helper', true);
Yii::import('application.helpers.expressions.em_manager_helper', true);
Yii::import('application.helpers.common_helper', true);
Yii::import('application.helpers.admin.import_helper', true);

class ImportSurveyCommand extends CConsoleCommand
{
    /**
     * @param string $filename the name of the file
     * @param string|null $baselang the base language either chosen or defaulted to null in the run method
     * @return array|null Import result
     */
    protected function importFile($filename, $baselang)
    {
        // TODO: Add support to customize these.
        $params = [
            "bTranslateLinkFields" => false,
            "sNewSurveyName" => null,
            "DestSurveyID" => null,
        ];
        return importSurveyFile(
            $filename,
            $params["bTranslateLinkFields"],
            $params["sNewSurveyName"],
            $params["DestSurveyID"],
            $baselang
        );
    }

    /**
     * Sample command: php application/commands/console.php importsurvey tmp/upload/youfile.lss
     * An optional base language can be appended after a colon: tmp/upload/youfile.lss:de
     *
     * @param array $args Command line arguments, the first one is the file to import
     * @return int Exit code: 0 on success, 1 on failure
     */
    #[\Override]
    public function run($args)
    {
        if (empty($args[0])) {
            echo "Usage: php application/commands/console.php importsurvey <file>[:<baselanguage>]\n";
            echo "Imports a survey from a .lss, .lsa, .txt or .tsv file and prints the new survey ID.\n";
            return 1;
        }
        $source = $args[0];
        $baselang = null;
        if (strpos($args[0], ':') !== false) {
            [$source, $baselang] = explode(":", $args[0], 2);
        }
        if (!is_file($source) || !is_readable($source)) {
            echo "File {$source} not found or not readable.\n";
            return 1;
        }
        $result = $this->importFile($source, $baselang);
        if (is_array($result) && isset($result['newsid'])) {
            echo $result['newsid'] . "\n";
            return 0;
        }
        echo "The import has failed.\n";
        return 1;
    }
}
