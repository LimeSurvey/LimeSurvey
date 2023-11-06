<?php
$this->widget('yiiwheels.widgets.switch.WhSwitch', array(
        'name'     => 'toggle_question_theme',
        'id'       => 'toggle_question_theme' . $id,
        'value'    => $buttons['visibility_button']['visible'],
        'onLabel'  => gT('On'),
        'offLabel' => gT('Off'),
        'htmlOptions' => array('class' => 'toggle_question_theme', 'data-url' => $buttons['visibility_button']['url'])
    )
);
?>
