<?php
/* @var $this AdminController */
/* @var $dataProvider CActiveDataProvider */

// DO NOT REMOVE This is for automated testing to validate we see that page
echo viewHelper::getViewTestTag('usersIndex');

?>

<?php if(!Permission::model()->hasGlobalPermission('users', 'read')) :?>
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <h2><?=gT("We are sorry but you don't have permissions to do this.")?></h2>
        </div>
    </div>
</div>
<?php App()->end();?>
<?php endif; ?>


<div class="menubar surveymanagerbar">
    <div class="row container-fluid">
        <div class="col-xs-12 col-md-12">
            <div class="h2"><?php eT("User management panel")?></div>
        </div>
    </div>
</div>
<?php Yii::app()->getController()->renderPartial('/admin/usermanagement/partial/_menubar', ['inImportView' => 1]); ?>
<div class="pagetitle h3"><?php eT("User control");?></div>
<div class="row" style="margin-bottom: 100px">
    <div class="container-fluid">
        <?=TbHtml::formTb(null, App()->createUrl('admin/usermanagement/sa/importfromjson'), 'post', ["id"=>"UserManagement--importjson"])?>
        <div class="row">
            <div class="col-sm-12">
                <textarea rows="10" style="width:100%" name="jsonstring" id="jsonstring"><?=json_encode($result,JSON_PRETTY_PRINT)?></textarea>
            </div>
        </div>
        <div class="row">
            <div class="col-sm-12">
                <div id="jsonContent" style="max-height:25rem;overflow:scroll;white-space:pre;"><?=json_encode($result,JSON_PRETTY_PRINT)?></div>
            </div>
        </div>
        <div class="row">
            <div class="col-sm-12">
                <input type="submit" class="btn btn-default" name="submit" value="Submit and save" />
            </div>
        </div>
        </form>
    </div>
    <script>
        var debouncedKeyup = LS.ld.debounce(function(){
            var jsonString = $("#jsonstring").val();
            var jsonContent = "";
            try{
                jsonContent = JSON.parse(jsonString);
            } catch(e) { console.error('no valid JSON', jsonString); }
            $('#jsonContent').html(JSON.stringify(jsonContent, null, 2));
        }, 1000);

        $("#jsonstring").on('keyup.debounce', debouncedKeyup);
    </script>
</div>