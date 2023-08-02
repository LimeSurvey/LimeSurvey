<?php

Yii::import('application.helpers.replacements_helper', true);
Yii::import('application.helpers.expressions.em_manager_helper', true);
Yii::import('application.helpers.common_helper', true);
Yii::import('application.helpers.admin.import_helper', true);

class ImportSurveyCommand extends CConsoleCommand
{

    /**
     * @param string $filename
     * 
     * Sample command: php application/commands/console.php importsurvey import-file abcf.lss
     *                 php application/commands/console.php importsurvey import-file limesurvey_survey_979573.lss '{"bTranslateLinkFields": true, "sNewSurveyName": "Orsson", "DestSurveyID": 666999}'
     */
    protected function importFile($filename, $furtherParams)
    {
        $params = [
            "bTranslateLinkFields" => false,
            "sNewSurveyName" => null,
            "DestSurveyID" => null,
        ];
        if ($json = json_decode($furtherParams ?? "{}", true)) {
            foreach ($params as $key => $value) {
                $params[$key] = $json[$key] ?? $params[$key];
            }
        }
        importSurveyFile(
            Yii::app()->getConfig('tempdir') . DIRECTORY_SEPARATOR . "templates" . DIRECTORY_SEPARATOR . $filename,
            $params["bTranslateLinkFields"],
            $params["sNewSurveyName"],
            $params["DestSurveyID"],
        );
    }

    /**
     * @param array $aArguments
     */
    public function run($sArgument)
    {
        if (!count($sArgument)) {
            throw new Exception("You need to specify the command to be executed");
        }
        $command = $sArgument[0];
        switch ($command) {
            case "import-file": {
                if (count($sArgument) < 2) {
                    throw new Exception("You need to specify the file to import from");
                }
                $filename = $sArgument[1];
                if (!preg_match('/^[a-zA-Z0-9_\.]*$/', $filename)) {
                    throw new Exception("Your filename can only contain letters, digits and dot");
                }
                $furtherParams = $sArgument[2] ?? null;
                $this->importFile($filename, $furtherParams);
            } break;
            default: throw new Exception("Unsupported command");
        }
    }
}