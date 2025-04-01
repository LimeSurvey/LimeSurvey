<?php
/**
 * @var ThemeOptionsController $this
 * @var CActiveDataProvider $dataProvider
 * @var bool $canImport
 * @var string $importErrorMessage
 * @var object $oQuestionTheme
 * @var TemplateConfig $oSurveyTheme
 * @var int $pageSize
 * @var array $aAdminThemes
 * @var array $aTemplatesWithoutDB
 */

// TODO: rename to template_list.php and move to template controller

// DO NOT REMOVE This is for automated testing to validate we see that page
echo viewHelper::getViewTestTag('templateOptions');
?>
<div class="list-themes">
    <ul class="nav nav-tabs" id="themelist">
        <li class="nav-item">
            <a class="nav-link active" href="#surveythemes" data-bs-toggle="tab">
                <?php eT('Survey themes'); ?>
            </a>
        </li>
        <li>
            <a class="nav-link" href="#adminthemes" data-bs-toggle="tab">
                <?php eT('Admin themes'); ?>
            </a>
        </li>
        <li>
            <a class="nav-link" href="#questionthemes" data-bs-toggle="tab">
                <?php eT('Question themes'); ?>
            </a>
        </li>
    </ul>
    <div class="tab-content">
        <div id="surveythemes" class="tab-pane active">
            <div class="list-surveys">
                <h3><?= gT('Installed survey themes:') ?></h3>
                <?php $this->renderPartial('./surveythemelist', [
                        'oSurveyTheme' => $oSurveyTheme,
                        'pageSize'     => $pageSize
                    ]
                ); ?>
                <!-- Available Themes -->
                <?php if (!empty($aTemplatesWithoutDB['valid'])) : ?>
                    <h3><?php eT('Available survey themes:'); ?></h3>
                    <div id="templates_no_db" >
                        <table class="items table table-hover">
                            <thead>
                            <tr>
                                <th><?php eT('Preview'); ?></th>
                                <th><?php eT('Folder'); ?></th>
                                <th><?php eT('Description'); ?></th>
                                <th><?php eT('Type'); ?></th>
                                <th><?php eT('Extends'); ?></th>
                                <th></th>
                            </tr>
                            </thead>

                            <tbody>
                            <?php /** @var TemplateManifest $oTemplate */ ?>
                            <?php $surveyThemeIterator = 0 ?>
                            <?php foreach ($aTemplatesWithoutDB['valid'] as $key => $oTemplate) : ?>
                                <tr class="odd">
                                    <td class="col-lg-1"><?php echo $oTemplate->getPreview(); ?></td>
                                    <td class="col-lg-2"><?php echo CHtml::encode($oTemplate->sTemplateName); ?></td>
                                    <td class="col-lg-3"><?php echo $oTemplate->getDescription(); ?></td>
                                    <td class="col-lg-2"><?php eT('XML themes'); ?></td>
                                    <td class="col-lg-1"><?php echo $oTemplate->config->metadata->extends; ?></td>
                                    <?php if (TemplateConfig::isCompatible($oTemplate->path . 'config.xml')): ?>
                                        <td class="col-lg-2"><?php echo $oTemplate->getButtons(); ?></td>
                                    <?php else: ?>
                                        <td class="col-lg-2">
                                            <div class="d-grid gap-2">
                                                <button type="button" class="btn btn-danger btn-sm" data-bs-toggle="modal"
                                                        data-bs-target="#templates_no_db_error_<?= $surveyThemeIterator ?>">
                                                    <i class="ri-error-warning-fill"></i><?= gT('Show errors') ?>
                                                </button>
                                                <div class="modal fade" id="templates_no_db_error_<?= $surveyThemeIterator ?>" tabindex="-1"
                                                     aria-labelledby="templates_no_db_error_title_<?= $surveyThemeIterator ?>" aria-hidden="true">
                                                    <div class="modal-dialog modal-lg">
                                                        <div class="modal-content">
                                                            <div class="modal-header">
                                                                <h5 class="modal-title" id="templates_no_db_error_title_<?= $surveyThemeIterator ?>"><?= gT('Errors') ?></h5>
                                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                            </div>
                                                            <div class="modal-body">
                                                                <p><?= gT('The theme is not compatible with your version of LimeSurvey.') ?><br>
                                                                    <a href="https://www.limesurvey.org/manual/Extension_compatibility" target="_blank">
                                                                        <?= gT('For more information consult our manual.') ?>
                                                                    </a>
                                                                </p>
                                                                <p><?= gT('Custom theme options set for this theme have been reset to the default.') ?></p>
                                                            </div>
                                                            <div class="modal-footer">
                                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><?= gT('Close') ?></button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <!-- Export -->
                                                <?php if (Permission::model()->hasGlobalPermission('templates', 'export') && class_exists('ZipArchive')) : ?>
                                                    <a class="btn btn-outline-secondary btn-sm" id="button-export"
                                                       href="<?php echo $this->createUrl('admin/themes/sa/templatezip/templatename/' . $oTemplate->sTemplateName) ?>">
                                                        <span class="ri-upload-fill"></span>
                                                        <?php eT("Export"); ?>
                                                    </a>
                                                <?php endif; ?>
                                                <!-- Delete -->
                                                <?php if (Permission::model()->hasGlobalPermission('templates', 'delete')) : ?>
                                                    <a id="template_editor_link_<?= $oTemplate->sTemplateName ?>"
                                                       href="<?php echo Yii::app()->getController()->createUrl('admin/themes/sa/deleteAvailableTheme/') ?>"
                                                       data-post='{ "templatename": "<?= CHtml::encode($oTemplate->sTemplateName) ?>" }'
                                                       data-text="<?php eT('Are you sure you want to delete this theme?'); ?>"
                                                       data-button-no="<?= gT('Cancel'); ?>"
                                                       data-button-yes="<?= gT('Delete'); ?>"
                                                       title="<?php eT('Delete'); ?>"
                                                       class="btn btn-danger btn-sm selector--ConfirmModal">
                                                        <span class="ri-delete-bin-fill"></span>
                                                        <?php eT('Delete'); ?>
                                                    </a>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    <?php endif; ?>
                                </tr>
                                <?php $surveyThemeIterator++ ?>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
                <!-- End Available Themes -->
                <!-- Broken Themes  -->
                <?php if (!empty($aTemplatesWithoutDB['invalid'])) : ?>
                    <h3><?php eT('Broken survey themes'); ?></h3>
                    <div id="thembes_broken" >
                        <table class="items table table-hover">
                            <thead>
                            <tr>
                                <th><?php eT('Name'); ?></th>
                                <th><?php eT('Error message'); ?></th>
                                <th></th>
                            </tr>
                            </thead>

                            <tbody>
                            <?php foreach ($aTemplatesWithoutDB['invalid'] as $sName => $oBrokenTheme) : ?>
                                <tr class="odd">
                                    <td class="col-lg-1 text-danger"><?= $sName ?></td>
                                    <td class="col-lg-8 ">
                                        <blockquote><?= $oBrokenTheme['error'] ?? '' ?></blockquote>
                                    </td>
                                    <td class="col-lg-2">
                                        <div class="d-grid gap-2">
                                            <!-- Export -->
                                            <?php if (Permission::model()->hasGlobalPermission('templates', 'export') && class_exists('ZipArchive')) : ?>
                                                <a class="btn btn-outline-secondary btn-sm" id="button-export"
                                                   href="<?php echo $this->createUrl('admin/themes/sa/brokentemplatezip/templatename/' . $sName) ?>">
                                                    <span class="ri-upload-fill"></span>
                                                    <?php eT("Export"); ?>
                                                </a>
                                            <?php endif; ?>
                                            <!-- Delete -->
                                            <?php if (Permission::model()->hasGlobalPermission('templates', 'delete')) : ?>
                                                <a id="button-delete"
                                                   href="<?php echo Yii::app()->getController()->createUrl('admin/themes/sa/deleteBrokenTheme/'); ?>"
                                                   data-post='{ "templatename": "<?php echo CHtml::encode($sName); ?>" }'
                                                   data-text="<?php eT('Are you sure you want to delete this theme?'); ?>"
                                                   data-button-no="<?= gT('Cancel'); ?>"
                                                   data-button-yes="<?= gT('Delete'); ?>"
                                                   title="<?php eT('Delete'); ?>"
                                                   class="btn btn-danger btn-sm selector--ConfirmModal">
                                                    <span class="ri-delete-bin-fill"></span>
                                                    <?php eT('Delete'); ?>
                                                </a>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
                <!-- End Broken Themes -->
                <!-- Deprecated Themes -->
                <?php $aDeprecatedThemes = Template::getDeprecatedTemplates(); ?>
                <?php if (count($aDeprecatedThemes) > 0) : ?>
                    <h3><?php eT('Deprecated survey themes:'); ?></h3>
                    <div id="deprecatedThemes" >
                        <table class="items table table-hover">
                            <thead>
                            <tr>
                                <th><?php eT('Name'); ?></th>
                                <th><?php eT('Export'); ?></th>
                            </tr>
                            </thead>

                            <tbody>
                            <?php foreach ($aDeprecatedThemes as $aDeprecatedTheme) : ?>
                                <tr class="odd">
                                    <td class="col-lg-9"><?php echo $aDeprecatedTheme['name']; ?></td>
                                    <td class="col-lg-2">
                                        <div class="d-grid gap-2">
                                            <?php if (Permission::model()->hasGlobalPermission('templates', 'export') && class_exists('ZipArchive')) : ?>
                                                <a class="btn btn-outline-secondary btn-sm" id="button-export"
                                                   href="<?php echo $this->createUrl('admin/themes/sa/deprecatedtemplatezip/templatename/' . $aDeprecatedTheme['name']) ?>"
                                                   role="button">
                                                    <span class="ri-upload-fill"></span>
                                                    <?php eT("Export"); ?>
                                                </a>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
                <!-- End Deprecated Themes -->
            </div>
        </div>
        <div id="adminthemes" class="tab-pane">
            <div class="list-surveys">
                <h3><?php eT('Available admin themes:'); ?></h3>
                <div id="admin_themes">
                    <table class="items table table-hover">
                        <thead>
                        <tr>
                            <th><?php eT('Preview'); ?></th>
                            <th><?php eT('Folder'); ?></th>
                            <th><?php eT('Description'); ?></th>
                            <th><?php eT('Type'); ?></th>
                            <th></th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php $adminThemeIterator = 0 ?>
                        <?php foreach ($aAdminThemes as $key => $oTheme) : ?>
                            <tr class="odd">
                                <td class="col-lg-1"><?php echo $oTheme->preview; ?></td>
                                <td class="col-lg-2"><?php echo $oTheme->metadata->name; ?></td>
                                <td class="col-lg-3"><?php echo $oTheme->metadata->description; ?></td>
                                <td class="col-lg-2"><?php eT('Core admin theme'); ?></td>
                                <td class="col-lg-1">
                                    <?php if (TemplateConfig::isCompatible($oTheme->path . 'config.xml')): ?>
                                        <?php if ($oTheme->name === App()->getConfig('admintheme')) : ?>
                                            <h3><strong class="text-info"><?php eT("Selected") ?></strong></h3>
                                        <?php else : ?>
                                            <a href="<?= $this->createUrl("themeOptions/setAdminTheme/", ['sAdminThemeName' => $oTheme->name]) ?>"
                                               class="btn btn-outline-secondary btn-sm">
                                                <?= gT("Select") ?>
                                            </a>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <button type="button" class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#admin_theme_error_<?= $adminThemeIterator ?>">
                                            <i class="ri-error-warning-fill"></i><?= gT('Show errors') ?>
                                        </button>
                                        <div class="modal fade" id="admin_theme_error_<?= $adminThemeIterator ?>" tabindex="-1"
                                             aria-labelledby="#admin_theme_error_title_<?= $adminThemeIterator ?>" aria-hidden="true">
                                            <div class="modal-dialog modal-lg">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title" id="admin_theme_error_title_<?= $adminThemeIterator ?>"><?= gT('Errors') ?></h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <p><?= gT('The theme is not compatible with your version of LimeSurvey.') ?></p>
                                                        <a href="https://www.limesurvey.org/manual/Extension_compatibility" target="_blank">
                                                            <?= gT('For more information consult our manual.') ?>
                                                        </a>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><?= gT('Close') ?></button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php $adminThemeIterator++?>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div id="questionthemes" class="tab-pane">
            <div class="col-12 list-surveys">
                <?php echo '<h3>' . gT('Question themes:') . '</h3>'; ?>
                <!-- Installed Question Themes -->
                <?php $this->renderPartial('./installedthemelist', array('oQuestionTheme' => $oQuestionTheme, 'pageSize' => $pageSize)); ?>
                <!-- Available Quesiton Themes and broken question themes-->
                <?php $this->renderPartial('./availablethemelist', array('oQuestionTheme' => $oQuestionTheme, 'pageSize' => $pageSize)); ?>
            </div>
        </div>
    </div>
</div>

<?php $this->renderPartial(
        './surveythememenu',
        [
            'canImport'=>$canImport,
            'importErrorMessage'=>$importErrorMessage,
            'importModal' => 'importSurveyModal',
            'importTemplate' => 'importSurveyTemplate',
            'themeType' => 'survey'
        ]
); ?>

<?php $this->renderPartial(
        './surveythememenu',
        [
            'canImport' => $canImport,
            'importErrorMessage' => $importErrorMessage,
            'importModal' => 'importQuestionModal',
            'importTemplate' => 'importQuestionTemplate',
            'themeType' => 'question'
        ]
); ?>
</div>
<script>
    $('#themelist a').click(function (e) {
        var target = $(e.target).attr("href");
        $("#uploadandinstall").css('visibility', 'visible');
        if (target === "#questionthemes") {
            $("#uploadandinstall").attr('data-bs-target', '#importQuestionModal');
        }
        if (target === "#surveythemes") {
            $("#uploadandinstall").attr('data-bs-target', '#importSurveyModal');
        }
        if(target === "#adminthemes") { //no upload$install for adminthemes
            $("#uploadandinstall").attr('data-bs-target', '');
            $("#uploadandinstall").css('visibility', 'hidden');
        }
    });
</script>
