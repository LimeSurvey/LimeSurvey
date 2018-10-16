<!-- Format -->
<div class="panel panel-primary" id="panel-1">
  <div class="panel-heading">
    <div class="panel-title h4">
      <?php eT("Format");?>
    </div>
  </div>
  <div class="panel-body">
    <div class="form-group">
      <!-- Format -->
      <label for='export_format' class="col-sm-6 control-label">
        <?php eT("Export format:"); ?>
      </label>
      <div class="col-sm-12">
        <div class="radio ls-flex-row wrap align-items-space-around">
          <?php foreach ($exports as $key => $info) { ?>
            <?php if (!empty($info['label'])) { ?>
              <div class="ls-flex col-4 ls-space padding all-5">
                <label>
                  <input type="radio" name="type" id="<?php echo $key;?>" value="<?php echo $key;?>" <?php if($info['label'] == $defaultexport ){ echo 'checked';}?>>
                  <?php echo $info['label'];?>
                </label>
              </div>
              <?php } ?>
                <?php } ?>
        </div>
      </div>
      <div class="col-sm-3">
      <label for="csvfieldseparator" class="control-label">
        <?php eT("CSV field separator:");?>
      </label>
      <div class="">
        <?php echo CHtml::dropDownList('csvfieldseparator', null, $aCsvFieldSeparator, array('class'=>'form-control')); ?>
      </div>
    </div>
    </div>
  </div>
</div>
