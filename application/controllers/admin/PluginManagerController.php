<?php

/**
 * LimeSurvey
 * Copyright (C) 2007-2026 The LimeSurvey Project Team
 * All rights reserved.
 * License: GNU/GPL License v2 or later, see LICENSE.php
 * LimeSurvey is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 */

use LimeSurvey\ExtensionInstaller\FileFetcherUploadZip;
use LimeSurvey\ExtensionInstaller\PluginInstaller;
use LimeSurvey\Menu\Menu;
use LimeSurvey\Menu\MenuItem;

/**
 * @todo Apply new permission 'extensions' instead of 'settings'.
 */
class PluginManagerController extends SurveyCommonAction
{
    /**
     * Init
     */
    public function init()
    {
    }

    /**
     * Overview for plugins
     * Copied from PluginsController 2015-10-02
     */
    public function index()
    {
        $jsFile = App()->getConfig('adminscripts') . 'plugin_manager.js';
        App()->getClientScript()->registerScriptFile($jsFile);

        $aoPlugins = Plugin::model()->findAll(array('order' => 'name'));
        $data      = [];
        foreach ($aoPlugins as $oPlugin) {
            $data[] = [
                'id'          => $oPlugin->id,
                'name'        => $oPlugin->name,
                'load_error'  => $oPlugin->getLoadError(),
                'description' => '',
                'active'      => $oPlugin->active,
                'settings'    => []
            ];
        }

        if (Yii::app()->request->getParam('pageSize')) {
            Yii::app()->user->setState('pageSize', intval(Yii::app()->request->getParam('pageSize')));
        }

        $aData['data'] = $data;
        $aData['plugins'] = $aoPlugins;
        $aData['extraMenus'] = $this->getExtraMenus();

        if (!Permission::model()->hasGlobalPermission('settings', 'read')) {
            Yii::app()->setFlashMessage(gT("No permission"), 'error');
            $this->getController()->redirect(array('/admin'));
        }

        $scanFilesUrl = $this->getController()->createUrl(
            '/admin/pluginmanager',
            [
                'sa' => 'scanFiles',
            ]
        );

        $aData['topbar']['title'] = gT('Plugins');
        $aData['topbar']['backLink'] = App()->createUrl('dashboard/view');

        $aData['topbar']['middleButtons'] = Yii::app()->getController()->renderPartial(
            '/admin/pluginmanager/partial/topbarBtns/leftSideButtons',
            [
                'showUpload' => !Yii::app()->getConfig('demoMode') && !Yii::app()->getConfig('disablePluginUpload'),
                'scanFilesUrl' => $scanFilesUrl,
            ],
            true
        );

        $this->renderWrappedTemplate('pluginmanager', 'index', $aData);
    }

    /**
     * @return Menu[]
     */
    protected function getExtraMenus()
    {
        $event = new PluginEvent('beforePluginManagerMenuRender', $this);
        $result = App()->getPluginManager()->dispatchEvent($event);
        $extraMenus = $result->get('extraMenus') ?? [];
        return $extraMenus;
    }

    /**
     * Scan files in plugin folder and add them to the database.
     * @return void
     */
    public function scanFiles()
    {
        if (!Permission::model()->hasGlobalPermission('settings', 'update')) {
            Yii::app()->setFlashMessage(gT('No permission'), 'error');
            $this->getController()->redirect($this->getPluginManagerUrl());
        }

        $oPluginManager = App()->getPluginManager();
        $result = $oPluginManager->scanPlugins();

        // Add delete URL for each plugin
        foreach ($result as $name => &$scannedPlugin) {
            if (isset($scannedPlugin['pluginType']) && $scannedPlugin['pluginType'] == 'upload') {
                $scannedPlugin['deleteUrl'] = $this->getController()->createUrl(
                    '/admin/pluginmanager',
                    [
                        'sa' => 'deleteFiles',
                        'plugin' => $name,
                    ]
                );
            }
        }

        Yii::app()->setFlashMessage(
            sprintf(
                gT('Found %s plugins in file system'),
                count($result)
            ),
            'notice'
        );

        $data = [];
        $data['result'] = $result;
        $data['installUrl'] = $this->getController()->createUrl(
            '/admin/pluginmanager',
            [
                'sa' => 'installPluginFromFile'
            ]
        );
        $scanFilesUrl = $this->getController()->createUrl(
            '/admin/pluginmanager',
            [
                'sa' => 'scanFiles',
            ]
        );

        $data['topbar']['title'] = gT('Plugins - scanned files');
        $data['topbar']['backLink'] = $this->getController()->createUrl('/admin/pluginmanager');
        $data['topbar']['middleButtons'] = Yii::app()->getController()->renderPartial(
            '/admin/pluginmanager/partial/topbarBtns/leftSideButtons',
            [
                'showUpload' => false,
                'scanFilesUrl' => $scanFilesUrl,
            ],
            true
        );

        $this->renderWrappedTemplate(
            'pluginmanager',
            'scanFilesResult',
            $data
        );
    }

    /**
     * Delete files
     * @param $plugin
     */
    public function deleteFiles($plugin)
    {
        $this->requirePostRequest();
        $this->checkUpdatePermission();

        // Pre supposes the plugin is in the uploads folder. Other plugins are not deletable by button.
        $pluginDir = Yii::getPathOfAlias(App()->getPluginManager()->pluginDirs['upload']) . DIRECTORY_SEPARATOR . $plugin;

        if (!file_exists($pluginDir)) {
            Yii::app()->setFlashMessage(gT('Plugin folder does not exist.'), 'error');
            $this->getController()->redirect($this->getPluginManagerUrl());
        }

        if (!is_writable($pluginDir)) {
            Yii::app()->setFlashMessage(gT('Plugin files cannot be deleted due to permissions problem.'), 'error');
            $this->getController()->redirect($this->getPluginManagerUrl());
        }

        if (!rmdirr($pluginDir)) {
            Yii::app()->setFlashMessage(gT('Could not remove plugin files.'), 'error');
            $this->getController()->redirect($this->getPluginManagerUrl());
        } else {
            Yii::app()->setFlashMessage(gT('Plugin files successfully deleted.'), 'success');
            $this->getController()->redirect($this->getPluginManagerUrl());
        }
    }

    /**
     * Activate a plugin
     *
     * @todo Defensive programming
     * @param int $id Plugin id
     * @return void
     */
    public function activate()
    {
        if (!Permission::model()->hasGlobalPermission('settings', 'update')) {
            Yii::app()->setFlashMessage(gT('No permission'), 'error');
            $this->getController()->redirect($this->getPluginManagerUrl());
        }

        $request = Yii::app()->request;
        $pluginId = (int) $request->getPost('pluginId');

        $oPlugin = Plugin::model()->findByPk($pluginId);
        if ($oPlugin && $oPlugin->active == 0) {
            if (!$oPlugin->isCompatible()) {
                $this->errorAndRedirect(gT('The plugin is not compatible with your version of LimeSurvey.'));
            }

            // Load the plugin:
            App()->getPluginManager()->loadPlugin($oPlugin->name, $pluginId);
            $result = App()->getPluginManager()->dispatchEvent(
                new PluginEvent('beforeActivate', $this),
                $oPlugin->name
            );
            if ($result->get('success', true)) {
                $oPlugin->active = 1;
                $oPlugin->save();
                Yii::app()->user->setFlash('success', gT('Plugin was activated.'));
            } else {
                $customMessage = $result->get('message');
                if ($customMessage) {
                    Yii::app()->user->setFlash('error', $customMessage);
                } else {
                    Yii::app()->user->setFlash('error', gT('Failed to activate the plugin.'));
                }
                $this->getController()->redirect(array('admin/pluginmanager/sa/index/'));
            }
        } else {
            Yii::app()->user->setFlash('error', gT('Found no plugin, or plugin already active.'));
        }
        $this->getController()->redirect(array('admin/pluginmanager/sa/index/'));
    }

    /**
     * Deactivate plugin.
     * @return void
     */
    public function deactivate()
    {
        if (!Permission::model()->hasGlobalPermission('settings', 'update')) {
            Yii::app()->setFlashMessage(gT("No permission"), 'error');
            $this->getController()->redirect(array('/admin/pluginmanager/sa/index'));
        }
        $pluginId = (int) Yii::app()->request->getPost('pluginId');
        $plugin = Plugin::model()->findByPk($pluginId);
        if ($plugin && $plugin->active) {
            $result = App()->getPluginManager()->dispatchEvent(
                new PluginEvent('beforeDeactivate', $this),
                $plugin->name
            );
            if ($result->get('success', true)) {
                $plugin->active = 0;
                $plugin->save();
                Yii::app()->user->setFlash('success', gT('Plugin was deactivated.'));
            } else {
                $customMessage = $result->get('message');
                if ($customMessage) {
                    Yii::app()->user->setFlash('error', $customMessage);
                } else {
                    Yii::app()->user->setFlash('error', gT('Failed to deactivate the plugin.'));
                }
                $this->getController()->redirect($this->getPluginManagerUrl());
            }
        } else {
            Yii::app()->user->setFlash('error', gT('Found no plugin, or plugin not active.'));
        }

        $this->getController()->redirect($this->getPluginManagerUrl());
    }

    /**
     * Configure for plugin
     * @param int $id
     */
    public function configure($id)
    {
        $url = $this->getController()->createUrl(
            '/admin/pluginmanager',
            [
                'sa' => 'index'
            ]
        );
        if (!Permission::model()->hasGlobalPermission('settings', 'read')) {
            Yii::app()->setFlashMessage(gT("No permission"), 'error');
            $this->getController()->redirect($url);
        }

        $plugin      = Plugin::model()->findByPk($id);
        $oPluginObject = App()->getPluginManager()->loadPlugin($plugin->name, $plugin->id, false);

        if (empty($oPluginObject)) {
            Yii::app()->user->setFlash('error', gT('Could not load plugin'));
            $this->getController()->redirect($url);
        }

        if (!$oPluginObject->readConfigFile()) {
            Yii::app()->user->setFlash('error', gT('Found no configuration file for this plugin.'));
            $this->getController()->redirect($url);
        }

        if ($plugin === null) {
            Yii::app()->user->setFlash('error', gT('The plugin was not found.'));
            $this->getController()->redirect($url);
        }

        // If post handle data, yt0 seems to be the submit button
        // TODO: Break out to separate method.
        if (App()->request->isPostRequest) {
            if (!Permission::model()->hasGlobalPermission('settings', 'update')) {
                Yii::app()->setFlashMessage(gT("No permission"), 'error');
                $this->getController()->redirect($url);
            }
            $aSettings = $oPluginObject->getPluginSettings(false);
            $aSave     = array();
            foreach (array_keys($aSettings) as $name) {
                $aSave[$name] = App()->request->getPost($name, null);
            }
            $oPluginObject->saveSettings($aSave);
            Yii::app()->user->setFlash('success', gT('The plugin settings were saved.'));
            if (App()->request->getPost('redirect')) {
                $this->getController()->redirect(App()->request->getPost('redirect'), true);
            }
        }

        // Prepare settings to be send to the view.
        $aSettings = $oPluginObject->getPluginSettings();
        // Add button if permission
        $aButtons = array();
        if (Permission::model()->hasGlobalPermission('settings', 'update')) {
            $url = App()->createUrl("admin/pluginmanager/sa/index");
            $aButtons = array(
                'cancel' => array(
                    'label' => '<span class="ri-close-fill"></span> ' . gT('Close'),
                    'class' => array('btn btn-danger'),
                    'type'  => 'link',
                    'href' => $url,
                ),
                'redirect' => array(
                    'label' => '<span class="ri-chat-check-fill"></span> ' . gT('Save and close'),
                    'class' => array('btn btn-outline-secondary'),
                    'role'  => 'button',
                    'type'  => 'submit',
                    'value' => $url,
                ),
                'save' => array(
                    'label' => '<span class="ri-check-fill"></span> ' . gT('Save'),
                    'class' => array('btn btn-primary'),
                    'type'  => 'submit'
                ),
            );
        }
        // Send to view plugin porperties: name and description
        $aPluginProp = App()->getPluginManager()->getPluginInfo($plugin->name);

        $topbar['title'] = gT('Plugins') . ' ' . $plugin['name'];
        $topbar['backLink'] = $this->getController()->createUrl('/admin/pluginmanager', ['sa' => 'index']);

        $this->renderWrappedTemplate(
            'pluginmanager',
            'configure',
            [
                'settings'     => $aSettings,
                'buttons'      => $aButtons,
                'plugin'       => $plugin,
                'pluginObject' => $oPluginObject,
                'properties'   => $aPluginProp,
                'topbar' => $topbar
            ]
        );
    }

    /**
     * Set load_error to 0 for plugin with id $pluginId.
     * This makes it possible to try to load the plugin again,
     * if a fix for previous load error has been implemented.
     *
     * @param int $pluginId
     * @return void
     */
    public function resetLoadError($pluginId)
    {
        $url = $this->getController()->createUrl(
            '/admin/pluginmanager',
            [
                'sa' => 'index'
            ]
        );

        if (!Permission::model()->hasGlobalPermission('settings', 'update')) {
            Yii::app()->setFlashMessage(gT('No permission'), 'error');
            $this->getController()->redirect($url);
        }

        $pluginId = (int) $pluginId;
        $plugin = Plugin::model()->find('id = :id', [':id' => $pluginId]);
        if ($plugin) {
            $plugin->load_error = 0;
            $plugin->load_error_message = '';
            $result = $plugin->update();
            if ($result) {
                Yii::app()->user->setFlash('success', sprintf(gT('Reset load error for plugin %s (%s)'), $plugin->name, $plugin->plugin_type));
            } else {
                Yii::app()->user->setFlash('error', sprintf(gT('Could not update plugin %s (%s)'), $plugin->name, $plugin->plugin_type));
            }
            $this->getController()->redirect($url);
        } else {
            Yii::app()->user->setFlash('error', sprintf(gT('Found no plugin with ID %d'), $pluginId));
            $this->getController()->redirect($url);
        }
    }

    /**
     * Install a plugin that has been discovered in the file system.
     * @return void
     */
    public function installPluginFromFile()
    {
        // Check permissions.
        $this->checkUpdatePermission();

        $request = Yii::app()->request;
        $pluginName = sanitize_alphanumeric($request->getPost('pluginName'));

        $pluginManager = App()->getPluginManager();
        $pluginInfo = $pluginManager->getPluginInfo($pluginName);

        if (empty($pluginInfo)) {
            Yii::app()->setFlashMessage(
                sprintf(
                    gT('Found no plugin with name %s'),
                    json_encode($pluginName)  // json_encode in case of null.
                ),
                'error'
            );
            $this->getController()->redirect($this->getPluginManagerUrl());
        } else {
            list($result, $errorMessage) = $pluginManager->installPlugin(
                $pluginInfo['extensionConfig'],
                $pluginInfo['pluginType']
            );
            if ($result) {
                Yii::app()->setFlashMessage(
                    gT('Plugin was installed.'),
                    'success'
                );
            } else {
                Yii::app()->setFlashMessage(
                    $errorMessage,
                    'error'
                );
            }
        }
        $this->getController()->redirect($this->getPluginManagerUrl());
    }

    /**
     * Run when user click button to uninstall plugin.
     * @return void
     */
    public function uninstallPlugin()
    {
        // Check permissions.
        $this->checkUpdatePermission();

        // Get plugin id from post.
        $request = Yii::app()->request;
        $pluginId = (int) $request->getPost('pluginId');

        $plugin = Plugin::model()->find('id = :id', [':id' => $pluginId]);

        // Check if plugin exists.
        if (empty($plugin)) {
            Yii::app()->setFlashMessage(
                sprintf(
                    gT('Found no plugin with ID %d'),
                    $pluginId
                ),
                'error'
            );
            $this->getController()->redirect($this->getPluginManagerUrl());
        } else {
            if ($plugin->delete()) {
                Yii::app()->setFlashMessage(gT('Plugin uninstalled.'), 'success');
            } else {
                Yii::app()->setFlashMessage(gT('Could not uninstall plugin.'), 'error');
            }
            $this->getController()->redirect($this->getPluginManagerUrl());
        }
    }

    /**
     * Upload a plugin ZIP file.
     * @return void
     */
    public function upload()
    {
        $this->checkUploadEnabled();

        $this->checkUpdatePermission();

        // Redirect back if demo mode is set.
        $this->checkDemoMode();

        $installer = $this->getInstaller();

        try {
            $installer->fetchFiles();
            $this->getController()->redirect($this->getPluginManagerUrl('uploadConfirm'));
        } catch (Exception $ex) {
            $installer->abort();
            $this->errorAndRedirect(gT('Could not fetch files.') . ' ' . $ex->getMessage());
        }

        //$tempdir = Yii::app()->getConfig("tempdir");
        //$destdir = createRandomTempDir($tempdir, 'install_');

        // Redirect back if $destdir is not writable OR if it already exists.
        //$this->checkDestDir($destdir, $sNewDirectoryName);

        // All OK if we're here.
        //$this->extractZipFile($destdir);
    }

    /**
     * Show confirm page after a plugin zip archive was successfully
     * uploaded.
     * @return void
     */
    public function uploadConfirm()
    {
        $this->checkUploadEnabled();

        $this->checkUpdatePermission();

        /** @var PluginInstaller */
        $installer = $this->getInstaller();

        try {

            /** @var ExtensionConfig */
            $config = $installer->getConfig();

            if (empty($config)) {
                $installer->abort();
                $this->errorAndRedirect(gT('Could not read plugin configuration file.'));
            }

            if (!$installer->isWhitelisted()) {
                $installer->abort();
                $this->errorAndRedirect(gT('The plugin is not in the plugin allowlist.'));
            }

            if (!$config->isCompatible()) {
                $installer->abort();
                $this->errorAndRedirect(gT('The plugin is not compatible with your version of LimeSurvey.'));
            }

            // Show confirmation page.
            $abortUrl = $this->getPluginManagerUrl('abortUploadedPlugin');
            $plugin = Plugin::model()->find('name = :name', [':name' => $config->getName()]);
            $data = [
                'config'   => $config,
                'abortUrl' => $abortUrl,
                'plugin'   => $plugin,
                'isUpdate' => !empty($plugin)
            ];
            $this->renderWrappedTemplate(
                'pluginmanager',
                'uploadConfirm',
                $data
            );
        } catch (Exception $ex) {
            $installer->abort();
            $this->errorAndRedirect($ex->getMessage());
        }
    }

    /**
     * After clicking "Install" on upload confirm page, run this action
     * and then redirect to plugin manager start page.
     * @return void
     */
    public function installUploadedPlugin()
    {
        $this->checkUploadEnabled();

        $this->checkUpdatePermission();

        /** @var LSHttpRequest */
        $request = Yii::app()->request;

        /** @var boolean */
        $isUpdate = $request->getPost('isUpdate') == 'true';

        /** @var PluginInstaller */
        $installer = $this->getInstaller();
        $installer->setPluginType('upload');

        try {
            if ($isUpdate) {
                $installer->update();
                Yii::app()->user->setFlash(
                    'success',
                    gT('The plugin was successfully updated. You might need to deactivate it and activate it again to apply changes.')
                );
            } else {
                $installer->install();
                Yii::app()->user->setFlash(
                    'success',
                    gT('The plugin was successfully installed. You need to activate it before you can use it.')
                );
            }
        } catch (Throwable $ex) {
            $installer->abort();
            Yii::app()->user->setFlash(
                'error',
                gT('The plugin could not be installed or updated:')
                . ' '
                . $ex->getMessage()
            );
        }

        $this->getController()->redirect($this->getPluginManagerUrl());
    }

    /**
     * @return void
     */
    public function abortUploadedPlugin()
    {
        $this->checkUpdatePermission();

        $installer = $this->getInstaller();
        $installer->abort();
        Yii::app()->user->setFlash(
            'warning',
            gT('Installation aborted.')
        );
        $this->getController()->redirect($this->getPluginManagerUrl());
    }

    /**
     * @return PluginInstaller
     * @todo Might have different file fetcher.
     */
    protected function getInstaller()
    {
        $fileFetcher = new FileFetcherUploadZip();
        $fileFetcher->setUnzipFilter('pluginExtractFilter');
        $installer = new PluginInstaller();
        $installer->setFileFetcher($fileFetcher);
        return $installer;
    }

    /**
     * Redirect back if $destdir is not writable or already exists.
     * @param string $destdir
     * @param string $sNewDirectoryName
     * @return void
     * @todo Duplicate from themes.php.
     */
    protected function checkDestDir($destdir, $sNewDirectoryName)
    {
        if (!is_writeable(dirname($destdir))) {
            Yii::app()->user->setFlash(
                'error',
                sprintf(
                    gT("Incorrect permissions in your %s folder."),
                    dirname($destdir)
                )
            );
            $this->getController()->redirect($this->getPluginManagerUrl());
        }

        if (is_dir($destdir)) {
            Yii::app()->user->setFlash(
                'error',
                sprintf(
                    gT("Plugin '%s' does already exist."),
                    $sNewDirectoryName
                )
            );
            $this->getController()->redirect($this->getPluginManagerUrl());
        }
    }

    /**
     * Redirects if demo mode is set.
     * @return void
     * @todo Duplicate from themes.php.
     */
    protected function checkDemoMode()
    {
        if (Yii::app()->getConfig('demoMode')) {
            Yii::app()->user->setFlash('error', gT("Demo mode: Uploading plugins is disabled."));
            $this->getController()->redirect($this->getPluginManagerUrl());
        }
    }

    /**
     * Return URL to plugin manager index..
     * @param string $sa Controller subaction.
     * @param array $extraParam
     * @return string
     */
    protected function getPluginManagerUrl($sa = null, $extraParams = [])
    {
        $params = [
            'sa' => $sa ? $sa : 'index'
        ];
        if ($extraParams) {
            $params = array_merge($params, $extraParams);
        }
        return $this->getController()->createUrl(
            '/admin/pluginmanager',
            $params
        );
    }

    /**
     * Sets an error flash message and redirects to plugin manager start page.
     * @param string $msg Error message.
     * @return void
     */
    protected function errorAndRedirect($msg)
    {
        Yii::app()->setFlashMessage($msg, 'error');
        $this->getController()->redirect($this->getPluginManagerUrl());
    }

    /**
     * Blocks action if user has no setting update permission.
     * @return void
     */
    protected function checkUpdatePermission()
    {
        if (!Permission::model()->hasGlobalPermission('settings', 'update')) {
            Yii::app()->setFlashMessage(gT('No permission'), 'error');
            $this->getController()->redirect($this->getPluginManagerUrl());
        }
    }

    /**
     * Blocks action if plugin upload is disabled.
     * @return void
     */
    protected function checkUploadEnabled()
    {
        if (Yii::app()->getConfig('disablePluginUpload')) {
            Yii::app()->setFlashMessage(gT('Plugin upload is disabled'), 'error');
            $this->getController()->redirect($this->getPluginManagerUrl());
        }
    }

    /**
     * Renders template(s) wrapped in header and footer
     *
     * @param string $sAction Current action, the folder to fetch views from
     * @param string $aViewUrls View url(s)
     * @param array $aData Data to be passed on. Optional.
     */
    protected function renderWrappedTemplate($sAction = 'pluginmanager', $aViewUrls = [], $aData = [], $sRenderFile = false)
    {
        parent::renderWrappedTemplate($sAction, $aViewUrls, $aData, $sRenderFile);
    }
}

/**
 * Callback for plugin ZIP install. Filters files by extension.
 * @param mixed $file
 * @return int Return 1 for yes (file can be extracted), 0 for no
 */
function pluginExtractFilter($file)
{
    $aAllowExtensions = explode(
        ',',
        Yii::app()->getConfig('allowedpluginuploads', '')
    );
    $info = pathinfo((string) $file['name']);

    if (
        $file['is_folder']
        || !isset($info['extension'])
        || in_array($info['extension'], $aAllowExtensions)
    ) {
        return 1;
    } else {
        return 0;
    }
}
