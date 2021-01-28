<div id="htmleditor-modal" class="modal fade" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header panel-heading">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <div id="htmleditor-modal-title" class="h3 modal-title"><?= eT('Editor') ?></div>
            </div>
            <div class="modal-body">
                <textarea id='htmleditor-modal-textarea' name='htmleditor-modal-textarea' rows='5'></textarea>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-success" id="htmleditor-modal-save"><?php eT("Confirm"); ?></button>
                <button type="button" class="btn btn-default" data-dismiss="modal"><?php eT("Cancel"); ?></button>
            </div>
        </div>
    </div>
</div>