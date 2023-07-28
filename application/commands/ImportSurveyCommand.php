<?php

include "application/helpers/admin/import_helper.php";

class ImportSurveyCommand extends CConsoleCommand
{

    protected function importFile($filename)
    {
        importSurveyFile(
            Yii::app()->getConfig('tempdir') . DIRECTORY_SEPARATOR . "templates" . DIRECTORY_SEPARATOR . $filename,
            false
        );
    }

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
                if (!preg_match('/^[a-zA-Z0-9\.]*$/', $filename)) {
                    throw new Exception("Your filename can only contain letters, digits and dot");
                }
                $this->importFile($filename);
            } break;
        }
    }
}