<?php

/**
 * Create or edit an API token of the current user
 *
 * @var UserApiToken $apiToken
 * @var array        $globalPermissions See UserApiToken::getPermissionGrid()
 * @var array        $surveyPermissions Survey specific permissions, indexed by survey ID, each with 'survey' and 'permissions'
 * @var Survey[]     $surveyOptions     Surveys that can be added to the token
 */

echo viewHelper::getViewTestTag('apitoken');

$lifetimeOptions = [
    30 => gT("30 days"),
    90 => gT("90 days"),
    365 => gT("1 year"),
    0 => gT("No expiration"),
];
?>
<div class="container">
    <?php echo TbHtml::form(
        ['admin/user/sa/apitoken', 'tokenid' => (int) $apiToken->id],
        'post',
        ['id' => 'apitoken-form', 'autocomplete' => 'off']
    ); ?>
    <h2 class="pagetitle h3">
        <?php echo $apiToken->isNewRecord ? gT("Create API token") : gT("Edit API token"); ?>
    </h2>
    <p>
        <?php eT("Scripts and integrations can use an API token instead of your password to log in to RemoteControl or the REST API. Two-factor authentication is not requested for API tokens."); ?>
        <?php eT("The token never has more permissions than you have. Select what the token is allowed to do."); ?>
    </p>

    <div class="row">
        <div class="col-12 col-lg-6 mb-3">
            <label for="apitoken-name" class="form-label"><?php eT("Name:"); ?></label>
            <input type="text" class="form-control" id="apitoken-name" name="name" maxlength="150" required aria-describedby="apitoken-name-help" value="<?php echo CHtml::encode($apiToken->name); ?>" />
            <div id="apitoken-name-help" class="form-text"><?php eT("Helps you to recognize the token, e.g. the name of the script or integration using it."); ?></div>
        </div>
        <div class="col-12 col-lg-6 mb-3">
            <?php if ($apiToken->isNewRecord) : ?>
                <label for="apitoken-lifetime" class="form-label"><?php eT("Expiration:"); ?></label>
                <select class="form-select" id="apitoken-lifetime" name="lifetime">
                    <?php foreach ($lifetimeOptions as $days => $label) : ?>
                        <option value="<?php echo $days; ?>" <?php echo $days === 90 ? 'selected' : ''; ?>><?php echo $label; ?></option>
                    <?php endforeach; ?>
                </select>
            <?php else : ?>
                <p class="form-label mb-1"><?php eT("Expiration:"); ?></p>
                <p><?php echo $apiToken->expires ? CHtml::encode($apiToken->getFormattedDate('expires')) : gT("No expiration"); ?></p>
            <?php endif; ?>
        </div>
    </div>

    <h3 class="h4 mt-3"><?php eT("Global permissions"); ?></h3>
    <p class="form-text"><?php eT("Global permissions apply to the whole installation. The global permission for surveys covers all surveys you have access to."); ?></p>
    <?php $this->renderPartial('/admin/user/_apitokenpermissions', [
        'permissions' => $globalPermissions,
        'namePrefix' => 'ApiTokenPermission[global][0]',
        'idPrefix' => 'apitoken-global',
        'caption' => gT("Global permissions of the API token"),
    ]); ?>

    <h3 class="h4 mt-4"><?php eT("Survey permissions"); ?></h3>
    <p class="form-text"><?php eT("Give the token access to single surveys only, without the global permission for surveys."); ?></p>
    <div id="apitoken-surveys">
        <?php foreach ($surveyPermissions as $surveyData) : ?>
            <?php $this->renderPartial('/admin/user/_apitokensurvey', $surveyData); ?>
        <?php endforeach; ?>
    </div>
    <div class="row align-items-end mb-4">
        <div class="col-12 col-lg-6">
            <label for="apitoken-add-survey" class="form-label"><?php eT("Survey:"); ?></label>
            <select class="form-select" id="apitoken-add-survey">
                <option value=""><?php eT("Please choose..."); ?></option>
                <?php foreach ($surveyOptions as $survey) : ?>
                    <option value="<?php echo (int) $survey->sid; ?>" <?php echo isset($surveyPermissions[$survey->sid]) ? 'disabled' : ''; ?>>
                        <?php echo CHtml::encode(sprintf(gT("%s (ID: %s)", 'unescaped'), flattenText($survey->getLocalizedTitle(), false, true), $survey->sid)); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-12 col-lg-6 mt-2 mt-lg-0">
            <button
                type="button"
                class="btn btn-outline-secondary"
                id="apitoken-add-survey-button"
                data-url="<?php echo CHtml::encode(App()->createUrl('admin/user/sa/apitokensurveypermissions')); ?>"
            >
                <i class="ri-add-line" aria-hidden="true"></i>
                <?php eT("Add survey"); ?>
            </button>
        </div>
    </div>
    <div id="apitoken-surveys-status" class="visually-hidden" aria-live="polite"></div>
    <?php echo TbHtml::submitButton(gT("Save", 'unescaped'), ['class' => 'd-none']); ?>
    <?php echo TbHtml::endForm(); ?>
</div>
<?php
App()->getClientScript()->registerScript(
    'apitokenTexts',
    'window.LS = window.LS || {}; LS.apiTokenTexts = ' . json_encode([
        'surveyAdded' => gT("The survey was added to the API token.", 'unescaped'),
        'surveyRemoved' => gT("The survey was removed from the API token.", 'unescaped'),
        'surveyError' => gT("The survey could not be added.", 'unescaped'),
    ]) . ';',
    CClientScript::POS_HEAD
);
Yii::app()->getClientScript()->registerScriptFile(Yii::app()->getConfig('adminscripts') . 'apitoken.js');
