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
    <button class="btn btn-sm btn-outline-secondary ls-dropdown-toggle" data-bs-toggle="dropdown" type="button"
            aria-expanded="false">
        ...
    </button>
    <ul class="dropdown-menu">
        <?php $enabled = $survey->active !== "Y" && $permissions['create'] && $survey->groupsCount > 0 ?>
        <li>
            <a class="dropdown-item <?= $enabled ? "" : "disabled"?>"
               href="<?= $sAddquestion ?>"
               role="button" data-bs-toggle="tooltip"
               title="<?= gT('Add new question') ?>">
                <i class="ri-add-circle-fill"></i><?= gT('Add new question') ?>
            </a>
        </li>
        <?php $enabled = $survey->active !== "Y" && $permissions['create'] && $survey->groupsCount <= 0 ?>
        <li>
            <a class="dropdown-item <?= $enabled ? "" : "disabled"?>"
               href="<?= $sAddGroup ?>"
               role="button" data-bs-toggle="tooltip"
               title="<?= gT('Add new group') ?>">
                <i class="ri-add-circle-fill"></i><?= gT('Add new group') ?>
            </a>
        </li>
        <?php $enabled = Permission::model()->hasSurveyPermission($survey->sid, 'statistics', 'read')
            && $survey->active === 'Y'; ?>
        <li>
            <a class="dropdown-item <?= $enabled ? "" : "disabled" ?>" href="<?= $sStatUrl ?>" role="button"
               data-bs-toggle="tooltip" title="<?= gT('Statistics') ?>">
                <i class="ri-add-circle-fill"></i><?= gT('Statistics') ?>
            </a>
        </li>
        <?php $enabled = Permission::model()->hasSurveyPermission($survey->sid, 'survey', 'update') ?>
        <li>
            <a class="dropdown-item <?= $enabled ? "" : "disabled" ?>" href="<?= $sEditUrl ?>" role="button"
               data-bs-toggle="tooltip" title="<?= gT('General settings & texts') ?>">
                <i class="ri-settings-5-fill"></i><?= gT('General settings & texts') ?>
            </a>
        </li>
    </ul>
</div>
<script>
    var dropdownElementList = [].slice.call(document.querySelectorAll('.ls-dropdown-toggle'))
    var dropdownList = dropdownElementList.map(function (dropdownToggleEl) {
        return new bootstrap.Dropdown(dropdownToggleEl, {
            boundary: document.querySelector('body'),
            popperConfig: function (defaultBsPopperConfig) {
                return {defaultBsPopperConfig, strategy: "fixed"};
            }
        })
    })
</script>
