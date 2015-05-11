<div class="form-horizontal"><?php

$options = ['formLayout' => TbHtml::FORM_LAYOUT_HORIZONTAL];
echo TbHtml::openTag('fieldset', []);
echo TbHtml::activeTextFieldControlGroup($question, 'title', $options);
echo TbHtml::activeTextFieldControlGroup($question, 'relevance', $options);
echo TbHtml::activeTextFieldControlGroup($question, 'a_random_group', $options);
echo TbHtml::activeCheckBoxControlGroup($question, 'bool_mandatory', $options);
echo TbHtml::closeTag('fieldset');
?></div>