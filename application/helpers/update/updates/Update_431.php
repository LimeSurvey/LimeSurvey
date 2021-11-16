<?php

namespace LimeSurvey\Helpers\Update;

use SurveymenuEntries;
use LsDefaultDataSets;

/**
 * @SuppressWarnings(PHPMD)
 */
class Update_431 extends DatabaseUpdateBase
{
    public function up()
    {
        $this->db->createCommand()->update(
            '{{boxes}}',
            array(
                'url' => 'surveyAdministration/listsurveys',
            ),
            "url='admin/survey/sa/listsurveys'"
        );
        $this->db->createCommand()->update(
            '{{boxes}}',
            array(
                'url' => 'surveyAdministration/newSurvey',
            ),
            "url='admin/survey/sa/newSurvey'"
        );
        $this->db->createCommand()->update(
            '{{surveymenu_entries}}',
            array(
                'name' => 'listQuestionGroups',
                'menu_link' => 'questionGroupsAdministration/listquestiongroups',
            ),
            "name='listSurveyGroups'"
        );
        $this->db->createCommand()->update(
            '{{surveymenu_entries}}',
            array(
                'menu_link' => 'questionAdministration/listQuestions',
            ),
            "name='listQuestions'"
        );
        $this->db->createCommand()->update(
            '{{surveymenu_entries}}',
            array(
                'menu_link' => 'surveyAdministration/view',
            ),
            "name='overview'"
        );
        $this->db->createCommand()->update(
            '{{surveymenu_entries}}',
            array(
                'menu_link' => 'surveyAdministration/activate',
            ),
            "name='activateSurvey'"
        );
        $this->db->createCommand()->update(
            '{{surveymenu_entries}}',
            array(
                'menu_link' => 'surveyAdministration/deactivate',
            ),
            "name='deactivateSurvey'"
        );
        $this->db->createCommand()->update(
            '{{surveymenu_entries}}',
            array(
                'getdatamethod' => 'generalTabEditSurvey',
            ),
            "name='generalsettings'"
        );
        $this->db->createCommand()->update(
            '{{surveymenu_entries}}',
            array(
                'getdatamethod' => 'getTextEditData',
            ),
            "name='surveytexts'"
        );
        $this->db->createCommand()->update(
            '{{surveymenu_entries}}',
            array(
                'getdatamethod' => 'getDataSecurityEditData',
            ),
            "name='datasecurity'"
        );
        $this->db->createCommand()->update(
            '{{surveymenu_entries}}',
            array(
                'getdatamethod' => 'tabPresentationNavigation',
            ),
            "name='presentation'"
        );
        $this->db->createCommand()->update(
            '{{surveymenu_entries}}',
            array(
                'getdatamethod' => 'tabTokens',
            ),
            "name='tokens'"
        );
        $this->db->createCommand()->update(
            '{{surveymenu_entries}}',
            array(
                'getdatamethod' => 'tabNotificationDataManagement',
            ),
            "name='notification'"
        );
        $this->db->createCommand()->update(
            '{{surveymenu_entries}}',
            array(
                'getdatamethod' => 'tabPublicationAccess',
            ),
            "name='publication'"
        );
        $this->db->createCommand()->update(
            '{{surveymenu_entries}}',
            array(
                'getdatamethod' => 'tabPanelIntegration',
            ),
            "name='panelintegration'"
        );
        $this->db->createCommand()->update(
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
                    $this->db->createCommand()->insert('{{surveymenu_entries}}', $aSurveymenuentry);
                    SurveymenuEntries::reorderMenu(2);
                }
                break;
            }
        }
    }
}
