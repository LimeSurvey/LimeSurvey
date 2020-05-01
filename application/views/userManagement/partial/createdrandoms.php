
<div class="modal-header">
    <h3><?=gT('Created random users')?></h3>
</div>
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
        <div class="row ls-space margin top-35">
            <button id="exportUsers" data-users='<?=json_encode($randomUsers)?>' class="btn btn-default col-sm-3 col-xs-5 col-xs-offset-1">
            <i class="fa fa-file-excel-o"></i>&nbsp;<?=gT('Export as CSV')?>
            </button>
            <button id="exitForm" class="btn btn-default col-sm-3 col-xs-5 col-xs-offset-1">
            <i class="fa fa-times"></i>&nbsp;<?=gT('Close')?>
            </button>
        </div>
    </div>
</div>