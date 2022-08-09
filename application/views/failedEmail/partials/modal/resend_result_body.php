<?php /**
 * @var int $successfullEmailCount how many emails succeeded
 * @var int $failedEmailCount how many emails failed
 */ ?>
<div class="container-center">
    <div class="row">
        <div id="failedemail-action-modal--resendresult" class="col-sm-12">
            <p><?= sprintf(gT('Sucessfull emails: %s'), $successfullEmailCount) ?></p>
            <p><?= sprintf(gT('Failed emails: %s'), $failedEmailCount) ?></p>
        </div>
    </div>
</div>