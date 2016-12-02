<?php
/**
 * form
 *
 */
?>
<div class='clearall-survey-form row'>
    <div class='col-sm-7 col-md-offset-3'>
        <div class="btn-group btn-group-justified" role='group' aria-label='<?php echo gT("Please confirm you want to clear your response?") ?>'>
            <div class="btn-group" role="group">
                <button type='submit' name="confirm-clearall" class='btn btn-warning btn-confirm' value='confirm'><?php echo gT("Yes") ?></button>
            </div>
            <div class="btn-group" role="group">
                <button type='submit' name="move" class='btn btn-default btn-cancel' value='clearcancel'><?php echo gT("No") ?></button>
            </div>
        </div>
    </div>
</div>
