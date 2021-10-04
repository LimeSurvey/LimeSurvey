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
                    'Missing configuration file for plugin %s, looked in location %s',
                    $this->name,
                    $file
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
        if ($this->load_error == 1) {
            return sprintf(
                "<span data-toggle='tooltip' title='%s' class='btntooltip fa fa-times text-warning'></span>",
                gT('Plugin load error')
            );
        } elseif ($this->active == 1) {
            return "<span class='fa fa-circle'></span>";
        } else {
            return "<span class='fa fa-circle-thin'></span>";
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
        if ($this->load_error == 0) {
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
     * @return string HTML
     */
    public function getActionButtons()
    {
        $output = '';
        if (Permission::model()->hasGlobalPermission('settings', 'update')) {
            $output .= "<div class='icon-btn-row'>";
            if ($this->load_error == 1) {
                $reloadUrl = Yii::app()->createUrl(
                    'admin/pluginmanager',
                    [
                        'sa' => 'resetLoadError',
                        'pluginId' => $this->id
                    ]
                );
                $output .= "<a href='" . $reloadUrl . "' data-toggle='tooltip' title='" . gT('Attempt plugin reload') . "' class='btn btn-default btn-sm btntooltip'><span class='fa fa-refresh'></span></a>";
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
                <button data-toggle='tooltip' title='" . gT('Activate plugin') . "' class='btntooltip btn btn-default btn-sm'>
                    <i class='fa fa-power-off'></i>
                </button>
            </form>
        ";
        return $output;
    }

    /**
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
                <button data-toggle='tooltip' onclick='return confirm(\"" . gT('Are you sure you want to deactivate this plugin?') . "\");' title='" . gT('Deactivate plugin') . "' class='btntooltip btn btn-warning btn-sm'>
                    <i class='fa fa-power-off'></i>
                </button>
            </form>
        ";
        return $output;
    }

    /**
     * @todo: Don't use JS native confirm.
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
                <button data-toggle='tooltip' onclick='return confirm(\"" . gT('Are you sure you want to uninstall this plugin?') . "\");' title='" . gT('Uninstall plugin') . "' class='btntooltip btn btn-danger btn-sm'>
                    <i class='fa fa-times-circle'></i>
                </button>
            </form>
        ";
        return $output;
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
