<div class='header ui-widget-header'>
    <?php eT("Add user group"); ?>
</div>
<br/>

<?php echo CHtml::form(array("admin/usergroups/sa/add"), 'post', ['class' => 'form30', 'id' => 'usergroupform']); ?>

<ul>
    <li>
        <?php echo CHtml::label(gT("Name:"), 'group_name');
        ?>
        <?php echo CHtml::textField('group_name', '', [
            'type' => 'text',
            'size' => 50,
            'maxlength' => 20,
            'id' => 'group_name',
            'class' => 'col-md-5',
            'required' => 'required',
            'autofocus' => 'autofocus']);
        ?>
        <font color='red' face='verdana' size='1'> <?php eT("Required"); ?></font>
    </li>

    <li>
        <?php echo CHtml::label(gT("Description:"), 'group_name', [
            'class' => 'form30',
            'id' => 'usergroupform']);
        ?>
        <?php echo CHtml::textarea('group_description', '', [
            'type' => 'text',
            'cols' => 50,
            'rows' => 4,
            'id' => 'group_description']);
        ?>
    </li>
</ul>
<p>
    <input type='submit' value='<?php eT("Add group"); ?>'/>
    <input type='hidden' name='action' value='usergroupindb'/>
</p>


<?php echo CHtml::endForm(); ?>
</div><!-- form -->