<div class="form-horizontal">
<?php
/** @var \Question $question */

/** @var \Answer $answer */
$options = [
    'formLayout' => TbHtml::FORM_LAYOUT_INLINE,
//    'controlWidthClass' => 'col-sm-11',
//    'labelWidthClass' => 'col-sm-1'
];
echo TbHtml::tag('div', [
    'class' => 'form-group'
], TbHtml::activeLabel(new Answer(), "code", [
    'class' => 'col-sm-1'
]) . TbHtml::activeLabel(new Answer(), "answer", [
    'class '=> 'col-sm-10',
]) . TbHtml::label(gT('Actions'), null, ['class' => 'col-sm-1'])
);

echo TbHtml::openTag('div', ['class' => 'sortable']);
$i = 0;
foreach ($question->answers as $answer) {
    $answer->language = $language;
    echo TbHtml::openTag('div', ['class' => 'form-group', 'data-index' => $i]);
        if ($first) {
            echo TbHtml::activeTextField($answer, "[{$i}]code", ['class' => 'col-sm-1 code']);
        } else {
            echo TbHtml::textField("code", $answer->code, ['id' => "code_{$i}_$language", 'class' => 'col-sm-1 code']);
        }
        echo TbHtml::activeTextField($answer, "[{$i}]translatedFields[$language][answer]", [
            'class' => 'col-sm-10',
            'value' => $answer->answer // TranslatableBehavior makes sure we copy the base language if no translation is found.
        ]);
//        echo TbHtml::textField("Answer[{$i}]translatedFields[$language][answer]", $answer->answer, ['class' => 'col-sm-10']);
        echo TbHtml::openTag('div', ['class' => 'col-sm-1']);
        echo TbHtml::linkButton("", ['icon' => 'trash', 'class' => 'remove']);
        echo TbHtml::closeTag('div');
    echo TbHtml::closeTag('div');
    $i++;
}

echo TbHtml::closeTag('div');
?></div>
