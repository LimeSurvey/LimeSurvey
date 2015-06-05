<div class="row">
    <?php
        /** @var \ls\models\forms\FormattingOptions $options */
        echo TbHtml::tag('h1', [], gT("Export responses"));

    ?>
    <div class="col-md-4">
        <?php
        /** @var TbActiveForm $form */
        $form = $this->beginWidget(TbActiveForm::class, [
            'layout' => TbHtml::FORM_LAYOUT_VERTICAL,
            'method' => 'post',
            'enableClientValidation' => true,

            'htmlOptions' => [
                'validateOnSubmit' => true
            ]
        ]);

        echo $form->radioButtonListControlGroup($options, 'type', $options->typeOptions);




        echo $form->radioButtonListControlGroup($options, 'answerFormat',$options->answerFormatOptions);

        echo $form->textFieldControlGroup($options, 'nValue');
        echo $form->textFieldControlGroup($options, 'yValue');
    ?>
    </div><div class="col-md-4">
        <?php
            echo $form->numberFieldControlGroup($options, 'offset');
            echo $form->numberFieldControlGroup($options, 'limit');
            echo $form->dropDownListControlGroup($options, 'responseCompletionState', $options->responseCompletionStateOptions);
            echo $form->radioButtonListControlGroup($options, 'headingFormat', $options->headingFormatOptions);
            echo $form->checkBoxControlGroup($options, 'headerSpacesToUnderscores');
        ?>


    </div><div class="col-md-4">
        <?php

        echo $form->listBoxControlGroup($options, 'selectedColumns', $options->selectedColumnOptions, [
            'multiple' => true
        ]);

        ?>

    </div>
    <div class="col-md-12">
    <?php
    echo TbHtml::openTag('div', ['class' => 'pull-right btn-group']);
    echo TbHtml::submitButton(gT('Export'), [
        'color' => 'primary'
    ]);
    echo TbHtml::closeTag('div');

    $this->endWidget();

    ?>
    </div>
</div>

