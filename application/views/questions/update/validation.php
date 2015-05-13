<?php
/** @var TbActiveForm $form */
echo TbHtml::openTag('fieldset', []);
echo $form->textFieldControlGroup($question, 'preg');
echo $form->textFieldControlGroup($question, 'a_validation_q');
echo $form->textFieldControlGroup($question, 'a_validation_q_tip');
echo $form->textFieldControlGroup($question, 'a_validation_sq');
echo $form->textFieldControlGroup($question, 'a_validation_sq_tip');
echo TbHtml::closeTag('fieldset');