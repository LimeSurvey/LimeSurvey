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
            //echo '<pre>'; var_dump($this->setting); echo '</pre>';die;
        }
        $this->render($this->setting['inputtype']);
    }
}
