<?php

/**
 * SearchBoxWidget is a custom Yii widget used to render a search box with filtering capabilities.
 * It supports different view types and can switch between them based on user preferences or query parameters.
 */
class SearchBoxWidget extends CWidget
{
    /**
     * @var string $formUrl The URL to which the form will be submitted. Defaults to 'dashboard/view'.
     */
    public string $formUrl = 'dashboard/view';

    /**
     * @var CActiveRecord $model The model associated with the search form.
     */
    public CActiveRecord $model;

    /**
     * @var bool $onlyfilter If true, only the filter section of the widget is rendered.
     */
    public bool $onlyfilter = false;

    /**
     * @var string|null $viewtype The type of view widget (list-widget or box-widget) to render.
     * Can be set via query parameter, user settings, or defaults to 'list-widget'.
     */
    public ?string $viewtype = '';

    /**
     * @var bool $switch If true, the view type selection is saved to user settings.
     */
    public bool $switch = false;

    /**
     * @var int|null $resultCount Number of surveys matching the current filters.
     * When set and a filter is active, it is announced to screen readers after the page has been reloaded.
     */
    public ?int $resultCount = null;

    /**
     * Runs the widget, rendering the appropriate view based on the viewtype and switch properties.
     * It determines the viewtype from the query parameters, user settings, or defaults.
     *
     * @throws CException If an error occurs during rendering.
     */
    public function run()
    {
        $this->formUrl = $this->getFormUrl();
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

    /**
     * Returns the message announced to screen readers after filtering, e.g. "No surveys found.".
     * Empty when no filter is active or the number of results is unknown.
     *
     * @return string
     */
    public function getResultMessage(): string
    {
        $request = App()->request;
        $search = $request->getQuery('Survey');
        $isFiltered = !empty($request->getQuery('active'))
            || !empty($request->getQuery('gsid'))
            || (is_array($search) && isset($search['searched_value']) && $search['searched_value'] !== '');
        if (!$isFiltered || $this->resultCount === null) {
            return '';
        }
        if ($this->resultCount === 0) {
            return gT('No surveys found.', 'unescaped');
        }
        return sprintf(ngT('%s survey found.|%s surveys found.', $this->resultCount, 'unescaped'), $this->resultCount);
    }

    /**
     * Initializes the widget by registering necessary client scripts.
     */
    public function init(): void
    {
        $this->registerClientScript();
    }

    /**
     * Registers the necessary JavaScript and CSS files for the widget if needed.
     */
    public function registerClientScript()
    {
        // Register JavaScript file
        App()->getClientScript()->registerScriptFile(
            App()->getAssetManager()->publish(dirname(__FILE__) . '/assets/filters.js'),
            CClientScript::POS_END
        );
    }
    /**
     * Generates and returns the form URL, handling URL formatting and GET parameters.
     *
     * @return string The generated form URL.
     * @throws CException
     */
    public function getFormUrl(): string
    {
        $url = App()->createAbsoluteUrl(App()->request->getPathInfo());
        if (Yii::app()->getUrlManager()->getUrlFormat() == CUrlManager::GET_FORMAT) {
            // Ignore all GET params (searchbox filters) except the 'r' param.
            return $url . '?' . http_build_query(['r' => App()->request->getParam('r')]);
        }
        return $url;
    }
}
