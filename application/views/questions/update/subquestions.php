<?php
/** @var Question $question */
if (!$question->hasSubQuestions) return;

$first = true;
foreach ($question->survey->languages as $language) {
    $tabs[] = [
        'label' => App()->locale->getLanguage($language),
        'active' => $language == $question->survey->language,
        'id' => "questions-$language",
        'content' => $this->renderPartial('update/subQuestionTab', ['question' => $question, 'language' => $language, 'first' => $first, 'form' => $form], true)
    ];
    $first = false;
}
echo TbHtml::well("To assist you with editing, the base language is shown for untranslated fields.");
App()->clientScript->registerScriptFile('/components/samit-forms/samit-forms.js');
App()->clientScript->registerScript('stform', \SamIT\Form\FormHelper::activateForm('body'));

$this->widget(TbTabs::class, [
    'tabs' => $tabs,
    'id' => 'subQuestionTab',
    'htmlOptions' => \SamIT\Form\FormHelper::createAttributesForForm('has-error', 'has-success')
]);
if ($question->subQuestionScales == 1) {
    echo TbHtml::button('Add question', ['class' => 'addquestion', 'data-scale' => 0]);
} else {
    echo TbHtml::button('Add X question', ['class' => 'addquestion', 'data-scale' => 0]);
    echo TbHtml::button('Add Y question', ['class' => 'addquestion', 'data-scale' => 1]);
}

/**
 * @todo Create a css file for page specific fixes.
 */
App()->clientScript->registerCss('subquestion-form-margin-fix', '#subQuestionTab .form-group { overflow: auto; }');

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
        $('.addquestion').on('click', function (e) {
            var scale = $(this).attr('data-scale');
            $(this).closest('div').find('.tab-pane').each(function (i, pane) {

                var $group = $(pane).find('.form-group[data-index][data-scale=' + scale + ']:last');
                var $form = $group.closest('form');
                var i = parseInt($group.attr('data-index')) + 1;
                var $clone = $group.clone();

                updateProperties($clone, i, $form);
                var $code = $clone.find('.code');
                var prev = $code.val();
                $clone.find('input').val("");
                var regex = /^(.*?)(\d+)$/;
                var matches = prev.match(regex);
                if (matches != null) {
                    $code.val(matches[1] + (1 + parseInt(matches[2])));
                }
                $clone.clone().appendTo($group.parent());
                $form.yiiactiveform($form.data('settings'));
            });
        });

        /**
         * Sync answer code between tabs.
         * @type {*|jQuery|HTMLElement}
         */
        var $subQuestionTab = $('#subQuestionTab');
        $subQuestionTab.on('change', '.code', function (e) {
            var i = $(this).closest('.form-group').attr('data-index');
            // Update the other inputs.
            $subQuestionTab.find('.form-group[data-index=' + i + '] .code').val($(this).val());
        });

        /**
         * Sync sortorder between tabs.
         */
        $('.sortable').on('sortupdate', function (e) {
            // Update the others as well.
            $this = $(this);
            $this.find('.form-group').each(function(i, elem) {
                var index = $(elem).attr('data-index');
                $subQuestionTab.find('.sortable').not($this).find('.form-group[data-index=' + index + ']').each(function(j, group) {
                    var $group = $(group);
                    $group.appendTo($group.parent());
                });
            });
            // After moving update properties.
            renumber($subQuestionTab.find('.sortable'));
        });
        /**
         * Activate sorting.
         */
        $('.sortable').sortable();

        /**
         * Handle removal of answer options.
         */
        $subQuestionTab.on('click', 'a.remove', function(e) {
            var index = $(this).closest('.form-group').attr('data-index');
            $subQuestionTab.find('.form-group[data-index="' + index + '"]').remove();
            renumber($subQuestionTab.find('.sortable'));
        });
    });
</script>