<?php

/**
 * Survey specific permissions of an API token, for one survey
 *
 * @var Survey $survey
 * @var array  $permissions See UserApiToken::getPermissionGrid()
 */

$surveyTitle = sprintf(gT("%s (ID: %s)", 'unescaped'), flattenText($survey->getLocalizedTitle(), false, true), $survey->sid);
?>
<fieldset class="apitoken-survey mb-4" data-surveyid="<?php echo (int) $survey->sid; ?>">
    <legend class="h5"><?php echo CHtml::encode($surveyTitle); ?></legend>
    <div class="mb-2">
        <button type="button" class="btn btn-outline-secondary btn-sm apitoken-survey-remove" aria-label="<?php echo CHtml::encode(sprintf(gT("Remove survey %s from the API token", 'unescaped'), $surveyTitle)); ?>">
            <i class="ri-delete-bin-fill" aria-hidden="true"></i>
            <?php eT("Remove"); ?>
        </button>
    </div>
    <?php $this->renderPartial('/admin/user/_apitokenpermissions', [
        'permissions' => $permissions,
        'namePrefix' => 'ApiTokenPermission[survey][' . (int) $survey->sid . ']',
        'idPrefix' => 'apitoken-survey-' . (int) $survey->sid,
        'caption' => sprintf(gT("Permissions of the API token for survey %s", 'unescaped'), $surveyTitle),
    ]); ?>
</fieldset>
