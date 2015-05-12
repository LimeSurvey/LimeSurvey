<div class="form-horizontal"><?php

$options = ['formLayout' => TbHtml::FORM_LAYOUT_HORIZONTAL];
echo TbHtml::openTag('fieldset', []);
echo TbHtml::activeTextFieldControlGroup($group, 'grelevance', $options);
echo TbHtml::activeTextFieldControlGroup($group, 'randomization_group', $options);
echo TbHtml::closeTag('fieldset');
?></div>