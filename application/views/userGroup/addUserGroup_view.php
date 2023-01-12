<div class="col-12 list-surveys">

    <div class="row">
        <div class="col-lg-8 offset-lg-2 content-right">
            <?php echo CHtml::form(array("userGroup/addGroup"), 'post', array('class'=>'', 'id'=>'usergroupform')); ?>

                <!-- Name -->
                <div class="mb-3 col-12">
                    <label for='group_name' class="form-label">
                        <?php eT("Name:"); ?>
                    </label>

                    <div class="default controls">
                        <input type='text' size='50' maxlength='20' id='group_name' name='group_name' required="required" autofocus="autofocus" class="form-control"/>
                    </div>
                </div>

                <!-- Description -->
                <div class="mb-3 col-12">
                    <label for='group_description' class="form-label">
                        <?php eT("Description:"); ?>
                    </label>
                    <div class="default controls">
                        <textarea cols='50' rows='4' id='group_description' name='group_description'  class="form-control"></textarea>
                    </div>
                </div>

                <!-- Buttons -->
                <p>
                    <input type='submit' value='<?php eT("Add group"); ?>' class="d-none" />
                    <input type='hidden' name='action' value='saveusergroup'  />
                </p>
            </form>
        </div>
    </div>
</div>
