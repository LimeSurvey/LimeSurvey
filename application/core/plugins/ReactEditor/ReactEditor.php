<?php


class ReactEditor extends PluginBase
{

    /**
     * Where to save plugin settings etc.
     * @var string
     */
    protected $storage = 'DbStorage';

    /** @inheritdoc this plugin didn't have any public method */
    public $allowedPublicMethods = array('checkAll');

    /**
     * @return void
     */
    public function init()
    {
        $this->subscribe('beforeControllerAction', 'initEditor');
        $this->subscribe('beforeControllerAction', 'redirectQEAfterSurveyCreation');
    }

    /**
     * Init editor functionality
     */
    public function initEditor()
    {
        SettingsUser::setUserSetting('editorEnabled', true); // for testing

        $editorConfig = new EditorConfig(
            SettingsUser::getUserSettingValue('editorEnabled')
        );
        $editorConfig->initAppConfig();
        $editorRedirector = new EditorRedirector();
        $editorRedirector->handleRedirect();
    }

    /**
     * Override the redirect URL to QE after creating the survey.
     *
     * @return void
     * @throws CException
     */
    public function redirectQEAfterSurveyCreation()
    {
        $editorEnabled = SettingsUser::getUserSettingValue('editorEnabled');

        if (
            $editorEnabled
            && $this->getEvent()->get('controller') == 'surveyAdministration'
            && $this->getEvent()->get('action') == 'newSurvey'
        ) {
            //Override the submit event for the #addnewsurvey form
            App()->clientScript->registerScriptFile(
                App()->assetManager->publish(
                    dirname(__FILE__) . '/js'
                ) . '/redirectToQEAfterSurvey.js',
                LSYii_ClientScript::POS_END
            );
        }
    }

}
