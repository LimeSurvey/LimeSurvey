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
            $oDB->createCommand()->update('{{settings_global}}', array('stg_value' => 425), "stg_name='DBVersion'");
            $oTransaction->commit();
