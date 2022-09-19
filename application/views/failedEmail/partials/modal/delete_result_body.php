<?php /**
 * @var int $deletedCount how many emails have been deleted
 */ ?>
<div class="container-center">
    <div class="row">
        <div id="failedemail-action-modal--deleteresult" class="col-sm-12">
            <p><?= sprintf(gT('Sucessfully deleted email notifications: %s'), $deletedCount) ?></p>
        </div>
    </div>
</div>