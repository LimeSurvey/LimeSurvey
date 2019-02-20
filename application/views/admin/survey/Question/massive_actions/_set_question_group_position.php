<?php
/**
 * Set question group and position modal body (parsed to massive action widget)
 * @var $model      The question model
 * @var $oSurvey    The survey object
 */
?>

<?php eT("Set question group for those question"); ?>
<form class="custom-modal-datas  form-horizontal">
    <!-- select group -->
    <div class="form-group">
        <label class="control-label col-sm-4" for="group_gid"><?php et('Group:'); ?></label>
        <div class="col-sm-8">
            <select name="group_gid" class="form-control custom-data" id="gid">
                <?php foreach($model->AllGroups as $group): ?>
                    <option value="<?php echo $group->gid;?>">
                        <?php echo flattenText($group->group_name);?>
                    </option>
                <?php endforeach?>
            </select>
        </div>

    </div>
    <!-- Position widget -->
    <?php $this->widget('ext.admin.survey.question.PositionWidget.PositionWidget', array(
                'display' => 'ajax_form_group',
                'oSurvey' => $oSurvey,
                'classes' => 'custom-data'
        ));
    ?>
</form>
