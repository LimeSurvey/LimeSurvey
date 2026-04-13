<?php
/** @var array $aData */
$surveyid = $aData['surveyid'];
$gid = $aData['gid'];
$qid = $aData['qid'];
$currentMode = $aData['currentMode'];
$buttonClass = 'btn btn-outline-secondary pjax ';

$this->widget(
    'ext.ButtonWidget.ButtonWidget',
    [
        'name' => 'show_conditions',
        'text' => gT("Show conditions for this question"),
        'icon' => 'ri-information-line',
        'link' => Yii::App()->createUrl("/admin/conditions/sa/index/subaction/conditions/surveyid/$surveyid/gid/$gid/qid/$qid"),
        'htmlOptions' => [
            //'class' => isset($currentMode) && $currentMode == 'conditions' ? $buttonClass . 'active' : $buttonClass,
            'class' => 'btn btn-outline-secondary',
            'role' => 'button',
        ],
    ]
);

$this->widget(
    'ext.ButtonWidget.ButtonWidget',
    [
        'name' => 'edit_conditions',
        'text' => gT("Add and edit conditions"),
        'icon' => 'ri-git-pull-request-line_add',
        'link' => Yii::App()->createUrl("admin/conditions/sa/index/subaction/editconditionsform/surveyid/$surveyid/gid/$gid/qid/$qid"),
        'htmlOptions' => [
            //'class' => isset($currentMode) && $currentMode == 'edit' ? $buttonClass . 'active' : $buttonClass,
            'class' => 'btn btn-outline-secondary',
            'role' => 'button',
        ],
    ]
);

$this->widget(
    'ext.ButtonWidget.ButtonWidget',
    [
        'name' => 'copy_conditions',
        'text' => gT("Copy conditions"),
        'icon' => 'ri-file-copy-line',
        'link' => Yii::App()->createUrl("admin/conditions/sa/index/subaction/copyconditionsform/surveyid/$surveyid/gid/$gid/qid/$qid"),
        'htmlOptions' => [
            'class' => 'btn btn-outline-secondary',
            //'class' => isset($currentMode) && $currentMode == 'copyconditionsform' ? $buttonClass . 'active' : $buttonClass,
            'role' => 'button',
        ],
    ]
);
?>
<span data-bs-toggle="tooltip" title='<?php eT('Add multiple conditions without a page reload'); ?>'>
    <?php
    $this->widget(
        'ext.ButtonWidget.ButtonWidget',
        [
            'id' => 'quick-add-condition-button',
            'name' => 'quick-add-condition-button',
            'text' => gT("Quick-add conditions"),
            'icon' => 'ri-add-circle-fill',
            'htmlOptions' => [
                'class' => 'btn btn-outline-secondary',
                'role' => 'button',
                'data-bs-toggle' => 'modal',
                'data-bs-target' => '#quick-add-condition-modal',
            ],
        ]
    );
    ?>
</span>
