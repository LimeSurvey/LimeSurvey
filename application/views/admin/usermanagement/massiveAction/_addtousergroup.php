<?php
    $aUsergoups = UserGroup::model()->findAll();
?>

<div class="modal-body selector--edit-usergroup-container">
    <div class="container-center form">    
        <div class="form-group">
            <label for="addtousergroup"><?=gT("Select usergroup to add users to")?></label>
            <select class="form-control select post-value" name="addtousergroup" id="addtousergroup">
                <?php foreach($aUsergoups as $oUsergroup) {
                    echo "<option value='".$oUsergroup->ugid."'>".$oUsergroup->name."</option>";
                } ?>
            </select>
        </div>
    </div>
</div>