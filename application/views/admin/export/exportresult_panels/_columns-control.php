<!-- Column control -->
<div class="card" id="panel-6">
  <div class="card-header ">
    <div class="">
      <?php eT("Columns");?>
    </div>
  </div>
  <div class="card-body">
    <input type='hidden' name='sid' value='<?php echo $surveyid; ?>' />
    <?php if ($SingleResponse): ?>
      <input type='hidden' name='response_id' value="<?php echo $SingleResponse;?>" />
      <?php endif; ?>
        <label for='colselect' class="col-md-12 form-label">
          <?php eT("Select columns:");?>
        </label>
        <div class="col-md-12">
          <?php
                echo CHtml::listBox('colselect[]',array_keys($aFields),$aFields,array('multiple'=>'multiple','size'=>'20','options'=>$aFieldsOptions, 'class'=>'form-control'));
            ?>
        </div>
        <div class="col-md-12 text-center">
          <strong id='columncount'>&nbsp;</strong>
        </div>
        <?php if (count($aFields) + 20 > (int) ini_get('max_input_vars')): ?>
          <div class="col-md-12">
            <div class="alert alert-warning alert-dismissible" role="alert">
                <button type="button" class="btn-close limebutton" data-bs-dismiss="alert" aria-label="Close"></button>
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
