<?php
echo TbHtml::openTag('fieldset', []);
echo $form->textFieldControlGroup($survey, 'admin');
echo $form->emailFieldControlGroup($survey, 'adminemail');
echo $form->emailFieldControlGroup($survey, 'bounce_email');
echo $form->textFieldControlGroup($survey, 'faxto');
echo TbHtml::closeTag('fieldset');