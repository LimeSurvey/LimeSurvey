<?php
$this->widget('ext.ButtonGroupWidget.ButtonGroupWidget', [
    'name' => 'toggle_question_theme' . $id,
    'id' => 'toggle_question_theme'. $id,
    'checkedOption' => $buttons['visibility_button']['visible'],
    'selectOptions' => [
        '1' => gT('On'),
        '0' => gT('Off'),
    ],
    'htmlOptions' => [
        'class' => 'toggle_question_theme',
        'data-url' => $buttons['visibility_button']['url']
    ]
]);
