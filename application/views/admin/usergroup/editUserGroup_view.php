<div class='header ui-widget-header'><?php echo sprintf(gT("Editing user group (Owner: %s)"), Yii::app()->session['user']); ?></div>
    <?php echo CHtml::form(array("admin/usergroups/sa/edit/ugid/{$ugid}"), 'post', array('class'=>'form30', 'id'=>'usergroupform', 'name'=>'usergroupform')); ?>
        <ul>
        <li><label for='name'><?php eT("Name:"); ?></label>
        <input type='text' size='50' maxlength='20' id='name' name='name' value="<?php echo htmlspecialchars($esrow['name'],ENT_QUOTES, 'UTF-8'); ?>" /></li>
        <li><label for='description'><?php eT("Description:"); ?></label>
        <textarea cols='50' rows='4' id='description' name='description'><?php echo htmlspecialchars($esrow['description'],ENT_QUOTES, 'UTF-8'); ?></textarea></li>
        <ul><p><input type='submit' value='<?php eT("Update user group"); ?>' />
        <input type='hidden' name='action' value='editusergroupindb' />
        <input type='hidden' name='owner_id' value='<?php echo Yii::app()->session['loginID']; ?>' />
        <input type='hidden' name='ugid' value='<?php echo $ugid; ?>' />
    </form>