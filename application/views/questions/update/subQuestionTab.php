<div class="form-horizontal">
    <?php
    /** @var Controller $this */
    /** @var \ls\models\forms\SubQuestions $subQuestions */
    /** @var TbActiveForm $form */
    $options = [
        'formLayout' => TbHtml::FORM_LAYOUT_INLINE,
//    'controlWidthClass' => 'col-sm-11',
//    'labelWidthClass' => 'col-sm-1'
    ];
    echo TbHtml::tag('div', [
        'class' => 'form-group'
    ], TbHtml::activeLabel(new Question(), "title", [
            'class' => 'col-sm-1'
        ]) . TbHtml::activeLabel(new Question(), "question", [
            'class '=> 'col-sm-10',
        ]) . TbHtml::label(gT('Actions'), null, ['class' => 'col-sm-1'])
    );

    echo TbHtml::openTag('div', ['class' => 'sortable']);
    $i = 0;
    foreach ($subQuestions->titles as $i => $title) {
//        $question = $subQuestions->subQuestion[$title];
//        $question->language = $language;
//        echo TbHtml::errorSummary($question);
        $form->textFieldControlGroup($subQuestions->getInstance(), 'title');
        echo TbHtml::openTag('div', ['class' => 'form-group', 'data-index' => $i]);
        if ($first) {

//            echo TbHtml::activeTextField($subQuestions->getInstance(), "title", ['class' => 'col-sm-1 code']);
        } else {

//            echo TbHtml::textField("titles[$i]", $title, ['class' => 'col-sm-1 code']);
        }

        echo TbHtml::activeTextField($subQuestions, "questions[$i][$language]", [
            'class' => 'col-sm-10',
//            'value' => $question->question
            // TranslatableBehavior makes sure we copy the base language if no translation is found.
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
