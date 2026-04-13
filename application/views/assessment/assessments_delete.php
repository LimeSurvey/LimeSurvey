<?php
/*
 * Assessments delete
 */
?>
<div id="assesements-delete" class="modal fade" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <?php echo CHtml::form(array("assessment/delete/surveyid/$surveyid"), 'post', array('class'=>'form','id'=>'assessmentsdeleteform','name'=>'assessmentsdeleteform', 'role' => 'form'));?>
            <?php
            Yii::app()->getController()->renderPartial(
                '/layouts/partial_modals/modal_header',
                ['modalTitle' => gT('Delete assessment rule')]
            );
            ?>
                <div class="modal-body">
                    <!-- delete params -->
                    <div>
                        <input type='hidden' name='sid' value='<?php echo $surveyid;?>' />
                        <input type='hidden' name='id' value='' />
                    </div>
                    <p class='modal-body-text'><?php eT("Do you really want to delete this assessment rule?"); ?></p>
                </div>
                <div class="modal-footer modal-footer-buttons">
                    <button type="button" class="btn btn-cancel" data-bs-dismiss="modal"><?php eT('Cancel'); ?></button>
                    <button type="button" class="btn btn-danger" id="selector__assessements-delete-modal">
                        <?php eT("Delete")?>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
