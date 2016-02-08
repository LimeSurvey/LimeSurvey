<?php

/**
 * Subview for panel integration pop-up
 * From panel integration in survey settings
 *
 * @since 2016-02-08
 */

?>

<div id='dlgEditParameter'>
    <div id='dlgForm' class='form-horizontal'>
        <ul>
            <li>
                <label for='paramname'><?php eT('Parameter name:'); ?></label><input name='paramname' id='paramname' type='text' size='20' />
            </li>
            <li>
                <label for='targetquestion'><?php eT('Target (sub-)question:'); ?></label><select name='targetquestion' id='targetquestion' size='1'>
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
            </li>
        </ul>
    </div>
    <p><button id='btnSaveParams'><?php eT('Save'); ?></button> <button id='btnCancelParams'><?php eT('Cancel'); ?></button> </p>
</div>
