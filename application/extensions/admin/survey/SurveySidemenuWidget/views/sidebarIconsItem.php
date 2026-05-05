<?php
/** @var string $class */
/** @var string $dataTarget */
/** @var boolean $disabled */
/** @var string $onClick */
/** @var string $tooltip */
/** @var string $tooltipDisabled */
/** @var boolean $activePanel */
/** @var string $iconClass */
// Set default values for optional parameters
$class = $class ?? '';
$dataTarget = $dataTarget ?? '';
$disabled = $disabled ?? false;
$onClick = $onClick ?? '';
$tooltip = $tooltip ?? '';
$tooltipDisabled = $tooltipDisabled ?? '';
$activePanel = $activePanel ?? false;
$iconClass = $iconClass ?? '';
if ($disabled) {
    $onClick = '';
    $tooltip = $tooltipDisabled;
    $class .= ' disabled';
}
?>

<div class="sidebar-icons-item">
    <div class="sidebar-icon <?= $class ?>" <?= $dataTarget ? 'data-target="' . $dataTarget . '"' : '' ?> <?= $onClick ? 'onclick="' . $onClick . '"' : '' ?>>
        <div data-bs-toggle="tooltip"
             title="<?= $tooltip ?>"
             data-bs-offset="0, 20"
             data-bs-placement="right">
            <i class="<?= $iconClass ?> btn btn-g-800 btn-icon <?= $activePanel ? 'active' : ''?>"></i>
        </div>
    </div>
</div>
