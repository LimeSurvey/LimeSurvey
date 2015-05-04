<?php
// This is an update view so we use PUT.
echo TbHtml::well('This is not used yet.');
echo TbHtml::beginFormTb(TbHtml::FORM_LAYOUT_HORIZONTAL, ['questions/update', 'id' => $question->qid], 'put', []);

$options = ['formLayout' => TbHtml::FORM_LAYOUT_HORIZONTAL];
echo TbHtml::openTag('fieldset', []);
echo TbHtml::activeDropDownListControlGroup($question, 'a_statistics_graphtype', [
    0 => gT('Bar chart'),
    1 => gT('Pie chart')
], array_merge($options, ['empty' => gT("No graph")]));
echo TbHtml::openTag('div', ['class' => 'pull-right btn-group']);
echo TbHtml::submitButton('Save settings', [
    'color' => 'primary'
]);
echo TbHtml::closeTag('div');
echo TbHtml::closeTag('fieldset');
echo TbHtml::endForm();