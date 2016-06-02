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
            data-url="<?php echo App()->createUrl('/admin/tokens/sa/deleteMultiple/');?>"
            data-keepopen="no"
            data-sid="<?php echo $_GET['surveyid']?>"
            data-action="delete"
            data-action-title="<?php eT('Delete survey participants'); ?>"
            data-modal-warning-title="<?php eT('Warning');?>"
            data-modal-warning-text="<?php eT('Are you sure you want to delete the selected participants?');?>">
                <span class="text-danger glyphicon glyphicon-trash"></span>
                <?php eT('Delete');?>
            </a>
        </li>
        <li role="separator" class="divider"></li>
        <li class="dropdown-header"> <?php eT("Email");?></li>
        <li>
            <a href="#"
            data-url="<?php echo App()->createUrl('/admin/tokens/sa/email/surveyid/'.$_GET['surveyid']);?>"
            data-input-name="tokenids"
            data-post-redirect="true"
            data-action="invite"
            data-modal-warning-title="<?php eT('Send email invitations');?>"
            data-modal-warning-text="<?php eT('Send an invitation email to the selected entries (if they have not yet been sent an invitation email)');?> <?php eT('Continue?');?>">
            <span class="icon-invite text-success" ></span>
            <?php eT('Send email invitations');?>
            </a>
        </li>
        <li>
            <a href="#"
            data-url="<?php echo App()->createUrl('/admin/tokens/sa/email/action/remind/surveyid/'.$_GET['surveyid']);?>"
            data-input-name="tokenids"
            data-post-redirect="true"
            data-action="remind"
            data-modal-warning-title="<?php eT('Send email reminder');?>"
            data-modal-warning-text="<?php eT('Send a reminder email to the selected entries (if they have already received the invitation email)');?> <?php eT('Continue?');?>">
            <span class="icon-remind text-success" ></span>
            <?php eT('Send email reminder');?>
            </a>
        </li>
        <li role="separator" class="divider"></li>
        <li class="dropdown-header"> <?php eT("Central participant database/panel");?></li>
        <li>
            <a href="#"
            data-url="<?php echo App()->createUrl('admin/participants/sa/attributeMapToken/sid/'.$_GET['surveyid']);?>"
            data-action="addCPDB"
            data-fill-session-and-redirect="true"
            data-modal-warning-title="<?php eT('Add participants to central database');?>"
            data-modal-warning-text="<?php eT('This will add the selected participants to the central participant database (CPDB).');?> <?php eT('Continue?');?>">
            <span class="ui-icon ui-add-to-cpdb-link" ></span>
            <?php eT('Add participants to central database');?>
            </a>
        </li>
    </ul>
</div>
