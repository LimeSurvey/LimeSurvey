<div class="jumbotron message-box">
    <h2 class=""><?php eT('Database upgrade'); ?></h2>
    <p class="lead"><?php eT('Please verify the following information before continuing with the database upgrade:'); ?></p>
    <div class="row">
    <div class="col-md-offset-4 col-md-4">
        <table class="table table-striped">
        <tr><th><?php eT('Database type:'); ?></th><td><?php echo  Yii::app()->db->getDriverName(); ?></td></tr>
        <tr><th><?php eT('Database name:'); ?></th><td><?php echo  getDBConnectionStringProperty('dbname'); ?></td></tr>
        <tr><th><?php eT('Table prefix:'); ?></th><td><?php echo Yii::app()->db->tablePrefix; ?></td></tr>
        <tr><th><?php eT('Site name:'); ?></th><td><?php echo Yii::app()->getConfig("sitename"); ?></td></tr>
        <tr><th><?php eT('Root URL:'); ?></th><td><?php echo Yii::app()->getController()->createUrl('/'); ?></td></tr>
        <tr><th><?php eT('Current database version:'); ?></th><td><?php echo GetGlobalSetting('DBVersion'); ?></td></tr>
        <tr><th><?php eT('Target database version:'); ?></th><td><?php echo Yii::app()->getConfig('dbversionnumber'); ?></td></tr>
        </table>
    </div>
    </div>

    <p>
        <a class="btn btn-lg btn-success" href="<?php echo Yii::app()->getController()->createUrl("admin/databaseupdate/sa/db/continue/yes"); ?>" role="button">
            <?php eT('Click here to continue'); ?>
        </a>
    </p>

</div>
