<?php
/* @var Quota $oQuota */
/* @var QuotaLanguageSetting[] $aQuotaLanguageSettings */
?>

<div class='side-body'>
    <div class="row">
        <div class="col-12 content-right">
            <h3>
                <?php eT("Edit quota");?>
            </h3>
            <?php $this->renderPartial('/quotas/_form',
                array(
                    'oQuota'=>$oQuota,
                    'aQuotaLanguageSettings'=>$aQuotaLanguageSettings,
                ))?>
        </div>
    </div>
</div>
