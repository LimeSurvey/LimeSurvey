<!-- Modal error if no item selected -->
<div id="error-first-select<?php echo $this->gridid; ?>" class="modal fade" role="dialog" tabindex="-1" aria-modal="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" role="heading" aria-level="2"><?php eT('Error') ?></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <?php eT('Please select at least one item');?>
            </div>
            <div class="modal-footer modal-footer-buttons">
                <button type="button" class="btn btn-primary" data-bs-dismiss="modal">
                    <?php eT("Ok"); ?>
                </button>
            </div>
        </div>
    </div>
</div>
<!-- endof modal error if no item selected -->
