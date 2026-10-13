<?php

use Anper\Iuliia\Iuliia;

class QuickTranslationController extends LSBaseController
{
    // Define the language code mappings from LimeSurvey to Google Translate API
    // where the codes are different
    public $languageCodeMappings = array(
        'cs-informal' => 'cs',
        'de-informal' => 'de',
        'de-easy' => 'de',
        'es-informal' => 'es',
        'es-AR' => 'es',
        'es-AR-informal' => 'es',
        'es-CL' => 'es',
        'es-CO' => 'es',
        'es-MX' => 'es',
        'hat' => 'ht',
        'he' => 'iw',
        'ie' => 'ga',
        'it-informal' => 'it',
        'mya' => 'my',
        'nb' => 'no',
        'nl-informal' => 'nl',
        'nn' => 'no',
        'prs' => 'fa',
        'roh' => 'rm',
        'smi' => 'se',
        'swh' => 'sw',
        'xho' => 'xh',
        'yor' => 'yo',
        'zh-Hans' => 'zh-CN',
        'zh-Hant-HK' => 'zh-TW',
        'zh-Hant-TW' => 'zh-TW',
    );

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
        /* survey : read OK, not survey:translations:read … */
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
        App()->getClientScript()->registerPackage('quicktranslation');
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
                $this->translateSave($languageToTranslate, $quickTranslation, $surveyid);
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
     * Saves the posted translations without rendering the page, e.g. the texts of one question before
     * previewing it. The posted fields are the same as for saving the whole page.
     *
     * @param int $surveyid
     * @return void
     * @throws CHttpException
     */
    public function actionAjaxSave($surveyid)
    {
        $surveyid = (int) $surveyid;
        if (!Permission::model()->hasSurveyPermission($surveyid, 'translations', 'update')) {
            throw new CHttpException(403, gT("You do not have permission to access this page."));
        }
        if (!App()->getRequest()->getIsPostRequest()) {
            throw new CHttpException(405, gT("Invalid action"));
        }
        $survey = Survey::model()->findByPk($surveyid);
        $tolang = App()->getRequest()->getPost('tolang');
        if (!in_array($tolang, $survey->additionalLanguages)) {
            throw new CHttpException(400, gT("Invalid language"));
        }
        $this->translateSave($tolang, new \LimeSurvey\Models\Services\QuickTranslation($survey), $surveyid);
        $this->renderJSON(['success' => true]);
    }

    /**
     * @param $survey Survey the survey object
     * @param $tolang
     * @param $baselang
     * @param $quickTranslation \LimeSurvey\Models\Services\QuickTranslation the quicktranslation object
     * @param $surveyid
     * @return void
     */
    private function translateSave($tolang, $quickTranslation, $surveyid)
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

        $updateOccurred = false;
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
                        $updateOccurred = true;
                    }
                }
                $i++;
            } // end while
        } // end foreach

        if ($updateOccurred) {
            $survey = Survey::model()->findByPk($surveyid);
            if ($survey) {
                $surveyDetailService = new \LimeSurvey\Models\Services\SurveyDetailService();
                $surveyDetailService->updateSurveyLastModified($survey);
            }
        }
    }

    /**
     * Collecting database data and ckeditor data for the views.
     * The fields are arranged like the survey is structured (see QuickTranslation::getSurveyStructure()), the
     * names of the form fields are those translateSave() reads.
     *
     * @param \LimeSurvey\Models\Services\QuickTranslation $quickTranslation the quick translation object
     * @param string $tolang language to translate to
     * @param string $baselang the base language
     * @param string $baselangdesc the base language description
     * @param string $tolangdesc the language to translate description
     *
     * @return array
     * @throws CException
     */
    private function displayUntranslatedFields($quickTranslation, $tolang, $baselang, $baselangdesc, $tolangdesc)
    {
        $survey = $quickTranslation->getSurvey();
        $viewData = [
            'surveyid' => $survey->sid,
            'tolang' => $tolang,
            'baselang' => $baselang,
            'baselangdesc' => $baselangdesc,
            'tolangdesc' => $tolangdesc,
            'bReadOnly' => !Permission::model()->hasSurveyPermission($survey->sid, 'translations', 'update'),
            'canPreview' => Permission::model()->hasSurveyPermission($survey->sid, 'surveycontent', 'read'),
            'fieldCounts' => [],
        ];

        // All fields per field type, indexed by the record index used in the form field names
        $fieldsByType = [];
        foreach ($quickTranslation->getTabNames() as $tabName) {
            $translateFieldOptions = $quickTranslation->setupTranslateFields($tabName);
            $translationsBase = $quickTranslation->getTranslations($tabName, $baselang);
            $translationsTo = $quickTranslation->getTranslations($tabName, $tolang);
            $fieldTypes = [$tabName => $translateFieldOptions];
            $associatedName = $translateFieldOptions["associated"];
            if (!empty($associatedName)) {
                $fieldTypes[$associatedName] = $quickTranslation->setupTranslateFields($associatedName);
            }

            $countResultBase = count($translationsBase);
            foreach (array_keys($fieldTypes) as $type) {
                $viewData['fieldCounts'][$type] = $countResultBase;
                $fieldsByType[$type] = [];
            }
            for ($j = 0; $j < $countResultBase; $j++) {
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

                $gid = ($translateFieldOptions["gid"] == true) ? $translationAttributesBase['gid'] : null;
                $qid = ($translateFieldOptions["qid"] == true) ? $translationAttributesBase['qid'] : null;

                foreach ($fieldTypes as $type => $typeOptions) {
                    $textfrom = htmlspecialchars_decode((string) $translationAttributesBase[$typeOptions["dbColumn"]]);
                    $textto = $translationAttributesTo[$typeOptions["dbColumn"]];
                    $fieldsByType[$type][$j] = [
                        'surveyId'      => $survey->sid,
                        'gid'           => $gid,
                        'qid'           => $qid,
                        'type'          => $type,
                        'amTypeOptions' => $typeOptions,
                        'textfrom'      => $textfrom,
                        'textto'        => $textto,
                        'j'             => $j,
                        'rowfrom'       => $translationAttributesBase,
                        'nrows'         => max($this->calcNRows($textfrom), $this->calcNRows($textto)),
                    ];
                }
            }
        }
        $viewData['structure'] = $quickTranslation->getSurveyStructure($fieldsByType);
        return $viewData;
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
            $aData[2] = urlencode((string) $htmleditor['fieldLabel']);
        } elseif ($displayType == "modal") {
            $editor_function = "getModalEditor";
            $aData[2] = $htmleditor['fieldLabel'];
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
        /* No Permission check on survey, seems unneeded (return a josn with current string posted */

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
        $sourceLanguage   = Yii::app()->getRequest()->getPost('baselang', '');
        $destinationLanguage     = Yii::app()->getRequest()->getPost('tolang', '');
        $sToconvert  = Yii::app()->getRequest()->getPost('text', '');

        $replacements = $this->languageCodeMappings;
        $sourceLanguage = isset($replacements[$sourceLanguage]) ? $replacements[$sourceLanguage] : $sourceLanguage;
        $destinationLanguage = isset($replacements[$destinationLanguage]) ? $replacements[$destinationLanguage] : $destinationLanguage;
        $translateApiKey = App()->getConfig('googletranslateapikey');
        $errorMessageText = false;
        $sOutput = '';
        try {
            $gtranslate = new \GoogleTranslate\Client($translateApiKey);
            // use curl because http with fopen is disabled
            $parts = LimeExpressionManager::SplitStringOnExpressions($sToconvert);

            $sparts = array();
            foreach ($parts as $part) {
                if ($part[2] == 'EXPRESSION') {
                    $sparts[] = $part[0];
                } else {
                    $convertedPart = $gtranslate->translate($part[0], $destinationLanguage, $sourceLanguage);

                    $convertedPart  = str_replace("<br>", "\r\n", $convertedPart);
                    $convertedPart  = html_entity_decode(stripcslashes($convertedPart));
                    if ($destinationLanguage == 'sr-Latn') {
                        $convertedPart = Iuliia::translate($convertedPart, Iuliia::TELEGRAM);
                    }
                    $sparts[] = $convertedPart;
                }
            }
            $sOutput = implode(' ', $sparts);
        } catch (\Exception $ge) {
            // Get the error message and remove the key for security reasons
            $errorMessage = $ge->getMessage();
            if ($translateApiKey) {
                $errorMessage = str_replace($translateApiKey, '*****', $errorMessage);
            }

            // Use regex to extract the first "message": "..." pattern
            if (preg_match('/"message"\s*:\s*"([^"]+)"/', $errorMessage, $matches)) {
                $errorMessageText = $matches[1];
            } else {
                $errorMessageText = $errorMessage;
            }
        }

        $aOutput = array(
            'error'     =>  $errorMessageText,
            'baselang'  =>  $sourceLanguage,
            'tolang'    =>  $destinationLanguage,
            'converted' =>  $sOutput
        );

        header('Content-type: application/json');
        return ls_json_encode($aOutput);
    }
}
