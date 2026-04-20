<?php

class AdvancedSettingWidget extends CWidget
{
    /** @var AdvancedSetting */
    public $setting;

    /** @var Survey */
    public $survey;

    const SINGLEINPUTTYPE = array(
        'columns',
        'integer',
        'float',
        'singleselect',
        'text',
        'textarea'
    );
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
        $this->setting['hidden'] = !empty($this->setting['hidden']);
        $this->setting['i18n'] = !empty($this->setting['i18n']);
        $this->setting['help'] = trim((string) $this->setting['help']);
        if ($this->setting['help']) {
            /* @fixme : Must be done in Model : QuestionTheme must be allowed to have own translation, plugin can have own translation */
            $this->setting['help'] = gT($this->setting['help'], 'unescaped');
        }

        // Translate options
        if (!empty($this->setting['options'])) {
            foreach ($this->setting['options'] as $optionValue => $optionText) {
                $this->setting['options'][$optionValue] = is_string($optionText) ? gT($optionText, 'unescaped') : $optionText;
            }
        }

        $inputBaseName = "advancedSettings[" . strtolower((string) $this->setting['category']) . "][" . $this->setting['name'] ."]";
        $content = $this->render($this->setting['inputtype'],
            ['inputBaseName' => $inputBaseName]
            , true
        );
        $this->render('layout',
            [
                'content' => $content,
                'inputBaseName' => $inputBaseName
            ]
        );
    }
}
