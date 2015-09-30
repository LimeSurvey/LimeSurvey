<?php
/**
 * This view wraps the yii validators so they still work when fields are dynamically duplicated (when adding / removing answer options).
 *
 */
use ls\models\Question;

/** @var \ls\models\Question $question */
/** @var TbActiveForm $form */



$i = 0;

for ($scale = 0; $scale < $question->subQuestionScales; $scale++) {
    // Table header
    if ($question->subQuestionScales > 1) {
        echo TbHtml::tag('h1', [], $scale == 0 ? gT("Y-Scale") : gT("X-Scale"));
    }
    echo TbHtml::tag('div', [
        'class' => 'form-group',
    ], TbHtml::activeLabel(new Question(), "title", [
            'class' => 'col-sm-1'
        ]) . TbHtml::activeLabel(new Question(), "question", [
            'class '=> 'col-sm-10',
        ]) . TbHtml::label(gT('Actions'), null, ['class' => 'col-sm-1'])
    );
    echo TbHtml::openTag('div', ['class' => 'sortable']);
    $subQuestions = array_filter($question->subQuestions, function(Question $question) use ($scale) {
        return $question->scale_id == $scale;
    });
    if (empty($subQuestions)) {
        $subQuestion = new \ls\models\questions\SubQuestion();
        $subQuestion->title = $question->subQuestionScales == 1 ? "SQ001" : ($scale == 0 ? "YQ001" : "XQ001");
        $subQuestion->scale_id = $scale;
        $subQuestion->parent_qid = $question->primaryKey;
        $subQuestions = [$subQuestion];
    }
    // Take scales into account.
    foreach ($subQuestions as $subQuestion) {
//        vdd($subQuestion);
        $subQuestion->language = $language;
        $attribute = "[{$i}]title";
        echo TbHtml::openTag('div', array_merge(['class' => 'form-group', 'data-index' => $i, 'data-scale' => $scale],

            SamIT\Form\FormHelper::createAttributesForHighlight(TbHtml::resolveName($subQuestion, $attribute))));
        if ($first) {
            $validators = \SamIT\Form\ValidatorGenerator::createFromYii1Model($subQuestion, 'title');
            $message = gT("Answer codes must be unique.");
            /**
             * This is client side only. The server side is handled by the controller.
             * @todo Develop something proper that uses a collection model and validates that model.
             */
            $validators[] = "for (var key in values) { if (values[key] !== elem && values[key].value == value) return '$message'; } return true;";
            echo $form->hiddenField($subQuestion, "[{$i}]scale_id");
            echo $form->textField($subQuestion, "[{$i}]title", array_merge([
                'class' => 'col-sm-1 code',
            ], \SamIT\Form\FormHelper::createAttributesForInput($validators)));
        } else {
            echo TbHtml::textField("code", $subQuestion->title,
                ['id' => "code_{$i}_$language", 'class' => 'col-sm-1 code']);
        }

        echo $form->textField($subQuestion, "[{$i}]translatedFields[$language][question]", [
            'class' => 'col-sm-10',
            // TranslatableBehavior makes sure we copy the base language if no translation is found.
            'value' => $subQuestion->question
        ]);
        echo TbHtml::tag('div', ['class' => 'col-sm-1'],
            TbHtml::linkButton("", ['icon' => 'trash', 'class' => 'remove']));
        $attribute = "[{$i}]title";
        echo TbHtml::tag('div', [
            'st-error' => TbHtml::resolveName($subQuestion, $attribute),
            'class' => 'help-block'
        ], '');
        echo TbHtml::closeTag('div');
        $i++;
    }
    echo TbHtml::closeTag('div');
}



/**
 * @todo Create a css file for page specific fixes.
 */
App()->clientScript->registerCss('form-margin-fix', '#answerTab .form-group { overflow: auto; }');
?>