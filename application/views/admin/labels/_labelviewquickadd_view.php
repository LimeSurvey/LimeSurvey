<div class="modal fade" tabindex="-1" role="dialog" id='quickadd'>
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span >&times;</span></button>
        <h4 class="modal-title"><?php eT('Enter your labels:') ?></h4>
      </div>
      <div class="modal-body">
          <textarea id='quickaddarea' name='quickaddarea' class='tipme' title='<?php eT('Enter one label per line. You can provide a code by separating code and label text with a semicolon or tab. For multilingual surveys you add the translation(s) on the same line separated with a semicolon or tab.') ?>' rows='20' cols='100' style='width:570px;'></textarea>
      </div>
      <div class="modal-footer button-list">
        <button id='btnqareplace' type='button' class="btn btn-default"><?php eT('Replace') ?></button>
        <button id='btnqainsert' type='button' class="btn btn-default"><?php eT('Add') ?></button>
        <button id='btnqacancel' type='button' class="btn btn-warning"  data-dismiss="modal"><?php eT('Cancel') ?></button>
      </div>
    </div><!-- /.modal-content -->
  </div><!-- /.modal-dialog -->
</div><!-- /.modal -->
