<div class="modal fade" tabindex="-1" role="dialog" aria-modal="true" aria-labelledby="quickadd-title quickadd-title-dialogsr" id='quickadd'>
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h2 class="modal-title h5" id="quickadd-title"><?php eT('Enter your labels:') ?></h2>
        <?php // Only read as part of the dialog name ("…, Dialog"), see modal_header.php ?>
        <span class="visually-hidden" id="quickadd-title-dialogsr" aria-hidden="true"><?php eT('Dialog') ?></span>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="<?php eT('Close') ?>"></button>
      </div>
      <div class="modal-body row">
          <p id="quickadd-help"><?php eT('Enter one label per line. You can provide a code by separating code and label text with a semicolon or tab. For multilingual surveys you add the translation(s) on the same line separated with a semicolon or tab.') ?></p>
          <?php // Short name (the modal title), the instructions above are the description ?>
          <textarea id='quickaddarea' name='quickaddarea' aria-labelledby="quickadd-title" aria-describedby="quickadd-help" rows='10' cols='100' style='width:570px;'></textarea>
      </div>
      <div class="modal-footer">
          <button id='btnqacancel' type='button' class="btn btn-cancel"  data-bs-dismiss="modal"><?php eT('Cancel') ?></button>
          <button id='btnqareplace' type='button' class="btn btn-outline-secondary"><?php eT('Replace') ?></button>
          <button role="button" id='btnqainsert' type='button' class="btn btn-primary">
            <?php eT('Add') ?>
          </button>
      </div>
    </div><!-- /.modal-content -->
  </div><!-- /.modal-dialog -->
</div><!-- /.modal -->
