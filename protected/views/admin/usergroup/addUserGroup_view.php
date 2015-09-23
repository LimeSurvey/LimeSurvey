<div class='header ui-widget-header'><?php eT("Add user group"); ?></div>
<br />
<?php echo CHtml::form(array("admin/usergroups/sa/add"), 'post', array('class'=>'form30', 'id'=>'usergroupform')); ?>
    <ul>
        <li>
            <label for='group_name'><?php eT("Name:"); ?></label>
            <input type='text' size='50' maxlength='20' id='group_name' name='group_name' required="required" autofocus="autofocus" />
            <font color='red' face='verdana' size='1'> <?php eT("Required"); ?></font>
        </li>
        <li>
            <label for='group_description'><?php eT("Description:"); ?></label>
            <textarea cols='50' rows='4' id='group_description' name='group_description'></textarea>
        </li>
    </ul>
    <p>
        <input type='submit' value='<?php eT("Add group"); ?>' />
        <input type='hidden' name='action' value='usergroupindb' />
    </p>
</form>