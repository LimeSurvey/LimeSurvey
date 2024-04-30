<?php
/* @var Quota $oQuota */
/* @var QuotaLanguageSetting[] $aQuotaLanguageSettings */

// DO NOT REMOVE This is for automated testing to validate we see that page
echo viewHelper::getViewTestTag('addQuota');

?>
<div class='side-body <?php echo getSideBodyClass(false); ?>'>
    <?php $this->widget('ext.admin.survey.PageTitle.PageTitle', array(
        'title' => gT('New quota'),
        'model' => $oSurvey,
    )); ?>
    <div class="row">
        <div class="col-12 content-right">
            <?php $this->renderPartial('/quotas/_form',
                array(
                    'oQuota'=>$oQuota,
                    'aQuotaLanguageSettings'=>$aQuotaLanguageSettings,
                ))?>
        </div>
    </div>
</div>
