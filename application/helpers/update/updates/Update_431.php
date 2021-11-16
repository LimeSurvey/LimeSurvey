            $oTransaction = $oDB->beginTransaction();

            $oDB->createCommand()->update(
                '{{boxes}}',
                array(
                    'url' => 'surveyAdministration/listsurveys',
                ),
                "url='admin/survey/sa/listsurveys'"
            );
            $oDB->createCommand()->update(
                '{{boxes}}',
                array(
                    'url' => 'surveyAdministration/newSurvey',
                ),
                "url='admin/survey/sa/newSurvey'"
            );
            $oDB->createCommand()->update(
                '{{surveymenu_entries}}',
                array(
                    'name' => 'listQuestionGroups',
                    'menu_link' => 'questionGroupsAdministration/listquestiongroups',
                ),
                "name='listSurveyGroups'"
            );
            $oDB->createCommand()->update(
                '{{surveymenu_entries}}',
                array(
                    'menu_link' => 'questionAdministration/listQuestions',
                ),
                "name='listQuestions'"
            );
            $oDB->createCommand()->update(
                '{{surveymenu_entries}}',
                array(
                    'menu_link' => 'surveyAdministration/view',
                ),
                "name='overview'"
            );
            $oDB->createCommand()->update(
                '{{surveymenu_entries}}',
                array(
                    'menu_link' => 'surveyAdministration/activate',
                ),
                "name='activateSurvey'"
            );
            $oDB->createCommand()->update(
                '{{surveymenu_entries}}',
                array(
                    'menu_link' => 'surveyAdministration/deactivate',
                ),
                "name='deactivateSurvey'"
            );
            $oDB->createCommand()->update(
                '{{surveymenu_entries}}',
                array(
                    'getdatamethod' => 'generalTabEditSurvey',
                ),
                "name='generalsettings'"
            );
            $oDB->createCommand()->update(
                '{{surveymenu_entries}}',
                array(
                    'getdatamethod' => 'getTextEditData',
                ),
                "name='surveytexts'"
            );
            $oDB->createCommand()->update(
                '{{surveymenu_entries}}',
                array(
                    'getdatamethod' => 'getDataSecurityEditData',
                ),
                "name='datasecurity'"
            );
            $oDB->createCommand()->update(
                '{{surveymenu_entries}}',
                array(
                    'getdatamethod' => 'tabPresentationNavigation',
                ),
                "name='presentation'"
            );
            $oDB->createCommand()->update(
                '{{surveymenu_entries}}',
                array(
                    'getdatamethod' => 'tabTokens',
                ),
                "name='tokens'"
            );
            $oDB->createCommand()->update(
                '{{surveymenu_entries}}',
                array(
                    'getdatamethod' => 'tabNotificationDataManagement',
                ),
                "name='notification'"
            );
            $oDB->createCommand()->update(
                '{{surveymenu_entries}}',
                array(
                    'getdatamethod' => 'tabPublicationAccess',
                ),
                "name='publication'"
            );
            $oDB->createCommand()->update(
                '{{surveymenu_entries}}',
                array(
                    'getdatamethod' => 'tabPanelIntegration',
                ),
                "name='panelintegration'"
            );
            $oDB->createCommand()->update(
                '{{surveymenu_entries}}',
                array(
                    'getdatamethod' => 'pluginTabSurvey',
                ),
                "name='plugins'"
            );


            $aDefaultSurveyMenuEntries = LsDefaultDataSets::getSurveyMenuEntryData();
            foreach ($aDefaultSurveyMenuEntries as $aSurveymenuentry) {
                if ($aSurveymenuentry['name'] == 'reorder') {
                    if (SurveymenuEntries::model()->findByAttributes(['name' => $aSurveymenuentry['name']]) == null) {
                        $oDB->createCommand()->insert('{{surveymenu_entries}}', $aSurveymenuentry);
                        SurveymenuEntries::reorderMenu(2);
                    }
                    break;
                }
            }
            $oDB->createCommand()->update('{{settings_global}}', array('stg_value' => 431), "stg_name='DBVersion'");
            $oTransaction->commit();
