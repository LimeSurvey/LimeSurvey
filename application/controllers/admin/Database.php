<?php

/*
* LimeSurvey
* Copyright (C) 2013 The LimeSurvey Project Team / Carsten Schmitz
* All rights reserved.
* License: GNU/GPL License v2 or later, see LICENSE.php
* LimeSurvey is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*
*/

use LimeSurvey\Models\Services\Exception\PersistErrorException;

/**
 * Database
 *
 * @package LimeSurvey
 * @author
 * @copyright 2011
 * @access public
 */
class Database extends SurveyCommonAction
{
    /**
     * @var integer Group id
     */
    private $iQuestionGroupID;

    /**
     * @var integer Question id
     */
    private $iQuestionID;

    /**
     * @var integer Survey id
     */
    private $iSurveyID;

    /**
     * @var object LSYii_Validators
     * @todo : use model (and validate if we do it in model rules)
     */
    private $oFixCKeditor;

    /**
     * Database::index()
     * @todo move called functions to their respective Controllers
     * @return void
     */
    public function index()
    {
        $sAction = Yii::app()->request->getPost('action');
        $this->iSurveyID = (isset($_POST['sid'])) ? (int) $_POST['sid'] : (int) returnGlobal('sid');

        $this->iQuestionGroupID = (int) returnGlobal('gid');
        $this->iQuestionID = (int) returnGlobal('qid');

        $this->oFixCKeditor = new LSYii_Validators();
        $this->oFixCKeditor->fixCKeditor = true;
        $this->oFixCKeditor->xssfilter = false;

        if ($sAction == "updatedefaultvalues" && Permission::model()->hasSurveyPermission($this->iSurveyID, 'surveycontent', 'update')) {
            $this->actionUpdateDefaultValues($this->iSurveyID);
        }
        if (($sAction == "updatesurveylocalesettings") && (Permission::model()->hasSurveyPermission($this->iSurveyID, 'surveylocale', 'update') || Permission::model()->hasSurveyPermission($iSurveyID, 'surveysettings', 'update'))) {
            $this->actionUpdateSurveyLocaleSettings($this->iSurveyID);
        }
        if (
            ($sAction == "updatesurveylocalesettings_generalsettings") &&
            (Permission::model()->hasSurveyPermission($this->iSurveyID, 'surveylocale', 'update') ||
                Permission::model()->hasSurveyPermission($this->iSurveyID, 'surveysettings', 'update'))
        ) {
            $this->actionUpdateSurveyLocaleSettingsGeneralSettings($this->iSurveyID);
        }

        Yii::app()->setFlashMessage(gT("Unknown action or no permission."), 'error');
        $this->getController()->redirect(Yii::app()->request->urlReferrer);
    }

    /**
     * This is a convenience function to update/delete answer default values. If the given
     * $defaultvalue is empty then the entry is removed from table defaultvalues
     *
     * @param integer $qid   Question ID
     * @param integer $scale_id  Scale ID
     * @param string $specialtype  Special type (i.e. for  'Other')
     * @param string $language     Language (defaults are language specific)
     * @param mixed $defaultvalue    The default value itself
     */
    public function updateDefaultValues($qid, $sqid, $scale_id, $specialtype, $language, $defaultvalue)
    {
        $arDefaultValue = DefaultValue::model()
            ->find(
                'specialtype = :specialtype AND qid = :qid AND sqid = :sqid AND scale_id = :scale_id',
                array(
                    ':specialtype' => $specialtype,
                    ':qid' => $qid,
                    ':sqid' => $sqid,
                    ':scale_id' => $scale_id,
                )
            );
        $dvid = !empty($arDefaultValue->dvid) ? $arDefaultValue->dvid : null;

        if ($defaultvalue == '') {
            // Remove the default value if it is empty
            if ($dvid !== null) {
                DefaultValueL10n::model()->deleteAllByAttributes(array('dvid' => $dvid, 'language' => $language));
                $iRowCount = DefaultValueL10n::model()->countByAttributes(array('dvid' => $dvid));
                if ($iRowCount == 0) {
                    DefaultValue::model()->deleteByPk($dvid);
                }
            }
        } else {
            if (is_null($dvid)) {
                $data = array('qid' => $qid, 'sqid' => $sqid, 'scale_id' => $scale_id, 'specialtype' => $specialtype);
                $oDefaultvalue = new DefaultValue();
                $oDefaultvalue->attributes = $data;
                $oDefaultvalue->specialtype = $specialtype;
                $oDefaultvalue->save();
                if (!empty($oDefaultvalue->dvid)) {
                    $dataL10n = array('dvid' => $oDefaultvalue->dvid, 'language' => $language, 'defaultvalue' => $defaultvalue);
                    $oDefaultvalueL10n = new DefaultValueL10n();
                    $oDefaultvalueL10n->attributes = $dataL10n;
                    $oDefaultvalueL10n->save();
                }
            } else {
                if ($dvid !== null) {
                    $arDefaultValue->with('defaultvaluel10ns');
                    $idL10n = !empty($arDefaultValue->defaultvaluel10ns) && array_key_exists($language, $arDefaultValue->defaultvaluel10ns) ? $arDefaultValue->defaultvaluel10ns[$language]->id : null;
                    if ($idL10n !== null) {
                        DefaultValueL10n::model()->updateAll(array('defaultvalue' => $defaultvalue), 'dvid = ' . $dvid . ' AND language = \'' . $language . '\'');
                    } else {
                        $dataL10n = array('dvid' => $dvid, 'language' => $language, 'defaultvalue' => $defaultvalue);
                        $oDefaultvalueL10n = new DefaultValueL10n();
                        $oDefaultvalueL10n->attributes = $dataL10n;
                        $oDefaultvalueL10n->save();
                    }
                }
            }
        }
        $surveyid = $this->iSurveyID;
        updateFieldArray();
    }

    /**
     * action to do when update default value
     * @param integer $iSurveyID
     * @return void (redirect)
     */
    private function actionUpdateDefaultValues($iSurveyID)
    {
        $oSurvey = Survey::model()->findByPk($iSurveyID);
        $aSurveyLanguages = $oSurvey->allLanguages;
        $sBaseLanguage = $oSurvey->language;

        Question::model()->updateAll(array('same_default' => Yii::app()->request->getPost('samedefault') ? 1 : 0), 'sid=:sid ANd qid=:qid', array(':sid' => $iSurveyID, ':qid' => $this->iQuestionID));

        $arQuestion = Question::model()->findByAttributes(array('qid' => $this->iQuestionID));
        $sQuestionType = $arQuestion['type'];

        $questionThemeMetaData = QuestionTheme::findQuestionMetaData($sQuestionType);
        if ((int)$questionThemeMetaData['settings']->answerscales > 0 && $questionThemeMetaData['settings']->subquestions == 0) {
            for ($iScaleID = 0; $iScaleID < (int)$questionThemeMetaData['settings']->answerscales; $iScaleID++) {
                foreach ($aSurveyLanguages as $sLanguage) {
                    if (!is_null(Yii::app()->request->getPost('defaultanswerscale_' . $iScaleID . '_' . $sLanguage))) {
                        $this->updateDefaultValues($this->iQuestionID, 0, $iScaleID, '', $sLanguage, Yii::app()->request->getPost('defaultanswerscale_' . $iScaleID . '_' . $sLanguage));
                    }
                    if (!is_null(Yii::app()->request->getPost('other_' . $iScaleID . '_' . $sLanguage))) {
                        $this->updateDefaultValues($this->iQuestionID, 0, $iScaleID, 'other', $sLanguage, Yii::app()->request->getPost('other_' . $iScaleID . '_' . $sLanguage));
                    }
                }
            }
        }
        if ((int)$questionThemeMetaData['settings']->subquestions > 0) {
            foreach ($aSurveyLanguages as $sLanguage) {
                $arQuestions = Question::model()->with('questionl10ns', array('condition' => 'language = ' . $sLanguage))->findAllByAttributes(array('sid' => $iSurveyID, 'gid' => $this->iQuestionGroupID, 'parent_qid' => $this->iQuestionID, 'scale_id' => 0));

                for ($iScaleID = 0; $iScaleID < (int)$questionThemeMetaData['settings']->subquestions; $iScaleID++) {
                    foreach ($arQuestions as $aSubquestionrow) {
                        if (!is_null(Yii::app()->request->getPost('defaultanswerscale_' . $iScaleID . '_' . $sLanguage . '_' . $aSubquestionrow['qid']))) {
                            $this->updateDefaultValues($this->iQuestionID, $aSubquestionrow['qid'], $iScaleID, '', $sLanguage, Yii::app()->request->getPost('defaultanswerscale_' . $iScaleID . '_' . $sLanguage . '_' . $aSubquestionrow['qid']));
                        }
                    }
                }
            }
        }
        if ($questionThemeMetaData['settings']->answerscales == 0 && $questionThemeMetaData['settings']->subquestions == 0) {
            foreach ($aSurveyLanguages as $sLanguage) {
                // Qick and dirty insert for yes/no defaul value
                // write the the selectbox option, or if "EM" is slected, this value to table
                if ($sQuestionType == 'Y') {
                    /// value for all langs
                    if (Yii::app()->request->getPost('samedefault') == 1) {
                        $sLanguage = $aSurveyLanguages[0]; // turn
                    }

                    if (Yii::app()->request->getPost('defaultanswerscale_0_' . $sLanguage) == 'EM') {
                        // Case EM, write expression to database
                        $this->updateDefaultValues($this->iQuestionID, 0, 0, '', $sLanguage, Yii::app()->request->getPost('defaultanswerscale_0_' . $sLanguage . '_EM'));
                    } else {
                        // Case "other", write list value to database
                        $this->updateDefaultValues($this->iQuestionID, 0, 0, '', $sLanguage, Yii::app()->request->getPost('defaultanswerscale_0_' . $sLanguage));
                    }
                    ///// end yes/no
                } else {
                    if (!is_null(Yii::app()->request->getPost('defaultanswerscale_0_' . $sLanguage . '_0'))) {
                        $this->updateDefaultValues($this->iQuestionID, 0, 0, '', $sLanguage, Yii::app()->request->getPost('defaultanswerscale_0_' . $sLanguage . '_0'));
                    }
                }
            }
        }
        Yii::app()->session['flashmessage'] = gT("Default value settings were successfully saved.");
        //This is SUPER important! Recalculating the ExpressionScript Engine state!
        LimeExpressionManager::SetDirtyFlag();

        if (Yii::app()->request->getPost('close-after-save') === 'true') {
            $this->getController()->redirect(array('questionAdministration/view/surveyid/' . $iSurveyID . '/gid/' . $this->iQuestionGroupID . '/qid/' . $this->iQuestionID));
        }
        $this->getController()->redirect(['questionAdministration/editdefaultvalues/surveyid/' . $iSurveyID . '/gid/' . $this->iQuestionGroupID . '/qid/' . $this->iQuestionID]);
    }

    /**
     * Action to run when update survey settings + survey language
     *
     * Refactored to use Services\SurveyAggregateService 2023-05-30 (kfoster).
     *
     * @param integer $iSurveyID
     * @param ?array $input For dependency injection during testing
     * @return void (redirect)
     */
    private function actionUpdateSurveyLocaleSettings($surveyId, $input = [])
    {
        $diContainer = \LimeSurvey\DI::getContainer();
        $surveyUpdater = $diContainer->get(
            LimeSurvey\Models\Services\SurveyAggregateService::class
        );

        $surveyModel = $diContainer->get(Survey::class);

        //@todo  here is something wrong ...
        $oSurvey = $surveyModel->findByPk($surveyId);
        $languageList = $oSurvey->additionalLanguages;
        $languageList[] = $oSurvey->language;

        $request = Yii::app()->request;

        // $input optionally provided to function for unit testing
        // - otherwise we expect data from $_POST
        $post = isset($_POST) ? $_POST : [];
        $input = !empty($input) ? $input : $post;

        // form inputs are named differently from db fields
        // - they have a prefix and a language suffix
        // - we need to convert this to a array of database
        // - fields for each language indexed by lanuage code
        $langFields = [
            'surveyls_url' => 'url_',
            'surveyls_urldescription' => 'urldescrip_',
            'surveyls_title' => 'short_title_',
            'surveyls_alias' => 'alias_',
            'surveyls_description' => 'description_',
            'surveyls_welcometext' => 'welcome_',
            'surveyls_endtext' => 'endtext_',
            'surveyls_policy_notice' => 'datasec_',
            'surveyls_policy_error' => 'datasecerror_',
            'surveyls_policy_notice_label' => 'dataseclabel_',
            'surveyls_dateformat' => 'dateformat_',
            'surveyls_numberformat' => 'numberformat_',
        ];

        foreach ($languageList as $langCode) {
            $langInput = [];
            foreach ($langFields as $field => $inputPrefix) {
                $langInput[$field] = $request->getPost(
                    $inputPrefix . $langCode,
                    null
                );
            }
            if (!empty($langInput)) {
                $input[$langCode] = $langInput;
            }
        }


        $metaData = [];
        try {
            $metaData = $surveyUpdater->update(
                $surveyId,
                $input
            );
            Yii::app()
                ->setFlashMessage(gT('Survey settings were successfully saved.'));
        } catch (PersistErrorException $e) {
            // @todo: Should we be catching only this kind of exceptions or all Throwable?
            // BUt that could show sensitive information
            Yii::app()->setFlashMessage(
                $e->getMessage(),
                'error'
            );
        }

        if (Yii::app()->request->getPost('responsejson', 0) == 1) {
            return Yii::app()->getController()->renderPartial(
                '/admin/super/_renderJson',
                array(
                    'data' => [
                        'success' => true,
                        'updated' => is_array($metaData) && !empty($metaData['updatedFields'])
                            ? $metaData['updatedFields']
                            : null,
                        'DEBUG' => [
                            'POST' => $_POST,
                            'reloaded' => [],
                            'aURLParams' => '',
                            'initial' => '',
                            'afterApply' => ''
                        ]
                    ],
                ),
                false,
                false
            );
        } else {
            ////////////////////////////////////////
            if (Yii::app()->request->getPost('close-after-save') === 'true') {
                $this->getController()
                    ->redirect(
                        array('surveyAdministration/view/surveyid/' . $surveyId)
                    );
            }

            $referrer = Yii::app()->request->urlReferrer;
            if ($referrer) {
                $this->getController()
                    ->redirect(array($referrer));
            } else {
                $this->getController()
                    ->redirect(array(
                        '/surveyAdministration/rendersidemenulink/subaction/generalsettings/surveyid/' . $surveyId
                    ));
            }
        }
    }

    /**
     * Action for the page "General settings".
     * @param int $surveyId
     * @return void
     */
    protected function actionUpdateSurveyLocaleSettingsGeneralSettings($surveyId)
    {
        $diContainer = \LimeSurvey\DI::getContainer();
        $surveyUpdater = $diContainer->get(
            LimeSurvey\Models\Services\SurveyAggregateService::class
        );

        $request = Yii::app()->request;

        $input = [
            'language' => $request->getPost('language'),
            'additional_languages' => $request->getPost('additional_languages'),
            'admin' => $request->getPost('admin'),
            'adminemail' => $request->getPost('adminemail'),
            'bounce_email' => $request->getPost('bounce_email'),
            'format' => $request->getPost('format'),
            'owner_id' => $request->getPost('owner_id'),
            'gsid' => $request->getPost('gsid'),
            'template' => $request->getPost('template'),
            'non_numerical_answer_prefix' => $request->getPost('non_numerical_answer_prefix'),
            'non_numerical_subquestions_prefix' => $request->getPost('non_numerical_subquestions_prefix'),
        ];
        try {
            $surveyUpdater->update(
                $surveyId,
                $input
            );
            Yii::app()
                ->setFlashMessage(gT('Survey settings were successfully saved.'));
        } catch (PersistErrorException $e) {
            \Yii::app()->setFlashMessage(
                \CHtml::errorSummary(
                    $e->getErrorModel(),
                    \CHtml::tag(
                        "p",
                        array('class' => 'strong'),
                        gT("Survey could not be updated, please fix the following error:")
                    )
                ),
                "error"
            );
        }

        Yii::app()->end();
    }
}
