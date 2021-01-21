<?php

/** @var $this SurveyAdministrationController */
/** @var $sideMenuOpen boolean */

?>

<div id="vue-side-body-app" class='side-body <?php echo getSideBodyClass($sideMenuOpen); ?>'>
    <?php // OLD echo $content; ?>
    <?php  $this->renderPartial("surveySummary_view", $this->aData); ?>
</div>
