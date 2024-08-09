<?php

class SearchBoxWidget extends CWidget
{
    public $formUrl          = 'admin/index';

    public $model;
    public $onlyfilter = false;
    /**
     * For deciding which view widget to render
     * @var $viewtype string|null
     */
    public  $viewtype = '';
    /**
     * For rendering the switch to decide which view widget is rendered
     * @var $switch bool
     */
    public $switch = false;

    /**
     * @throws \CException
     */
    public function run()
    {
        if (App()->request->getQuery('viewtype')) {
            $this->viewtype = App()->request->getQuery('viewtype');
        } elseif (SettingsUser::getUserSettingValue('welcome_page_widget')) {
            $this->viewtype = SettingsUser::getUserSettingValue('welcome_page_widget');
        } else {
            $this->viewtype = 'list-widget';
        }
        if (!empty($this->viewtype) && $this->switch) {
            SettingsUser::setUserSetting('welcome_page_widget', $this->viewtype);
        }
        $this->render('searchBox');
    }

    /** Initializes the widget */
    public function init(): void
    {
        $this->registerClientScript();
    }

    public function registerClientScript()
    {
        App()->getClientScript()->registerScriptFile(
            App()->getConfig("extensionsurl") . 'admin/SearchBoxWidget/assets/filters.js',
            CClientScript::POS_END
        );
    }
}
