<?php

class RemoteControlApiTestPlugin extends PluginBase
{
    protected static $description = 'Dummy plugin for testing RemoteControl plugin API guard gates';
    protected static $name = 'RemoteControlApiTestPlugin';

    public const ACTION_GLOBAL = 'guard_global_action';
    public const ACTION_SURVEY = 'guard_survey_action';
    public const ACTION_INVALID = 'guard_invalid_permission_action';
    public const ACTION_LEGACY_SURVEY = 'guard_legacy_survey_action';
    public const ACTION_LEGACY_GLOBAL = 'guard_legacy_global_action';

    public function init()
    {
        $this->subscribe('listPluginApiActions');
        $this->subscribe('callPluginApiAction');
    }

    public function listPluginApiActions()
    {
        $event = $this->getEvent();
        $requestedPlugin = (string) $event->get('requestedPlugin', '');
        if ($requestedPlugin !== '' && $requestedPlugin !== get_class($this)) {
            return;
        }

        $pluginApi = $event->get('pluginApi', []);
        if (!is_array($pluginApi)) {
            $pluginApi = [];
        }

        $pluginApi[get_class($this)] = [
            'name' => self::$name,
            'description' => self::$description,
            'actions' => [
                self::ACTION_GLOBAL => [
                    'title' => 'Guard Global Action',
                    'remoteControlPermission' => [
                        'scope' => 'global',
                        'permission' => 'superadmin',
                        'crud' => 'read',
                    ],
                ],
                self::ACTION_SURVEY => [
                    'title' => 'Guard Survey Action',
                    'remoteControlPermission' => [
                        'scope' => 'survey',
                        'permission' => 'surveycontent',
                        'crud' => 'read',
                        'sid' => ['payload.sid', 'payload.surveyId', 'context.sid', 'context.surveyId'],
                    ],
                ],
                self::ACTION_INVALID => [
                    'title' => 'Guard Invalid Permission Action',
                    'remoteControlPermission' => [
                        'scope' => 'broken',
                        'permission' => 'surveycontent',
                        'crud' => 'read',
                    ],
                ],
                self::ACTION_LEGACY_SURVEY => [
                    'title' => 'Guard Legacy Survey Action',
                    'permissions' => ['surveycontent.read'],
                ],
                self::ACTION_LEGACY_GLOBAL => [
                    'title' => 'Guard Legacy Global Action',
                    'permissions' => ['superadmin.read'],
                ],
            ],
        ];

        $event->set('pluginApi', $pluginApi);
    }

    public function callPluginApiAction()
    {
        $event = $this->getEvent();
        if ((string) $event->get('plugin', '') !== get_class($this)) {
            return;
        }

        $action = (string) $event->get('action', '');
        if (
            !in_array(
                $action,
                [
                    self::ACTION_GLOBAL,
                    self::ACTION_SURVEY,
                    self::ACTION_INVALID,
                    self::ACTION_LEGACY_SURVEY,
                    self::ACTION_LEGACY_GLOBAL
                ],
                true
            )
        ) {
            return;
        }

        $payload = (array) $event->get('payload', []);
        $result = [
            'ok' => true,
            'action' => $action,
            'sid' => (int) ($payload['sid'] ?? $payload['surveyId'] ?? 0),
        ];

        $event->set('handled', true);
        $event->set('result', $result);
    }
}
