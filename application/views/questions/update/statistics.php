<?php
// This is an update view so we use PUT.
echo TbHtml::well('This is not used yet.');
$options = ['formLayout' => TbHtml::FORM_LAYOUT_HORIZONTAL];
echo TbHtml::openTag('fieldset', []);
echo TbHtml::activeDropDownListControlGroup($question, 'a_statistics_graphtype', [
    0 => gT('Bar chart'),
    1 => gT('Pie chart')
], array_merge($options, ['empty' => gT("No graph")]));
echo TbHtml::closeTag('fieldset');