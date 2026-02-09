<?php

use LimeSurvey\Models\Services\EditorService;
use LimeSurvey\Models\Services\EditorService\EditorConfig;

class ReactEditor extends PluginBase
{
    /**
     * @return void
     */
    public function init()
    {
        $this->subscribe('beforeDeactivate');
        $this->subscribe('beforeControllerAction', 'initEditor');
        $this->subscribe('beforeRenderSurveySidemenu');
    }

    public function beforeDeactivate()
    {
        $this->getEvent()->set('success', false);
        $this->getEvent()->set('message', gT('Core plugin can not be disabled.'));
    }

    /**
     * redirects to the React editor if it is enabled
     */
    public function initEditor()
    {
        EditorService::initEditorApp();
    }

    /**
     * renders the survey side-menu to link the classic editor to the React editor
     */
    public function beforeRenderSurveySidemenu()
    {
        EditorService::beforeRenderSurveySidemenu($this);
    }
}
