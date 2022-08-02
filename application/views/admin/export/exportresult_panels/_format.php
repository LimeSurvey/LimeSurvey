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
      <div class="row">
          <?php foreach ($exports as $key => $info):  ?>
            <?php if (!empty($info['label'])): ?>
            <div class="col-4">
              <div class="form-check form-check-inline">
                <input class="form-check-input" type="radio" name="type" id="<?= $key;?>" value="<?= $key;?>" <?php if($info['label'] == $defaultexport ){ echo 'checked';}?>>
                <label class="form-check-label" for="<?= $key ?>" style="font-weight: 400">
                  <?php echo $info['label'];?>
                </label>
              </div>
</div>
              <?php endif; ?>
          <?php endforeach; ?>
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
