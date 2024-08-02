<?php

class SearchBoxWidget extends CWidget
{
    public $formUrl          = 'admin/index';

    public $model;
    public $switch = false;
    public $onlyfilter = false;
    /**
     * @throws \CException
     */
    public function run()
    {
        if ($viewtype = App()->request->getQuery('viewtype')) {
            SettingsUser::setUserSetting('welcome_page_widget', $viewtype);
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

        App()->getClientScript()->registerCssFile(
            App()->getConfig("extensionsurl") . 'admin/SearchBoxWidget/assets/css/SearchBoxWidget.css'
        );
    }
}
