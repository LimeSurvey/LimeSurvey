<?php

/*
* LimeSurvey
* Copyright (C) 2013-2026 The LimeSurvey Project Team
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

        if (($sAction == "updatesurveylocalesettings") && (Permission::model()->hasSurveyPermission($this->iSurveyID, 'surveylocale', 'update') || Permission::model()->hasSurveyPermission($this->iSurveyID, 'surveysettings', 'update'))) {
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

        if (Yii::app()->request->getPost('responsejson', 0) == 1) {
            return Yii::app()->getController()->renderPartial(
                '/admin/super/_renderJson',
                array(
                    'data' => [
                        'success' => false,
                        'updated' => null,
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
        }

        $this->getController()->redirect(Yii::app()->request->urlReferrer);
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
        // - fields for each language indexed by language code
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
                $e->getErrorModel()
                    ? CHtml::errorSummary(
                        $e->getErrorModel(),
                        CHtml::tag('p', array('class' => 'strong'), CHtml::encode($e->getMessage()))
                    )
                    : $e->getMessage(),
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
            'template' => $request->getPost('template')
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
