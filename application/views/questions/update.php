<div class="row">
    <?php
        echo TbHtml::tag('h1', [], "Question {$this->question->title} ({$this->question->typeName}) -- class: " . get_class($question));
    ?>
    <div class="col-md-12">
        <?php
        // This is an update view so we use PUT.
        // We specify layout per tab.
        echo TbHtml::beginForm(['questions/update', 'id' => $question->qid], 'put', []);

        $this->widget('TbTabs', [
            'tabs' => [
                [
                    'label' => 'Texts',
                    'content' => $this->renderPartial('update/texts', ['question' => $question], true),
                    'active' => true
                ], [
                    'label' => gT('Routing'),
                    'content' => $this->renderPartial('update/routing', ['question' => $question], true),
                ], [
                    'label' => gT('Validation'),
                    'content' => $this->renderPartial('update/validation', ['question' => $question], true),
                ], [
                    'label' => gT('Presentation & Navigation'),
                    'content' => "@todo",
                ], [
                    'label' => gT('Timers'),
                    'content' => "@todo",
                ], [
                    'label' => gT('Statistics'),
                    'content' => $this->renderPartial('update/statistics', ['question' => $question], true),
                ], [
                    'label' => gT('Subquestions'),
                    'visible' => $question->hasSubQuestions,
                    // This will make sure we don't render if the tab is not visible.
                    'content' => $this->renderPartial('update/subquestions', ['question' => $question], true),
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
//            'class' => 'ajaxSubmit'
        ]);
        echo TbHtml::closeTag('div');
        echo TbHtml::endForm();
        ?>
    </div>


</div>