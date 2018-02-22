<?php

/**
 * Subview for panel integration pop-up
 * From panel integration in survey settings
 *
 * @since 2016-02-08
 */

?>

<div id='dlgEditParameter'>
    <div id='dlgForm' class=''>
        <div class='row'>
            <div class='form-group'>
                <label class='control-label ' for='paramname'><?php eT('Parameter name:'); ?></label>
                <div class=''>
                    <input class='form-control' name='paramname' id='paramname' type='text' size='20' />
                </div>
            </div>
            <div class='form-group'>
                <label class='control-label ' for='targetquestion'><?php eT('Target (sub-)question:'); ?></label>
                <div class=''>
                    <select class='form-control' name='targetquestion' id='targetquestion' size='1'>
                        <option value=''><?php eT('(No target question)'); ?></option>
                        <?php foreach ($questions as $question){?>
                            <option value='<?php echo $question['qid'].'-'.$question['sqid'];?>'><?php echo $question['title'].': '.ellipsize(flattenText($question['question'],true,true),43,.70);
                                if ($question['sqquestion']!='')
                                {
                                    echo ' - '.ellipsize(flattenText($question['sqquestion'],true,true),30,.75);
                                }
                            ?></option> <?php
                        }?>
                    </select>
                </div>
            </div>
            <div class='form-group'>
                <div class='col-sm-12 text-center'>
                    <button class='btn btn-success' id='btnSaveParams'>
                        <span class="fa fa-floppy-o"></span>
                        <?php eT('Save'); ?>
                    </button>
                    <button class='btn btn-danger' id='btnCancelParams'><?php eT('Cancel'); ?></button>
                </div>
            </div>
        </div>
    </div>
</div>
