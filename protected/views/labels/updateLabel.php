<?php
/** @var \ls\models\LabelSet $model */
/** @var TbActiveForm $form */
$form = $this->beginWidget(TbActiveForm::class, [
    'enableAjaxValidation' => false,
    'enableClientValidation' => true,
    'layout' => TbHtml::FORM_LAYOUT_HORIZONTAL,
    'method' => 'PUT',
    'action' => ['labels/set-label'],
    'htmlOptions' => [
        'validateOnSubmit' => true
    ]
]);
$labels = array_filter($model->labels, function(\ls\models\Label $label) use ($language) {
    return $label->language == $language;
});
$label = new \ls\models\Label();
$label->lid = $model->primaryKey();
$labels[] = $label;
foreach($labels as $label) {
    echo $form->textFieldControlGroup($label, 'code');
    echo $form->numberFieldControlGroup($label, 'assessment_value');
    echo $form->textFieldControlGroup($label, 'title');
}
$this->endWidget();