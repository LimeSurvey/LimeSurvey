<?php
/** @var TbActiveForm $form */
/** @var \ls\models\forms\ParticipantDatabaseSettings $settings */
echo TbHtml::openTag('fieldset', []);
echo $form->checkBoxControlGroup($settings, 'userideditable', ['uncheckedValue' => 'N', 'checkedValue' => 'Y']);
echo TbHtml::closeTag('fieldset');
