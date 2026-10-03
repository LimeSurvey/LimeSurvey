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
        $aData = array_merge($aData, $this->getServerConfigurationData());

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
        $aData = array_merge($aData, $this->getServerConfigurationData());

        $aData['topbar']['title'] = gT('Check data integrity');
        $aData['topbar']['backLink'] = App()->createUrl('dashboard/view');

        $this->renderWrappedTemplate('checkintegrity', 'check_view', $aData);
    }

    /**
     * Data for the server configuration check section of check_view.php
     *
     * PHP settings below the recommendation can silently truncate (max_input_vars)
     * or completely drop (post_max_size) the POST data of large forms and uploads,
     * reject uploaded files (upload_max_filesize) or make memory intensive actions
     * fail (memory_limit). The memory_limit shown
     * is the effective one, after LSYii_Controller tried to raise it to the
     * configured value.
     *
     * The section can be hidden with the config setting 'showserverconfigurationcheck',
     * then no checks are returned.
     *
     * @return array{serverSettingChecks: array<int, array{setting: string, current: string, recommended: string, ok: bool, hint: string}>}
     */
    private function getServerConfigurationData()
    {
        if (!App()->getConfig('showserverconfigurationcheck')) {
            return ['serverSettingChecks' => []];
        }
        $maxInputVars = (int) ini_get('max_input_vars');
        $postMaxSize = convertPHPSizeToBytes(ini_get('post_max_size')) / 1024 / 1024;
        $uploadMaxFilesize = convertPHPSizeToBytes(ini_get('upload_max_filesize')) / 1024 / 1024;
        $memoryLimit = convertPHPSizeToBytes(ini_get('memory_limit')) / 1024 / 1024;
        $recommendedMemoryLimit = (int) App()->getConfig('memory_limit');
        return [
            'serverSettingChecks' => [
                [
                    'setting' => 'memory_limit',
                    'current' => (string) ini_get('memory_limit'),
                    'recommended' => $recommendedMemoryLimit . 'M',
                    // -1 means unlimited
                    'ok' => $memoryLimit == -1 || $memoryLimit >= $recommendedMemoryLimit,
                    'hint' => gT("Imports, exports and statistics of large surveys may fail."),
                ],
                [
                    'setting' => 'max_input_vars',
                    'current' => (string) $maxInputVars,
                    'recommended' => (string) InstallerConfigForm::RECOMMENDED_MAX_INPUT_VARS,
                    'ok' => $maxInputVars >= InstallerConfigForm::RECOMMENDED_MAX_INPUT_VARS,
                    'hint' => gT("Large surveys may lose data when saving or exporting."),
                ],
                [
                    'setting' => 'post_max_size',
                    'current' => (string) ini_get('post_max_size'),
                    'recommended' => InstallerConfigForm::RECOMMENDED_POST_MAX_SIZE . 'M',
                    // 0 means unlimited
                    'ok' => $postMaxSize == 0 || $postMaxSize >= InstallerConfigForm::RECOMMENDED_POST_MAX_SIZE,
                    'hint' => gT("Saving large forms or uploading files may fail."),
                ],
                [
                    'setting' => 'upload_max_filesize',
                    'current' => (string) ini_get('upload_max_filesize'),
                    'recommended' => InstallerConfigForm::RECOMMENDED_UPLOAD_MAX_FILESIZE . 'M',
                    // 0 means unlimited
                    'ok' => $uploadMaxFilesize == 0 || $uploadMaxFilesize >= InstallerConfigForm::RECOMMENDED_UPLOAD_MAX_FILESIZE,
                    'hint' => gT("Importing surveys or uploading files may fail."),
                ],
            ],
        ];
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
