<?php

use ls\components\SurveyRenderer;

echo TbHtml::beginFormTb();
/** @var SurveyRenderer $renderer */
echo $renderer->render();

echo TbHtml::submitButton('Next');
echo TbHtml::endForm();