<?php
/**
 * @var array $labelsets
 */
?>
<script type='text/javascript'>
    var strSelectLabelset = '<?php eT('You have to select at least one label set.', 'js');?>';
</script>

<div class="col-lg-12 list-surveys">
    <div class="row">
        <div class="col-lg-12 content-right text-center">
            <?php echo CHtml::form(["admin/export/sa/dumplabel"], 'post', ['id' => 'exportlabelset', 'class' => '']); ?>
            <div class="form-group row">
                <label class="col-sm-3 form-control-label" for='labelsets'>
                    <?php eT('Please choose the label sets you want to export:'); ?>
                    <br/>
                    <?php eT('(Select multiple label sets by using the Ctrl key)'); ?>
                </label>
                <div class="col-sm-3">
                    <select id='labelsets' multiple='multiple' name='lids[]' size='20' class="form-control">
                        <?php if (count($labelsets) > 0) {
                            foreach ($labelsets as $lb) {
                                echo "<option value='{$lb[0]}'>{$lb[0]}: {$lb[1]}</option>\n";
                            }
                        } ?>
                    </select>
                </div>
                <p>
                    <br/>
                    <input type='submit' id='btnDumpLabelSets' value='<?php eT('Export selected label sets'); ?>' class="hidden"/>
                    <input type='hidden' name='action' value='dumplabel'/>
                </p>
            </div>
            <?php echo CHtml::endForm() ?>
        </div>
    </div>
</div>

<script type='text/javascript'>
    /** If one or more elements are selected, then the save button will be enabled.
     *  otherwise its disabled by default. 
     *  Export Button will be hidden. */
    let isSelected    = false;
    let exportButton  = document.getElementById('exportButton');
    let saveButton    = document.getElementById('save-form-button');
    
    /* Hide Export Button */
    $(exportButton).hide();

    $('select').change(function() {
        $('#labelsets option:selected').each(function() {
            isSelected = true;
            return isSelected;
        });

        if (isSelected) {
            saveButton.removeAttribute('disabled');
        } else {
            saveButton.setAttribute('disabled', true);
        }
    }).trigger('change');
</script>
