<?php
echo TbHtml::beginFormTb(TbHtml::FORM_LAYOUT_HORIZONTAL, '', 'post', []);

$options = ['formLayout' => TbHtml::FORM_LAYOUT_HORIZONTAL];
echo TbHtml::openTag('fieldset', []);
echo TbHtml::activeTextFieldControlGroup($survey, 'admin', $options);
echo TbHtml::activeEmailFieldControlGroup($survey, 'adminemail', $options);
echo TbHtml::activeEmailFieldControlGroup($survey, 'bounce_email', $options);
echo TbHtml::activeTextFieldControlGroup($survey, 'faxto', $options);
echo TbHtml::hiddenField('id', $survey->sid);
echo TbHtml::openTag('div', ['class' => 'pull-right btn-group']);
echo TbHtml::submitButton('Save settings', [
    'color' => 'primary'
]);
echo TbHtml::closeTag('div');
echo TbHtml::closeTag('fieldset');
echo TbHtml::endForm();