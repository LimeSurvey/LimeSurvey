<?php
    $license = file_get_contents(Yii::getPathOfAlias('application') . '/../docs/license.txt');
    echo CHtml::form(array("installer/license"), 'post', ['class' => 'form-horizontal']);
    echo CHtml::tag('div', [
        'style' => 'white-space: pre; max-height: 400px; overflow: auto; margin-bottom: 15px;',
    ], htmlentities($license));

    echo CHtml::openTag('div', ['class' => 'btn-group pull-right']);

    echo TbHtml::linkButton(gT('Previous'), ['url' => ['installer/index']]);
    echo TbHtml::submitButton(gT('I accept'), ['color' => TbHtml::BUTTON_COLOR_PRIMARY]);
    
    echo TbHtml::closeTag('div');
    echo TbHtml::endForm();


