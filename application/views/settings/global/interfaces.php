<?php
/** @var TbActiveForm $form */
/** @var \ls\models\forms\Settings $settings */
echo TbHtml::openTag('fieldset', []);

echo $form->dropDownListControlGroup($settings, 'RPCInterface', $settings->getRpcInterfaceOptions());
echo $form->customControlGroup(\CHtml::tag('code', [], $settings->RPCurl), $settings, 'RPCurl');
echo $form->checkBoxControlGroup($settings, 'rpc_publish_api');
echo TbHtml::closeTag('fieldset');
?>