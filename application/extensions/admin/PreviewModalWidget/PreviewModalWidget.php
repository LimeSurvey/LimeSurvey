<?php

/**
 * Used for question type select, display export options.
 * @todo Split into two widgets?
 */
class PreviewModalWidget extends CWidget
{
    /**
     * The name the widget will be rendered to, please make sure it is unique!
     * @var string
     */
    public $widgetsJsName = "";

    /**
     * The title the widgets modal will have
     * @var string
     */
    public $modalTitle = "Select please";

    /**
     * If using the grouped view this should be the key to get the group title
     * @var string
     */
    public $groupTitleKey = "title";

    /**
     * If using the grouped view this should be the key of the grouped items array key
     * @var string
     */
    public $groupItemsKey = "items";

    /**
     * Display this with the items key in debug mode
     * @var string
     */
    public $debugKeyCheck = "Key: ";

     /**
     * The title of the preview window
     * @var string
     */
    public $previewWindowTitle = "";

    /**
     * Either a group or an items array must be given
     * @var array
     */
    public $groupStructureArray = [];

    /** @var array */
    public $itemsArray = [];

    /**
     * There should be a value set
     * @var mixed
     */
    public $value = null;

    /**
     * QuestionTheme name
     * @var string
     */
    public $theme = '';

    /**
     * This may be the value also, but oftentimes you'd want this to be an easy to read title
     * @var string
     */
    public $currentSelected = "";

    /**
     * This is the option array that gets fed into the javascript.
     * @var array
     */
    public $optionArray = [];

    /**
     * If the button should have any extra classes or modifications.
     * @var string[]
     */
    public $buttonClasses = ['btn-outline-secondary'];

    /**
     * The position the icon has in the modal button
     * @var string
     */
    public $iconPosition = 'front';

    /**
     * These are pretty standard and may not need to be changed
     * @var string
     */
    public $closeButton = "Close";

    /**
     * @var string
     */
    public $selectButton = "Select";

    /**
     * RenderType should either be modal, simple, group-simple or group-modal
     * @var string
     */
    public $renderType = 'modal';

    /**
     * @var bool
     */
    public $debug = false;

    /**
     * @var bool true if survey is active, in this case questiontype should not be changed (button disabled)
     */
    public $survey_active = false;

    /**
     * @return void
     */
    public function run()
    {
        //clean up the widgets name to be js friendly
        $this->widgetsJsName = preg_replace('/[^a-zA-Z0-9_-]/','',$this->widgetsJsName);
        $this->registerScripts();
    }

    /**
     * @param bool $return
     * @return string|void
     * @throws CException
     */
    public function getModal($return = false)
    {
        if(preg_match("/modal/",$this->renderType)) {
            return $this->render($this->getView(), null, $return);
        }
    }

    /**
     * @param bool $return
     * @return string|void
     */
    public function getButtonOrSelect($return = false)
    {
        if(preg_match("/modal/",$this->renderType)) {
            return $this->render("open_modal_button", null, $return);
        }

        return $this->render($this->view, null, $return);
    }

    /**
     * @return string
     */
    public function getView()
    {
        switch($this->renderType) {
            case 'simple' : return 'simple_select';
            case 'group-simple' : return 'simple_grouped_select';
            case 'group-modal' : return 'grouped_select_modal';
            case 'modal' : //fallthrough
            default: return 'select_modal';
        }
    }

    /**
     * @return void
     */
    private function registerScripts()
    {
        $oClientScript = App()->getClientScript();
        $basePath = dirname(__FILE__) . '/assets/';

        //publish Assets
        $sStyleFile = App()->getAssetManager()->publish($basePath.'previewModalWidget.css');
        $sScriptFile = App()->getAssetManager()->publish($basePath.'previewModalWidget.js');
        //register Assets
        $oClientScript->registerCssFile($sStyleFile);
        $oClientScript->registerScriptFile($sScriptFile, CClientScript::POS_BEGIN);
        $oClientScript->registerScript(
            'WIDGETSCRIPT--' . $this->widgetsJsName,
            'var runner_' . $this->widgetsJsName . ' = new PreviewModalScript("' . $this->widgetsJsName . '",'
            . json_encode(
                array_merge(
                    $this->optionArray,
                    [
                        'value' => $this->value,
                        'theme' => $this->theme,
                        'debugString' => $this->debugKeyCheck,
                        'debug' => $this->debug,
                        'viewType' => $this->view
                    ]
                )
            )
            . '); runner_'.$this->widgetsJsName.'.bind();',
            LSYii_ClientScript::POS_POSTSCRIPT
        );
    }
}
