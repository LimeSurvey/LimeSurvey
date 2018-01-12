<?php
/**
 * Display the template carousel
 *
 * @var $templates
 * @var Survey $oSurvey
 * @var $iSurveyId
 */
 $count = 0;

 App()->getClientScript()->registerScriptFile( App()->getConfig('adminscripts') . 'jcarousel.min.js', LSYii_ClientScript::POS_BEGIN);
 App()->getClientScript()->registerScriptFile( App()->getConfig('adminscripts') . 'template.jcarousel.js', LSYii_ClientScript::POS_BEGIN);

?>

<div class="row template-caroussel">
    <div class="col-sm-12" id='carrousel-container'>
        <div class="row">
            <div class="col-sm-12" id="item-container"> <!-- width defined in css -->
                <div class="h4"><?php eT('Select your theme:'); ?></div>

                <?php foreach($templates as $key=>$template):?>
                    <?php if (Permission::model()->hasGlobalPermission('superadmin','read') || Permission::model()->hasGlobalPermission('templates','read') || hasTemplateManageRights(Yii::app()->session["loginID"], $key) == 1 || $oSurvey->template==htmlspecialchars($key) ): ?>
                    <div class="item text-center <?php if($key==$oSurvey->template){echo ' active ';}else{echo ' inactive ';}?>" id="template-big-<?php echo $key;?>">
                        <?php echo $template['preview']; ?>
                            <h3><?php echo $key;?></h3>
                            <?php if($key==$oSurvey->template):?>
                                <button
                                    class="selectTemplate btn btn-default btn-success  disabled"
                                    data-selectedtext="<?php eT("Selected!");?>"
                                    data-unselectedtext="<?php eT('Select');?> &nbsp; <?php echo $key;?>">
                                        <?php eT('Selected!');?>
                                </button>
                            <?php else:?>
                                <button
                                    data-selectedtext="<?php eT("Selected!");?>"
                                    data-unselectedtext="<?php eT('Select');?> &nbsp; <?php echo $key;?>"
                                    data-url="<?php echo Yii::app()->urlManager->createUrl("admin/survey/sa/changetemplate/surveyid/$iSurveyId/template/$key" ); ?>"
                                    data-template="<?php echo $key;?>"
                                    class="selectTemplate btn btn-default">
                                    <?php eT('Select');?>&nbsp;<?php echo $key;?>
                                </button>
                            <?php endif;?>
                    </div>
                <?php endif;?>
                <?php endforeach;?>
            </div>
        </div>
        <div class="row">
            <div class="col-sm-12" id="jcarousel-wrapper-container"> <!-- width defined in css -->
                <div class="jcarousel-wrapper" >
                    <div class="jcarousel">
                        <ul >
                            <?php foreach($templates as $key=>$template):?>
                                <?php if (Permission::model()->hasGlobalPermission('superadmin','read') || Permission::model()->hasGlobalPermission('templates','read') || hasTemplateManageRights(Yii::app()->session["loginID"], $key) == 1 || $oSurvey->template==htmlspecialchars($key) ): ?>
                                <li class="template-miniature <?php if($key==$oSurvey->template){echo ' active';}?>" data-big="#template-big-<?php echo $key;?>">
                                    <?php echo $template['preview']; ?>
                                </li>
                            <?php endif; ?>
                            <?php endforeach;?>
                        </ul>
                    </div>

                    <?php if(count($templates)>4):?>
                        <a href="#" class="jcarousel-control-prev">&lsaquo;</a>
                        <a href="#" class="jcarousel-control-next">&rsaquo;</a>
                    <?php endif;?>
                </div>

            </div>
        </div>
    </div>
  </div>
