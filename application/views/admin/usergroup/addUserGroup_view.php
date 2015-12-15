<div class="col-lg-12 list-surveys">
    <h3>
        <?php eT("Add user group"); ?>
    </h3>

    <div class="row">
        <div class="col-lg-12 content-right text-center">
            <?php echo CHtml::form(array("admin/usergroups/sa/add"), 'post', array('class'=>'form-horizontal', 'id'=>'usergroupform')); ?>

                <!-- Name -->
                <div class="form-group">
                    <label for='group_name' class="control-label col-lg-2 col-sm-5 col-md-7">
                        <?php eT("Name:"); ?>
                    </label>

                    <div class="default col-lg-4 col-sm-5 col-md-7 controls">
                        <input type='text' size='50' maxlength='20' id='group_name' name='group_name' required="required" autofocus="autofocus" class="form-control"/>
                    </div>
                </div>

                <!-- Description -->
                <div class="form-group">
                    <label for='group_description' class="control-label col-lg-2 col-sm-5 col-md-7">
                        <?php eT("Description:"); ?>
                    </label>
                    <div class="default col-lg-4 col-sm-5 col-md-7 controls">
                        <textarea cols='50' rows='4' id='group_description' name='group_description'  class="form-control"></textarea>
                    </div>
                </div>

                <!-- Buttons -->
                <p>
                    <input type='submit' value='<?php eT("Add group"); ?>' class="hidden" />
                    <input type='hidden' name='action' value='usergroupindb'  />
                </p>
            </form>
        </div>
    </div>
</div>
