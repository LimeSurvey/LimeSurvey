<?php
$options = ['formLayout' => TbHtml::FORM_LAYOUT_HORIZONTAL];
echo TbHtml::openTag('fieldset', []);
echo TbHtml::activeTextFieldControlGroup($survey, 'admin', $options);
echo TbHtml::activeEmailFieldControlGroup($survey, 'adminemail', $options);
echo TbHtml::activeEmailFieldControlGroup($survey, 'bounce_email', $options);
echo TbHtml::activeTextFieldControlGroup($survey, 'faxto', $options);
echo TbHtml::hiddenField('id', $survey->sid);
echo TbHtml::closeTag('fieldset');