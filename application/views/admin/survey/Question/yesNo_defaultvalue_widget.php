<?php

    /**
     * @file
     *
     * This file holds the widget for the yes-no question type, to edit it's default values.
     *
     * Features:
     * - YES/NO preselection
     * - EM integration to insert an em expression like {TOKEN:ATTRIBUTE_6}. At this state there is no validation implemented. Attributes must hold Y or N.
     *
     * DEV MEMO:
     * Validation could be difficult cause if you using tokens and you don't had setup a working token dataset
     *
     * Used by the "Default answers" tab of the question editor (questionAdministration/defaultValues/yesNo.twig)
     */

class yesNo_defaultvalue_widget extends CWidget
{
    public $widgetOptions;

    //init() method is called automatically before all others
    public function init()
    {
        /*you can set initial default values and other stuff here.
         * it's also a good place to register any CSS or Javascript your
         * widget may need. */
    }

    /**
     * Render the default value select of a Yes/No question and the field for an expression.
     *
     * Widget options: language, questionrow (Question or its attributes), langopts (see
     * QuestionAdministrationController::getDefaultValues()), and optionally elementId and
     * emElementId as the input names of the select and the expression field.
     *
     * @return void
     */
    public function run()
    {

        $questionrow = $this->widgetOptions['questionrow'];
        $langopts = $this->widgetOptions['langopts'];
        $language = $this->widgetOptions['language'];
        $defaultValues =  $this->widgetOptions['langopts'][$language][$questionrow['type']][0] ?? null;

        $elementId = $this->widgetOptions['elementId'] ?? null;
        $emElementId = $this->widgetOptions['emElementId'] ?? null;

        $emfield_css = '';
        $emValue = '';
        $select = '';
        $sEmfield_css_class = '';

        // prepare variables for prefilling the form
        if (!is_null($defaultValues)) {
            $sDefaultValue = $defaultValues;
            if (($sDefaultValue == 'N') || ($sDefaultValue == 'Y') || ($sDefaultValue == '')) { //|| 'Y' || NULL)){
                $select = $defaultValues;
            } else {
                $select = 'EM';
                $emValue = $defaultValues;
            }
        }

        if ($questionrow['type'] == Question::QT_Y_YES_NO_RADIO) { // do we need this?
            if (empty($elementId)) {
                $elementId = 'defaultanswerscale_0_' . $language;
            }

            if (empty($emElementId)) {
                $emElementId = $elementId . '_EM';
            }

            $aList = array(
                'N'    => gT('No', 'unescaped'),
                'Y'    => gT('Yes', 'unescaped'),
                'EM'   => gT('EM value', 'unescaped')
            );

            $aHtmlOptions = array(
                'empty'    => gT('(No default value)'),
                'class'    => $elementId . ' form-control',
                'onchange' => '// show EM Value Field
                                   if ($(this).val() == "EM"){
                                       $("#"+$(this).closest("select").attr("id")+ "_EM").removeClass("d-none");
                                   }else{
                                       $("#"+$(this).closest("select").attr("id")+ "_EM").addClass("d-none");} '
            );

            echo CHtml::dropDownList($elementId, $select, $aList, $aHtmlOptions);

            // The expression field is only needed while "EM value" is selected
            if ($select !== 'EM') {
                $sEmfield_css_class = 'd-none';
            }
            // The onchange handler above finds the EM field by the select's id + "_EM"
            echo CHtml::textField($emElementId, $emValue, array(
                    'id'    => CHtml::getIdByName($elementId) . '_EM',
                    'class' => 'form-control ' . $sEmfield_css_class,
                    'aria-label' => gT('EM value'),
                    'width' => 100
                ));
        }
    }
}
