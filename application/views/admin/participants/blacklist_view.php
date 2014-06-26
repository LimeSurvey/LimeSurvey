<script src="<?php echo Yii::app()->getConfig('adminscripts') . "userControl.js" ?>" type="text/javascript"></script>
<div class='header ui-widget-header'>
    <strong>
        <?php
        $clang->eT("Blacklist settings");
        ?>
    </strong>
</div>
<div id='tabs'>
    <ul>
        <li>
            <a href='#usercontrol'><?php
        $clang->eT("Blacklist control");
        ?></a>
        </li>
    </ul>
<div id='usercontrol-1'>
        <?php
        if (Permission::model()->hasGlobalPermission('superadmin','read'))
        {
            $attribute = array('class' => 'form44');
            echo CHtml::beginForm($this->createUrl('/admin/participants/sa/storeBlacklistValues'), 'post', $attribute);
            $options = array('Y' => $clang->gT('Yes'), 'N' => $clang->gT('No'));
            ?>
            <ul>
                <li>
                    <label for='blacklistallsurveys' id='blacklistallsurveys'>
                        <?php $clang->eT('Blacklist all current surveys for participant once the global field is set:'); ?>
                    </label>
                    <?php echo CHtml::dropDownList('blacklistallsurveys', $blacklistallsurveys, $options); ?>
                </li>
                <li>
                    <label for='blacklistnewsurveys' id='blacklistnewsurveys'>
                        <?php $clang->eT('Blacklist participant for any new added survey once the global field is set:'); ?>
                    </label>
                    <?php echo CHtml::dropDownList('blacklistnewsurveys', $blacklistnewsurveys, $options); ?>
                </li>
                <li>
                    <label for='blockaddingtosurveys' id='blockaddingtosurveys'>
                        <?php $clang->eT('Allow blacklisted participants to be added to a survey:'); ?>
                    </label>
                    <?php echo CHtml::dropDownList('blockaddingtosurveys', $blockaddingtosurveys, $options); ?>
                </li>
                <li>
                    <label for='hideblacklisted' id='hideblacklisted'>
                        <?php $clang->eT('Hide blacklisted participants:'); ?>
                    </label>
                    <?php echo CHtml::dropDownList('hideblacklisted', $hideblacklisted, $options); ?>
                </li>
                <li>
                    <label for='deleteblacklisted' id='deleteblacklisted'>
                        <?php $clang->eT('Delete globally blacklisted participant from the database:'); ?>
                    </label>
                    <?php echo CHtml::dropDownList('deleteblacklisted', $deleteblacklisted, $options); ?>
                </li>
                <li>
                    <label for='allowunblacklist' id='allowunblacklist'>
                        <?php $clang->eT('Allow participant to unblacklist himself/herself:'); ?>
                    </label>
                    <?php echo CHtml::dropDownList('allowunblacklist', $allowunblacklist, $options); ?>
                </li>
            </ul>
            <p>
                <?php
                echo CHtml::submitButton('submit', array('value' => $clang->gT('Save')));
                ?>
            </p>
            <?php
            echo CHtml::endForm();
        }
        else
        {
            echo "<div class='messagebox ui-corner-all'>" . $clang->gT("You don't have sufficient permissions.") . "</div>";
        }
        ?>
    </div>
</div>
