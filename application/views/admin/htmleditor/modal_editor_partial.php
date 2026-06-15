<div id="htmleditor-modal" class="modal fade" role="dialog" aria-labelledby="htmleditor-modal-title" aria-modal="true" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <?php
            Yii::app()->getController()->renderPartial(
                '/layouts/partial_modals/modal_header',
                ['modalTitle' => gT('Editor'), 'modalTitleId' => 'htmleditor-modal-title']
            );
            ?>
            <div class="modal-body">
                <textarea id='htmleditor-modal-textarea' name='htmleditor-modal-textarea' rows='5'></textarea>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-cancel" data-bs-dismiss="modal"><?php eT("Cancel"); ?></button>
                <button type="button" class="btn btn-primary" id="htmleditor-modal-save">
                    <?php eT("Confirm"); ?>
                </button>
            </div>
        </div>
    </div>
</div>