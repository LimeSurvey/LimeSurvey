<?php
// This is an update view so we use PUT.
echo TbHtml::beginFormTb(TbHtml::FORM_LAYOUT_HORIZONTAL, ['questions/update', 'id' => $question->qid], 'put', []);
//
$options = ['formLayout' => TbHtml::FORM_LAYOUT_HORIZONTAL];
echo TbHtml::openTag('fieldset', []);
echo TbHtml::activeTextFieldControlGroup($question, 'preg', $options);
echo TbHtml::activeTextFieldControlGroup($question, 'a_validation_q', $options);
echo TbHtml::activeTextFieldControlGroup($question, 'a_validation_q_tip', $options);
echo TbHtml::activeTextFieldControlGroup($question, 'a_validation_sq', $options);
echo TbHtml::activeTextFieldControlGroup($question, 'a_validation_sq_tip', $options);
echo TbHtml::openTag('div', ['class' => 'pull-right btn-group']);
echo TbHtml::submitButton('Save settings', [
'color' => 'primary'
]);
echo TbHtml::closeTag('div');
echo TbHtml::closeTag('fieldset');
echo TbHtml::endForm();