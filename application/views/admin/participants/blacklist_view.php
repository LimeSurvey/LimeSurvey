<script src="<?php echo Yii::app()->getConfig('adminscripts') . "userControl.js" ?>" type="text/javascript"></script>

<div class="col-lg-12 list-surveys">
    <h3><?php eT("Blacklist settings"); ?></h3>

    <div class="row">
        <div class="col-lg-12 content-right">


<div id='usercontrol-1'>
        <?php
        if (Permission::model()->hasGlobalPermission('superadmin','read'))
        {
            $attribute = array('class' => 'col-md-6 col-md-offset-1 form-horizontal');
            echo CHtml::beginForm($this->createUrl('/admin/participants/sa/storeBlacklistValues'), 'post', $attribute);
            $options = array('Y' => gT('Yes','unescaped'), 'N' => gT('No','unescaped'));
            ?>
                <div class="form-group">
                    <label class='control-label col-sm-8'>
                        <?php eT('Blacklist all current surveys for participant once the global field is set:'); ?>
                    </label>
                    <div class='col-sm-3'>
                        <?php $this->widget('yiiwheels.widgets.switch.WhSwitch', array(
                            'name' => 'blacklistallsurveys',
                            'onLabel' => gT('Yes'),
                            'offLabel' => gT('No'),
                            'value' => $blacklistallsurveys == 'Y' ? '1' : 0
                        )); ?>
                    </div>
                </div>
                <div class="form-group">
                    <label class='control-label col-sm-8'>
                        <?php eT('Blacklist participant for any new added survey once the global field is set:'); ?>
                    </label>
                    <div class='col-sm-3'>
                        <?php $this->widget('yiiwheels.widgets.switch.WhSwitch', array(
                            'name' => 'blacklistnewsurveys',
                            'onLabel' => gT('Yes'),
                            'offLabel' => gT('No'),
                            'value' => $blacklistnewsurveys == 'Y' ? '1' : 0
                        )); ?>
                    </div>
                </div>
                <div class="form-group">
                    <label class='control-label col-sm-8'>
                        <?php eT('Allow blacklisted participants to be added to a survey:'); ?>
                    </label>
                    <div class='col-sm-3'>
                        <?php $this->widget('yiiwheels.widgets.switch.WhSwitch', array(
                            'name' => 'blockaddingtosurveys',
                            'onLabel' => gT('Yes'),
                            'offLabel' => gT('No'),
                            'value' => $blockaddingtosurveys == 'Y' ? '1' : 0
                        )); ?>
                    </div>
                </div>
                <div class="form-group">
                    <label class='control-label col-sm-8'>
                        <?php eT('Hide blacklisted participants:'); ?>
                    </label>
                    <div class='col-sm-3'>
                        <?php $this->widget('yiiwheels.widgets.switch.WhSwitch', array(
                            'name' => 'hideblacklisted',
                            'onLabel' => gT('Yes'),
                            'offLabel' => gT('No'),
                            'value' => $hideblacklisted == 'Y' ? '1' : 0
                        )); ?>
                    </div>
                </div>
                <div class="form-group">
                    <label class='control-label col-sm-8'>
                        <?php eT('Delete globally blacklisted participant from the database:'); ?>
                    </label>
                    <div class='col-sm-3'>
                        <?php $this->widget('yiiwheels.widgets.switch.WhSwitch', array(
                            'name' => 'deleteblacklisted',
                            'onLabel' => gT('Yes'),
                            'offLabel' => gT('No'),
                            'value' => $deleteblacklisted == 'Y' ? '1' : 0
                        )); ?>
                    </div>
                </div>
                <div class="form-group">
                    <label class='control-label col-sm-8'>
                        <?php eT('Allow participant to unblacklist himself/herself:'); ?>
                    </label>
                    <div class='col-sm-3'>
                        <?php $this->widget('yiiwheels.widgets.switch.WhSwitch', array(
                            'name' => 'allowunblacklist',
                            'onLabel' => gT('Yes'),
                            'offLabel' => gT('No'),
                            'value' => $allowunblacklist == 'Y' ? '1' : 0
                        )); ?>
                    </div>
                </div>
                <div class="form-group">
                    <div class='col-sm-8'></div>
                    <div class='col-sm-3'>
                        <?php echo CHtml::submitButton('submit', array('value' => gT('Save'), 'class'=>'btn btn-default')); ?>
                    </div>
                </div>
            <?php
            echo CHtml::endForm();
        }
        else
        {
            echo "<div class='messagebox ui-corner-all'>" . gT("We are sorry but you don't have permissions to do this.") . "</div>";
        }
        ?>
    </div>


        </div>
    </div>

</div>


