
<?php
Yii::app()->getController()->renderPartial(
    '/layouts/partial_modals/modal_header',
    ['modalTitle' => 'Created random users']
);
?>
<div class="modal-body">
    <div class="container-center">
        <div class="row">
            <div class="col-xs-12 text-center">
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
            <ul class="list-group">
            <?php foreach($randomUsers as $randomUser) {?>
                <li class="list-group-item">
                    <div class="container-center">
                        <div class="row">
                            <div class="col-xs-6">
                                <?=gT('Username')?>   
                            </div>
                            <div class="col-xs-6">
                                <?=gT('Password')?>   
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-xs-6">
                                <?=$randomUser['username']?>
                            </div>
                            <div class="col-xs-6">
                                <?=$randomUser['password']?>
                            </div>
                        </div>
                    </div>
                </li>
            <?php } ?>
            </ul>
        </div>
    </div>
</div>
<div class="modal-footer">
    <button id="exitForm" class="btn btn-default">
        <?=gT('Close')?>
        </button>
    <button id="exportUsers" data-users='<?=json_encode($randomUsers)?>' class="btn btn-success">
        &nbsp;<?=gT('Export as CSV')?>
    </button>
</div>