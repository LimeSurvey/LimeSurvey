<div class="form-horizontal"><?php
$options = ['formLayout' => TbHtml::FORM_LAYOUT_HORIZONTAL];
echo TbHtml::openTag('fieldset', []);
echo TbHtml::activeTextFieldControlGroup($question, 'preg', $options);
echo TbHtml::activeTextFieldControlGroup($question, 'a_validation_q', $options);
echo TbHtml::activeTextFieldControlGroup($question, 'a_validation_q_tip', $options);
echo TbHtml::activeTextFieldControlGroup($question, 'a_validation_sq', $options);
echo TbHtml::activeTextFieldControlGroup($question, 'a_validation_sq_tip', $options);
echo TbHtml::closeTag('fieldset');
?></div>