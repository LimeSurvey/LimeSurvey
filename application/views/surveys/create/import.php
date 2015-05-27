<?php

echo TbHtml::fileFieldControlGroup('importFile', 'structureFile', [
    'label' => gT('Select survey structure file (*.lss, *.csv, *.txt) or survey archive (*.lsa)')
]);
echo TbHtml::checkBoxControlGroup('importConvert', false, [
    'label' => gT('Convert resource links and INSERTANS fields?')
]);
echo TbHtml::openTag('div', ['class' => 'pull-right btn-group']);
echo TbHtml::submitButton(gT('Import survey'), [
    'color' => 'primary',
    'formaction' => App()->createUrl('surveys/import'),
    'formenctype' => 'multipart/form-data'
]);
echo TbHtml::closeTag('div');
