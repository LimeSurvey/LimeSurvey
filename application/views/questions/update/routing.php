<?php
// This is an update view so we use PUT.
echo TbHtml::beginFormTb(TbHtml::FORM_LAYOUT_HORIZONTAL, ['questions/update', 'id' => $question->qid], 'put', []);

$options = ['formLayout' => TbHtml::FORM_LAYOUT_HORIZONTAL];
echo TbHtml::openTag('fieldset', []);
echo TbHtml::activeTextFieldControlGroup($question, 'relevance', $options);
echo TbHtml::activeTextFieldControlGroup($question, 'a_random_group', $options);
echo TbHtml::activeCheckBoxControlGroup($question, 'bool_mandatory', $options);
echo TbHtml::openTag('div', ['class' => 'pull-right btn-group']);
echo TbHtml::submitButton('Save settings', [
'color' => 'primary'
]);
echo TbHtml::closeTag('div');
echo TbHtml::closeTag('fieldset');
echo TbHtml::endForm();