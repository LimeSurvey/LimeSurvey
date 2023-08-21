<?php

Yii::import('application.helpers.replacements_helper', true);
Yii::import('application.helpers.expressions.em_manager_helper', true);
Yii::import('application.helpers.common_helper', true);
Yii::import('application.helpers.admin.import_helper', true);

class ImportSurveyCommand extends CConsoleCommand
{

    const ALLOWED_MAIN_PATHS = [
        'tmp' .DIRECTORY_SEPARATOR . 'upload',
    ];

    /**
     * @param string $path
     * @param string $filename
     * @param string $furtherParams JSON representation
     * 
     * @return void
     * 
     * Sample command: php application/commands/console.php importsurvey import-file tmp/upload/youfile.lss
     *                 php application/commands/console.php importsurvey import-file tmp/upload/youfile.lss '{"bTranslateLinkFields": true, "sNewSurveyName": "Orsson", "DestSurveyID": 666999}'
     */
    protected function importFile($path, $filename, $furtherParams)
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
            $path . DIRECTORY_SEPARATOR . $filename,
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
                    throw new Exception("You need to specify the import path");
                }
                $fullPath = $sArgument[1];

                if (($lastSlashPosition = strripos($fullPath, DIRECTORY_SEPARATOR)) === false) {
                    throw new Exception("Invalid path");
                }
                $path = substr($fullPath, 0, $lastSlashPosition);
                $filename = substr($fullPath, $lastSlashPosition + 1);
                if (!preg_match('/^[a-zA-Z0-9_\. ]*$/', $filename)) {
                    throw new Exception("Your filename can only contain letters, digits and dot");
                }
                $furtherParams = $sArgument[2] ?? null;
                $result = $this->importFile($path, $filename, $furtherParams);
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