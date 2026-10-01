<?php

/*
 * LimeSurvey
 * Copyright (C) 2007-2026 The LimeSurvey Project Team
 * All rights reserved.
 * License: GNU/GPL License v2 or later, see LICENSE.php
 * LimeSurvey is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 *
 */
/**
 * CheckIntegrity Controller
 *
 * This controller performs database repair functions.
 *
 * @package       LimeSurvey
 * @subpackage    Backend
 */
class CheckIntegrity extends SurveyCommonAction
{
    /** @var DataIntegrityChecker Detection and fix logic, shared with the checkintegrity console command. */
    private $integrityChecker;

    /**
     * Restricts access to superadmins and sets up the integrity checker.
     *
     * @param CController|null $controller The controller owning this action.
     * @param string|null $id The action ID.
     */
    public function __construct($controller, $id)
    {
        parent::__construct($controller, $id);

        if (!Permission::model()->hasGlobalPermission('superadmin', 'read')) {
            Yii::app()->setFlashMessage(gT("You do not have permission to access this page."), 'error');
            $this->getController()->redirect($this->getController()->createUrl("/admin/"));
        }

        $this->integrityChecker = new DataIntegrityChecker();
    }

    /**
     * Index
     *
     * The data consistency check itself only runs when the "Run data consistency
     * check" button is submitted (see fixintegrity()), not on every page load, so
     * this GET request never deletes anything for that section - it only fetches
     * the (separate) data redundancy check's current state.
     *
     * @return void
     * @throws Exception
     */
    public function index()
    {
        App()->getClientScript()->registerScriptFile(App()->getConfig('adminscripts') . 'checkintegrity.js');

        $aData = $this->integrityChecker->checkintegrity();
        $aData['consistencyCheckRan'] = false;

        $aData['topbar']['title'] = gT('Check data integrity');
        $aData['topbar']['backLink'] = App()->createUrl('dashboard/view');

        $this->renderWrappedTemplate('checkintegrity', 'check_view', $aData);
    }

    /**
     * Drops the old survey/participant list tables the admin checked in the data
     * redundancy check, together with their related archived questions tables.
     *
     * @return void
     */
    public function fixredundancy()
    {
        $oldsmultidelete = Yii::app()->request->getPost('oldsmultidelete', array());
        $aData = [];
        $aData['messages'] = array();
        if (Permission::model()->hasGlobalPermission('settings', 'update') && Yii::app()->request->getPost('ok') == 'Y') {
            $aDelete = $this->integrityChecker->checkintegrity();
            if (isset($aDelete['redundanttokentables'])) {
                foreach ($aDelete['redundanttokentables'] as $aTokenTable) {
                    if (in_array($aTokenTable['table'], $oldsmultidelete)) {
                        Yii::app()->db->createCommand()->dropTable($aTokenTable['table']);
                        $aData['messages'][] = sprintf(gT('Deleting survey participant list: %s'), $aTokenTable['table']);
                    }
                }
            }
            if (isset($aDelete['redundantsurveytables'])) {
                foreach ($aDelete['redundantsurveytables'] as $aSurveyTable) {
                    if (in_array($aSurveyTable['table'], $oldsmultidelete)) {
                        Yii::app()->db->createCommand()->dropTable($aSurveyTable['table']);
                        $aData['messages'][] = sprintf(gT('Deleting survey table: %s'), $aSurveyTable['table']);
                        $aData = $this->integrityChecker->dropRelatedArchivedQuestionsTable($aSurveyTable['table'], $aData);
                    }
                }
            }
            if (count($aData['messages']) == 0) {
                $aData['messages'][] = gT('No old survey or survey participant list selected.');
            }
            $this->renderWrappedTemplate('checkintegrity', 'fix_view', $aData);
        }
    }

    /**
     * Fix integrity
     *
     * Runs the data consistency check (the only place it ever runs) and re-renders
     * the same page index() does, with the results of this run added on top, so the
     * admin sees the redundancy check's state alongside the consistency check's
     * feedback rather than a separate results page.
     *
     * @return void
     * @throws CHttpException If the user lacks the settings/update permission (401) or
     *                        the request lacks the confirmation marker (403).
     */
    public function fixintegrity()
    {
        if (!Permission::model()->hasGlobalPermission('settings', 'update')) {
            throw new CHttpException(401, "401 Unauthorized");
        }
        if (Yii::app()->request->getPost('ok') != 'Y') {
            throw new CHttpException(403);
        }
        // This renders the same check_view.php as index(), including the redundancy
        // check's checkbox UI, so it needs the same script registered here too - the
        // admin theme's pjax layer only re-executes (and fires pjax:scriptcomplete for)
        // scripts present in the response it just swapped in.
        App()->getClientScript()->registerScriptFile(App()->getConfig('adminscripts') . 'checkintegrity.js');

        $aFixResult = $this->integrityChecker->applyAutomaticFixes();

        $aData = $this->integrityChecker->checkintegrity();
        $aData['consistencyCheckRan'] = true;
        $aData['consistencyCheckMessages'] = $aFixResult['messages'];
        $aData['consistencyCheckWarnings'] = $aFixResult['warnings'];

        $aData['topbar']['title'] = gT('Check data integrity');
        $aData['topbar']['backLink'] = App()->createUrl('dashboard/view');

        $this->renderWrappedTemplate('checkintegrity', 'check_view', $aData);
    }

    /**
     * Renders template(s) wrapped in header and footer
     *
     * @param string $sAction Current action, the folder to fetch views from
     * @param string|array $aViewUrls View url(s)
     * @param array $aData Data to be passed on. Optional.
     * @param string|false $sRenderFile File to render, if not the default. Optional.
     * @return void
     */
    protected function renderWrappedTemplate($sAction = 'checkintegrity', $aViewUrls = array(), $aData = array(), $sRenderFile = false)
    {
        parent::renderWrappedTemplate($sAction, $aViewUrls, $aData, $sRenderFile);
    }
}
