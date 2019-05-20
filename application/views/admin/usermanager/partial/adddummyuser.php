
<div class="modal-header">
    <?=gT('Adding anonymous users')?>
</div>
<div class="modal-body">
    <?=TbHtml::formTb(null, App()->createUrl('plugins/direct', ['plugin' => 'SMKUserManager', 'function' => 'runadddummyuser']), 'post', ["id"=>"SMKUserManager--modalform"])?>
        <div class="container-center">
            <div class="row ls-space margin top-5">
                <label for="AnonUser_times">How many users</label>
                <input id="AnonUser_times" name="times" class="form-control" type="number" value="5">
            </div>
            <div class="row ls-space margin top-5">
                <label for="AnonUser_prefix">Prefix for the users</label>
                <input id="AnonUser_prefix" name="prefix" class="form-control" type="text" value="randuser_">
            </div>
            <div class="row ls-space margin top-5">
                <label for="AnonUser_email">Email address to use</label>
                <input id="AnonUser_email" name="email" class="form-control" type="text" value="<?=User::model()->findByPk(App()->user->id)->email?>">
            </div>
            <div class="row ls-space margin top-35">
                <button class="btn btn-success col-sm-3 col-xs-5 col-xs-offset-1" id="submitForm"><?=gT('Create')?></button>
                <button id="exitForm" class="btn btn-default  col-sm-3 col-xs-5 col-xs-offset-1"><?=gT('Close')?></button>
            </div>
        </div>
    </form>
</div>