<?php
Yii::app()->getController()->renderPartial(
    '/layouts/partial_modals/modal_header',
    ['modalTitle' => gT('Created random users')]
);
?>
<div class="modal-body">
    <div class="row">
        <div class="col-12 text-center">
            <div class="check_mark">
                <div class="sa-icon sa-success animate">
                    <span class="sa-line sa-tip animateSuccessTip"></span>
                    <span class="sa-line sa-long animateSuccessLong"></span>
                    <div class="sa-placeholder"></div>
                    <div class="sa-fix"></div>
                </div>
            </div>
        </div>
    </div>
    <div class="row ls-space margin top-10 bottom-10">
        <div class="col-12">
            <ul class="list-group">
                <?php foreach ($randomUsers as $randomUser) { ?>
                    <li class="list-group-item">
                        <div class="row">
                            <div class="col-6">
                                <?= gT('Username') ?>
                            </div>
                            <div class="col-6">
                                <?= gT('Password') ?>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-6">
                                <?= $randomUser['username'] ?>
                            </div>
                            <div class="col-6">
                                <?= $randomUser['password'] ?>
                            </div>
                        </div>
                    </li>
                <?php } ?>
            </ul>
        </div>
    </div>
</div>
<div class="modal-footer">
    <button id="exitForm" class="btn btn-cancel" data-bs-dismiss="modal">
        <?= gT('Close') ?>
    </button>
    <button id="exportUsers" data-users='<?= json_encode($randomUsers) ?>' class="btn btn-primary">
        <?= gT('Export as CSV') ?>
    </button>
</div>
