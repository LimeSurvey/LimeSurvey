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

        // The 'expression' property comes from the question attribute definition (either from the theme's config.xml
        // or from newQuestionAttributes() plugin event).
        // The value 2 indicates that the attribute must be treated as an EM expression (survey logic file and question
        // summary automatically add the brackets before evaluation).
        if (isset($this->setting['expression']) && $this->setting['expression'] == 2) {
            $this->setting['aFormElementOptions']['inputGroup'] = ['prefix' => '{', 'suffix' => '}'];
        }

        $content = $this->render($this->setting['inputtype'], null, true);
        $this->render('layout', ['content' => $content]);
    }
}
