<?php

Yii::import('application.helpers.replacements_helper', true);
Yii::import('application.helpers.expressions.em_manager_helper', true);
Yii::import('application.helpers.common_helper', true);
Yii::import('application.helpers.admin.import_helper', true);

class ImportSurveyCommand extends CConsoleCommand
{

    const SUPPORTED_CATEGORIES = [
        'Business',
        /* Further categories here */
    ];

    /**
     * @param string $filename
     * 
     * Sample command: php application/commands/console.php importsurvey import-file Business abcf.lss
     *                 php application/commands/console.php importsurvey import-file Business limesurvey_survey_979573.lss '{"bTranslateLinkFields": true, "sNewSurveyName": "Orsson", "DestSurveyID": 666999}'
     */
    protected function importFile($category, $filename, $furtherParams)
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
        return importSurveyFile(
            "assets" . DIRECTORY_SEPARATOR . "templates" . DIRECTORY_SEPARATOR . $category . DIRECTORY_SEPARATOR . $filename,
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
                if (count($sArgument) < 3) {
                    throw new Exception("You need to specify the category and the file to import from");
                }
                $category = $sArgument[1];
                if (!in_array($category, self::SUPPORTED_CATEGORIES)) {
                    throw new Exception("Unsupported category");
                }
                $filename = $sArgument[2];
                if (!preg_match('/^[a-zA-Z0-9_\.]*$/', $filename)) {
                    throw new Exception("Your filename can only contain letters, digits and dot");
                }
                $furtherParams = $sArgument[3] ?? null;
                $result = $this->importFile($category, $filename, $furtherParams);
                if (is_array($result) && isset($result['newsid'])) {
                    echo $result['newsid'];
                } else {
                    echo "something went wrong";
                }
            } break;
            default: throw new Exception("Unsupported command");
        }
    }
}