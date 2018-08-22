<?php
/**
* This modal display a form to upload ressources
* It's called from the accordion "Ressources"
* It has been move from inside the settings because form nesting is not valid in HTML, and can create problems.
*/
?>

  <div class="modal fade" id="importRessourcesModal" tabindex="-1" role="dialog" aria-labelledby="importRessourcesModalLabel">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
          <h4 class="modal-title" id="importRessourcesModalLabel">
              <?php  eT("Import resources ZIP archive"); ?>
          </h4>
        </div>
        <?php echo CHtml::form(array('admin/survey/sa/importsurveyresources'), 'post', array('id'=>'importsurveyresources', 'name'=>'importsurveyresources', 'class'=>'form30', 'enctype'=>'multipart/form-data', 'onsubmit'=>'return window.LS.validatefilename(this,"'. gT('Please select a file to import!', 'js').'");')); ?>
          <div class="modal-body">
            <input type='hidden' name='surveyid' value='<?php echo $surveyid; ?>' />
            <input type='hidden' name='action' value='importsurveyresources' />
            <label for='the_file'>
              <?php  eT("Select ZIP file:"); ?>
            </label>
            <input id='the_file' name='the_file' type='file' />
          </div>

          <div class="modal-footer">
            <button type="button" class="btn btn-danger" data-dismiss="modal">
              <?php  eT("Close");?>
            </button>
            <input type='button' class="btn btn-default" value='<?php  eT("Import resources ZIP archive"); ?>' <?php echo $ZIPimportAction; ?> />
          </div>
        </form>
    </div>
  </div>
</div>
