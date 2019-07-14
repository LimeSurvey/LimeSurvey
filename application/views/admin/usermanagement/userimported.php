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
            <h2><?=gT("You don't have permission to enter this page!")?></h2>
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
    <div class="container-fluid ls-space padding left-50 right-50">
        <div class="row">
            <pre>
                <?=json_encode($created)?>
            </pre>
        </div>
    </div>
</div>
