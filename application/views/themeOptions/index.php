<?php
/**
 * @var ThemeOptionsController $this
 * @var CActiveDataProvider $dataProvider
 * @var bool $canImport
 * @var string $importErrorMessage
 * @var object $oQuestionTheme
 * @var object $oSurveyTheme
 */

// TODO: rename to template_list.php and move to template controller

// DO NOT REMOVE This is for automated testing to validate we see that page
echo viewHelper::getViewTestTag('templateOptions');
?>
<div class="container-fluid">
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
                    <?php echo '<h3>' . gT('Installed survey themes:') . '</h3>'; ?>
                    <?php $this->renderPartial(
                        './surveythemelist',
                        ['oSurveyTheme' => $oSurveyTheme, 'pageSize' => $pageSize]
                    ); ?>
                    <!-- Available Themes -->
                    <?php if (count($oSurveyTheme->templatesWithNoDb) > 0) : ?>
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
                                <?php foreach ($oSurveyTheme->templatesWithNoDb as $oTemplate) : ?>
                                    <?php // echo $oTemplate; ?>
                                    <tr class="odd">
                                        <td class="col-lg-1"><?php echo $oTemplate->preview; ?></td>
                                        <td class="col-lg-2"><?php echo $oTemplate->sTemplateName; ?></td>
                                        <td class="col-lg-3"><?php echo $oTemplate->description; ?></td>
                                        <td class="col-lg-2"><?php eT('XML themes'); ?></td>
                                        <td class="col-lg-2"><?php echo $oTemplate->config->metadata->extends; ?></td>
                                        <td class="col-lg-1"><?php echo $oTemplate->buttons; ?></td>
                                    </tr>
                                <?php endforeach; ?>


                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                    <!-- End Available Themes -->
                    <!-- Broken Themes  -->
                    <?php $aBrokenThemes = Template::getBrokenThemes();
                    if (count($aBrokenThemes) > 0) : ?>
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
                                <?php foreach ($aBrokenThemes as $sName => $oBrokenTheme) : ?>
                                    <?php // echo $oTemplate; ?>
                                    <tr class="odd">
                                        <td class="col-lg-1 text-danger"><?php echo $sName; ?></td>
                                        <td class="col-lg-10 ">
                                            <blockquote><?php echo $oBrokenTheme->getMessage(); ?></blockquote>
                                        </td>
                                        <td class="col-lg-1">

                                            <!-- Export -->
                                            <?php if (Permission::model()->hasGlobalPermission('templates',
                                                    'export') && class_exists('ZipArchive')) : ?>
                                                <a class="btn btn-default  btn-block" id="button-export"
                                                   href="<?php echo $this->createUrl('admin/themes/sa/brokentemplatezip/templatename/' . $sName) ?>"
                                                   role="button">
                                                    <span class="icon-export text-success"></span>
                                                    <?php eT("Export"); ?>
                                                </a>
                                            <?php endif; ?>

                                            <!-- Delete -->
                                            <?php if (Permission::model()->hasGlobalPermission('templates', 'delete')) : ?>
                                                <a
                                                    id="button-delete"
                                                    href="<?php echo Yii::app()->getController()->createUrl('admin/themes/sa/deleteBrokenTheme/'); ?>"
                                                    data-post='{ "templatename": "<?php echo $sName; ?>" }'
                                                    data-text="<?php eT('Are you sure you want to delete this theme?'); ?>"
                                                data-button-no="<?= gT('Cancel'); ?>"
                                                data-button-yes="<?= gT('Delete'); ?>"
                                                    title="<?php eT('Delete'); ?>"
                                                    class="btn btn-danger selector--ConfirmModal">
                                                    <span class="fa fa-trash"></span>
                                                    <?php eT('Delete'); ?>
                                                </a>
                                            <?php endif; ?>

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
                                        <td class="col-lg-10"><?php echo $aDeprecatedTheme['name']; ?></td>
                                        <td class="col-lg-2">
                                            <?php if (Permission::model()->hasGlobalPermission('templates',
                                                    'export') && class_exists('ZipArchive')) : ?>
                                                <a class="btn btn-default" id="button-export"
                                                   href="<?php echo $this->createUrl('admin/themes/sa/deprecatedtemplatezip/templatename/' . $aDeprecatedTheme['name']) ?>"
                                                   role="button">
                                                    <span class="icon-export text-success"></span>
                                                    <?php eT("Export"); ?>
                                                </a>
                                            <?php endif; ?>
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
                    <div id="templates_no_db">
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
                            <?php foreach ($oAdminTheme->adminThemeList as $oTheme) : ?>
                                <tr class="odd">
                                    <td class="col-lg-1"><?php echo $oTheme->preview; ?></td>
                                    <td class="col-lg-2"><?php echo $oTheme->metadata->name; ?></td>
                                    <td class="col-lg-3"><?php echo $oTheme->metadata->description; ?></td>
                                    <td class="col-lg-2"><?php eT('Core admin theme'); ?></td>
                                    <td class="col-lg-1">
                                        <?php if ($oTheme->path == getGlobalSetting('admintheme')) : ?>
                                            <h3><strong class="text-info"><?php eT("Selected") ?></strong></h3>
                                        <?php else : ?>
                                            <a href="<?php echo $this->createUrl("themeOptions/setAdminTheme/",
                                                ['sAdminThemeName' => $oTheme->path]); ?>" class="btn btn-default btn-lg ">
                                                <?php eT("Select"); ?>
                                            </a>
                                        <?php endif; ?>
                                    </td>
                                </tr>
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
        if (target === "#questionthemes") {
            $("#uploadandinstall").attr('data-bs-target', '#importQuestionModal');
        }
        if (target === "#surveythemes") {
            $("#uploadandinstall").attr('data-bs-target', '#importSurveyModal');
        }
    });
</script>
