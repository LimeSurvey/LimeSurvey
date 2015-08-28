<div class="row">
    <div class="col-md-12">
        <?php
        /** @var TbActiveForm $form */
        $form = $this->beginWidget(TbActiveForm::class, [
            'enableAjaxValidation' => false,
            'enableClientValidation' => true,
            'layout' => TbHtml::FORM_LAYOUT_VERTICAL,
            'action' => ['surveys/update', 'id' => $survey->sid],
            'method' => 'put',
            'htmlOptions' => [
                'validateOnSubmit' => true
            ]
        ]);
        $this->widget('TbTabs', [
            'tabs' => [
                [
                    'label' => gT('Overview'),
                    'content' => $this->renderPartial('update/properties', ['survey' => $survey], true),
                    'active' => true
                ], [
                    'label' => gT('Texts'),
                    'content' => $this->renderPartial('update/texts', ['survey' => $survey, 'form' => $form], true),
                ], [
                    'label' => gT('General'),
                    'content' => $this->renderPartial('update/general', ['survey' => $survey, 'form' => $form], true),
                ], [
                    'label' => gT('Languages'),
                    'content' => $this->renderPartial('update/languages', ['survey' => $survey, 'form' => $form], true),
                ], [
                    'label' => gT('Presentation & Navigation'),
                    'content' => $this->renderPartial('update/presentation', ['survey' => $survey, 'form' => $form], true),

                ], [
                    'label' => gT('Notification & data management'),
                    'content' => $this->renderPartial('update/data', ['survey' => $survey, 'form' => $form], true),

                ], [
                    'label' => gT('Tokens'),
                    'content' => $this->renderPartial('update/tokens', ['survey' => $survey, 'form' => $form], true),
                    'visible' => $survey->bool_usetokens

                ], [
                    'label' => gT('Panel integration'),
                    'content' => "@todo",

                ], [
                    'label' => gT('Resources'),
                    'content' => "@todo",

                ], [
                    'label' => 'Optional features',
                    'content' => $this->renderPartial('update/features', ['survey' => $survey, 'form' => $form], true),
//                    'active' => true
                ], [
                    'label' => 'Access control',
                    'content' => $this->renderPartial('update/access', ['survey' => $survey, 'form' => $form], true),

                ], [
                    'label' => 'File management',
                    'content' => $this->renderPartial('update/files', ['survey' => $survey], true),

                ]

            ]
        ]);

        echo TbHtml::openTag('div', ['class' => 'pull-right btn-group']);
        echo TbHtml::submitButton('Save settings', [
            'color' => 'primary'
        ]);
        echo TbHtml::closeTag('div');
        $this->endWidget();

        ?>
    </div>


</div>