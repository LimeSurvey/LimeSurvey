<?php

/*
 * LimeSurvey
 * Copyright (C) 2007-2011 The LimeSurvey Project Team / Carsten Schmitz
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
 * This is the model class for table "{{plugins}}".
 *
 * @property integer $id
 * @property string $name
 * @property integer $active default 0
 * @property integer $priority default 0
 * @property string $version
 * @property string $load_error
 * @property string $plugin_type
 */
class Plugin extends LSActiveRecord
{
    /**
     * @var string
     */
    public $load_error;

    /**
     * @var string
     */
    public $plugin_type;

    /**
     * @inheritdoc
     */
    public function init()
    {
        /* This default values are set by DB
        /* $this->priority = 0;
        /* $this->active = 0;
        **/
    }

    /**
     * @inheritdoc
     * @return Plugin
     */
    public static function model($className = __CLASS__)
    {
        /** @var self $model */
        $model = parent::model($className);
        return $model;
    }

    /** @inheritdoc */
    public function tableName()
    {
        return '{{plugins}}';
    }

    /**
     * Set this plugin as load error in database, and saves the error message.
     * @param array $error Array with 'message' and 'file' keys (as get from error_get_last).
     * @return int Rows affected
     */
    public function setLoadError(array $error)
    {
        // NB: Don't use ActiveRecord here, since it will trigger events and
        // load the plugin system all over again.
        // TODO: Works on all SQL systems?
        $sql = sprintf(
            "UPDATE {{plugins}} SET load_error = 1, load_error_message = '%s' WHERE id = " . $this->id,
            addslashes($error['message'] . ' ' . $error['file'])
        );
        Yii::log(
            "Plugin $this->name} ({$this->id}) deactivated with error '" . CHtml::encode($error['message']) . "' at file '" . CHtml::encode($error['file']) . "'",
            CLogger::LEVEL_ERROR,
            'application.model.plugin.setLoadError'
        );
        return \Yii::app()->db->createCommand($sql)->execute();
    }

    /**
     * Returns true if this plugin is compatible with this version of LS.
     * @return boolean
     */
    public function isCompatible()
    {
        $config = $this->getExtensionConfig();
        return $config->isCompatible();
    }

    /**
     * @return ExtensionConfig
     * @throws Exception if file does not exist.
     */
    public function getExtensionConfig()
    {
        $file = $this->getDir() . DIRECTORY_SEPARATOR . 'config.xml';
        if (file_exists($file)) {
            if (\PHP_VERSION_ID < 80000) {
                libxml_disable_entity_loader(false);
            }
            $config = simplexml_load_file(realpath($file));
            if (\PHP_VERSION_ID < 80000) {
                libxml_disable_entity_loader(true);
            }
            return new ExtensionConfig($config);
        } else {
            throw new \Exception(
                sprintf(
                    'Missing configuration file for plugin %s, looked in "%s", inside the folder related to "%s" plugin type.',
                    $this->name,
                    $this->name . DIRECTORY_SEPARATOR . 'config.xml',
                    $this->plugin_type
                )
            );
        }
    }

    /**
     * Plugin status as shown in plugin list.
     * @return string HTML
     */
    public function getStatus()
    {
        if ($this->getLoadError()) {
            return sprintf(
                "<span data-bs-toggle='tooltip' title='%s' class='btntooltip ri-close-fill text-danger'></span>",
                CHtml::encode(sprintf(gT('Plugin load error: %s'), $this->load_error_message))
            );
        } elseif ($this->active == 1) {
            return "<span class='ri-checkbox-blank-circle-fill'></span>";
        } else {
            return "<span class='ri-checkbox-blank-circle-line'></span>";
        }
    }

    /**
     * Name as shown in plugin list.
     * @return string
     */
    public function getName()
    {
        $url = Yii::app()->getController()->createUrl(
            '/admin/pluginmanager',
            [
                'sa' => 'configure',
                'id' => $this->id
            ]
        );
        if (!$this->getLoadError()) {
            return sprintf(
                '<a href="%s">%s</a>',
                $url,
                $this->name
            );
        } else {
            return $this->name;
        }
    }

    /**
     * Description as shown in plugin list.
     * @return string
     * @throws Exception
     */
    public function getDescription()
    {
        $config = $this->getExtensionConfig();
        // Harden for XSS
        $filter = LSYii_HtmlPurifier::getXssPurifier();
        return $filter->purify($config->getDescription());
    }

    /**
     * As getDescription, but catches the exception (to be used in plugin gridview)
     *
     * @return string
     */
    public function getPossibleDescription()
    {
        try {
            return $this->getDescription();
        } catch (\Throwable $ex) {
            return sprintf(gT('Error: Could not get plugin description: %s'), $ex->getMessage());
        }
    }


    /**
     * Action buttons in plugin list.
     * @deprecated 6.0
     * @return string HTML
     */
    public function getActionButtons()
    {
        $output = '';
        if (Permission::model()->hasGlobalPermission('settings', 'update')) {
            $output .= "<div class='icon-btn-row'>";
            if ($this->getLoadError()) {
                $reloadUrl = Yii::app()->createUrl(
                    'admin/pluginmanager',
                    [
                        'sa' => 'resetLoadError',
                        'pluginId' => $this->id
                    ]
                );
                $output .= "<a href='" . $reloadUrl . "' data-bs-toggle='tooltip' title='" . gT('Attempt plugin reload') . "' class='btn btn-outline-secondary btn-sm btntooltip'><span class='ri-refresh-line'></span></a>";
            } elseif ($this->active == 0) {
                $output .= $this->getActivateButton();
            } else {
                $output .= $this->getDeactivateButton();
            }

            if ($this->active == 0) {
                $output .= $this->getUninstallButton();
            }
            $output .= "</div>";
        }

        return $output;
    }

    /**
     * @deprecated 6.0
     * @return string HTML
     */
    public function getActivateButton()
    {
        $activateUrl = App()->getController()->createUrl(
            '/admin/pluginmanager',
            [
                'sa' => 'activate'
            ]
        );
        $output = CHtml::beginForm(
            $activateUrl,
            'post',
            [
                'style' => 'display: inline-block'
            ]
        );
        $output .= "
                <input type='hidden' name='pluginId' value='" . $this->id . "' />
                <button data-bs-toggle='tooltip' title='" . gT('Activate plugin') . "' class='btntooltip btn btn-outline-secondary btn-sm'>
                    <i class='ri-shut-down-line'></i>
                </button>
            </form>
        ";
        return $output;
    }


    /**
     * @deprecated 6.0
     * @return string HTML
     */
    public function getDeactivateButton()
    {
        $deactivateUrl = App()->getController()->createUrl(
            '/admin/pluginmanager',
            [
                'sa' => 'deactivate'
            ]
        );
        $output = CHtml::beginForm(
            $deactivateUrl,
            'post',
            [
                'style' => 'display: inline-block'
            ]
        );
        $output .= "
                <input type='hidden' name='pluginId' value='" . $this->id . "' />
                <button data-bs-toggle='tooltip' onclick='return confirm(\"" . gT('Are you sure you want to deactivate this plugin?') . "\");' title='" . gT('Deactivate plugin') . "' class='btntooltip btn btn-warning btn-sm'>
                    <i class='ri-shut-down-line'></i>
                </button>
            </form>
        ";
        return $output;
    }

    /**
     * @todo: Don't use JS native confirm.
     * @deprecated 6.0
     * @return string HTML
     */
    protected function getUninstallButton()
    {
        $uninstallUrl = App()->getController()->createUrl(
            '/admin/pluginmanager',
            [
                'sa' => 'uninstallPlugin'
            ]
        );
        $output = CHtml::beginForm(
            $uninstallUrl,
            'post',
            [
                'style' => 'display: inline-block'
            ]
        );
        $output .= "
                <input type='hidden' name='pluginId' value='" . $this->id . "' />
                <button data-bs-toggle='tooltip' onclick='return confirm(\"" . gT('Are you sure you want to uninstall this plugin?') . "\");' title='" . gT('Uninstall plugin') . "' class='btntooltip btn btn-danger btn-sm'>
                    <i class='ri-close-circle-fill'></i>
                </button>
            </form>
        ";
        return $output;
    }

    public function getButtons(): string
    {

        $reloadUrl = Yii::app()->createUrl(
            'admin/pluginmanager',
            [
                'sa' => 'resetLoadError',
                'pluginId' => $this->id
            ]
        );

        $activateUrl = App()->getController()->createUrl(
            '/admin/pluginmanager',
            [
                'sa' => 'activate'
            ]
        );
        $deactivateUrl = App()->getController()->createUrl(
            '/admin/pluginmanager',
            [
                'sa' => 'deactivate'
            ]
        );
        $uninstallUrl = App()->getController()->createUrl(
            '/admin/pluginmanager',
            [
                'sa' => 'uninstallPlugin'
            ]
        );
        $dropdownItems = [];
        if ($this->load_error) {
            $dropdownItems[] = [
                'title'            => gT('Attempt plugin reload'),
                'url'              => $reloadUrl,
                'iconClass'        => "ri-refresh-line text-warning",
                'enabledCondition' => $this->load_error == 1,
                'linkAttributes'   => [
                    'data-post-url'   => $reloadUrl,
                    'data-post-datas' => json_encode(['pluginId' => $this->id]),
                ],

            ];
        } else {
            $dropdownItems[] = [
                'title'            => gT('Activate'),
                'url'              => $activateUrl,
                'iconClass'        => "ri-play-fill text-success",
                'enabledCondition' => $this->active == 0,
                'linkAttributes'   => [
                    'data-bs-toggle'  => 'modal',
                    'data-bs-target'  => '#confirmation-modal',
                    'data-btnclass'   => 'btn-success',
                    'type'            => 'submit',
                    'data-btntext'    => gT("Activate"),
                    'data-title'      => gT('Activate plugin'),
                    'data-message'    => gT("Are you sure you want to activate this plugin?"),
                    'data-post-url'   => $activateUrl,
                    'data-post-datas' => json_encode(['pluginId' => $this->id]),
                ],

            ];
            $dropdownItems[] = [
                'title'            => gT('Deactivate'),
                'url'              => $deactivateUrl,
                'iconClass'        => 'ri-stop-fill text-danger',
                'enabledCondition' => $this->active == 1,
                'linkAttributes'   => [
                    'data-bs-toggle'  => 'modal',
                    'data-bs-target'  => '#confirmation-modal',
                    'data-btnclass'   => 'btn-danger',
                    'type'            => 'submit',
                    'data-btntext'    => gT("Deactivate"),
                    'data-title'      => gT('Deactivate plugin'),
                    'data-message'    => gT("Are you sure you want to deactivate this plugin?"),
                    'data-post-url'   => $deactivateUrl,
                    'data-post-datas' => json_encode(['pluginId' => $this->id]),
                ],

            ];
            $dropdownItems[] = [
                'title'            => gT('Uninstall'),
                'url'              => $uninstallUrl,
                'iconClass'        => 'ri-delete-bin-fill text-danger',
                'enabledCondition' => $this->active == 0,
                'linkAttributes'   => [
                    'data-bs-toggle'  => 'modal',
                    'data-bs-target'  => '#confirmation-modal',
                    'data-btnclass'   => 'btn-danger',
                    'type'            => 'submit',
                    'data-btntext'    => gT("Uninstall"),
                    'data-title'      => gT('Uninstall plugin'),
                    'data-message'    => gT("Are you sure you want to uninstall this plugin?"),
                    'data-post-url'   => $uninstallUrl,
                    'data-post-datas' => json_encode(['pluginId' => $this->id]),
                ],
            ];
        }
        return App()->getController()->widget('ext.admin.grid.GridActionsWidget.GridActionsWidget', ['dropdownItems' => $dropdownItems], true);
    }

    /**
     * @param Plugin|null $plugin
     * @param string $pluginName
     * @param array $error Array with 'message' and 'file' keys (as get from error_get_last).
     * @return int Rows affected, always 0 for debug >=2
     */
    public static function handlePluginLoadError($plugin, $pluginName, array $error)
    {
        if (App()->getConfig('debug') >= 2) {
            return 0;
        }
        return self::setPluginLoadError($plugin, $pluginName, $error);
    }

    /**
     * @param Plugin|null $plugin
     * @param string $pluginName
     * @param array $error Array with 'message' and 'file' keys (as get from error_get_last).
     * @return int Rows affected
     */
    public static function setPluginLoadError($plugin, $pluginName, array $error)
    {
        if ($plugin) {
            $result = $plugin->setLoadError($error);
        } else {
            $result = Yii::app()->db->createCommand()
                ->insert(
                    '{{plugins}}',
                    [
                        'name' => $pluginName,
                        'active' => 0,
                        'load_error' => 1,
                        'load_error_message' => addslashes($error['message'] . ' ' . $error['file'])
                    ]
                );
        }
        return $result;
    }

    /**
     * Get load error as boolean
     * @return boolean
     */
    public function getLoadError()
    {
        if (App()->getConfig('debug') >= 2) {
            return false;
        }
        return isset($this->load_error) && boolval($this->load_error);
    }

    /**
     * Get installation folder of this plugin.
     * Installation folder is different for core and
     * user plugins.
     * @return string
     * @throws Exception
     */
    protected function getDir()
    {
        $pluginManager = App()->getPluginManager();
        $alias = $pluginManager->pluginDirs[$this->plugin_type];

        if (empty($alias)) {
            throw new \Exception('Unknown plugin type: ' . json_encode($this->plugin_type));
        }

        $folder = Yii::getPathOfAlias($alias);

        if (empty($folder)) {
            throw new \Exception('Alias has no folder: ' . json_encode($alias));
        }

        // NB: Name is same as plugin folder and plugin main class.
        return $folder . DIRECTORY_SEPARATOR . $this->name;
    }
}
