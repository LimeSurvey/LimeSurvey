<?php

/** @var string $class */
/** @var string $dataTarget */
/** @var boolean $disabled */
/** @var string $onClick */
/** @var string $tooltip */
/** @var string $tooltipDisabled */
/** @var boolean $activePanel */
/** @var string $icon Key of SurveySidemenuWidget::SIDEBAR_ICON_PATHS */
/** @var int $iconSize */
// Set default values for optional parameters
$class = $class ?? '';
$dataTarget = $dataTarget ?? '';
$disabled = $disabled ?? false;
$onClick = $onClick ?? '';
$tooltip = $tooltip ?? '';
$tooltipDisabled = $tooltipDisabled ?? '';
$activePanel = $activePanel ?? false;
$icon = $icon ?? '';
$iconSize = $iconSize ?? 24;
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
            <span class="btn btn-g-800 btn-icon <?= $activePanel ? 'active' : ''?>">
                <svg width="<?= (int)$iconSize ?>" height="<?= (int)$iconSize ?>" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true" focusable="false">
                    <path d="<?= SurveySidemenuWidget::SIDEBAR_ICON_PATHS[$icon] ?? '' ?>"></path>
                </svg>
            </span>
        </div>
    </div>
</div>
