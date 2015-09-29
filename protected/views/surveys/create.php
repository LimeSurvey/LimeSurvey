<div class="row">
    <div class="col-md-4 col-md-offset-4">
<?php
/** @var Controller $this */
use ls\models\Survey;

/** @var TbActiveForm $form */
/** @var Survey $survey */
$form = $this->beginWidget(TbActiveForm::class, [
    'enableAjaxValidation' => false,
    'enableClientValidation' => true,
    'layout' => TbHtml::FORM_LAYOUT_VERTICAL,
    'action' => ['surveys/create'],
    'method' => 'post',
    'htmlOptions' => [
        'validateOnSubmit' => true
    ]
]);

$this->widget('TbTabs', [
    'tabs' => [
        [
            'label' => gT('Create'),
            'content' => $this->renderPartial('create/create', ['survey' => $survey, 'languageSetting' => $languageSetting, 'form' => $form], true),
            'active' => true
        ], [
            'label' => gT('Import'),
            'content' => $this->renderPartial('create/import', ['survey' => $survey, 'languageSetting' => $languageSetting, 'form' => $form], true),
        ],
        // Move copy to a survey action.
//        [
//            'label' => gT('Copy'),
//            'content' => $this->renderPartial('create/copy', ['survey' => $survey, 'languageSetting' => $languageSetting, 'form' => $form], true),
//        ],
    ]
]);

$this->endWidget();

?>
        </div>
    </div>