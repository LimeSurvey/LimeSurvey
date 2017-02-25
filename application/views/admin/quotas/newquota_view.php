<?php
/* @var $this AdminController */
/* @var Survey $oSurvey */
/* @var string $lang */
/* @var string[] $langs */
/* @var Quota $oQuota */
/* @var array $aTabContents*/
?>
<div class='side-body <?php echo getSideBodyClass(false); ?>'>
    <div class='col-lg-8'>
    <div class="row">
        <div class="col-lg-12 content-right">
            <?php $this->renderPartial('/admin/survey/breadcrumb', array('oSurvey'=>$oSurvey, 'active'=> gT("New quota"))); ?>
            <h3>
                <?php eT("New quota");?>
            </h3>
            <?php $this->renderPartial('/admin/quotas/_form',
                array(
                    'oSurvey'=>$oSurvey,
                    'oQuota'=>$oQuota,
                    'lang'=>$lang,
                    'langs'=>$langs,
                    'aTabContents'=>$aTabContents,
                ))?>
        </div>
    </div>
</div>
