<?php
/* @var $this AdminController */
/* @var $dataProvider CActiveDataProvider */
/* @var bool $canImport */
/* @var string $importErrorMessage */


// TODO: rename to template_list.php and move to template controller

// DO NOT REMOVE This is for automated testing to validate we see that page
echo viewHelper::getViewTestTag('templateOptions');

$this->renderPartial('super/fullpagebar_view', array(
'fullpagebar' => array(
    'returnbutton'=>array(
        'url'=>'index',
        'text'=>gT('Close'),
        ),
    )
));
?>

<div class="ls-space margin left-15 right-15 row list-themes">
    <ul class="nav nav-tabs" id="themelist" role="tablist">
        <li class="active"><a href="#surveythemes"><?php eT('Survey themes'); ?></a></li>
        <li><a href="#adminthemes"><?php eT('Admin themes'); ?></a></li>
        <li><a href="#questionthemes"><?php eT('Question themes'); ?></a></li>
    </ul>
    <div class="tab-content">
        <div id="surveythemes" class="tab-pane active">
            <div class="col-lg-12 list-surveys">

                <?php echo '<h3>'.gT('Installed survey themes:').'</h3>'; ?>

                <?php $this->renderPartial('themeoptions/surveythememenu',['canImport'=>$canImport,'importErrorMessage'=>$importErrorMessage]); ?>
                <?php $this->renderPartial('themeoptions/surveythemelist', array( 'oSurveyTheme'=> $oSurveyTheme, 'pageSize'=>$pageSize )); ?>

                <!-- Available Themes -->
                <?php if (count($oSurveyTheme->templatesWithNoDb) > 0 ):?>
                    <h3><?php eT('Available survey themes:'); ?></h3>
                    <div class="row">
                        <div class="col-sm-12 content-right">

                            <div id="templates_no_db" class="grid-view">
                                <table class="items table">
                                    <thead>
                                        <tr>
                                            <th><?php eT('Preview'); ?></th><th><?php eT('Folder'); ?></th><th><?php eT('Description'); ?></th><th><?php eT('Type'); ?></th><th><?php eT('Extends'); ?></th><th></th>
                                        </tr>
                                    </thead>

                                    <tbody>
                                        <?php foreach ($oSurveyTheme->templatesWithNoDb as $oTemplate):?>
                                            <?php // echo $oTemplate; ?>
                                            <tr class="odd">
                                                <td class="col-md-1"><?php echo $oTemplate->preview; ?></td>
                                                <td class="col-md-2"><?php echo $oTemplate->sTemplateName; ?></td>
                                                <td class="col-md-3"><?php echo $oTemplate->config->metadata->description; ?></td>
                                                <td class="col-md-2"><?php eT('XML themes');?></td>
                                                <td class="col-md-2"><?php echo $oTemplate->config->metadata->extends; ?></td>
                                                <td class="col-md-1"><?php echo $oTemplate->buttons; ?></td>
                                            </tr>
                                        <?php endforeach;?>


                                    </tbody>
                                </table>

                            </div>

                        </div>
                    </div>
                <?php endif;?>

                <!-- Broken Themes  -->
                <?php $aBrokenThemes = Template::getBrokenThemes(); if (count($aBrokenThemes) > 0 ):?>

                    <div class="alert alert-danger" role="alert">
                      <?php eT('Broken survey themes:'); ?>
                    </div>

                    <div class="row" >
                        <div class="col-sm-12 content-right">

                            <div id="thembes_broken" class="grid-view">
                                <table class="items table">
                                    <thead>
                                        <tr>
                                            <th><?php eT('Name'); ?></th><th><?php eT('Error message'); ?></th><th></th>
                                        </tr>
                                    </thead>

                                    <tbody>
                                        <?php foreach ($aBrokenThemes as $sName => $oBrokenTheme):?>
                                            <?php // echo $oTemplate; ?>
                                            <tr class="odd">
                                                <td class="col-md-1 text-danger"><?php echo $sName; ?></td>
                                                <td class="col-md-10 "><blockquote><?php echo $oBrokenTheme->getMessage(); ?></blockquote></td>
                                                <td class="col-md-1">

                                                    <!-- Export -->
                                                    <?php if(Permission::model()->hasGlobalPermission('templates','export') && function_exists("zip_open")):?>
                                                        <a class="btn btn-default  btn-block" id="button-export" href="<?php echo $this->createUrl('admin/themes/sa/brokentemplatezip/templatename/' . $sName) ?>" role="button">
                                                            <span class="icon-export text-success"></span>
                                                            <?php eT("Export"); ?>
                                                        </a>
                                                    <?php endif;?>

                                                    <!-- Delete -->
                                                    <?php if(Permission::model()->hasGlobalPermission('templates','delete')):?>
                                                        <a
                                                            id="button-delete"
                                                            href="<?php echo Yii::app()->getController()->createUrl('admin/themes/sa/deleteBrokenTheme/'); ?>"
                                                            data-post='{ "templatename": "<?php echo $sName; ?>" }'
                                                            data-text="<?php eT('Are you sure you want to delete this theme?'); ?>"
                                                            title="<?php eT('Delete'); ?>"
                                                            class="btn btn-danger selector--ConfirmModal">
                                                                <span class="fa fa-trash "></span>
                                                                <?php eT('Delete'); ?>
                                                        </a>
                                                    <?php endif;?>

                                                </td>
                                            </tr>
                                        <?php endforeach;?>
                                    </tbody>
                                </table>

                            </div>

                        </div>
                    </div>
                <?php endif;?>


                <!-- Broken Themes -->

                <!-- Deprecated Themes -->
                <?php $aDeprecatedThemes =  Template::getDeprecatedTemplates(); ?>
                <?php if (count( $aDeprecatedThemes ) > 0 ):?>
                    <h3><?php eT('Deprecated survey themes:'); ?></h3>
                    <div class="row">
                        <div class="col-sm-12 content-right">
                            <div id="deprecatedThemes" class="grid-view">
                                <table class="items table">
                                    <thead>
                                        <tr>
                                            <th><?php eT('Name'); ?></th>
                                            <th><?php eT('Export'); ?></th>
                                        </tr>
                                    </thead>

                                    <tbody>
                                        <?php foreach ($aDeprecatedThemes as $aDeprecatedTheme):?>
                                            <tr class="odd">
                                                <td class="col-md-10"><?php echo $aDeprecatedTheme['name']; ?></td>
                                                <td class="col-md-2">
                                                    <?php if(Permission::model()->hasGlobalPermission('templates','export') && function_exists("zip_open")):?>
                                                        <a class="btn btn-default" id="button-export" href="<?php echo $this->createUrl('admin/themes/sa/deprecatedtemplatezip/templatename/' . $aDeprecatedTheme['name']) ?>" role="button">
                                                            <span class="icon-export text-success"></span>
                                                            <?php eT("Export"); ?>
                                                        </a>
                                                    <?php endif;?>
                                                </td>
                                            </tr>

                                        <?php endforeach;?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                <?php endif;?>


            </div>
        </div>
        <div id="adminthemes" class="tab-pane">
            <div class="col-lg-12 list-surveys">
                <h3><?php eT('Available admin themes:'); ?></h3>
                <div class="row">
                    <div class="col-sm-12 content-right">
                        <div id="templates_no_db" class="grid-view">
                            <table class="items table">
                                <thead>
                                    <tr>
                                        <th><?php eT('Preview'); ?></th><th><?php eT('Folder'); ?></th><th><?php eT('Description'); ?></th><th><?php eT('Type'); ?></th><th></th>
                                    </tr>
                                </thead>

                                <tbody>
                                    <?php foreach ($oAdminTheme->adminThemeList as $oTheme ):?>
                                        <tr class="odd">
                                            <td class="col-md-1"><?php echo $oTheme->preview; ?></td>
                                            <td class="col-md-2"><?php echo $oTheme->metadata->name; ?></td>
                                            <td class="col-md-3"><?php echo $oTheme->metadata->description; ?></td>
                                            <td class="col-md-2"><?php eT('Core admin theme');?></td>
                                            <td class="col-md-1">
                                                <?php if ($oTheme->path == getGlobalSetting('admintheme')):?>
                                                    <h3><strong class="text-info"><?php eT("Selected")?></strong></h3>
                                                <?php else: ?>
                                                    <a href="<?php echo Yii::app()->getController()->createUrl("admin/themeoptions/sa/setAdminTheme/", ['sAdminThemeName'=>$oTheme->path]);?>" class="btn btn-default btn-lg ">
                                                        <?php eT("Select");?>
                                                    </a>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach;?>
                                </tbody>
                            </table>

                        </div>

                    </div>
                </div>
            </div>
        </div>
        <div id="questionthemes" class="tab-pane">
            <div class="col-lg-12 list-surveys">

                <?php echo '<h3>'.gT('Question themes:').'</h3>'; ?>

                Soon, here, you'll have the list of all question types, and all customed question types. <br>
                You'll be able to manage them like the Survey Themes (inheritance, theme editor, configuration at global/survey group/survey ; show/hide by survey group, etc)
                <br>Engine is already working, the interface will come very soon.
            </div>

        </div>
    </div>
</div>




<script>
    $('#themelist a').click(function (e) {
        window.location.hash = $(this).attr('href');
        e.preventDefault();
        $(this).tab('show');
    });
    $(document).on('ready pjax:scriptcomplete', function(){
        if(window.location.hash){
            $('#themelist').find('a[href='+window.location.hash+']').trigger('click');
        }
    })
</script>
