<?php

/**
 * Subview for panel integration pop-up
 * From panel integration in survey settings
 *
 * @since 2016-02-08
 */

?>

<div id='dlgEditParameter' class='hide'
    data-save-url='<?= Yii::app()->createUrl("surveyAdministration/saveUrlParam") ?>'
    data-delete-url='<?= Yii::app()->createUrl("surveyAdministration/deleteUrlParam") ?>'
>
    <div id='dlgForm' class='form-horizontal'>
        <div class='row'>
            <div class='mb-3'>
                <label class='form-label col-sm-3' for='paramname'><?php eT('Parameter name:'); ?></label>
                <div class='col-sm-4'>
                    <input class='form-control' name='paramname' id='paramname' type='text' size='20' />
                </div>
            </div>
            <div class='mb-3'>
                <label class='form-label col-sm-3' for='targetquestion'><?php eT('Target (sub-)question:'); ?></label>
                <div class='col-sm-4'>
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
            <div class='mb-3'>
                <div class='col-12 text-center'>
                    <button class='btn btn-primary' id='btnSaveParams' type="button">
                        <span class="ri-save-3-fill icon"></span>
                        <?php eT('Save'); ?>
                    </button>
                    <button type="button" class='btn btn-cancel' id='btnCancelParams'>
                        <?php eT('Cancel'); ?>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
