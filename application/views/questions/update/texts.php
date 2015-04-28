<?php
// This is an update view so we use PUT.
echo TbHtml::beginFormTb(TbHtml::FORM_LAYOUT_HORIZONTAL, ['questions/update', 'id' => $question->qid], 'put', []);

$options = ['formLayout' => TbHtml::FORM_LAYOUT_HORIZONTAL];
echo TbHtml::openTag('fieldset', []);
echo TbHtml::activeTextFieldControlGroup($question, 'title', $options);
echo TbHtml::activeTextAreaControlGroup($question, 'question', array_merge($options, ['class' => 'html']));
echo TbHtml::activeTextAreaControlGroup($question, 'help', array_merge($options, ['class' => 'html']));
echo TbHtml::openTag('div', ['class' => 'pull-right btn-group']);
echo TbHtml::submitButton('Save settings', [
'color' => 'primary'
]);
echo TbHtml::closeTag('div');
echo TbHtml::closeTag('fieldset');
echo TbHtml::endForm();