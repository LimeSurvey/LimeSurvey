<?php PrepareEditorScript(true, $this); ?>
<div id="in_survey_common_action" class="container-fluid full-page-wrapper">
<div class="col-lg-12 list-surveys">
    <h3>
        <?php eT("Send custom email to selected users"); ?>
    </h3>

    <div class="row">

        <?php echo CHtml::form(array("admin/user/sa/sendMailToUser"), 'post', array('class'=>'col-md-6 col-md-offset-3', 'id'=>'mailusergroup', 'name'=>'mailusergroup')); ?>
        <!--div style="  color: #666; float: left; margin-right: 0.5em;text-align: right; width: 30%;"-->
        <div class='form-group'>
            <label for='subject'><?php eT("Users to send email:");?></label>

            <div class='form-group' style="width:100%;padding:5px; height:<?php echo (count($arrayUsers)>=5)?'100px':'30px'?>; overflow: auto; border:1px gray solid;  background: #effbdb none repeat scroll 0 0"-->
                <ul  style="list-style-type: none;">
                    <?php foreach ($arrayUsers as $user){
                        echo '<li>'.$user['full_name'] . ', &lt;' . $user['email'] . '&gt;</li>';
                    }?>
                </ul>
            </div>
        </div>

            <div class='form-group'>
                <label for='subject'><?php eT("Subject:"); ?></label><br />
                <input type='text' id='subject' size='50' name='subject' value='' />
            </div>
            <div class='form-group'>
                <label for='body'><?php eT("Message:"); ?></label>
                    <div class="">
                        <?php echo CHtml::textArea("body","",array('cols'=>5,'rows'=>20)); ?>
                        <?php echo getEditor("email-inv", "body", '', null, '', '', "tokens"); ?>
                    </div>
            </div>


            <p><input type='submit' value='<?php eT("Send"); ?>' />
                <input type='reset' value='<?php eT("Reset"); ?>'  onClick="CKEDITOR.instances.body.setData( '', function() { this.updateElement(); } )" /><br />

                <input type='hidden' name='usersString' value='<?php echo $usersString?>' />
                <input type='hidden' name='action' value='mailsenduser' />
            </p>
        </form>
    </div>
</div>
</div>