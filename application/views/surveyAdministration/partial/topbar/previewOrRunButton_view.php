<!-- test/execute survey -->
<?php
/** @var Survey $survey */
/** @var array $surveyLanguages */
/** @var int $id id for the button (optional) */
/** @var int $name name for the button (optional) */

//todo: this view comes from old TobarWidget and should be use in new topbar

$notActive = $survey->active == 'N';
// An active survey past its expiry date can no longer be run/previewed via this button
// (see SurveyIndex::index(), which blocks it for active surveys regardless of newtest=Y).
$isExpired = !$notActive && $survey->getIsDateExpired();
if (!isset($id)) {
    $id = $notActive ? 'ls-preview-button' : 'ls-run-button';
}
if (!isset($name)) {
    $name = $notActive ? 'ls-preview-button' : 'ls-run-button';
}

$languagesDropDownItems = '';

if (!$isExpired && count($surveyLanguages) > 1) {
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

if ($isExpired) {
    $expiredTooltip = sprintf(
        gT('This survey expired on %s and can no longer be run.'),
        convertToGlobalSettingFormat(dateShift($survey->expires, "Y-m-d H:i:s"))
    );
}
?>

<?php if ($isExpired) : ?>
<span class="btntooltip" style="display: inline-block" data-bs-toggle="tooltip" data-bs-placement="bottom"
      title="<?php echo CHtml::encode($expiredTooltip); ?>">
<?php endif; ?>
<?php
$this->widget('ext.ButtonWidget.ButtonWidget', [
    'name' => $name,
    'id' => $id,
    'text' => $isExpired ? gT('Survey expired') : ($notActive ? gT('Preview survey') : gT('Run survey')),
    'icon' => $isExpired ? 'ri-skip-forward-fill' : ($notActive ? 'ri-eye-fill' : 'ri-play-fill'),
    'isDropDown' => !$isExpired && count($surveyLanguages) > 1,
    'dropDownContent' => $languagesDropDownItems,
    'link' => Yii::App()->createUrl(
        "survey/index",
        array('sid' => $survey->sid, 'newtest' => "Y", 'lang' => $survey->language)
    ),
    'htmlOptions' => $isExpired ? [
        'class' => 'btn btn-secondary',
        'role' => 'button',
        'disabled' => 'disabled',
    ] : [
        'class' => 'btn btn-secondary btntooltip',
        'role' => 'button',
        'target' => '_blank',
    ],
]); ?>
<?php if ($isExpired) : ?>
</span>
<?php endif; ?>
