<?php
/** @var UserGroup $model the UserGroup model */
/** @var integer $ugid */
?>

<div class="col-12 list-surveys">
    <div class="row">
        <?php echo CHtml::form(array("userGroup/edit/ugid/{$ugid}"), 'post', array('class' => 'col-lg-6 offset-lg-3', 'id' => 'usergroupform', 'name' => 'usergroupform')); ?>
            
            <div class="mb-3">
                <label class="form-label" for='name'><?php eT("Name:"); ?></label>
                <input type='text' size='50' maxlength='20' id='name' name='name' value="<?php echo htmlspecialchars((string) $model['name'], ENT_QUOTES, 'UTF-8'); ?>" class="form-control" />
            </div>

            <div class="mb-3">
                <label class="form-label " for='description'><?php eT("Description:"); ?></label>
                <textarea cols='50' rows='4' id='description' name='description' class="form-control"><?php echo htmlspecialchars((string) $model['description'], ENT_QUOTES, 'UTF-8'); ?></textarea>
            </div>

            <input type='submit' value='<?php eT("Update user group"); ?>' class="d-none" />
            <input type='hidden' name='action' value='editusergroupindb' />
            <input type='hidden' name='owner_id' value='<?php echo Yii::app()->session['loginID']; ?>' />
            <input type='hidden' name='ugid' value='<?php echo $ugid; ?>' />
        <?php echo CHtml::endForm() ?>
    </div>
</div>

