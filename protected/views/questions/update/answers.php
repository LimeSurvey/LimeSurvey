<?php
/** @var \ls\models\questions\ChoiceQuestion $question */
if (!$question->hasAnswers) return;

$first = true;
foreach ($question->survey->languages as $language) {
    $tabs[] = [
        'label' => App()->locale->getLanguage($language),
        'active' => $language == $question->survey->language,
        'id' => "answers-$language",
        'content' => $this->renderPartial('update/answerTab', ['question' => $question, 'language' => $language, 'first' => $first, 'form' => $form], true)
    ];
    $first = false;
}
echo TbHtml::well("To assist you with editing, the base language is shown for untranslated fields.");
App()->clientScript->registerScriptFile('/components/samit-forms/samit-forms.js');
App()->clientScript->registerScript('stform', \SamIT\Form\FormHelper::activateForm('body'));
$this->widget(TbTabs::class, [
    'tabs' => $tabs,
    'id' => 'answerTab',
    'htmlOptions' => \SamIT\Form\FormHelper::createAttributesForForm('has-error', 'has-success')
]);
if ($question->answerScales == 1) {
    echo TbHtml::button('Add answer option', ['class' => 'addanswer', 'data-scale' => 0]);
} else {
    for ($i = 0; $i < $question->answerScales; $i++) {
        echo TbHtml::button('Add answer (Scale ' . ($i + 1). ')', ['class' => 'addanswer', 'data-scale' => $i]);
    }
}

/**
 * @todo Create a css file for page specific fixes.
 */
App()->clientScript->registerCss('answer-form-margin-fix', '#answerTab .form-group { overflow: auto; }');

?>
<script>
    /**
     * @todo Move this to a separate file, or even better create a generic purpose utility for dynamic forms.
     */
    $(document).ready(function() {
        /*
            This function updates the indexes for the field, use it after copying a group.
         */
        function updateProperties(group, i) {
            var $group = $(group);
            var j = $group.attr('data-index');
            $group.attr('data-index', i);
            $group.find('input').each(function(_, input) {
                var $input = $(input);
                $input.attr('name', $input.attr('name').replace('[' + j + ']', '[' + i + ']'));
                $input.attr('id', $input.attr('id').replace('_' + j, '_' + i));
            });
            $group.find('[st-error]').each(function(_, err) {
                var $err = $(err);
                $err.attr('st-error', $err.attr('st-error').replace('[' + j + ']', '[' + i + ']'));
            });

            if ($group.is('[st-mark]')) {
                $group.attr('st-mark', $group.attr('st-mark').replace('[' + j + ']', '[' + i + ']'));
            }
        }

        /**
         * This function iterates over a set of elements and updates their indexes.
         * @param elements
         */
        function renumber(elements) {
            elements.each(function(i, elem) {
                $(elem).find('.form-group').each(function (j, group) {
                    updateProperties(group, j);
                })
            });
        }

        /**
         * Add an answer option.
         */
        $('.addanswer').on('click', function (e) {
            var scale = $(this).attr('data-scale');
            $(this).closest('div').find('.tab-pane').each(function (i, pane) {
                var $group = $(pane).find('.form-group[data-index][data-scale=' + scale + ']:last');
                var $form = $group.closest('form');
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
                $clone.clone().appendTo($group.parent()).trigger('change');
                $form.yiiactiveform($form.data('settings'));
            });
        });

        /**
         * Sync answer code between tabs.
         * @type {*|jQuery|HTMLElement}
         */
        var $answerTab = $('#answerTab');
        $answerTab.on('input', '.code', function (e) {
            var i = $(this).closest('.form-group').attr('data-index');
            // Update the other inputs.
            $answerTab.find('.form-group[data-index=' + i + '] .code').val($(this).val());
        });

        /**
         * Sync sortorder between tabs.
         */
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
        /**
         * Activate sorting.
         */
        $('.sortable').sortable();

        /**
         * Handle removal of answer options.
         */
        $answerTab.on('click', 'a.remove', function(e) {
            e.preventDefault();
            var index = $(this).closest('.form-group').attr('data-index');
            $answerTab.find('.form-group[data-index="' + index + '"]').remove();
            renumber($answerTab.find('.sortable'));
        });
    });
</script>