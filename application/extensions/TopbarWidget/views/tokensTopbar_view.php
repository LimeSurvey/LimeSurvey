<?php
/**
 * Tokens topbar layout
 *
 * This is basically the baseTopbar + the "tokenBounceModal"
 * 
 */
?>

<?php
    /**
     * Include the base topbar
     */
    $this->render('baseTopbar_view', get_defined_vars());
?>

<?php if (!empty($leftSideContent)): // Only add the modal if the left side is rendered ?>
    <!-- Token Bounce -->
    <div id="tokenBounceModal" class="modal fade" tabindex="-1" role="dialog">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title"><?php eT('Bounce processing');?></h4>
                </div>
                <div class="modal-body">
                    <!-- Here will come the result of the ajax request -->
                    <p class='modal-body-text'>

                    </p>

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger" data-dismiss="modal"><?php eT("Cancel");?></button>
                </div>
            </div><!-- /.modal-content -->
        </div><!-- /.modal-dialog -->
    </div><!-- /.modal -->
<?php endif; ?>