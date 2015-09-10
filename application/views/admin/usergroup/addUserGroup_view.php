<div class="col-lg-12 list-surveys">
    <h3>
        <?php eT("Add user group"); ?>
    </h3>

    <div class="row">
        <div class="col-lg-12 content-right text-center">
            <?php echo CHtml::form(array("admin/usergroups/sa/add"), 'post', array('class'=>'col-md-6 col-md-offset-3', 'id'=>'usergroupform')); ?>
                <div class="form-group">
                    <label for='group_name'><?php eT("Name:"); ?></label>
                    <input type='text' size='50' maxlength='20' id='group_name' name='group_name' required="required" autofocus="autofocus" class="form-control"/>         
                    <font color='red' face='verdana' size='1'> <?php eT("Required"); ?></font>
                </div>
                <div class="form-group">
                    <label for='group_description'><?php eT("Description:"); ?></label>
                    <textarea cols='50' rows='4' id='group_description' name='group_description'  class="form-control"></textarea>
                </div>
                <p>
                    <input type='submit' value='<?php eT("Add group"); ?>' class="hidden" />
                    <input type='hidden' name='action' value='usergroupindb'  />
                </p>
            </form>            
        </div>
    </div>
</div>    
