<?php

class AdvancedSettingWidget extends CWidget
{
    /** @var AdvancedSetting */
    public $setting;

    /** @var Survey */
    public $survey;

    /**
     * @todo Classes instead of array.
     * @return void
     */
    public function run()
    {
        // Debug code.
        //echo '<pre>'; var_dump($this->setting); echo '</pre>';die;
        //if ($this->setting['inputtype'] === 'singleselect') {
            //echo '<pre>'; var_dump($this->setting['aFormElementOptions']['options']); echo '</pre>';
        //}
        $content = $this->render($this->setting['inputtype'], null, true);
        $this->render('layout', ['content' => $content]);
    }
}
