<?php
/** @var InstallerController $this */
/** @var InstallerConfigForm $model */
$this->widget('ext.AlertWidget.AlertWidget', [
    'text' => '<strong>' . gT("Database doesn't exist!") . '</strong>',
    'type' => 'warning',
]);
?>
<?php eT("The database you specified does not exist:"); ?>
<br /><br />
<strong><?= $model->dbname; ?></strong><br /><br />
<?php eT("GititSurvey can attempt to create this database for you.")?><br /><br />
