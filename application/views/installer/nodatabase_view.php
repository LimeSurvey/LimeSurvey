<?php
/** @var InstallerController $this */
/** @var InstallerConfigForm $model */
?>
<div class='alert alert-warning'>
    <strong><?php eT("Database doesn't exist!"); ?></strong>
</div>
<?php eT("The database you specified does not exist:"); ?>
<br /><br />
<strong><?= $model->dbname; ?></strong><br /><br />
<?php eT("LimeSurvey can attempt to create this database for you.")?><br /><br />
