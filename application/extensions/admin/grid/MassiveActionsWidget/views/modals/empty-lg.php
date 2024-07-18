<!-- Modal confirmation for <?php echo $aAction['action'];?> -->
<div id="massive-actions-modal-<?php echo $this->gridid;?>-<?php echo $aAction['action'];?>-<?php echo $key; ?>" class="modal fade" role="dialog" tabindex="-1" aria-modal="true">
    <div class="modal-dialog modal-lg">
        <!-- Modal content-->
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title" role="heading" aria-level="2"><?php echo $aAction['sModalTitle']; ?></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body">
                <?php echo $aAction['htmlModalBody']; ?>
            </div>

            <div class="modal-footer modal-footer-buttons">
                <?php foreach ($aAction['htmlFooterButtons'] as $buttonHtml): ?>
                    <?php echo $buttonHtml; ?>
                <?php endforeach;?>
            </div>
        </div>
    </div>
</div>
