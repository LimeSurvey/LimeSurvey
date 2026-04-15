<?php
/* @var $this SurveysGroupsController */

/* @var $model SurveysGroups */

// DO NOT REMOVE This is for automated testing to validate we see that page
echo viewHelper::getViewTestTag('createSurveyGroups');

?>
<div class="tab-content flex-lg-shrink-1 ps-4">
    <div class="col-12">
        <?php $this->renderPartial('./surveysgroups/_form', $_data_); ?>
    </div>
</div>
