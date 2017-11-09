<!-- Modal error if no item selected -->
<div id="error-first-select" class="modal fade" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <div class="modal-title h4"><?php eT('Error') ?></div>
            </div>
            <div class="modal-body">
                <?php eT('Please select at least one item');?>
            </div>
            <div class="modal-footer modal-footer-buttons">
                <button type="button" class="btn btn-primary" data-dismiss="modal">
                    <?php eT("Ok"); ?>
                </button>
            </div>
        </div>
    </div>
</div>
<!-- endof modal error if no item selected -->
