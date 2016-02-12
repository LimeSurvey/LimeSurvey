<script src="<?php echo Yii::app()->getConfig('adminscripts') . "userControl.js" ?>" type="text/javascript"></script>

<div class="col-lg-12 list-surveys">
    <h3><?php eT("Blacklist control"); ?></h3>

    <div class="row">
        <div class="col-lg-12 content-right">


<div id='usercontrol-1'>
        <?php
        if (Permission::model()->hasGlobalPermission('superadmin','read'))
        {
            $attribute = array('class' => 'col-md-6 col-md-offset-3');
            echo CHtml::beginForm($this->createUrl('/admin/participants/sa/storeBlacklistValues'), 'post', $attribute);
            $options = array('Y' => gT('Yes','unescaped'), 'N' => gT('No','unescaped'));
            ?>
                <div class="form-group">
                    <label for='blacklistallsurveys' id='blacklistallsurveys'>
                        <?php eT('Blacklist all current surveys for participant once the global field is set:'); ?>
                    </label>
                    <?php echo CHtml::dropDownList('blacklistallsurveys', $blacklistallsurveys, $options, array('class' => 'form-control' )); ?>
                </div>
                <div class="form-group">
                    <label for='blacklistnewsurveys' id='blacklistnewsurveys'>
                        <?php eT('Blacklist participant for any new added survey once the global field is set:'); ?>
                    </label>
                    <?php echo CHtml::dropDownList('blacklistnewsurveys', $blacklistnewsurveys, $options, array('class' => 'form-control' ) ); ?>
                </div>
                <div class="form-group">
                    <label for='blockaddingtosurveys' id='blockaddingtosurveys'>
                        <?php eT('Allow blacklisted participants to be added to a survey:'); ?>
                    </label>
                    <?php echo CHtml::dropDownList('blockaddingtosurveys', $blockaddingtosurveys, $options, array('class' => 'form-control' ) ); ?>
                </div>
                <div class="form-group">
                    <label for='hideblacklisted' id='hideblacklisted'>
                        <?php eT('Hide blacklisted participants:'); ?>
                    </label>
                    <?php echo CHtml::dropDownList('hideblacklisted', $hideblacklisted, $options, array('class' => 'form-control' ) ); ?>
                </div>
                <div class="form-group">
                    <label for='deleteblacklisted' id='deleteblacklisted'>
                        <?php eT('Delete globally blacklisted participant from the database:'); ?>
                    </label>
                    <?php echo CHtml::dropDownList('deleteblacklisted', $deleteblacklisted, $options, array('class' => 'form-control' ) ); ?>
                </div>
                <div class="form-group">
                    <label for='allowunblacklist' id='allowunblacklist'>
                        <?php eT('Allow participant to unblacklist himself/herself:'); ?>
                    </label>
                    <?php echo CHtml::dropDownList('allowunblacklist', $allowunblacklist, $options, array('class' => 'form-control' ) ); ?>
                </div>
            <p>
                <?php
                echo CHtml::submitButton('submit', array('value' => gT('Save'), 'class'=>'btn btn-default'));
                ?>
            </p>
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


