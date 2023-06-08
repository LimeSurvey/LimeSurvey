<div class="modal fade" tabindex="-1" role="dialog" id='quickadd'>
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title"><?php eT('Enter your labels:') ?></h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body row">
          <p><?php eT('Enter one label per line. You can provide a code by separating code and label text with a semicolon or tab. For multilingual surveys you add the translation(s) on the same line separated with a semicolon or tab.') ?></p>
          <textarea id='quickaddarea' name='quickaddarea' class='tipme' title='<?php eT('Enter one label per line. You can provide a code by separating code and label text with a semicolon or tab. For multilingual surveys you add the translation(s) on the same line separated with a semicolon or tab.') ?>' rows='10' cols='100' style='width:570px;'></textarea>
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
