<?php
/**
 * Set question group and position modal.
 * @var $model      The question model
 * @var $oSurvey    The survey object
 */
?>

<!-- Set group and position modal  -->
<div id="setquestiongroup-modal" class="modal fade" role="dialog">
    <div class="modal-dialog">
        <!-- Modal content-->
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title"><?php eT("Set question group"); ?></h4>
            </div>
            <div class="modal-body">
                <p class='modal-body-text'>

                    <?php eT("Set question group for those question"); ?>
                    <form class="custom-modal-datas">

                        <!-- select group -->
                        <div class="form-group">
                            <label class="control-label col-sm-4" for="group_gid"><?php et('Group:'); ?></label>
                            <div class="col-sm-8">
                                <select name="group_gid" class="form-control custom-data" id="gid">
                                    <?php foreach($model->AllGroups as $group): ?>
                                        <option value="<?php echo $group->gid;?>">
                                            <?php echo $group->group_name;?>
                                        </option>
                                    <?php endforeach?>
                                </select>
                            </div>

                        </div>

                        <br/><br/>

                        <!-- Position widget -->
                        <?php $this->widget('ext.admin.survey.question.PositionWidget.PositionWidget', array(
                                    'display' => 'ajax_form_group',
                                    'oSurvey' => $oSurvey,
                                    'classes' => 'custom-data'
                            ));
                        ?>
                    </form>

                </p>
                <!-- Ajax loader -->
                <div id="ajaxContainerLoading" >
                    <p><?php eT('Please wait, loading data...');?></p>
                    <div class="preloader loading">
                        <span class="slice"></span>
                        <span class="slice"></span>
                        <span class="slice"></span>
                        <span class="slice"></span>
                        <span class="slice"></span>
                        <span class="slice"></span>
                    </div>
                </div>
            </div>
            
            <div class="modal-footer">
                <a class="btn btn-primary btn-ok"><span class='fa fa-check'></span>&nbsp;<?php eT("Apply"); ?></a>
                <button type="button" class="btn btn-danger" data-dismiss="modal"><span class='fa fa-ban'></span>&nbsp;<?php eT("Cancel"); ?></button>
            </div>
        </div>
    </div>
</div>
