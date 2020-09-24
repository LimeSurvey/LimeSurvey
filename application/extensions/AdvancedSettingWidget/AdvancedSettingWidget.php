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
        echo '<div class="col-lg-6">';
        $this->render($this->setting['inputtype']);
        echo '</div>';
    }
}
