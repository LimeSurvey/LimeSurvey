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
  </div>
</div>
