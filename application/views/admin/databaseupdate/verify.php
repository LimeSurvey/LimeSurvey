<div class="jumbotron message-box">
    <h2 class=""><?php eT('Database upgrade'); ?></h2>
    <p class="lead"><?php eT('Make sure to make a database backup before proceeding.'); ?></p>
    <p class="lead"><?php eT('Please verify the following information before continuing with the database upgrade:'); ?></p>
    <div class="row">
        <div class="offset-lg-4 col-lg-4">
            <table class="table table-striped">
                <tr><th><?php eT('Database type:'); ?></th><td><?php echo  Yii::app()->db->getDriverName(); ?></td></tr>
                
                <tr><th><?php eT('Database name:'); ?></th><td><?php 
                // MySQL and Postgres use 'dbname', MSSQL uses 'Database' - just display both because only one would show
                echo  getDBConnectionStringProperty('dbname') . getDBConnectionStringProperty('Database')  ; ?></td></tr>
                <tr><th><?php eT('Table prefix:'); ?></th><td><?php echo Yii::app()->db->tablePrefix; ?></td></tr>
                <tr><th><?php eT('Site name:'); ?></th><td><?php echo Yii::app()->getConfig("sitename"); ?></td></tr>
                <tr><th><?php eT('Root URL:'); ?></th><td><?php echo Yii::app()->getController()->createUrl('/'); ?></td></tr>
                <tr><th><?php eT('Current database version:'); ?></th><td><?php echo GetGlobalSetting('DBVersion'); ?></td></tr>
                <tr><th><?php eT('Target database version:'); ?></th><td><?php echo Yii::app()->getConfig('dbversionnumber'); ?></td></tr>
            </table>
        </div>
    </div>

    <?php if ((int)GetGlobalSetting('DBVersion')<132) { ?>
        <?php
        $message = '<strong>' . gT("Error:") . '</strong>' . gT("You will not be able to update because your previous LimeSurvey version is too old.") .
                '<br>' .
                 gT("Please first update to Version 2.6.4 or any later 2.x version before you update to Version 3.x.");
        App()->getController()->widget('ext.AlertWidget.AlertWidget', [
            'text' => $message,
            'type' => 'danger',
        ])
        ?>
    <?php }
    else
    { ?>
        <p>
            <a class="btn btn-lg btn-primary" href="<?php echo Yii::app()->getController()->createUrl("admin/databaseupdate/sa/db/continue/yes"); ?>" role="button">
                <?php eT('Start database upgrade'); ?>
            </a>
        </p>
    <?php } ?>
</div>
