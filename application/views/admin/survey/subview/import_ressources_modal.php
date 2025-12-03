<?php
/**
* This modal display a form to upload resources
* It's called from the accordion "Resources"
* It has been move from inside the settings because form nesting is not valid in HTML, and can create problems.
*/
?>

  <div class="modal fade" id="importRessourcesModal" tabindex="-1" role="dialog" aria-labelledby="importRessourcesModalLabel">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5  aria-level="2" class="modal-title" id="importRessourcesModalLabel"><?php eT("Import resources ZIP archive"); ?></h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <?php echo CHtml::form(array('surveyAdministration/importsurveyresources'), 'post', array('id'=>'importsurveyresources', 'name'=>'importsurveyresources', 'class'=>'form30', 'enctype'=>'multipart/form-data', 'onsubmit'=>'return window.LS.validatefilename(this,"'. gT('Please select a file to import!', 'js').'");')); ?>
          <div class="modal-body">
            <input type='hidden' name='surveyid' value='<?php echo $surveyid; ?>' />
            <input type='hidden' name='action' value='importsurveyresources' />
            <div class="mb-3">
                <label class="form-label" for='the_file'><?php  eT("Select ZIP file:"); ?></label>
                <input id='the_file' class="form-control" name='the_file' type='file' />
            </div>
          </div>

          <div class="modal-footer">
            <button type="button" class="btn btn-cancel" data-bs-dismiss="modal"><?php  eT("Cancel");?></button>
            <!--TODO: Im not sure what this button does -->
            <input type='button' class="btn btn-primary" value='<?php  eT("Import"); ?>' <?php echo $ZIPimportAction; ?> />
          </div>
        </form>
    </div>
  </div>
</div>
