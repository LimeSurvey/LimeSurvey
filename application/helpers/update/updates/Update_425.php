<?php

namespace LimeSurvey\Helpers\Update;

class Update_425 extends DatabaseUpdateBase
{
    public function run()
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
                    } catch (throwable $e) {
                        continue;
                    }
                }
            }
    }
}