<?php
/** @var Survey $survey */

echo TbHtml::pageHeader($survey->getLocalizedTitle(), $survey->getLocalizedDescription());
echo TbHtml::tag('div', ['class'=>'welcome'], $survey->getLocalizedWelcomeText());
echo TbHtml::beginFormTb(TbHtml::FORM_LAYOUT_HORIZONTAL);
echo TbHtml::submitButton('Start survey');
echo TbHtml::endForm();
//$survey->attributes);