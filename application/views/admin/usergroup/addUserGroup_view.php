<div class="col-lg-12 list-surveys">
    <div class="pagetitle h3">
        <?php eT("Add user group"); ?>
    </div>

    <div class="row">
        <div class="col-md-8 col-md-offset-2 content-right">
            <?php echo CHtml::form(array("admin/usergroups/sa/add"), 'post', array('class'=>'', 'id'=>'usergroupform')); ?>

                <!-- Name -->
                <div class="form-group col-lg-12">
                    <label for='group_name' class="control-label">
                        <?php eT("Name:"); ?>
                    </label>

                    <div class="default controls">
                        <input type='text' size='50' maxlength='20' id='group_name' name='group_name' required="required" autofocus="autofocus" class="form-control"/>
                    </div>
                </div>

                <!-- Description -->
                <div class="form-group col-lg-12">
                    <label for='group_description' class="control-label">
                        <?php eT("Description:"); ?>
                    </label>
                    <div class="default controls">
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
