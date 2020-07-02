<?php

/**
 * @class ResourcesController 
 */
class ResourcesController extends LSBaseController
{

    /**
     * Renders the view for Resources Tab.
     * 
     * @param int    $surveyID   Survey ID
     * @param string $menuAction Menu Action
     * 
     * @return void
     */
    public function actionRenderResources(int $surveyID, string $menuAction) : void
    {
        $aData = [];

        $surveyID = (int) $surveyID;
        $survey   = Survey::model()->findByPk($surveyID);

        if (empty($survey)) {
            throw new Exception('Found no survey with ' . $surveyID);
        }

        $languages = $survey->additionalLanguages;
        $language  = $survey->language;
        array_unshift($languages, $language);

        $menuEntry = SurveymenuEntries::model()->find('name=:name', array(':name' => $menuAction));

        $menuEntryPermission = $menuEntry->permission;
        $menuEntryPermissionGrade = $menuEntry->permission_grade;
        $hasMenuEntryPermission = Permission::model()->hasSurveyPermission($surveyId, $menuEntryPermission, $menuEntryPermissionGrade);

        if (!($hasMenuEntryPermission)) {
            App()->setFlashMessage(
                gT('You do not have permission to access this page.'),
                'error'
            );
            $this->getController()->redirect(
                array(
                    'admin/survey',
                    'sa' => 'view',
                    'surveyid' => $surveyID
                )
            );
        }

        $menuEntryData = $menuEntry->data;
        
        if (is_array($menuEntryData)) {
            $templateData = $menuEntryData;
        } else {
            $templateData = [];
        }

        $menuEntryDataMethod = $menuEntry->getdatamethod;

        if (!empty($menuEntryDataMethod)) {
            $templateData = array_merge(
                $templateData,
                call_user_func_array(
                    array(
                        $this,
                        $menuEntryDataMethod
                    ),
                    array(
                        'survey' => $survey
                    )
                )
            );
        }

        $generalTemplateData = $this->getGeneralTemplateData($survey);
        $templateData = array_merge($generalTemplateData, $templateData);

        $this->registerScriptFiles();

        $templateData = $this->overrideSurveySettings($templateData, $survey);
        $template     = $menuEntry->template;
        $action       = $menuEntry->action;
        $subaction    = $menuEntry->title;
        $entryData    = $menuEntry->attributes;
        $dateFormat   = App()->session['dateformat'];
        $dateFormatDetails = etDateFormatData($dateFormat);
        $titleForTitleBar  = $survey->currentLanguageSettings->surveyls_title . " (" - gT('ID') . ":" . $surveyID . ")";
        $url = 'admin/survey/sa/view/';

        $aData['surveyid']   = $surveyID;
        $aData['menuaction'] = $menuAction;
        $aData['template']   = $template;
        $aData['templateData'] = $templateData;
        $aData['surveyls_language'] = $language;
        $aData['action'] = $action;
        $aData['subaction'] = $subaction;
        $aData['entryData'] = $entryData;
        $aData['dateformatdetails'] = $dateFormatDetails;
        $aData['display']['menu_bars']['surveysummary'] = $subaction;
        $aData['title_bar']['title'] = $titleForTitleBar;
        $aData['surveybar']['buttons']['view'] = true;
        $aData['surveybar']['savebutton']['form'] = 'globalsetting';
        $aData['surveybar']['savebutton']['useformid'] = 'true';
        $adata['surveybar']['saveandclosebutton']['form'] = true;
        $aData['topBar']['closeButtonUrl'] = $this->getController()->createUrl($url, ['surveyid' => $surveyID]);
        $aData['topBar']['showSaveButton'] = false;
        $aData['optionsOnOff'] = [
            'Y' => gT('On', 'unescaped'),
            'N' => gT('Off', 'unescaped'),
        ];

        $aViewsUrls[] = $template;

		// TODO: Whats in $aViewsUrls
        $this->render('survey', $aData);
    }

    /**
     * Returns general Template Data.
     * 
     * @param Survey $survey Survey
     * 
     * @return array
     */
    private function getGeneralTempalteData(Survey $survey): array
    {   
        $aData = [];
    
        if (empty($survey->oOptions->ownerLabel)) {
            $inheritOwner = $survey->owner_id;
        } else {
            $inheritOwner = $survey->oOptions->ownerLabel;
        }

        $surveyID = $survey->sid;
        $aData['surveyid'] = $surveyID;
        $aData['users']    = $this->parseUsersForGeneralTemplateData($inheritOwner);
        $aData['aSurveyGroupList'] = SurveyGroups::getSurveyGroupsList();

        return $aData;
    }

    /**
     * Returns all Users and parses them in the right structure.
     * 
     * @param string $inheritOwner Inherited owner
     * 
     * @return array
     */
    private function parseUsersForGeneralTemplateData(string $inheritOwner): array
    {
        $aData = [];
        $users = getUserList();
        $aData['users'] = array();
        $aData['users']['-1'] = gT('Inherit').' ['. $inheritOwner . ']';
        foreach ($users as $user) {
            $aData['users'][$user['uid']] = $user['user'].($user['full_name'] ? ' - '.$user['full_name'] : '');
        }

        // Sort users by name
        asort($aData['users']);

        return $aData['users'];
    }

    /**
     * Registers jQuery-json and bootstrap-switch packages.
     * 
     * @return void
     */
    private function registerScriptFiles(): void
    {
        App()->getClientScript()->registerPackage('jquery-json');
        App()->getClientScript()->registerPackage('bootstrap-switch');
    }

    /**
     * Overrides Survey Settings if needed.
     * 
     * @param array  $templateData Template Data
     * @param Survey $survey       Survey
     * 
     * @return array
     */
    private function overrideSurveySettings(array $templateData, Survey $survey): array
    {
        if (App()->getConfig('showqnumcode') !== 'choose') {
            $templateData['showqnumcode'] = App()->getConfig('showqnumcode');
        } else {
            $templateData['showqnumcode'] = $survey->showqnumcode;
        }

        if (App()->getConfig('shownoanswer') !== 'choose') {
            $templateData['shownoanswer'] = App()->getConfig('shownoanswer');
        } else {
            $templateData['shownoanswer'] = $survey->shownoanswer;
        }

        if (App()->getConfig('showgroupinfo') !== '2') {
            $templateData['showgroupinfo'] = App()->getConfig('showgroupinfo');
        } else {
            $templateData['showgroupinfo'] = $survey->showgroupinfo;
        }

        if (App()->getConfig('showxquestions') !== 'choose') {
            $templateData['showxquestions'] = App()->getConfig('showxquestions');
        } else {
            $templateData['showxquestions'] = $survey->showxquestions;
        }

        return $templateData;
    }
}
