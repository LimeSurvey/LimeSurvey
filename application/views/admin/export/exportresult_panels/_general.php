<!-- General -->
<div class="card mb-4" id="panel-various">
    <div class="card-header ">
        <?php eT("General"); ?>
    </div>
    <div class="card-body">
        <div class="mb-3 row">
            <label for='completionstate' class="form-label">
                <?php eT("Completion state:"); ?>
            </label>

            <div class="">
                <select name='completionstate' id='completionstate' class='form-select'>
                    <option value='complete' <?php echo $selecthide; ?>>
                        <?php eT("Completed responses only"); ?>
                    </option>
                    <option value='all' <?php echo $selectshow; ?>>
                        <?php eT("All responses"); ?>
                    </option>
                    <option value='incomplete' <?php echo $selectinc; ?>>
                        <?php eT("Incomplete responses only"); ?>
                    </option>
                </select>
            </div>
        </div>

        <div class="mb-3 row">
            <label for='exportlang' class="form-label">
                <?php eT("Export language:"); ?>
            </label>
            <div class=''>
                <?php echo CHtml::dropDownList('exportlang', null, $aLanguages, ['class' => 'form-select']); ?>
            </div>
        </div>
    </div>
</div>
