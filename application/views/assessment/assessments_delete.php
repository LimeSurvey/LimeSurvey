<?php
/*
 * Assessments delete
 */
?>
<div id="assesements-delete" class="modal fade" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <?php echo CHtml::form(array("assessment/delete/surveyid/$surveyid"), 'post', array('class'=>'form','id'=>'assessmentsdeleteform','name'=>'assessmentsdeleteform', 'role' => 'form'));?>
                <div class="modal-header"> 
                    <h4 class="modal-title"> 
                        <?php eT("Do you really want to delete this asessment rule?"); ?> 
                    </h4> 
                </div>
                <div class="modal-body">
                    <!-- delete params -->
                    <div>
                        <input type='hidden' name='sid' value='<?php echo $surveyid;?>' />
                        <input type='hidden' name='id' value='' />
                    </div>
                    <div class="row">
                        <div class="col-sm-6">
                            <button type="button" class="btn btn-default" data-dismiss="modal"><?php eT('Close'); ?></button>
                        </div>
                        <div class="col-sm-6">
                            <button type="button" class="btn btn-danger" id="selector__assessements-delete-modal" ><?php eT("Yes, delete")?></button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
