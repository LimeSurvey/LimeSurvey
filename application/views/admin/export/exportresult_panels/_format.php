<!-- Format -->
<div class="card" id="panel-export-format">
  <div class="card-header bg-primary">
      <?php eT("Format");?>
  </div>
  <div class="card-body">
    <div class="mb-3">
      <!-- Format -->
      <label for='export_format' class="col-md-6 form-label">
        <?php eT("Export format:"); ?>
      </label>
      <div class="col-md-12">
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
      <div class="col-md-3">
      <label for="csvfieldseparator" class="form-label">
        <?php eT("CSV field separator:");?>
      </label>
      <div class="">
        <?php echo CHtml::dropDownList('csvfieldseparator', null, $aCsvFieldSeparator, array('class'=>'form-select')); ?>
      </div>
    </div>
    </div>
  </div>
</div>
