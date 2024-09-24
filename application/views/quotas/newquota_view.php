<?php
/* @var Quota $oQuota */
/* @var QuotaLanguageSetting[] $aQuotaLanguageSettings */

// DO NOT REMOVE This is for automated testing to validate we see that page
echo viewHelper::getViewTestTag('addQuota');

?>
<div class='side-body'>
    <div class="row">
        <div class="col-12 content-right">
            <h3>
                <?php eT("New quota");?>
            </h3>
            <?php $this->renderPartial('/quotas/_form',
                array(
                    'oQuota'=>$oQuota,
                    'aQuotaLanguageSettings'=>$aQuotaLanguageSettings,
                ))?>
        </div>
    </div>
</div>
