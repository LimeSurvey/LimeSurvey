<div class="row">
    <?php
        echo TbHtml::tag('h1', [], "Question {$this->question->title} ({$this->question->typeName})");
    ?>
    <div class="col-md-12">
        <?php
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
                ]
            ]
        ]);

        ?>
    </div>


</div>