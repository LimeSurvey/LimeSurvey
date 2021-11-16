<?php

namespace LimeSurvey\Helpers\Update;

use QuestionTheme;
use Throwable;

class Update_425 extends DatabaseUpdateBase
{
    public function up()
    {
        $aUserDirectory = QuestionTheme::getAllQuestionXMLPaths(false, false, true);
        if (!empty($aUserDirectory)) {
            reset($aUserDirectory);
            $aUserXMLPaths = key($aUserDirectory);
            foreach ($aUserDirectory[$aUserXMLPaths] as $sXMLDirectoryPath) {
                try {
                    $aSuccess = QuestionTheme::convertLS3toLS5($sXMLDirectoryPath);
                    if ($aSuccess['success']) {
                        $oQuestionTheme = new QuestionTheme();
                        $oQuestionTheme->importManifest($sXMLDirectoryPath, true);
                    }
                } catch (Throwable $e) {
                    continue;
                }
            }
        }
    }
}
