<?php
// This is an update view so we use PUT.
echo TbHtml::well('This is not used yet.');
echo TbHtml::openTag('fieldset', []);
echo $form->dropDownListControlGroup($question, 'a_statistics_graphtype', [
    0 => gT('Bar chart'),
    1 => gT('Pie chart')
], ['empty' => gT("No graph")]);
echo TbHtml::closeTag('fieldset');