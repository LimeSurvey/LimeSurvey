<div class="row">
    <div class="col-md-12">
        <?php
            echo TbHtml::tag('h1', [], "Question {$this->question->title} ({$this->question->typeName}) -- class: " . get_class($question));
        // This is an update view so we use PUT.
        /** @var TbActiveForm $form */
        $form = $this->beginWidget(TbActiveForm::class, [
            'enableAjaxValidation' => false,
            'enableClientValidation' => true,
            'layout' => TbHtml::FORM_LAYOUT_VERTICAL,
            'action' => ['questions/update', 'id' => $question->qid],
            'method' => 'put'
        ]);


        $this->widget('TbTabs', [
            'tabs' => [
                [
                    'label' => 'Texts',
                    'content' => $this->renderPartial('update/texts', ['question' => $question, 'form' => $form], true),
                    'active' => true
                ], [
                    'label' => gT('Routing'),
                    'content' => $this->renderPartial('update/routing', ['question' => $question, 'form' => $form], true),
                ], [
                    'label' => gT('Validation'),
                    'content' => $this->renderPartial('update/validation', ['question' => $question, 'form' => $form], true),
                ], [
                    'label' => gT('Presentation & Navigation'),
                    'content' => "@todo",
                ], [
                    'label' => gT('Timers'),
                    'content' => "@todo",
                ], [
                    'label' => gT('Statistics'),
                    'content' => $this->renderPartial('update/statistics', ['question' => $question, 'form' => $form], true),
                ], [
                    'label' => gT('Subquestions'),
                    'visible' => $question->hasSubQuestions,
                    // This will make sure we don't render if the tab is not visible.
                    'content' => $this->renderPartial('update/subquestions', ['subQuestions' => $subQuestions, 'form' => $form], true),
                ], [
                    'label' => gT('Answers'),
                    'visible' => is_subclass_of($question, \ls\models\questions\ChoiceQuestion::class) && $question->hasAnswers,
                    // This will make sure we don't render if the tab is not visible.
                    'content' => $this->renderPartial('update/answers', ['question' => $question], true),
                ]
            ]
        ]);
        echo TbHtml::openTag('div', ['class' => 'pull-right btn-group']);
        echo TbHtml::submitButton('Save settings', [
            'color' => 'primary',
            'class' => 'ajaxSubmit'
        ]);
        echo TbHtml::closeTag('div');
        $this->endWidget();
        ?>
    </div>


</div>