<?php
/** @var \ls\models\questions\ChoiceQuestion $question */
if ($question->hasSubQuestions) {
//    echo TbHtml::well("Answer options for this question: " . print_r(TbHtml::listData($question->answers, 'code', 'code'), true));

    $first = true;
    foreach ($question->survey->languages as $language) {
        $tabs[] = [
            'label' => App()->locale->getLanguage($language),
            'active' => $language == $question->survey->language,
            'id' => "questions-$language",
            'content' => $this->renderPartial('update/subQuestionTab', ['question' => $question, 'language' => $language, 'first' => $first], true)
        ];
        $first = false;
    }
    echo TbHtml::well("To assist you with editing, the base language is shown for untranslated fields.");
    $this->widget(TbTabs::class, [
        'tabs' => $tabs,
        'id' => 'questionTab'
    ]);
    echo TbHtml::button('Add question', ['id' => 'addquestion']);

}
?>
<script>
    $(document).ready(function() {
        function updateProperties(group, i) {
            var $group = $(group);
            var j = $group.attr('data-index');
            $group.attr('data-index', i);
            $group.find('input').each(function(_, input) {
                var $input = $(input);
                $input.attr('name', $input.attr('name').replace('Question[' + j + ']', 'Question[' + i + ']'));
                $input.attr('id', $input.attr('id').replace('Question_' + j, 'Question_' + i));
            });
        }
        function renumber(elements) {
            elements.each(function(i, elem) {
                $(elem).find('.form-group').each(function (j, group) {
                    updateProperties(group, j);
                })
            });
        }
        $('#addquestion').on('click', function (e) {
            $(this).closest('div').find('.tab-pane').each(function (i, pane) {
                var $group = $(pane).find('.form-group:last');
                var i = parseInt($group.attr('data-index')) + 1;
                var $clone = $group.clone();

                updateProperties($clone, i);
                var $code = $clone.find('.code');
                var prev = $code.val();
                $clone.find('input').val("");
                var regex = /^(.*?)(\d+)$/;
                var matches = prev.match(regex);
                if (matches != null) {
                    $code.val(matches[1] + (1 + parseInt(matches[2])));
                }
                $clone.clone().appendTo($group.parent());
            });
        });
        var $answerTab = $('#answerTab');
        $answerTab.on('change', '.code', function (e) {
            var i = $(this).closest('.form-group').attr('data-index');
            // Update the other inputs.
            $answerTab.find('.form-group[data-index=' + i + '] .code').val($(this).val());
        });
        $('.sortable').on('sortupdate', function (e) {
            // Update the others as well.
            $this = $(this);
            $this.find('.form-group').each(function(i, elem) {
                var index = $(elem).attr('data-index');
                $answerTab.find('.sortable').not($this).find('.form-group[data-index=' + index + ']').each(function(j, group) {
                    var $group = $(group);
                    $group.appendTo($group.parent());
                });
            });
            // After moving update properties.
            renumber($answerTab.find('.sortable'));
        });
        $('.sortable').sortable();
        $answerTab.on('click', 'a.remove', function(e) {
            var index = $(this).closest('.form-group').attr('data-index');
            $answerTab.find('.form-group[data-index="' + index + '"]').remove();
            renumber($answerTab.find('.sortable'));
        });
    });
</script>