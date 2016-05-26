<?php
/**
 * Render the selector for surveys massive actions.
 *
 */
?>
<div class="col-sm-4 pull-left dropup listActions" data-pk="tid"  data-grid-id="token-grid" id="tokenListActions">
    <button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true">
      <?php eT('Selected participant(s)...');?>
    <span class="caret"></span>
    </button>
    <ul class="dropdown-menu" aria-labelledby="tokenListActions">
        <li>
            <a href="#"
            data-url="<?php echo App()->createUrl('/admin/tokens/sa/deleteMultipleSurveys/');?>"
            data-action="delete"
            data-action-title="<?php eT('Delete tokens'); ?>"
            data-modal-warning-title="<?php eT('Warning');?>"
            data-modal-warning-text="<?php eT('Are you sure you want to delete all those tokens?');?>">
                <span class="text-danger glyphicon glyphicon-trash"></span>
                <?php eT('Delete');?>
            </a>
        </li>
        <li role="separator" class="divider"></li>
        <li class="dropdown-header"> <?php eT("email");?></li>
        <li>
            <a href="#"
            data-url="<?php echo App()->createUrl('/admin/token/sa/inviteMultipleTokens/');?>"
            data-action="invite"
            data-modal-warning-title="<?php eT('Send email invitations');?>"
            data-modal-warning-text="<?php eT('Send an invitation email to the selected entries (if they have not yet been sent an invitation email)');?> <?php eT('Continue?');?>">
            <span class="ui-icon ui-icon-mail-closed" ></span>
            <?php eT('Send email invitations');?>
            </a>
        </li>
        <li>
            <a href="#"
            data-url="<?php echo App()->createUrl('/admin/token/sa/remindMultipleTokens/');?>"
            data-action="remind"
            data-modal-warning-title="<?php eT('Send email reminder');?>"
            data-modal-warning-text="<?php eT('Send a reminder email to the selected entries (if they have already received the invitation email)');?> <?php eT('Continue?');?>">
            <span class="ui-icon ui-icon-mail-closed" ></span>
            <?php eT('Send email reminder');?>
            </a>
        </li>
        <li role="separator" class="divider"></li>
        <li class="dropdown-header"> <?php eT("Central participant database/panel");?></li>
        <li>
            <a href="#"
            data-url="<?php echo App()->createUrl('/admin/token/sa/addCPDBMultipleTokens/');?>"
            data-action="addCPDB"
            data-modal-warning-title="<?php eT('Add participants to central database');?>"
            data-modal-warning-text="<?php eT('This will add all those participants to central database.');?> <?php eT('Continue?');?>">
            <span class="ui-icon ui-add-to-cpdb-link" ></span>
            <?php eT('Add participants to central database');?>
            </a>
        </li>
    </ul>
</div>
