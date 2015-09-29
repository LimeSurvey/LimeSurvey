<div class="row">
    <?php
    //    echo TbHtml::tag('h1', [], "Group {$group->displayLabel}");
    ?>
    <div class="col-md-offset-4 col-md-4">
        <?php
        // This is an update view so we use PUT.
        /** @var TbActiveForm $form */
        $form = $this->beginWidget(TbActiveForm::class, [
            'enableAjaxValidation' => false,
            'enableClientValidation' => true,
            'layout' => TbHtml::FORM_LAYOUT_HORIZONTAL,
            'method' => 'put',
            'htmlOptions' => [
                "autocomplete" => "off",
                'validateOnSubmit' => true
            ]
        ]);
        // We specify layout per tab.
        $this->widget('TbTabs', [
            'tabs' => [
                [
                    'label' => gT('ls\models\User control'),
                    'content' => $this->renderPartial('settings/user', ['form' => $form, 'settings' => $settings], true),
                    'active' => true
                ], [
                    'label' => gT('Blacklist control'),
                    'content' => $this->renderPartial('settings/blacklist', ['form' => $form, 'settings' => $settings], true),
                ]
            ]
        ]);
        echo TbHtml::openTag('div', ['class' => 'pull-right btn-group']);
        echo TbHtml::submitButton('Save settings', [
            'color' => 'primary',
//            'class' => 'ajaxSubmit'
        ]);
        echo TbHtml::closeTag('div');
        $this->endWidget();
        ?>
    </div>


</div>