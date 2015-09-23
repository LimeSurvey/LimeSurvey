<?php
namespace ls\core\plugins;
use ls\pluginmanager\PluginBase;
use \ls\pluginmanager\PluginEvent;
use \Yii;
use \CHtml;

class DemoAuth extends PluginBase implements \ls\pluginmanager\iAuthenticationPlugin
{
    use \ls\pluginmanager\InternalUserDbTrait;
    protected $storage = 'DbStorage';
    protected $_onepass = null;

    public function init()
    {
    }
    public function getLoginSettings()
    {
        return [
            'label' => $this->name,
            'settings' => [
                'id' => [
                    'type' => 'select',
                    'label' => gT("User"),
                    'options' => CHtml::listData($this->getUsers()->data, 'id', 'name')
                ],
            ]
        ];
    }

    /**
     * This function performs username password configuration.
     * @param \CHttpRequest $request
     */
    public function authenticate(\CHttpRequest $request) {
        if ($request->isPostRequest) {
            $user = \User::model()->findByPk($request->getParam('id'));
            if (isset($user)) {
                return $user;
            }
        }
    }
}
