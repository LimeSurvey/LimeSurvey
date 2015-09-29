<?php
/** @var TbActiveForm $form */
use ls\models\Question;

/** @var Question $question */
//$form->layout = TbHtml::FORM_LAYOUT_HORIZONTAL;
echo TbHtml::openTag('fieldset', []);
//echo $form->textFieldControlGroup($question, 'title');
//echo $form->textFieldControlGroup($question, 'relevance');
//echo $form->textFieldControlGroup($question, 'random_group');
echo $form->checkBoxControlGroup($question, 'bool_other');

if ($question->hasAttribute('exclude_all_others')) {
    echo $form->checkBoxListControlGroup($question, 'exclude_all_others', TbHtml::listData($question->subQuestions, 'title', 'question'));
}
if ($question->hasAttribute('array_filter')) {
    echo $form->textFieldControlGroup($question, 'array_filter');
//    echo $form->checkBoxListControlGroup($question, 'array_filter', TbHtml::listData(array_filter($question->survey->questions, function(ls\models\Question $question) {
//        return $question->type == ls\models\Question::TYPE_MULTIPLE_CHOICE
//            || $question->type == ls\models\Question::TYPE_MULTIPLE_CHOICE_WITH_COMMENT;
//    }), 'title', 'question'));
}

if ($question->hasAttribute('array_filter_exclude')) {
    echo $form->textFieldControlGroup($question, 'array_filter_exclude');
}

if ($question->hasAttribute('array_filter_style')) {
    echo $form->dropDownListControlGroup($question, 'array_filter_style', $question->getArrayFilterStyleOptions());
}



echo TbHtml::closeTag('fieldset');
?>