<!-- Range -->
<div class="card" id="panel-2" <?php if ($SingleResponse) { echo 'style="display:none"';} ?> >
  <div class="card-header bg-primary">
    <div class="">
      <?php eT("Range");?>
    </div>
  </div>
  <div class="card-body">
    <div class="mb-3">
      <!-- From -->
      <label for='export_from' class=" form-label">
        <?php eT("From:"); ?>
      </label>
      <div class="">
        <?php printf('<input min="%s" max="%s" step="1" type="number" value="%s" name="export_from" id="export_from" class="form-control" />', $min_datasets, $max_datasets, $min_datasets) ?>
      </div>

      <!-- To -->
      <label for='export_to' class=" form-label">
        <?php eT("to:"); ?>
      </label>
      <div class="">
        <?php printf('<input min="%s" max="%s" step="1" type="number" value="%s" name="export_to" id="export_to" class="form-control" />', $min_datasets, $max_datasets, $max_datasets) ?>
      </div>
    </div>
  </div>
</div>
