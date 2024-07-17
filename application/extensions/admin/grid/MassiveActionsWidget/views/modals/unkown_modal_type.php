<!-- Modal for confirmation -->
<div id="massive-action-modal-<?php $aAction['action'];?>-<?php echo $key; ?>" class="modal fade" data-keepopen="<?php echo $aAction['keepopen'];?>" tabindex="-1" aria-modal="true"  role="dialog">
    <div class="modal-dialog">
        <!-- Modal content-->
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" role="heading" aria-level="2">ERROR!</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                UNKNOW MODAL VIEW TYPE IN MASSSIVE ACTIONS WIDGET !
            </div>
            <div class="modal-footer modal-footer-buttons">
                <button type="button" class="btn btn-danger" data-bs-dismiss="modal">
                    <span class='ri-forbid-2-line'></span>
                    &nbsp;
                    <?php eT("Close"); ?>
                </button>
            </div>
        </div>
    </div>
</div>
