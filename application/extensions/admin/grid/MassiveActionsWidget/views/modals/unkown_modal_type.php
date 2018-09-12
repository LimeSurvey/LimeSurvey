<!-- Modal for confirmation -->
<div id="massive-action-modal-<?php $aAction['action'];?>-<?php echo $key; ?>" class="modal fade" role="dialog" data-keepopen="<?php echo $aAction['keepopen'];?>">
    <div class="modal-dialog">
        <!-- Modal content-->
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">ERROR!</h4>
            </div>
            <div class="modal-body">
                UNKNOW MODAL VIEW TYPE IN MASSSIVE ACTIONS WIDGET !
            </div>
            <div class="modal-footer modal-footer-buttons">
                <button type="button" class="btn btn-danger" data-dismiss="modal"><span class='fa fa-ban'></span>&nbsp;<?php eT("Close"); ?></button>
            </div>
        </div>
    </div>
</div>
