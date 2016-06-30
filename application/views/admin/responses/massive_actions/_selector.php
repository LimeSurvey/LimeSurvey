<?php
/**
 * Render the selector for surveys massive actions.
 *
 */
?>
<div class="col-sm-4 pull-left dropup listActions" data-pk="id"  data-grid-id="responses-grid" id="responsesListActions">
    <button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true">
      <?php eT('Selected response(s)...');?>
    <span class="caret"></span>
    </button>
    <ul class="dropdown-menu" aria-labelledby="responsesListActions">
        <li>
            <a href="#"
            data-url="<?php echo App()->createUrl('/admin/responses/sa/actionDelete/surveyid/'.$_GET['surveyid']);?>"
            data-keepopen="no"
            data-sid="<?php echo $_GET['surveyid']?>"
            data-action="delete"
            data-action-title="<?php eT('Delete reponses'); ?>"
            data-modal-warning-title="<?php eT('Warning');?>"
            data-modal-warning-text="<?php eT('Are you sure you want to delete the selected responses?');?>">
                <span class="text-danger glyphicon glyphicon-trash"></span>
                <?php eT('Delete');?>
            </a>
        </li>
    </ul>
</div>
