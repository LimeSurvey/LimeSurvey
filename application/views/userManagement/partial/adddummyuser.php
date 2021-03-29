
<div class="modal-header">
    <?=gT('Adding anonymous users')?>
</div>
<div class="modal-body">
    <?=TbHtml::formTb(null, App()->createUrl('userManagement/runAddDummyUser'), 'post', ["id"=>"UserManagement--modalform"])?>
        <div class="container-center">
            <div class="row ls-space margin top-5">
                <label for="AnonUser_times"><?=gT('How many users should be created')?></label>
                <input id="AnonUser_times" name="times" class="form-control" type="number" value="1">
            </div>
            <div class="row ls-space margin top-5">
                <label for="AnonUser_passwordsize"><?=gT('The size of the randomly generated password (min. 8)')?></label>
                <input id="AnonUser_passwordsize" name="passwordsize" class="form-control" type="number" min="8" value="8">
            </div>
            <div class="row ls-space margin top-5">
                <label for="AnonUser_prefix"><?=gT("Prefix for the users (a random value will be appended)")?></label>
                <input id="AnonUser_prefix" name="prefix" class="form-control" type="text" value="dummyuser">
            </div>
            <div class="row ls-space margin top-5">
                <label for="AnonUser_email"><?=gT('Email address to use')?></label>
                <input id="AnonUser_email" name="email" class="form-control" type="email" value="<?=User::model()->findByPk(App()->user->id)->email?>">
            </div>
            <div class="row ls-space margin top-35">
                <button class="btn btn-success col-sm-3 col-xs-5 col-xs-offset-1" id="submitForm"><?=gT('Create')?></button>
                <button id="exitForm" class="btn btn-default  col-sm-3 col-xs-5 col-xs-offset-1"><?=gT('Close')?></button>
            </div>
        </div>
    </form>
</div>
