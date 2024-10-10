<?php

class QuickTranslationController extends LSBaseController
{
    /**
     * Here we have to use the correct layout (NOT main.php)
     *
     * @param string $view
     * @return bool
     */
    protected function beforeRender($view)
    {
        $this->layout = 'layout_questioneditor';
        LimeExpressionManager::SetSurveyId($this->aData['surveyid']);
        LimeExpressionManager::StartProcessingPage(false, true);

        return parent::beforeRender($view);
    }

    /**
     *
     *
     * @param $surveyid
     * @return void
     * @throws CHttpException
     */
    public function actionIndex($surveyid)
    {
        /* existing + read (survey) already checked in SurveyCommonAction : existing use model : then if surveyid is not valid : return a 404 */
        /* survey : read OK, not survey:tranlations:read … */
        if (!Permission::model()->hasSurveyPermission($surveyid, 'translations', 'read')) {
            throw new CHttpException(401, "401 Unauthorized");
        }

        $oSurvey = Survey::model()->findByPk($surveyid);

        //------------------------ Initial and get helper classes  --------------
        //KCFINDER SETTINGS
        Yii::app()->session['FileManagerContext'] = "edit:survey:{$oSurvey->sid}";
        Yii::app()->loadHelper('admin.htmleditor');
        initKcfinder();

        App()->getClientScript()->registerScriptFile(App()->getConfig('adminscripts') . 'translation.js');
        Yii::app()->loadHelper("database");
        Yii::app()->loadHelper("admin.htmleditor");
        Yii::app()->loadHelper("surveytranslator");
        //-----------------------------------------------------------------------

        //this GET-Param is the language to which it should be translated (e.g. 'de')
        $languageToTranslate = Yii::app()->getRequest()->getParam('lang');

        if (!empty($languageToTranslate) && !in_array($languageToTranslate, $oSurvey->getAllLanguages())) {
            Yii::app()->setFlashMessage(gT("Invalid language"), 'warning');
            $languageToTranslate = null;
        }
        $action = Yii::app()->getRequest()->getParam('action');
        $actionvalue = Yii::app()->getRequest()->getPost('actionvalue');

        if ($action == "ajaxtranslategoogleapi") {
            echo $this->translateGoogleApi();
            return;
        }

        $baselang = $oSurvey->language;
        $additionalLanguages = $oSurvey->additionalLanguages;

        //set it directly to the first additional language (if any exists), if no language was selected by user
        if (empty($languageToTranslate) && count($additionalLanguages) > 0) {
            $languageToTranslate = $additionalLanguages[0];
        }

        $survey_title = $oSurvey->defaultlanguage->surveyls_title;
        $supportedLanguages = getLanguageData(false, Yii::app()->session['adminlang']);

        $baselangdesc = $supportedLanguages[$baselang]['description'];

        $aData = array(
            "surveyid" => $surveyid,
            "survey_title" => $survey_title,
            "tolang" => $languageToTranslate,
        );
        $quickTranslation = new \LimeSurvey\Models\Services\QuickTranslation($oSurvey);

        if (!empty($languageToTranslate)) {
            // Only save if the administration user has the correct permission
            //todo: this is only necessary on save ...
            if ($actionvalue == "translateSave" && Permission::model()->hasSurveyPermission($surveyid, 'translations', 'update')) {
                $this->translateSave($languageToTranslate, $quickTranslation);
                Yii::app()->setFlashMessage(gT("Saved"), 'success');
            }

            $tolangdesc = $supportedLanguages[$languageToTranslate]['description'];
            // display tabs with fields to translate, as well as input fields for translated values

            //todo: this view information has to be passed to the view
            $views = $this->displayUntranslatedFields($quickTranslation, $languageToTranslate, $baselang, $baselangdesc, $tolangdesc);
        }

        $aData['sidemenu']['state'] = false;
        $aData['title_bar']['title'] = $oSurvey->currentLanguageSettings->surveyls_title . " (" . gT("ID") . ":" . $surveyid . ")";
        if (Permission::model()->hasSurveyPermission($surveyid, 'translations', 'update')) {
            $aData['surveybar']['savebutton']['form'] = 'translateform';
            $aData['topBar']['showSaveButton'] = true;
            //buttons in topbar
            $topbarData = TopbarConfiguration::getSurveyTopbarData($oSurvey->sid);
            $topbarData = array_merge($topbarData, $aData['topBar']);
            $aData['topbar']['middleButtons'] = $this->renderPartial(
                '/surveyAdministration/partial/topbar/surveyTopbarLeft_view',
                $topbarData,
                true
            );
            $aData['topbar']['rightButtons'] = $this->renderPartial(
                '/surveyAdministration/partial/topbar/surveyTopbarRight_view',
                $topbarData,
                true
            );
        }

        $aData['display']['menu_bars'] = false;
        $this->aData = $aData;
        $this->render('index', [
            'survey' => $oSurvey,
            'languageToTranslate' => $languageToTranslate,
            'additionalLanguages' => $additionalLanguages,
            'viewData' => $views
        ]);
    }

    /**
     * @param $survey Survey the survey object
     * @param $tolang
     * @param $baselang
     * @param $quickTranslation \LimeSurvey\Models\Services\QuickTranslation the quicktranslation object
     * @return void
     */
    private function translateSave($tolang, $quickTranslation)
    {
        $tab_names = $quickTranslation->getTabNames();
        $tab_names_full = $tab_names;

        //todo: this part could also into QuickTranslation
        foreach ($tab_names as $type) {
            $amTypeOptions = $quickTranslation->setupTranslateFields($type);
            $type2 = $amTypeOptions["associated"];

            if (!empty($type2)) {
                $tab_names_full[] = $type2;
            }
        }

        foreach ($tab_names_full as $type) {
            $size = (int) Yii::app()->getRequest()->getPost("{$type}_size"); //todo: what is size here?
            // start a loop in order to update each record
            $i = 0;
            while ($i <= $size) {
                // define each variable
                if (Yii::app()->getRequest()->getPost("{$type}_newvalue_{$i}")) {
                    $old = Yii::app()->getRequest()->getPost("{$type}_oldvalue_{$i}");
                    $new = Yii::app()->getRequest()->getPost("{$type}_newvalue_{$i}");

                    // check if the new value is different from old, and then update database
                    if ($new != $old) {
                        $qidOrGid = Yii::app()->getRequest()->getPost("{$type}_id1_{$i}");
                        $answerCode = Yii::app()->getRequest()->getPost("{$type}_id2_{$i}");
                        $iScaleID = Yii::app()->getRequest()->getPost("{$type}_scaleid_{$i}");
                        $quickTranslation->updateTranslations($type, $tolang, $new, $qidOrGid, $answerCode, $iScaleID);
                    }
                }
                $i++;
            } // end while
        } // end foreach
    }

    /**
     * Collecting database data and ckeditor data for the views.
     *
     * @param $quickTranslation \LimeSurvey\Models\Services\QuickTranslation the quick translation object
     * @param $tolang string language to translate to
     * @param $baselang  string the base language
     * @param $baselangdesc string the base language description
     * @param $tolangdesc string the language to translate description
     *
     * @return array
     * @throws CException
     */
    private function displayUntranslatedFields($quickTranslation, $tolang, $baselang, $baselangdesc, $tolangdesc)
    {
        // Define aData
        $survey = $quickTranslation->getSurvey();
        $tabsViewData['surveyid'] = $survey->sid;
        $tabsViewData['tab_names'] = $quickTranslation->getTabNames();
        $tabsViewData['tolang'] = $tolang;
        $tabsViewData['baselang'] = $baselang;
        $tabsViewData['baselangdesc'] = $baselangdesc;
        $tabsViewData['tolangdesc'] = $tolangdesc;

        //This is for the tab navbar
        $tabsViewData['amTypeOptions'] = $quickTranslation->getAllTranslateFields();

        //this array will contain all necessary data for the views
        /*
         * structure will be like
         *
         * $tabsViewData['tabname'] = [
         *      'data1' => ''
         *      ....
         *      'tabFieldData = []
         * ]
         */
        $tabsViewData['singleTabs'] = [];

        //iterate through all tabs and define content of each tab
        foreach ($tabsViewData['tab_names'] as $tabName) {
            $singleTabData = [];
            $translateFieldOptions = $quickTranslation->setupTranslateFields($tabName);
            $translationsBase = $quickTranslation->getTranslations($tabName, $baselang);
            $translationsTo = $quickTranslation->getTranslations($tabName, $tolang);
            $associatedName = $translateFieldOptions["associated"];
            $associated = false;
            if (!empty($associatedName)) {
                $associated = true;
                //get type options again
                $associatedData = $quickTranslation->setupTranslateFields($associatedName);
            }
            $singleTabData['type'] = $tabName;
            //always set first tab active
            $singleTabData['activeTab'] = $tabName === 'title';

            //iterates through active record results depending on the tab
            $countResultBase = count($translationsBase);
            for ($j = 0; $j < $countResultBase; $j++) {
                $singleTabFieldsData = [];
                $translationBase = $translationsBase[$j];
                $translationTo = $translationsTo[$j];

                $translationAttributesBase = [];
                $translationAttributesTo = [];
                $class = get_class($translationBase);
                if ($class === 'QuestionGroup') {
                    $translationAttributesBase = $translationBase->questiongroupl10ns[$baselang]->getAttributes();
                    $translationAttributesTo = array_key_exists($tolang, $translationTo->questiongroupl10ns)
                        ? $translationTo->questiongroupl10ns[$tolang]->getAttributes()
                        : $translationAttributesBase;
                } elseif ($class === 'Question' || $class === 'Subquestion') {
                    $translationAttributesBase = $translationBase->questionl10ns[$baselang]->getAttributes();
                    if (!empty($translationBase['parent_qid'])) {
                        $translationAttributesBase['parent'] = $translationBase->parent->getAttributes();
                    }
                    $translationAttributesTo = array_key_exists($tolang, $translationTo->questionl10ns)
                        ? $translationTo->questionl10ns[$tolang]->getAttributes()
                        : $translationAttributesBase;
                } elseif ($class === 'Answer') {
                    $translationAttributesBase = $translationBase->answerl10ns[$baselang]->getAttributes();
                    $translationAttributesBase['question_title'] = $translationBase->question->title; ///this is the question code
                    $translationAttributesTo = array_key_exists($tolang, $translationTo->answerl10ns)
                        ? $translationTo->answerl10ns[$tolang]->getAttributes()
                        : $translationAttributesBase;
                }
                $translationAttributesBase = array_merge($translationAttributesBase, $translationBase->getAttributes());
                $translationAttributesTo = array_merge($translationAttributesTo, $translationTo->getAttributes());

                $textfrom = htmlspecialchars_decode((string)$translationAttributesBase[$translateFieldOptions["dbColumn"]]);

                $textto = $translationAttributesTo[$translateFieldOptions["dbColumn"]];
                if ($associated) {
                    $associatedResultBase = htmlspecialchars_decode((string)$translationAttributesBase[$associatedData["dbColumn"]]);
                    $associatedResultTo = $translationAttributesTo[$associatedData["dbColumn"]];
                }

                $gid = ($translateFieldOptions["gid"] == true) ? $translationAttributesBase['gid'] : null;
                $qid = ($translateFieldOptions["qid"] == true) ? $translationAttributesBase['qid'] : null;

                $textform_length = strlen(trim($textfrom));
                $textfrom2_length = $associated ? strlen(trim((string)$associatedResultBase)) : 0;

                $singleTabFieldsData['all_fields_empty'] = ($textform_length == 0) && ($textfrom2_length == 0);

                $singleTabFieldsData['fieldData'] = [
                    'textfrom'       => $this->cleanup($textfrom),
                    'textto'         => $this->cleanup($textto),
                    'rowfrom'        => $translationAttributesBase,
                    'gid'            => $gid,
                    'qid'            => $qid,
                    'amTypeOptions'  => $translateFieldOptions,
                    'associatedData' => $associatedData,
                    'i'              => $j,
                    'type'           => $tabName,
                    'associatedName' => $associatedName,
                    'associated'     => $associated,
                ];
                $singleTabFieldsData['translateFields'] = [];
                $singleTabFieldsData['translateFields'][] = [
                    'surveyId'      => $survey->sid,
                    'gid'           => $gid,
                    'qid'           => $qid,
                    'type'          => $tabName,
                    'amTypeOptions' => $translateFieldOptions,
                    'textfrom'      => $textfrom,
                    'textto'        => $textto,
                    'j'             => $j,
                    'rowfrom'       => $translationAttributesBase,
                    'nrows'         => max($this->calcNRows($textfrom), $this->calcNRows($textto))
                ];
                if ($associated && strlen(trim((string)$associatedResultBase)) > 0) {
                    $singleTabFieldsData['translateFields'][] = [
                        'surveyId'      => $survey->sid,
                        'gid'           => $gid,
                        'qid'           => $qid,
                        'type'          => $associatedName,
                        'amTypeOptions' => $associatedData,
                        'textfrom'      => $associatedResultBase,
                        'textto'        => $associatedResultTo,
                        'j'             => $j,
                        'rowfrom'       => $translationAttributesBase,
                        'nrows'         => max($this->calcNRows($associatedResultBase), $this->calcNRows($associatedResultTo))
                    ];
                }
                $singleTabData['singleTabFieldsData'][] = $singleTabFieldsData;
            } // end for

            $tabsViewData['bReadOnly'] = !Permission::model()->hasSurveyPermission($survey->sid, 'translations', 'update');
            $tabsViewData['singleTabs'][] = $singleTabData;
        } // end foreach
        return $tabsViewData;
    }

    /**
     *
     *
     * @param string $string
     * @return string|null
     */
    private function cleanup($string): ?string
    {
        if (extension_loaded('tidy')) {
            $oTidy = new tidy();

            $cleansedString = $oTidy->repairString($string, array(), 'utf8');
        } else {
            //We should check for tidy on Installation!
            $cleansedString = $string;
        }

        return $cleansedString;
    }

    /**
     * It loads the correct editor mode (inline, popup, modal).
     * This is used in the view file translateFieldData.
     *
     * @param $htmleditor
     * @param string[] $aData
     * @return mixed
     */
    protected function loadEditor($htmleditor, $aData)
    {
        $editor_function = "";
        $displayType = strtolower((string) $htmleditor["HTMLeditorDisplay"]);
        $displayTypeIsEmpty = empty($displayType);

        if ($displayType == "inline" || $displayTypeIsEmpty) {
            $editor_function = "getEditor";
        } elseif ($displayType == "popup") {
            $editor_function = "getPopupEditor";
            $aData[2] = urlencode((string) $htmleditor['description']);
        } elseif ($displayType == "modal") {
            $editor_function = "getModalEditor";
            $aData[2] = $htmleditor['description'];
        }
        return call_user_func_array($editor_function, $aData);
    }

    /**
     * calcNRows($subject) calculates the vertical size of textbox for survey translation.
     * The function adds the number of line breaks <br /> to the number of times a string wrap occurs.
     * @param string $subject The text string that is being translated
     * @return double
     */
    private function calcNRows($subject)
    {
        if ($subject === null) {
            $subject = "";
        }
        // Determines the size of the text box
        // A proxy for box sixe is string length divided by 80
        $pattern = "(<br..?>)";
        $pattern = '[(<br..?>)|(/\n/)]';

        $nrows_newline = preg_match_all($pattern, $subject, $matches);

        $subject_length = strlen((string) $subject);
        $nrows_char = ceil($subject_length / 80);

        return $nrows_newline + $nrows_char;
    }

    /**
     *
     *
     * @return void
     */
    public function actionAjaxtranslategoogleapi($surveyid)
    {
        // Ensure YII_CSRF_TOKEN, we are in admin, then only user with admin right can post
        /* No Permission check on survey, seems unneded (return a josn with current string posted */

        //todo: check if googletranslate is activated ...
        if (!Permission::model()->hasSurveyPermission($surveyid, 'translations', 'read')) {
            throw new CHttpException(401, "401 Unauthorized");
        }
        if (Yii::app()->request->isPostRequest) {
            echo self::translateGoogleApi();
        }
    }

    /**
     * translateGoogleApi.php
     * Creates a JSON interface for the auto-translate feature
     *
     * @psalm-suppress UndefinedClass TODO: Dead code?
     * @psalm-suppress MissingFile
     */
    private function translateGoogleApi()
    {
        $sBaselang   = Yii::app()->getRequest()->getPost('baselang', '');
        $sTolang     = Yii::app()->getRequest()->getPost('tolang', '');
        $sToconvert  = Yii::app()->getRequest()->getPost('text', '');

        $aSearch     = array('zh-Hans', 'zh-Hant-HK', 'zh-Hant-TW', 'nl-informal', 'de-informal', 'de-easy', 'it-formal', 'pt-BR', 'es-MX', 'nb', 'nn');
        $aReplace    = array('zh-CN', 'zh-TW', 'zh-TW', 'nl', 'de', 'de', 'it', 'pt', 'es', 'no', 'no');
        $sBaselang = str_replace($aSearch, $aReplace, $sBaselang);
        $sTolang = str_replace($aSearch, $aReplace, $sTolang);

        $error = false;

        try {
            require_once(APPPATH . '/../vendor/gtranslate-api/GTranslate.php');
            $gtranslate = new Gtranslate();
            // use curl because http with fopen is disabled
            $gtranslate->setRequestType('curl');
            $objGt = $gtranslate;

            // Gtranslate requires you to run function named XXLANG_to_XXLANG
            $sProcedure = $sBaselang . "_to_" . $sTolang;

            $parts = LimeExpressionManager::SplitStringOnExpressions($sToconvert);

            $sparts = array();
            foreach ($parts as $part) {
                if ($part[2] == 'EXPRESSION') {
                    $sparts[] = $part[0];
                } else {
                    $convertedPart = (string) $objGt->$sProcedure($part[0]);
                    $convertedPart  = str_replace("<br>", "\r\n", $convertedPart);
                    $convertedPart  = html_entity_decode(stripcslashes($convertedPart));
                    $sparts[] = $convertedPart;
                }
            }
            $sOutput = implode(' ', $sparts);
        } catch (GTranslateException $ge) {
            // Get the error message and build the ouput array
            $error = true;
            $sOutput = $ge->getMessage();
        }

        $aOutput = array(
            'error'     =>  $error,
            'baselang'  =>  $sBaselang,
            'tolang'    =>  $sTolang,
            'converted' =>  $sOutput
        );

        header('Content-type: application/json');
        return ls_json_encode($aOutput);
    }
}
