<?php
/**
 * @var array $permissions
 * @var Survey $survey
 */

$sEditUrl = App()->createUrl("/surveyAdministration/rendersidemenulink/subaction/generalsettings/surveyid/" . $survey->sid);
$sStatUrl = App()->createUrl("/admin/statistics/sa/simpleStatistics/surveyid/" . $survey->sid);
$sAddGroup = App()->createUrl("/questionGroupsAdministration/add/surveyid/" . $survey->sid);
$sAddquestion = App()->createUrl("/questionAdministration/create/surveyid/" . $survey->sid);
?>

<div class="dropdown">
    <button class="btn btn-sm btn-outline-secondary" type="button" id="action1" data-bs-toggle="dropdown" aria-expanded="false">
        ...
    </button>
    <ul class="dropdown-menu" aria-labelledby="action1">
        <li>
            <a class="dropdown-item <?= $survey->active !== "Y" && $permissions['create'] && $survey->groupsCount > 0 ? "" : "disabled" ?>" href="<?= $sAddquestion ?>"
               role="button" data-bs-toggle="tooltip"
               title="<?= gT('Add new question') ?>">
                <i class="ri-add-circle-fill"></i><?= gT('Add new question') ?>
            </a>
            <a class="dropdown-item <?= $survey->active !== "Y" && $permissions['create'] && !($survey->groupsCount > 0) ? "" : "disabled" ?>" href="<?= $sAddGroup ?>"
               role="button" data-bs-toggle="tooltip"
               title="<?= gT('Add new group') ?>">
                <i class="ri-add-circle-fill"></i><?= gT('Add new group') ?>
            </a>
        </li>
        <?php if (Permission::model()->hasSurveyPermission($survey->sid, 'statistics', 'read') && $survey->active === 'Y') : ?>
            <li>
                <a class="dropdown-item" href="<?= $sStatUrl ?>" role="button" data-bs-toggle="tooltip" title="<?= gT('Statistics') ?>">
                    <i class="ri-add-circle-fill"></i><?= gT('Statistics') ?>
                </a>
            </li>
        <?php endif; ?>
        <?php if (Permission::model()->hasSurveyPermission($survey->sid, 'survey', 'update')) : ?>
            <li>
                <a class="dropdown-item" href="<?= $sEditUrl ?>" role="button" data-bs-toggle="tooltip" title="<?= gT('General settings & texts') ?>">
                    <i class="ri-settings-5-fill"></i><?= gT('General settings & texts') ?>
                </a>
            </li>
        <?php endif; ?>
    </ul>
</div>
