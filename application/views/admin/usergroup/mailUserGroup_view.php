<div class="col-lg-12 list-surveys">
    <div class="pagetitle h3">
        <?php eT("Mail to all Members"); ?>
    </div>

    <div class="row">
        <?php echo CHtml::form(array("admin/usergroups/sa/mail/ugid/{$ugid}"), 'post', array('class'=>'col-md-6 col-md-offset-3', 'id'=>'mailusergroup', 'name'=>'mailusergroup')); ?>
            <div class="form-group">
                <label for='copymail'>
                    <?php eT("Send me a copy:"); ?>
                    <input id='copymail' name='copymail' type='checkbox' class='checkboxbtn' value='1'  class="form-control"/>
                </label>
            </div>    
            <div class="form-group">
                <label for='subject'>
                <?php eT("Subject:"); ?>
                </label>
            <input type='text' id='subject' size='50' name='subject' value='' class="form-control" />
                </div>    
            <div class="form-group">
                <label for='body'>
                <?php eT("Message:"); ?>
                </label>
                <textarea cols='50' rows='4' id='body' name='body'  class="form-control"></textarea>
                </div>    
            <p>
                <input type='submit' value='<?php eT("Send"); ?>' class="btn btn-default" />
                <input type='reset' value='<?php eT("Reset"); ?>' class="btn btn-default" />
            <input type='hidden' name='action' value='mailsendusergroup' />
            <input type='hidden' name='ugid' value='<?php echo $ugid; ?>' />
        </form>        
    </div>
</div>
