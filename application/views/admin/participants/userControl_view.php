<script src="<?php echo Yii::app()->getConfig('adminscripts') . "userControl.js" ?>" type="text/javascript"></script>
<div class='header ui-widget-header'>
    <strong>
        <?php
        $clang->eT("Global participant settings");
        ?>
    </strong>
</div>
<div id='tabs'>
    <ul>
        <li>
            <a href='#usercontrol'>User Control</a>
        </li>
    </ul>
    <div id='usercontrol-1'>
        <?php
        if (Yii::app()->session['USER_RIGHT_SUPERADMIN'])
        {
            $attribute = array('class' => 'form44');
            echo CHtml::beginForm('admin/participants/sa/storeUserControlValues', 'post', $attribute);
            $options = array('Y' => 'Yes', 'N' => 'No');
            ?>
            <ul>
                <li>
                    <label for='userideditable' id='userideditable'>
                        <?php $clang->eT('User ID editable:'); ?>
                    </label>
                    <?php echo CHtml::dropDownList('userideditable', $userideditable, $options); ?>
                </li>
            </ul>
            <p>
                <?php
                echo CHtml::submitButton('submit', array('value' => 'Submit'));
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
