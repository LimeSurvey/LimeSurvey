<!-- Range -->
<div class="card mb-4 <?= $SingleResponse ? 'd-none' : ''?>" id="panel-2">
    <div class="card-header ">
        <div class="">
            <?php eT("Range"); ?>
        </div>
    </div>
    <div class="card-body">
        <div class="mb-3">
            <!-- From -->
            <label for='export_from' class=" form-label">
                <?php eT("From:"); ?>
            </label>
            <div class="">
                <?php printf(
                    '<input min="%s" max="%s" step="1" type="number" value="%s" name="export_from" id="export_from" class="form-control" />',
                    $min_datasets,
                    $max_datasets,
                    $min_datasets
                ) ?>
            </div>

            <!-- To -->
            <label for='export_to' class=" form-label">
                <?php eT("to:"); ?>
            </label>
            <div class="">
                <?php printf(
                    '<input min="%s" max="%s" step="1" type="number" value="%s" name="export_to" id="export_to" class="form-control" />',
                    $min_datasets,
                    $max_datasets,
                    $max_datasets
                ) ?>
            </div>
        </div>
    </div>
</div>
