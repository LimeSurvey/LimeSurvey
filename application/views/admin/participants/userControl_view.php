<script src="<?php echo Yii::app()->getConfig('adminscripts') . "userControl.js" ?>" type="text/javascript"></script>

<div class="col-lg-12 list-surveys">
    <h3><?php eT("Global participant settings"); ?></h3>

    <div class="row">
        <div class="col-lg-12 content-right">
    <div id='usercontrol-1'>
        <?php
        if (Permission::model()->hasGlobalPermission('superadmin','read'))
        {
            $attribute = array('class' => 'col-md-6 col-md-offset-3');
            echo CHtml::beginForm($this->createUrl('/admin/participants/sa/storeUserControlValues'), 'post', $attribute);
            $options = array('Y' => gT('Yes','unescaped'), 'N' => gT('No','unescaped'));
            ?>
                <div class="form-group">
                    <label for='userideditable' id='userideditable'>
                        <?php eT('User ID editable:'); ?>
                    </label>
                    <?php echo CHtml::dropDownList('userideditable', $userideditable, $options, array('class' => 'form-control' ) ); ?>
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
            echo "<div class='messagebox ui-corner-all'>" . gT("You don't have sufficient permissions.") . "</div>";
        }
        ?>
    </div>

        </div>
    </div>
</div>

