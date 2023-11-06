<?php
/* @var $this SurveysGroupsController */
/* @var $model SurveysGroups */

// DO NOT REMOVE This is for automated testing to validate we see that page
echo viewHelper::getViewTestTag('createSurveyGroups');

?>

<div class="col-lg-12 list-surveys">
    <?php $this->renderPartial('./surveysgroups/_form', $_data_); ?>
</div>
