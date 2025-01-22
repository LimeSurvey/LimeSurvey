<?php

/**
 * This view generate the 'overview' tab inside global settings.
 *
 * @var int $usercount
 * @var int $surveycount
 * @var int $activesurveycount
 * @var int $deactivatedsurveys
 * @var int $activetokens
 * @var int $deactivatedtokens
 * @var int TODO: seems to be deprecated see modules/admin/globalsettings/views/_overview.php
 */
?>

<style>
    .nav-tabs {
        border-bottom: 1px solid #ddd;
    }

    .nav-tabs .nav-link {
        color: #555;

        font-weight: bold;
    }

    .nav-tabs .nav-link.active {
        border-bottom: 3px solid #122867;

        border-top: none;
        background: none;

    }

    .tab-content {
        padding: 0px;
        margin: 0px;
    }

    .text-primary {
        color: #122867;
    }
</style>
<br />

<div style="height:100vh">
    <div class="row mb-5">
        <div class="col-3">
            <div class="card card-body mb-3">
                <span class="text-primary fw-bold"> <?php eT("Users"); ?></span>
                <span class="text-primary fw-bolder fs-6"><?php echo $usercount; ?></span>
            </div>
        </div>
        <div class="col-3">
            <div class="card card-body">
                <span class="text-primary fw-bold"> <?php eT("Surveys"); ?></span>
                <span class="text-primary fw-bolder fs-6"><?php echo $surveycount; ?></span>
            </div>
        </div>
        <div class="col-3">
            <div class="card card-body">
                <span class="text-primary fw-bold"> <?php eT("Active surveys"); ?></span>
                <span class="text-primary fw-bolder fs-6"><?php echo $activesurveycount; ?></span>
            </div>
        </div>
        <div class="col-3">
            <div class="card card-body">
                <span class="text-primary fw-bold"> <?php eT("Deactivated result tables"); ?></span>
                <span class="text-primary fw-bolder fs-6"><?php echo $deactivatedsurveys; ?></span>
            </div>
        </div>
        <div class="col-3">
            <div class="card card-body">
                <span class="text-primary fw-bold"> <?php eT("Active survey participants tables"); ?></span>
                <span class="text-primary fw-bolder fs-6"><?php echo $activetokens; ?></span>
            </div>
        </div>
        <div class="col-3">
            <div class="card card-body">
                <span class="text-primary fw-bold"> <?php eT("Deactivated survey participants tables"); ?></span>
                <span class="text-primary fw-bolder fs-6"><?php echo $deactivatedtokens; ?></span>
            </div>
        </div>

    </div>
    <?php
    if (Permission::model()->hasGlobalPermission('superadmin', 'read')) {
    ?>
        <p><a href="<?php echo $this->createUrl('admin/globalsettings', array('sa' => 'showphpinfo')) ?>" target="blank" class="button"><?php eT("Show PHPInfo"); ?></a></p>
    <?php
    }
    ?>
</div>
