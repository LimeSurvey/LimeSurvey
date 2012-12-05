<div class='header ui-widget-header'><?php $clang->eT("Mail to all Members"); ?></div>
    <?php echo CHtml::form(array("admin/usergroups/sa/mail/ugid/{$ugid}"), 'post', array('class'=>'form30', 'id'=>'mailusergroup', 'name'=>'mailusergroup')); ?>
        <li><label for='copymail'><?php $clang->eT("Send me a copy:"); ?></label>
        <input id='copymail' name='copymail' type='checkbox' class='checkboxbtn' value='1' /></li>
        <li><label for='subject'><?php $clang->eT("Subject:"); ?></label>
        <input type='text' id='subject' size='50' name='subject' value='' /></li>
        <li><label for='body'><?php $clang->eT("Message:"); ?></label>
        <textarea cols='50' rows='4' id='body' name='body'></textarea></li>
        </ul><p><input type='submit' value='<?php $clang->eT("Send"); ?>' />
        <input type='reset' value='<?php $clang->eT("Reset"); ?>' /><br />
        <input type='hidden' name='action' value='mailsendusergroup' />
        <input type='hidden' name='ugid' value='<?php echo $ugid; ?>' />
    </form>
