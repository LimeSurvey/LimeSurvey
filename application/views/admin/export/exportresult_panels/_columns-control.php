<!-- Column control -->
<div class="panel panel-primary" id="panel-6">
  <div class="panel-heading">
    <div class="panel-title h4">
      <?php eT("Columns");?>
    </div>
  </div>
  <div class="panel-body">
    <input type='hidden' name='sid' value='<?php echo $surveyid; ?>' />
    <?php if ($SingleResponse): ?>
      <input type='hidden' name='response_id' value="<?php echo $SingleResponse;?>" />
      <?php endif; ?>
        <label for='colselect' class="col-sm-12 control-label">
          <?php eT("Select columns:");?>
        </label>
        <div class="col-sm-12">
          <?php
                echo CHtml::listBox('colselect[]',array_keys($aFields),$aFields,array('multiple'=>'multiple','size'=>'20','options'=>$aFieldsOptions, 'class'=>'form-control'));
            ?>
        </div>
        <div class="col-sm-12 text-center">
          <strong id='columncount'>&nbsp;</strong>
        </div>
        <?php if (count($aFields) + 20 > (int) ini_get('max_input_vars')): ?>
          <div class="col-sm-12">
            <div class="alert alert-warning alert-dismissible" role="alert">
                <button type="button" class="close limebutton" data-dismiss="alert" aria-label="Close"><span>X</span>
                </button>
                <?php
                eT("The number of fields in your survey exceeds the maximum numbers of fields you can export."); ?>
                <br/>
                <?php
                printf(gT("If data is missing in the exported file, please contact your system administrator to raise the setting max_input_vars to at least %s."), count($aFields)+20); ?>
            </div>
         </div>
        <?php endif; ?>
  </div>
</div>
