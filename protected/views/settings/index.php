<div class="row">
    <?php
//    echo TbHtml::tag('h1', [], "Group {$group->displayLabel}");
    ?>
    <div class="col-md-12">
        <?php
        // This is an update view so we use PUT.
        /** @var TbActiveForm $form */
        $form = $this->beginWidget(TbActiveForm::class, [
            'enableAjaxValidation' => false,
            'enableClientValidation' => true,
            'layout' => TbHtml::FORM_LAYOUT_HORIZONTAL,
            'action' => ['settings/update'],
            'labelWidthClass' => 'col-sm-4',
            'controlWidthClass' => 'col-sm-8',
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
                    'label' => gT('Overview & update'),
                    'content' => $this->renderPartial('global/overview', [], true),
                    'active' => true
                ], [
                    'label' => gT('General'),
                    'content' => $this->renderPartial('global/general', ['form' => $form, 'settings' => $settings], true),
                ], [
                    'label' => gT('Email settings'),
                    'content' => $this->renderPartial('global/email', ['form' => $form, 'settings' => $settings], true),
                ], [
                    'label' => gT('Bounce settings'),
                    'content' => $this->renderPartial('global/bounce', ['form' => $form, 'settings' => $settings], true),
                ], [
                    'label' => gT('Security'),
                    'content' => $this->renderPartial('global/security', ['form' => $form, 'settings' => $settings], true),
                ], [
                    'label' => gT('Presentation'),
                    'content' => $this->renderPartial('global/presentation', ['form' => $form, 'settings' => $settings], true),
                ], [
                    'label' => gT('Language'),
                    'content' => $this->renderPartial('global/language', ['form' => $form, 'settings' => $settings], true),
                ], [
                    'label' => gT('Interfaces'),
                    'content' => $this->renderPartial('global/interfaces', ['form' => $form, 'settings' => $settings], true),
                ],
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