<?php

class AdvancedSettingWidget extends CWidget
{
    /** @var AdvancedSetting */
    public $setting;

    /**
     * @todo Classes instead of switch.
     */
    public function run()
    {
        if ($this->setting['inputtype'] === 'singleselect') {
            //echo '<pre>'; var_dump($this->setting['aFormElementOptions']['options']); echo '</pre>';
        }
        $content = $this->render($this->setting['inputtype'], null, true);
        $this->render('layout', ['content' => $content]);
    }
}
