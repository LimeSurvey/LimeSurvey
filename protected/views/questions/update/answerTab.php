<?php
/**
 * This view wraps the yii validators so they still work when fields are dynamically duplicated (when adding / removing answer options).
 *
 */

/** @var \Question $question */
/** @var TbActiveForm $form */
/** @var \Answer $answer */


$i = 0;
for ($scale = 0; $scale < $question->answerScales; $scale++) {

    if ($question->answerScales > 1) {
        echo TbHtml::tag('h1', [], $scale == 0 ? gT("Scale 1") : gT("Scale 2"));
    }
    // Table header
    echo TbHtml::tag('div', [
        'class' => 'form-group'
    ], TbHtml::activeLabel(new Answer(), "code", [
            'class' => 'col-sm-1'
        ]) . TbHtml::activeLabel(new Answer(), "answer", [
            'class '=> 'col-sm-10',
        ]) . TbHtml::label(gT('Actions'), null, ['class' => 'col-sm-1'])
    );

    echo TbHtml::openTag('div', ['class' => 'sortable']);
    $answers = array_filter($question->answers, function(Answer $answer) use ($scale) {
        return $answer->scale_id == $scale;
    });
    if (empty($answers)) {
        $answer = new Answer();
        $answer->code = $question->answerScales == 1 ? "A1" : ($scale == 0 ? "L1" : "R1");
        $answer->scale_id = $scale;
        $answer->question_id = $question->primaryKey;
        $answers = [$answer];
    }
    foreach ($answers as $answer) {
        $answer->language = $language;
        $attribute = "[{$i}]code";
        echo TbHtml::openTag('div', array_merge(['class' => 'form-group', 'data-index' => $i, 'data-scale' => $scale],

            SamIT\Form\FormHelper::createAttributesForHighlight(TbHtml::resolveName($answer, $attribute))));
        if ($first) {
            $validators = \SamIT\Form\ValidatorGenerator::createFromYii1Model($answer, 'code');
            $message = gT("Answer codes must be unique.");
            /**
             * This is client side only. The server side is handled by the controller.
             * @todo Develop something proper that uses a collection model and validates that model.
             */
            $validators[] = "for (var key in values) { if (values[key] !== elem && values[key].value == value) return '$message'; } return true;";
            echo $form->hiddenField($answer, "[{$i}]scale_id");
            echo $form->textField($answer, "[{$i}]code", array_merge([
                'class' => 'col-sm-1 code',
            ], \SamIT\Form\FormHelper::createAttributesForInput($validators)));
        } else {
            echo TbHtml::textField("code", $answer->code, ['id' => "code_{$i}_$language", 'class' => 'col-sm-1 code']);
        }

        echo $form->textField($answer, "[{$i}]translatedFields[$language][answer]", [
            'class' => 'col-sm-10',
            // TranslatableBehavior makes sure we copy the base language if no translation is found.
            'value' => $answer->answer
        ]);
        echo TbHtml::tag('div', ['class' => 'col-sm-1'],
            TbHtml::linkButton("", ['icon' => 'trash', 'class' => 'remove']));
        $attribute = "[{$i}]code";
        echo TbHtml::tag('div', [
            'st-error' => TbHtml::resolveName($answer, $attribute),
            'class' => 'help-block'
        ], '');
        echo TbHtml::closeTag('div');
        $i++;
    }

    echo TbHtml::closeTag('div');

}



?>