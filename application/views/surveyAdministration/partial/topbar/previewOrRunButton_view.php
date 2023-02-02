<!-- test/execute survey -->
<?php
/** @var Survey $survey */
/** @var array $surveyLanguages */
/** @var int $id id for the button (optional) */
/** @var int $name name for the button (optional) */

//todo: this view comes from old TobarWidget and should be use in new topbar

$notActive = $survey->active=='N';
if(!isset($id)) {
    $id = $notActive ? 'ls-preview-button' : 'ls-run-button';
}
if(!isset($name)) {
    $name = $notActive ? 'ls-preview-button' : 'ls-run-button';
}

$languagesDropDownItems = '';

if (count($surveyLanguages) > 1) {
    $languagesDropDownItems = '<ul class="dropdown-menu" style="min-width : 252px;">';
    $languagesDropDownItems .= $this->renderPartial(
        '/surveyAdministration/partial/topbar/languagesDropdownItems',
        [
            'surveyLanguages' => $surveyLanguages,
            'type' => 'survey',
            'sid' => $survey->sid
        ],
        true
    );
    $languagesDropDownItems .= '</ul>';
}
?>

<?php
$this->widget('ext.ButtonWidget.ButtonWidget', [
    'name' => $name,
    'id' => $id,
    'text' => $notActive ? gT('Preview survey') : gT('Run survey'),
    'icon' => $notActive ? 'ri-eye-fill' : 'ri-play-fill',
    'isDropDown' => count($surveyLanguages) > 1,
    'dropDownContent' => $languagesDropDownItems,
    'link' => Yii::App()->createUrl(
        "survey/index",
        array('sid' => $survey->sid, 'newtest' => "Y", 'lang' => $survey->language)
    ),
    'htmlOptions' => [
        'class' => 'btn btn-secondary btntooltip',
        'role' => 'button',
        'accesskey' => 'd',
        'target' => '_blank',
    ],
]); ?>
