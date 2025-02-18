<?php

Yii::import('application.helpers.replacements_helper', true);
Yii::import('application.helpers.expressions.em_manager_helper', true);
Yii::import('application.helpers.common_helper', true);
Yii::import('application.helpers.admin.import_helper', true);

class ImportSurveyCommand extends CConsoleCommand
{
    /**
     * @param string $filename
     * @return array Import result
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
     *
     * @param array $args
     * @return void
     */
    public function run($args)
    {
        $source = $args[0];
        $baselang = null;
        if (strpos($args[0], ':') !== 0) {
            $split = explode(":", $args[0]);
            $source = $split[0];
            $baselang = $split[1];
        }
        $file = $source;
        $result = $this->importFile($file, $baselang);
        if (is_array($result) && isset($result['newsid'])) {
            echo $result['newsid'];
        } else {
            echo "The import has failed.";
        }
    }
}
